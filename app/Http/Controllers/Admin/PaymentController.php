<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::with(['printJob', 'printJob.printer']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $payments = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.payments.index', ['payments' => $payments]);
    }

    public function show(Payment $payment): View
    {
        $payment->load(['printJob', 'printJob.printer', 'events']);
        return view('admin.payments.show', ['payment' => $payment]);
    }
}