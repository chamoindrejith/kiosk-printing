<?php

namespace App\Http\Controllers\Kiosk;

use App\Jobs\ProcessPrintJob;
use App\Domain\Pricing\PricingService;
use App\Domain\Pdf\PdfService;
use App\Models\PrintJob;
use App\Models\Printer;
use App\Models\Payment;
use App\Services\Payments\MockPaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class KioskController
{
    private PdfService $pdfService;
    private PricingService $pricingService;
    private MockPaymentGateway $paymentGateway;

    public function __construct()
    {
        $this->pdfService = new PdfService();
        $this->pricingService = new PricingService();
        $this->paymentGateway = app(MockPaymentGateway::class);
    }

    public function landing(string $printerCode)
    {
        $printer = Printer::where('code', $printerCode)->where('is_active', true)->firstOrFail();
        $jobId = Session::get("print_job_{$printerCode}");
        $existingJob = null;
        
        if ($jobId) {
            $existingJob = PrintJob::find($jobId);
        }
        
        return view('kiosk.landing', [
            'printer' => $printer,
            'printerCode' => $printerCode,
            'existingJob' => $existingJob,
        ]);
    }

    public function upload(Request $request, string $printerCode)
    {
        $printer = Printer::where('code', $printerCode)->where('is_active', true)->firstOrFail();

        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:51200',
        ]);

        $uploadedPdf = $request->file('pdf');
        
        $validation = $this->pdfService->validate($uploadedPdf);
        
        if (!$validation['valid']) {
            return back()->withErrors($validation['errors'])->withInput();
        }

        $filePath = $this->pdfService->store($uploadedPdf, $printerCode);
        $pageCount = $this->pdfService->getPageCount($filePath);
        
        $printJob = PrintJob::create([
            'printer_id' => $printer->id,
            'status' => 'draft',
            'original_filename' => $uploadedPdf->getClientOriginalName(),
            'file_path' => $filePath,
            'original_page_count' => $pageCount,
        ]);

        Session::put("print_job_{$printerCode}", $printJob->id);

        return redirect()->route('kiosk.options', ['printerCode' => $printerCode]);
    }

    public function options(string $printerCode)
    {
        $printer = Printer::where('code', $printerCode)->where('is_active', true)->firstOrFail();
        $jobId = Session::get("print_job_{$printerCode}");
        
        if (!$jobId) {
            return redirect()->route('kiosk.landing', ['printerCode' => $printerCode]);
        }

        $printJob = PrintJob::with('printer')->findOrFail($jobId);

        return view('kiosk.options', [
            'printer' => $printer,
            'printJob' => $printJob,
            'printerCode' => $printerCode,
        ]);
    }

    public function configure(Request $request, string $printerCode)
    {
        $printer = Printer::where('code', $printerCode)->where('is_active', true)->firstOrFail();
        $jobId = Session::get("print_job_{$printerCode}");
        
        if (!$jobId) {
            return redirect()->route('kiosk.landing', ['printerCode' => $printerCode]);
        }

        $printJob = PrintJob::findOrFail($jobId);

        $request->validate([
            'color' => 'required|boolean',
            'duplex' => 'required|boolean',
            'copies' => 'required|integer|min:1|max:10',
            'paper_size' => 'required|string|in:A4,A5,Letter,Legal',
            'page_range' => 'nullable|string',
        ]);

        $printJob->update([
            'status' => 'configured',
            'color' => $request->boolean('color'),
            'duplex' => $request->boolean('duplex'),
            'copies' => $request->integer('copies'),
            'paper_size' => $request->input('paper_size'),
            'page_range' => $request->input('page_range'),
        ]);

        return redirect()->route('kiosk.price', ['printerCode' => $printerCode]);
    }

    public function price(string $printerCode)
    {
        $printer = Printer::where('code', $printerCode)->where('is_active', true)->firstOrFail();
        $jobId = Session::get("print_job_{$printerCode}");
        
        if (!$jobId) {
            return redirect()->route('kiosk.landing', ['printerCode' => $printerCode]);
        }

        $printJob = PrintJob::with('printer')->findOrFail($jobId);

        $pricing = $this->pricingService->calculateWithRange(
            $printJob->original_page_count,
            $printJob->page_range,
            $printJob->paper_size,
            $printJob->color,
            $printJob->duplex,
            $printJob->copies,
            $printer->id
        );

        $printJob->update([
            'effective_page_count' => $pricing['effective_pages'],
            'sheet_count' => $pricing['sheet_count'],
            'total_price' => $pricing['total_price'],
            'status' => 'awaiting_payment',
        ]);

        return view('kiosk.price', [
            'printer' => $printer,
            'printJob' => $printJob,
            'pricing' => $pricing,
            'printerCode' => $printerCode,
        ]);
    }

    public function initiatePayment(Request $request, string $printerCode)
    {
        $printer = Printer::where('code', $printerCode)->where('is_active', true)->firstOrFail();
        $jobId = Session::get("print_job_{$printerCode}");
        
        if (!$jobId) {
            return redirect()->route('kiosk.landing', ['printerCode' => $printerCode]);
        }

        $printJob = PrintJob::findOrFail($jobId);

        $payment = Payment::create([
            'print_job_id' => $printJob->id,
            'gateway' => 'mock',
            'reference' => 'PAY-' . Str::upper(Str::random(8)),
            'amount' => $printJob->total_price,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(15),
        ]);

        $mockPayment = $this->paymentGateway->createPayment([
            'amount' => $printJob->total_price,
            'reference' => $payment->reference,
        ]);

        $payment->update([
            'gateway_payment_id' => $mockPayment['id'],
            'qr_code' => $mockPayment['qr_code'],
        ]);

        $printJob->update([
            'payment_id' => $payment->id,
            'status' => 'payment_pending',
        ]);

        return redirect()->route('kiosk.payment.show', ['printerCode' => $printerCode]);
    }

    public function payment(string $printerCode)
    {
        $printer = Printer::where('code', $printerCode)->where('is_active', true)->firstOrFail();
        $jobId = Session::get("print_job_{$printerCode}");
        
        if (!$jobId) {
            return redirect()->route('kiosk.landing', ['printerCode' => $printerCode]);
        }

        $printJob = PrintJob::with(['payment', 'printer'])->findOrFail($jobId);
        $payment = $printJob->payment;

        if (!$payment || $payment->status !== 'pending') {
            return redirect()->route('kiosk.price', ['printerCode' => $printerCode]);
        }

        return view('kiosk.payment', [
            'printer' => $printer,
            'printJob' => $printJob,
            'payment' => $payment,
            'printerCode' => $printerCode,
        ]);
    }

    public function checkPayment(Request $request, string $printerCode)
    {
        $jobId = Session::get("print_job_{$printerCode}");
        
        if (!$jobId) {
            return response()->json(['status' => 'error', 'message' => 'No session']);
        }

        $printJob = PrintJob::with('payment')->findOrFail($jobId);
        $payment = $printJob->payment;

        if (!$payment) {
            return response()->json(['status' => 'error', 'message' => 'No payment']);
        }

        $result = $this->paymentGateway->verifyPayment($payment->gateway_payment_id);

        if ($result['valid']) {
            $payment->markAsSuccessful();
            $printJob->update(['status' => 'payment_success']);

            return response()->json([
                'status' => 'success',
                'redirect' => route('kiosk.confirm.form', ['printerCode' => $printerCode]),
            ]);
        }

        if ($payment->isExpired()) {
            $payment->markAsFailed('Payment expired');
            $printJob->update(['status' => 'expired']);

            return response()->json(['status' => 'expired']);
        }

        return response()->json(['status' => 'pending']);
    }

    public function confirmForm(string $printerCode)
    {
        $printer = Printer::where('code', $printerCode)->where('is_active', true)->firstOrFail();
        $jobId = Session::get("print_job_{$printerCode}");
        
        if (!$jobId) {
            return redirect()->route('kiosk.landing', ['printerCode' => $printerCode]);
        }

        $printJob = PrintJob::with(['payment', 'printer'])->findOrFail($jobId);

        if ($printJob->status !== 'payment_success') {
            return redirect()->route('kiosk.landing', ['printerCode' => $printerCode]);
        }

        return view('kiosk.confirm', [
            'printer' => $printer,
            'printJob' => $printJob,
            'printerCode' => $printerCode,
        ]);
    }

    public function confirmPrint(Request $request, string $printerCode)
    {
        $jobId = Session::get("print_job_{$printerCode}");
        
        if (!$jobId) {
            return redirect()->route('kiosk.landing', ['printerCode' => $printerCode]);
        }

        $printJob = PrintJob::with(['payment', 'printer'])->findOrFail($jobId);

        if ($printJob->status !== 'payment_success') {
            return redirect()->route('kiosk.landing', ['printerCode' => $printerCode]);
        }

        $printJob->update(['status' => 'dispatching']);

        $this->createPageRecords($printJob);

        ProcessPrintJob::dispatchSync($printJob->id);

        return redirect()->route('kiosk.status', ['printerCode' => $printerCode, 'jobId' => $printJob->id]);
    }

    public function status(string $printerCode, int $jobId)
    {
        $printer = Printer::where('code', $printerCode)->where('is_active', true)->firstOrFail();
        $printJob = PrintJob::with(['printer', 'pages'])->findOrFail($jobId);

        return view('kiosk.status', [
            'printer' => $printer,
            'printJob' => $printJob,
            'printerCode' => $printerCode,
        ]);
    }

    public function mockPay(string $printerCode, string $paymentId)
    {
        $this->paymentGateway->simulateSuccess($paymentId);

        $payment = Payment::where('gateway_payment_id', $paymentId)->first();
        
        if ($payment) {
            $payment->markAsSuccessful();
            $printJob = $payment->printJob;
            if ($printJob) {
                $printJob->update(['status' => 'payment_success']);
                return redirect()->route('kiosk.confirm.form', ['printerCode' => $printerCode]);
            }
        }

        return redirect()->route('kiosk.landing', ['printerCode' => $printerCode]);
    }

    private function createPageRecords(PrintJob $printJob): void
    {
        $pricing = $this->pricingService->calculateWithRange(
            $printJob->original_page_count,
            $printJob->page_range,
            $printJob->paper_size,
            $printJob->color,
            $printJob->duplex,
            $printJob->copies
        );

        $pageNumbers = $pricing['effective_page_numbers'] ?? range(1, $printJob->original_page_count);
        $sequence = 1;

        foreach ($pageNumbers as $pageNum) {
            for ($copy = 1; $copy <= $printJob->copies; $copy++) {
                $printJob->pages()->create([
                    'page_number' => $pageNum,
                    'copy_number' => $copy,
                    'sequence_order' => $sequence++,
                    'status' => 'pending',
                ]);
            }
        }
    }

    public function cancelJob(\Illuminate\Http\Request $request, string $printerCode)
    {
        $jobId = Session::get("print_job_{$printerCode}");
        
        if ($jobId) {
            $printJob = PrintJob::find($jobId);
            if ($printJob && in_array($printJob->status, ['queued', 'dispatching', 'printing', 'paused'])) {
                $printJob->update(['status' => 'cancelled']);
                $printJob->recordEvent('cancelled_by_user');
            }
        }
        
        return redirect()->route('kiosk.landing', ['printerCode' => $printerCode]);
    }

    public function removeUpload(string $printerCode)
    {
        $jobId = Session::get("print_job_{$printerCode}");
        
        if ($jobId) {
            $printJob = PrintJob::find($jobId);
            if ($printJob) {
                if ($printJob->file_path && file_exists(storage_path("app/{$printJob->file_path}"))) {
                    @unlink(storage_path("app/{$printJob->file_path}"));
                }
                $printJob->delete();
            }
            Session::forget("print_job_{$printerCode}");
        }
        
        return redirect()->route('kiosk.landing', ['printerCode' => $printerCode]);
    }
}