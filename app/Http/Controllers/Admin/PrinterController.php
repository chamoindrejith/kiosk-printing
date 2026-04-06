<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use App\Services\Printers\PrinterCupsQueueManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PrinterController extends Controller
{
    public function index(): View
    {
        $printers = Printer::orderBy('name')->get();
        return view('admin.printers.index', ['printers' => $printers]);
    }

    public function create(): View
    {
        return view('admin.printers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:printers,code|max:50',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'ip_address' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'protocol' => 'nullable|string|in:ipp,raw,http',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['port'] = $validated['port'] ?? 631;
        $validated['protocol'] = $validated['protocol'] ?? 'ipp';

        $printer = Printer::create($validated);

        if ($printer->ip_address) {
            try {
                app(PrinterCupsQueueManager::class)->sync($printer);
            } catch (\Throwable $e) {
                Log::warning('Failed to auto-create CUPS queue for printer', [
                    'printer_id' => $printer->id,
                    'printer_code' => $printer->code,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('admin.printers.index')->with('success', 'Printer created');
    }

    public function edit(Printer $printer): View
    {
        return view('admin.printers.edit', ['printer' => $printer]);
    }

    public function update(Request $request, Printer $printer): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:printers,code,' . $printer->id . '|max:50',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'protocol' => 'nullable|string|in:ipp,raw,http',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['port'] = $validated['port'] ?? 631;
        $validated['protocol'] = $validated['protocol'] ?? 'ipp';

        $printer->update($validated);

        if ($printer->ip_address) {
            try {
                app(PrinterCupsQueueManager::class)->sync($printer->fresh());
            } catch (\Throwable $e) {
                Log::warning('Failed to auto-sync CUPS queue for printer', [
                    'printer_id' => $printer->id,
                    'printer_code' => $printer->code,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('admin.printers.index')->with('success', 'Printer updated');
    }

    public function destroy(Printer $printer): RedirectResponse
    {
        $printer->delete();
        return redirect()->route('admin.printers.index')->with('success', 'Printer deleted');
    }
}