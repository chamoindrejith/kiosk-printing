<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingRule;
use App\Models\Printer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        $pricingRules = PricingRule::with('printer')->orderByDesc('created_at')->get();
        return view('admin.pricing.index', ['pricingRules' => $pricingRules]);
    }

    public function create(): View
    {
        $printers = Printer::orderBy('name')->get();
        return view('admin.pricing.create', ['printers' => $printers]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'paper_size' => 'required|string|in:A4,A5,Letter,Legal',
            'color_mode' => 'required|string|in:color,bw',
            'duplex_mode' => 'required|string|in:simplex,duplex',
            'price_per_sheet' => 'required|numeric|min:0',
            'printer_id' => 'nullable|exists:printers,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        PricingRule::create($validated);

        return redirect()->route('admin.pricing.index')->with('success', 'Pricing rule created');
    }

    public function edit(PricingRule $pricing): View
    {
        $printers = Printer::orderBy('name')->get();
        return view('admin.pricing.edit', ['pricingRule' => $pricing, 'printers' => $printers]);
    }

    public function update(Request $request, PricingRule $pricing): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'paper_size' => 'required|string|in:A4,A5,Letter,Legal',
            'color_mode' => 'required|string|in:color,bw',
            'duplex_mode' => 'required|string|in:simplex,duplex',
            'price_per_sheet' => 'required|numeric|min:0',
            'printer_id' => 'nullable|exists:printers,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $pricing->update($validated);

        return redirect()->route('admin.pricing.index')->with('success', 'Pricing rule updated');
    }

    public function destroy(PricingRule $pricing): RedirectResponse
    {
        $pricing->delete();
        return redirect()->route('admin.pricing.index')->with('success', 'Pricing rule deleted');
    }
}