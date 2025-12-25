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
        return view('admin.settings.edit', compact('packagesEnabled', 'maxSlotsPerHour'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'packages_enabled' => 'nullable|boolean',
            'max_slots_per_hour' => 'required|integer|min:1|max:10',
        ]);

        $enabled = $request->has('packages_enabled') && $request->packages_enabled ? true : false;
        Setting::setValue('packages_enabled', $enabled);
        Setting::setValue('max_slots_per_hour', $request->max_slots_per_hour);

        return redirect()->route('admin.settings.index')->with('success', __('messages.updated_successfully'));
    }
} 