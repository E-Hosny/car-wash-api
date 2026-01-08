<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\GeographicalBound;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $packagesEnabled = (bool) (Setting::getValue('packages_enabled', '1') === '1' || Setting::getValue('packages_enabled', true));
        $maxSlotsPerHour = (int) Setting::getValue('max_slots_per_hour', 2);
        $supportWhatsapp = Setting::getValue('support_whatsapp', '966542327025');
        $minimumBookingAdvance = (int) Setting::getValue('minimum_booking_advance_minutes', 60);
        
        // الحدود الجغرافية (للتوافق مع البيانات القديمة)
        $dubaiMinLat = (float) Setting::getValue('dubai_min_latitude', 24.5);
        $dubaiMaxLat = (float) Setting::getValue('dubai_max_latitude', 25.5);
        $dubaiMinLng = (float) Setting::getValue('dubai_min_longitude', 54.5);
        $dubaiMaxLng = (float) Setting::getValue('dubai_max_longitude', 56.0);
        
        // الحصول على جميع الحدود الجغرافية
        $geographicalBounds = GeographicalBound::orderBy('created_at', 'desc')->get();
        
        return view('admin.settings.edit', compact(
            'packagesEnabled', 
            'maxSlotsPerHour', 
            'supportWhatsapp', 
            'minimumBookingAdvance',
            'dubaiMinLat',
            'dubaiMaxLat',
            'dubaiMinLng',
            'dubaiMaxLng',
            'geographicalBounds'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'packages_enabled' => 'nullable|boolean',
            'max_slots_per_hour' => 'required|integer|min:1|max:10',
            'support_whatsapp' => 'required|string|max:20|regex:/^[0-9+]+$/',
            'minimum_booking_advance_minutes' => 'required|integer|min:1|max:1440',
            'dubai_min_latitude' => 'required|numeric|min:-90|max:90',
            'dubai_max_latitude' => 'required|numeric|min:-90|max:90',
            'dubai_min_longitude' => 'required|numeric|min:-180|max:180',
            'dubai_max_longitude' => 'required|numeric|min:-180|max:180',
        ]);

        // التحقق من أن الحد الأدنى أقل من الحد الأقصى
        if ($request->dubai_min_latitude >= $request->dubai_max_latitude) {
            return redirect()->back()->withErrors([
                'dubai_min_latitude' => 'الحد الأدنى للخط العرض يجب أن يكون أقل من الحد الأقصى'
            ])->withInput();
        }

        if ($request->dubai_min_longitude >= $request->dubai_max_longitude) {
            return redirect()->back()->withErrors([
                'dubai_min_longitude' => 'الحد الأدنى للخط الطول يجب أن يكون أقل من الحد الأقصى'
            ])->withInput();
        }

        $enabled = $request->has('packages_enabled') && $request->packages_enabled ? true : false;
        Setting::setValue('packages_enabled', $enabled);
        Setting::setValue('max_slots_per_hour', $request->max_slots_per_hour);
        Setting::setValue('minimum_booking_advance_minutes', $request->minimum_booking_advance_minutes);
        
        // Remove + if present, we'll add it when needed
        $whatsappNumber = str_replace('+', '', $request->support_whatsapp);
        Setting::setValue('support_whatsapp', $whatsappNumber);

        // حفظ الحدود الجغرافية
        Setting::setValue('dubai_min_latitude', (float) $request->dubai_min_latitude);
        Setting::setValue('dubai_max_latitude', (float) $request->dubai_max_latitude);
        Setting::setValue('dubai_min_longitude', (float) $request->dubai_min_longitude);
        Setting::setValue('dubai_max_longitude', (float) $request->dubai_max_longitude);

        return redirect()->route('admin.settings.index')->with('success', __('messages.updated_successfully'));
    }

    /**
     * عرض قائمة الحدود الجغرافية
     */
    public function boundsIndex()
    {
        $bounds = GeographicalBound::orderBy('created_at', 'desc')->get();
        return response()->json($bounds);
    }

    /**
     * إضافة حد جغرافي جديد
     */
    public function boundsStore(Request $request)
    {
        $validated = $request->validate(
            GeographicalBound::validationRules(),
            GeographicalBound::validationMessages()
        );

        // التحقق من أن الحد الأدنى أقل من الحد الأقصى
        if ($validated['min_latitude'] >= $validated['max_latitude']) {
            return redirect()->back()->withErrors([
                'min_latitude' => 'الحد الأدنى للخط العرض يجب أن يكون أقل من الحد الأقصى'
            ])->withInput();
        }

        if ($validated['min_longitude'] >= $validated['max_longitude']) {
            return redirect()->back()->withErrors([
                'min_longitude' => 'الحد الأدنى للخط الطول يجب أن يكون أقل من الحد الأقصى'
            ])->withInput();
        }

        $bound = GeographicalBound::create($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'تم إضافة الحد الجغرافي بنجاح');
    }

    /**
     * تعديل حد جغرافي موجود
     */
    public function boundsUpdate(Request $request, $id)
    {
        $bound = GeographicalBound::findOrFail($id);

        $validated = $request->validate(
            GeographicalBound::validationRules(),
            GeographicalBound::validationMessages()
        );

        // التحقق من أن الحد الأدنى أقل من الحد الأقصى
        if ($validated['min_latitude'] >= $validated['max_latitude']) {
            return redirect()->back()->withErrors([
                'min_latitude' => 'الحد الأدنى للخط العرض يجب أن يكون أقل من الحد الأقصى'
            ])->withInput();
        }

        if ($validated['min_longitude'] >= $validated['max_longitude']) {
            return redirect()->back()->withErrors([
                'min_longitude' => 'الحد الأدنى للخط الطول يجب أن يكون أقل من الحد الأقصى'
            ])->withInput();
        }

        $bound->update($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'تم تعديل الحد الجغرافي بنجاح');
    }

    /**
     * حذف حد جغرافي
     */
    public function boundsDestroy($id)
    {
        $bound = GeographicalBound::findOrFail($id);
        $bound->delete();

        return redirect()->route('admin.settings.index')
            ->with('success', 'تم حذف الحد الجغرافي بنجاح');
    }
} 