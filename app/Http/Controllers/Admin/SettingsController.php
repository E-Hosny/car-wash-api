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

        // تحديث التطبيق (إجباري)
        $minAndroidVersion = (string) Setting::getValue('min_android_version', '');
        $minIosVersion = (string) Setting::getValue('min_ios_version', '');
        $androidStoreUrl = (string) Setting::getValue('android_store_url', '');
        $iosStoreUrl = (string) Setting::getValue('ios_store_url', '');

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
            'minAndroidVersion',
            'minIosVersion',
            'androidStoreUrl',
            'iosStoreUrl',
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
            'min_android_version' => 'nullable|string|max:20',
            'min_ios_version' => 'nullable|string|max:20',
            'android_store_url' => 'nullable|url|max:500',
            'ios_store_url' => 'nullable|url|max:500',
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

        // تحديث التطبيق
        Setting::setValue('min_android_version', trim((string) $request->input('min_android_version', '')));
        Setting::setValue('min_ios_version', trim((string) $request->input('min_ios_version', '')));
        Setting::setValue('android_store_url', trim((string) $request->input('android_store_url', '')));
        Setting::setValue('ios_store_url', trim((string) $request->input('ios_store_url', '')));

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
        
        // التحقق من نقاط المضلع
        if (!$request->has('polygon_points') || !$request->polygon_points) {
            return redirect()->back()->withErrors([
                'polygon_points' => 'يجب رسم منطقة على الخريطة أولاً'
            ])->withInput();
        }
        
        $polygonPoints = json_decode($request->polygon_points, true);
        if (!is_array($polygonPoints) || count($polygonPoints) < 3) {
            return redirect()->back()->withErrors([
                'polygon_points' => 'يجب أن يحتوي المضلع على 3 نقاط على الأقل'
            ])->withInput();
        }
        
        // تنظيف النقاط: إزالة المكررة والتأكد من الإغلاق
        $polygonPoints = $this->cleanPolygonPoints($polygonPoints);
        
        // التحقق من صحة المضلع
        $validation = $this->validatePolygonPoints($polygonPoints);
        if (!$validation['valid']) {
            return redirect()->back()->withErrors([
                'polygon_points' => $validation['message']
            ])->withInput();
        }
        
        $validated['polygon_points'] = $polygonPoints;
        
        // حساب الحدود من النقاط (للعرض فقط)
        $lats = array_column($polygonPoints, 'lat');
        $lngs = array_column($polygonPoints, 'lng');
        $validated['min_latitude'] = min($lats);
        $validated['max_latitude'] = max($lats);
        $validated['min_longitude'] = min($lngs);
        $validated['max_longitude'] = max($lngs);

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
        
        // التحقق من نقاط المضلع
        if (!$request->has('polygon_points') || !$request->polygon_points) {
            return redirect()->back()->withErrors([
                'polygon_points' => 'يجب رسم منطقة على الخريطة أولاً'
            ])->withInput();
        }
        
        $polygonPoints = json_decode($request->polygon_points, true);
        if (!is_array($polygonPoints) || count($polygonPoints) < 3) {
            return redirect()->back()->withErrors([
                'polygon_points' => 'يجب أن يحتوي المضلع على 3 نقاط على الأقل'
            ])->withInput();
        }
        
        // تنظيف النقاط: إزالة المكررة والتأكد من الإغلاق
        $polygonPoints = $this->cleanPolygonPoints($polygonPoints);
        
        // التحقق من صحة المضلع
        $validation = $this->validatePolygonPoints($polygonPoints);
        if (!$validation['valid']) {
            return redirect()->back()->withErrors([
                'polygon_points' => $validation['message']
            ])->withInput();
        }
        
        $validated['polygon_points'] = $polygonPoints;
        
        // حساب الحدود من النقاط (للعرض فقط)
        $lats = array_column($polygonPoints, 'lat');
        $lngs = array_column($polygonPoints, 'lng');
        $validated['min_latitude'] = min($lats);
        $validated['max_latitude'] = max($lats);
        $validated['min_longitude'] = min($lngs);
        $validated['max_longitude'] = max($lngs);

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
    
    /**
     * تنظيف نقاط المضلع: إزالة المكررة والتأكد من الإغلاق
     */
    private function cleanPolygonPoints(array $points): array
    {
        if (empty($points)) {
            return [];
        }
        
        $cleaned = [];
        $tolerance = 0.000001;
        
        foreach ($points as $point) {
            $lat = (float) ($point['lat'] ?? 0);
            $lng = (float) ($point['lng'] ?? 0);
            
            // تقريب إلى 6 خانات عشرية
            $lat = round($lat, 6);
            $lng = round($lng, 6);
            
            // إزالة النقاط المكررة
            if (empty($cleaned)) {
                $cleaned[] = ['lat' => $lat, 'lng' => $lng];
            } else {
                $last = $cleaned[count($cleaned) - 1];
                if (abs($lat - $last['lat']) > $tolerance || 
                    abs($lng - $last['lng']) > $tolerance) {
                    $cleaned[] = ['lat' => $lat, 'lng' => $lng];
                }
            }
        }
        
        // لا نضيف النقطة المكررة - Google Maps Polygon يغلق المضلع تلقائياً
        // نحفظ النقاط كما هي بدون النقطة الأخيرة المكررة
        return $cleaned;
    }
    
    /**
     * التحقق من صحة نقاط المضلع
     */
    private function validatePolygonPoints(array $points): array
    {
        // التحقق من عدد النقاط
        if (count($points) < 3) {
            return [
                'valid' => false,
                'message' => 'يجب أن يحتوي المضلع على 3 نقاط على الأقل'
            ];
        }
        
        // التحقق من أن النقاط ليست على خط مستقيم (للمثلث)
        if (count($points) === 3 || count($points) === 4) {
            $p1 = $points[0];
            $p2 = $points[1];
            $p3 = $points[2];
            
            // حساب المسافات
            $dist12 = sqrt(pow($p2['lat'] - $p1['lat'], 2) + pow($p2['lng'] - $p1['lng'], 2));
            $dist23 = sqrt(pow($p3['lat'] - $p2['lat'], 2) + pow($p3['lng'] - $p2['lng'], 2));
            $dist13 = sqrt(pow($p3['lat'] - $p1['lat'], 2) + pow($p3['lng'] - $p1['lng'], 2));
            
            // إذا كانت النقاط على خط مستقيم
            $tolerance = 0.0001;
            if (abs($dist12 + $dist23 - $dist13) < $tolerance ||
                abs($dist12 + $dist13 - $dist23) < $tolerance ||
                abs($dist23 + $dist13 - $dist12) < $tolerance) {
                return [
                    'valid' => false,
                    'message' => 'النقاط على خط مستقيم. يرجى اختيار نقاط تشكل مضلعاً صالحاً'
                ];
            }
        }
        
        // لا نحتاج للتحقق من إغلاق المضلع - Google Maps يغلقه تلقائياً
        return ['valid' => true];
    }
} 