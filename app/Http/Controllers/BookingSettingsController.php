<?php

namespace App\Http\Controllers;

use App\Models\BookingOption;
use Illuminate\Http\Request;
use App\Models\SettingNotification;
use Illuminate\Support\Facades\DB;

class BookingSettingsController extends Controller
{
    public function index()
    {
        $options = BookingOption::whereIn('type', ['insurance', 'damage_waiver'])
            ->orderBy('type')
            ->orderByRaw('COALESCE(sort_order, 999999) asc')
            ->get();

        $emailContent = BookingOption::where('type', 'email_content')->value('description');
        $emailContentHtml = BookingOption::where('type', 'email_content_html')->value('description');
        $chatInitial = BookingOption::where('type', 'chat_initial_message')->value('description');
        $smsBooking = BookingOption::where('type', 'sms_booking_confirmation')->value('description');
        $smsConfirmed = BookingOption::where('type', 'sms_status_confirmed')->value('description');
        $smsOutForDelivery = BookingOption::where('type', 'sms_status_out_for_delivery')->value('description');
        $smsDelivered = BookingOption::where('type', 'sms_status_delivered')->value('description');
        $smsCancelled = BookingOption::where('type', 'sms_status_cancelled')->value('description');
        $emailPreEndReminder = BookingOption::where('type', 'email_pre_end_reminder')->value('description');
        $smsPreEndReminder = BookingOption::where('type', 'sms_pre_end_reminder')->value('description');
        $emailEndDayMorning = BookingOption::where('type', 'email_end_day_morning')->value('description');
        $smsEndDayMorning = BookingOption::where('type', 'sms_end_day_morning')->value('description');

        // Email status templates (rich text) - new keys
        $emailStatusConfirmedHtml = BookingOption::where('type', 'email_status_confirmed_html')->value('description');
        $emailStatusDeliveredHtml = BookingOption::where('type', 'email_status_delivered_html')->value('description');
        $emailStatusCancelledHtml = BookingOption::where('type', 'email_status_cancelled_html')->value('description');
        $notif = SettingNotification::current();
        return view('booking_settings.index', compact('options', 'emailContent', 'emailContentHtml', 'chatInitial', 'smsBooking', 'smsConfirmed', 'smsOutForDelivery', 'smsDelivered', 'smsCancelled', 'notif', 'emailPreEndReminder', 'smsPreEndReminder', 'emailEndDayMorning', 'smsEndDayMorning', 'emailStatusConfirmedHtml', 'emailStatusDeliveredHtml', 'emailStatusCancelledHtml'));
    }

    public function saveNotifications(Request $request)
    {
        $data = $request->validate([
            'email_enabled' => 'required|boolean',
            'sms_enabled' => 'required|boolean',
            'fcm_enabled' => 'required|boolean',
        ]);
        $row = SettingNotification::query()->orderBy('id', 'asc')->first();
        if (!$row) {
            $row = new SettingNotification();
        }
        $row->fill($data);
        $row->save();
        $row->refreshCache();
        return response()->json(['success' => true]);
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
        DB::transaction(function () use ($data) {
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
            'email_content_html' => 'required|string',
        ]);

        BookingOption::updateOrCreate(
            ['type' => 'email_content'],
            [
                'label' => 'Rental Booking SMS Content',
                'description' => $data['email_content'],
                'price' => 0,
                'enabled' => true,
            ]
        );

        BookingOption::updateOrCreate(
            ['type' => 'email_content_html'],
            [
                'label' => 'Rental Booking Email Content (HTML)',
                'description' => $data['email_content_html'],
                'price' => 0,
                'enabled' => true,
            ]
        );

        return redirect()->route('booking-settings.index')->with('success', 'Booking confirmation templates updated.');
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
            // Email rich-text templates (HTML)
            'email_status_confirmed_html' => 'nullable|string',
            'email_status_delivered_html' => 'nullable|string',
            'email_status_cancelled_html' => 'nullable|string',
        ]);

        $map = [
            'sms_booking_confirmation' => 'Booking Confirmation SMS',
            'sms_status_confirmed' => 'Status Confirmed SMS',
            'sms_status_out_for_delivery' => 'Status Out For Delivery SMS',
            'sms_status_delivered' => 'Status Delivered SMS',
            'sms_status_cancelled' => 'Status Cancelled SMS',
            // Email rich text
            'email_status_confirmed_html' => 'Email Status Confirmed (HTML)',
            'email_status_delivered_html' => 'Email Status Delivered (HTML)',
            'email_status_cancelled_html' => 'Email Status Cancelled (HTML)',
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

    public function savePreEndReminders(Request $request)
    {
        $data = $request->validate([
            'email_pre_end_reminder' => 'nullable|string',
            'sms_pre_end_reminder' => 'nullable|string',
        ]);

        $map = [
            'email_pre_end_reminder' => 'Email: Pre-End Reminder',
            'sms_pre_end_reminder' => 'SMS: Pre-End Reminder',
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

        return redirect()->route('booking-settings.index')->with('success', 'Pre-end reminder templates saved.');
    }

    public function saveEndDayMorning(Request $request)
    {
        $data = $request->validate([
            'email_end_day_morning' => 'nullable|string',
            'sms_end_day_morning' => 'nullable|string',
        ]);

        $map = [
            'email_end_day_morning' => 'Email: End-Day Morning Reminder',
            'sms_end_day_morning' => 'SMS: End-Day Morning Reminder',
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

        return redirect()->route('booking-settings.index')->with('success', 'End-day morning templates saved.');
    }
}