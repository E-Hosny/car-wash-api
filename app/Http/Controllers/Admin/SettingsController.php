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
        return view('admin.settings.edit', compact('packagesEnabled'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'packages_enabled' => 'nullable|boolean',
        ]);

        $enabled = $request->has('packages_enabled') && $request->packages_enabled ? true : false;
        Setting::setValue('packages_enabled', $enabled);

        return redirect()->route('admin.settings.index')->with('success', __('messages.updated_successfully'));
    }
} 