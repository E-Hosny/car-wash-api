<?php
/**
 * ملف اختبار ترتيب الخدمات في API
 * يمكنك تشغيله من المتصفح لاختبار الترتيب
 */

// تضمين Laravel
require_once 'vendor/autoload.php';

// إعداد التطبيق
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>اختبار ترتيب الخدمات في API</h1>";
echo "<hr>";

// اختبار النموذج مباشرة
echo "<h2>1. اختبار النموذج مباشرة</h2>";
try {
    $services = \App\Models\Service::ordered()->get();
    echo "<p><strong>عدد الخدمات:</strong> " . $services->count() . "</p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>الترتيب</th><th>الاسم</th><th>السعر</th><th>الوصف</th></tr>";
    
    foreach ($services as $service) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>{$service->sort_order}</td>";
        echo "<td style='padding: 8px;'>{$service->name}</td>";
        echo "<td style='padding: 8px;'>{$service->price} د.إ</td>";
        echo "<td style='padding: 8px;'>" . ($service->description ?: 'لا يوجد وصف') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>خطأ: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// اختبار API endpoint
echo "<h2>2. اختبار API Endpoint</h2>";
try {
    $request = \Illuminate\Http\Request::create('/api/services', 'GET');
    $response = app()->handle($request);
    
    if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getContent(), true);
        echo "<p><strong>API Status:</strong> " . $response->getStatusCode() . "</p>";
        echo "<p><strong>عدد الخدمات من API:</strong> " . count($data) . "</p>";
        
        if (count($data) > 0) {
            echo "<p><strong>أول خدمة:</strong> " . $data[0]['name'] . " (ترتيب: " . $data[0]['sort_order'] . ")</p>";
            echo "<p><strong>آخر خدمة:</strong> " . $data[count($data)-1]['name'] . " (ترتيب: " . $data[count($data)-1]['sort_order'] . ")</p>";
        }
        
        echo "<p style='color: green;'>✅ API يعمل بشكل صحيح ويعيد الخدمات مرتبة</p>";
    } else {
        echo "<p style='color: red;'>❌ خطأ في API: " . $response->getStatusCode() . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>خطأ في اختبار API: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// اختبار scope ordered
echo "<h2>3. اختبار Scope Ordered</h2>";
try {
    $orderedServices = \App\Models\Service::ordered()->get();
    $allServices = \App\Models\Service::all();
    
    echo "<p><strong>الخدمات المرتبة:</strong> " . $orderedServices->count() . "</p>";
    echo "<p><strong>جميع الخدمات:</strong> " . $allServices->count() . "</p>";
    
    if ($orderedServices->count() === $allServices->count()) {
        echo "<p style='color: green;'>✅ عدد الخدمات متطابق</p>";
        
        // التحقق من الترتيب
        $isOrdered = true;
        $previousOrder = 0;
        
        foreach ($orderedServices as $service) {
            if ($service->sort_order < $previousOrder) {
                $isOrdered = false;
                break;
            }
            $previousOrder = $service->sort_order;
        }
        
        if ($isOrdered) {
            echo "<p style='color: green;'>✅ الخدمات مرتبة بشكل صحيح</p>";
        } else {
            echo "<p style='color: red;'>❌ الخدمات غير مرتبة بشكل صحيح</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ عدد الخدمات غير متطابق</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>خطأ في اختبار Scope: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// معلومات إضافية
echo "<h2>4. معلومات إضافية</h2>";
echo "<p><strong>اللغة الحالية:</strong> " . app()->getLocale() . "</p>";
echo "<p><strong>الوقت:</strong> " . now() . "</p>";

echo "<hr>";
echo "<p><strong>ملاحظة:</strong> هذا الملف للاختبار فقط. احذفه بعد التأكد من عمل الترتيب.</p>";
echo "<p><strong>Note:</strong> This file is for testing only. Delete it after confirming ordering works.</p>";
?>
