<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $packagesEnabled = (bool) (Setting::getValue('packages_enabled', '1') === '1' || Setting::getValue('packages_enabled', true));
        $maxSlotsPerHour = (int) Setting::getValue('max_slots_per_hour', 2);
        $supportWhatsapp = Setting::getValue('support_whatsapp', '966542327025');
        $minimumBookingAdvance = (int) Setting::getValue('minimum_booking_advance_minutes', 60);
        return view('admin.settings.edit', compact('packagesEnabled', 'maxSlotsPerHour', 'supportWhatsapp', 'minimumBookingAdvance'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'packages_enabled' => 'nullable|boolean',
            'max_slots_per_hour' => 'required|integer|min:1|max:10',
            'support_whatsapp' => 'required|string|max:20|regex:/^[0-9+]+$/',
            'minimum_booking_advance_minutes' => 'required|integer|min:1|max:1440',
        ]);

        $enabled = $request->has('packages_enabled') && $request->packages_enabled ? true : false;
        Setting::setValue('packages_enabled', $enabled);
        Setting::setValue('max_slots_per_hour', $request->max_slots_per_hour);
        Setting::setValue('minimum_booking_advance_minutes', $request->minimum_booking_advance_minutes);
        
        // Remove + if present, we'll add it when needed
        $whatsappNumber = str_replace('+', '', $request->support_whatsapp);
        Setting::setValue('support_whatsapp', $whatsappNumber);

        return redirect()->route('admin.settings.index')->with('success', __('messages.updated_successfully'));
    }
} 