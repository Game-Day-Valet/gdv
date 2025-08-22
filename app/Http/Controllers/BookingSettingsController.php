<?php

namespace App\Http\Controllers;

use App\Models\BookingOption;
use Illuminate\Http\Request;

class BookingSettingsController extends Controller
{
    public function index()
    {
        $options = BookingOption::orderBy('type')->orderByRaw('COALESCE(sort_order, 999999) asc')->get();
        return view('booking_settings.index', compact('options'));
    }

    public function create()
    {
        return view('booking_settings.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:insurance,damage_waiver',
            'label' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'enabled' => 'nullable|boolean',
        ]);
        $data['enabled'] = $request->boolean('enabled');
        BookingOption::create($data);
        return redirect()->route('booking-settings.index')->with('success', 'Option created successfully.');
    }

    public function edit($id)
    {
        $option = BookingOption::findOrFail($id);
        return view('booking_settings.edit', compact('option'));
    }

    public function update(Request $request, $id)
    {
        $option = BookingOption::findOrFail($id);
        $data = $request->validate([
            'type' => 'required|in:insurance,damage_waiver',
            'label' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'enabled' => 'nullable|boolean',
        ]);
        $data['enabled'] = $request->boolean('enabled');
        $option->update($data);
        return redirect()->route('booking-settings.index')->with('success', 'Option updated successfully.');
    }

    public function destroy($id)
    {
        $option = BookingOption::findOrFail($id);
        $option->delete();
        return redirect()->route('booking-settings.index')->with('success', 'Option deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer|exists:booking_options,id',
            'orders.*.sort_order' => 'required|integer|min:0',
        ]);
        \DB::transaction(function() use ($data) {
            foreach ($data['orders'] as $o) {
                BookingOption::where('id', $o['id'])->update(['sort_order' => (int) $o['sort_order']]);
            }
        });
        return response()->json(['success' => true]);
    }
} 