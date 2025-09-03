<?php

namespace App\Http\Controllers;

use App\Models\BookingOption;
use Illuminate\Http\Request;

class BookingSettingsController extends Controller
{
    public function index()
    {
        $options = BookingOption::whereIn('type', ['insurance','damage_waiver'])
            ->orderBy('type')
            ->orderByRaw('COALESCE(sort_order, 999999) asc')
            ->get();
        $emailContent = BookingOption::where('type', 'email_content')->value('description');
        $chatInitial = BookingOption::where('type', 'chat_initial_message')->value('description');
        $smsBooking = BookingOption::where('type', 'sms_booking_confirmation')->value('description');
        $smsConfirmed = BookingOption::where('type', 'sms_status_confirmed')->value('description');
        $smsOutForDelivery = BookingOption::where('type', 'sms_status_out_for_delivery')->value('description');
        $smsDelivered = BookingOption::where('type', 'sms_status_delivered')->value('description');
        $smsCancelled = BookingOption::where('type', 'sms_status_cancelled')->value('description');
        return view('booking_settings.index', compact('options', 'emailContent', 'chatInitial', 'smsBooking', 'smsConfirmed', 'smsOutForDelivery', 'smsDelivered', 'smsCancelled'));
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

    public function saveEmailContent(Request $request)
    {
        $data = $request->validate([
            'email_content' => 'required|string',
        ]);

        BookingOption::updateOrCreate(
            ['type' => 'email_content'],
            [
                'label' => 'Rental Booking Email Content',
                'description' => $data['email_content'],
                'price' => 0,
                'enabled' => true,
            ]
        );

        return redirect()->route('booking-settings.index')->with('success', 'Booking confirmation email content updated.');
    }

    public function saveChatInitialMessage(Request $request)
    {
        $data = $request->validate([
            'chat_initial_message' => 'required|string',
        ]);

        BookingOption::updateOrCreate(
            ['type' => 'chat_initial_message'],
            [
                'label' => 'Chat Initial Message',
                'description' => $data['chat_initial_message'],
                'price' => 0,
                'enabled' => true,
            ]
        );

        return redirect()->route('booking-settings.index')->with('success', 'Chat initial message updated.');
    }

    public function saveSmsTemplates(Request $request)
    {
        $data = $request->validate([
            'sms_booking_confirmation' => 'nullable|string',
            'sms_status_confirmed' => 'nullable|string',
            'sms_status_out_for_delivery' => 'nullable|string',
            'sms_status_delivered' => 'nullable|string',
            'sms_status_cancelled' => 'nullable|string',
        ]);

        $map = [
            'sms_booking_confirmation' => 'Booking Confirmation SMS',
            'sms_status_confirmed' => 'Status Confirmed SMS',
            'sms_status_out_for_delivery' => 'Status Out For Delivery SMS',
            'sms_status_delivered' => 'Status Delivered SMS',
            'sms_status_cancelled' => 'Status Cancelled SMS',
        ];

        foreach ($map as $type => $label) {
            if (array_key_exists($type, $data)) {
                BookingOption::updateOrCreate(
                    ['type' => $type],
                    [
                        'label' => $label,
                        'description' => $data[$type] ?? null,
                        'price' => 0,
                        'enabled' => true,
                    ]
                );
            }
        }

        return redirect()->route('booking-settings.index')->with('success', 'Twilio SMS templates updated.');
    }
} 