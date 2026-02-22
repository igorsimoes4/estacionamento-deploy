<?php

namespace App\Http\Controllers;

use App\Models\DynamicPricingRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DynamicPricingRuleController extends Controller
{
    public function index(): View
    {
        $rules = DynamicPricingRule::query()->orderBy('priority')->paginate(20);

        return view('dynamic_pricing.index', compact('rules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'vehicle_type' => ['nullable', 'in:carro,moto,caminhonete'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
            'occupancy_from' => ['required', 'integer', 'min:0', 'max:100'],
            'occupancy_to' => ['required', 'integer', 'min:0', 'max:100'],
            'multiplier' => ['required', 'numeric', 'min:0.1', 'max:10'],
            'flat_addition' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DynamicPricingRule::query()->create([
            'name' => $payload['name'],
            'vehicle_type' => $payload['vehicle_type'] ?? null,
            'day_of_week' => $payload['day_of_week'] ?? null,
            'starts_at' => $payload['starts_at'] ?? null,
            'ends_at' => $payload['ends_at'] ?? null,
            'occupancy_from' => (int) $payload['occupancy_from'],
            'occupancy_to' => (int) $payload['occupancy_to'],
            'multiplier' => (float) $payload['multiplier'],
            'flat_addition_cents' => (int) round(((float) ($payload['flat_addition'] ?? 0)) * 100),
            'priority' => (int) ($payload['priority'] ?? 100),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('create', 'Regra dinâmica cadastrada.');
    }

    public function update(Request $request, DynamicPricingRule $dynamicPricingRule): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'vehicle_type' => ['nullable', 'in:carro,moto,caminhonete'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i'],
            'occupancy_from' => ['required', 'integer', 'min:0', 'max:100'],
            'occupancy_to' => ['required', 'integer', 'min:0', 'max:100'],
            'multiplier' => ['required', 'numeric', 'min:0.1', 'max:10'],
            'flat_addition' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $dynamicPricingRule->fill([
            'name' => $payload['name'],
            'vehicle_type' => $payload['vehicle_type'] ?? null,
            'day_of_week' => $payload['day_of_week'] ?? null,
            'starts_at' => $payload['starts_at'] ?? null,
            'ends_at' => $payload['ends_at'] ?? null,
            'occupancy_from' => (int) $payload['occupancy_from'],
            'occupancy_to' => (int) $payload['occupancy_to'],
            'multiplier' => (float) $payload['multiplier'],
            'flat_addition_cents' => (int) round(((float) ($payload['flat_addition'] ?? 0)) * 100),
            'priority' => (int) ($payload['priority'] ?? 100),
            'is_active' => $request->boolean('is_active', false),
        ])->save();

        return back()->with('create', 'Regra dinâmica atualizada.');
    }

    public function destroy(DynamicPricingRule $dynamicPricingRule): RedirectResponse
    {
        $dynamicPricingRule->delete();

        return back()->with('create', 'Regra dinâmica removida.');
    }
}
