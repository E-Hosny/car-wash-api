<?php
/**
 * ملف اختبار نظام الترجمة
 * يمكنك تشغيله من المتصفح لاختبار الترجمة
 */

// تضمين Laravel
require_once 'vendor/autoload.php';

// إعداد التطبيق
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// اختبار الترجمة العربية
echo "<h2>اختبار الترجمة العربية</h2>";
echo "<p>اللغة: " . app()->getLocale() . "</p>";

// تغيير اللغة للعربية
app()->setLocale('ar');

echo "<h3>المصطلحات العربية:</h3>";
echo "<ul>";
echo "<li>ترتيب الخدمات: " . __('messages.service_ordering') . "</li>";
echo "<li>حفظ الترتيب: " . __('messages.save_order') . "</li>";
echo "<li>نقل لأعلى: " . __('messages.move_up') . "</li>";
echo "<li>نقل لأسفل: " . __('messages.move_down') . "</li>";
echo "<li>ترتيب الظهور: " . __('messages.sort_order') . "</li>";
echo "<li>جاري الحفظ: " . __('messages.saving') . "</li>";
echo "<li>العملة: " . __('messages.currency') . "</li>";
echo "</ul>";

// اختبار الترجمة الإنجليزية
echo "<h2>اختبار الترجمة الإنجليزية</h2>";

// تغيير اللغة للإنجليزية
app()->setLocale('en');

echo "<h3>English Terms:</h3>";
echo "<ul>";
echo "<li>Service Ordering: " . __('messages.service_ordering') . "</li>";
echo "<li>Save Order: " . __('messages.save_order') . "</li>";
echo "<li>Move Up: " . __('messages.move_up') . "</li>";
echo "<li>Move Down: " . __('messages.move_down') . "</li>";
echo "<li>Display Order: " . __('messages.sort_order') . "</li>";
echo "<li>Saving: " . __('messages.saving') . "</li>";
echo "<li>Currency: " . __('messages.currency') . "</li>";
echo "</ul>";

// اختبار الرسائل
echo "<h2>اختبار رسائل العمليات</h2>";

app()->setLocale('ar');
echo "<h3>الرسائل العربية:</h3>";
echo "<ul>";
echo "<li>إضافة خدمة: " . __('messages.service_added_successfully') . "</li>";
echo "<li>تحديث خدمة: " . __('messages.service_updated_successfully') . "</li>";
echo "<li>حذف خدمة: " . __('messages.service_deleted_successfully') . "</li>";
echo "</ul>";

app()->setLocale('en');
echo "<h3>English Messages:</h3>";
echo "<ul>";
echo "<li>Add Service: " . __('messages.service_added_successfully') . "</li>";
echo "<li>Update Service: " . __('messages.service_updated_successfully') . "</li>";
echo "<li>Delete Service: " . __('messages.service_deleted_successfully') . "</li>";
echo "</ul>";

// اختبار العملة
echo "<h2>اختبار العملة</h2>";
echo "<p><strong>العملة المستخدمة:</strong></p>";

app()->setLocale('ar');
echo "<p>العربية: " . __('messages.currency') . " (درهم إماراتي)</p>";

app()->setLocale('en');
echo "<p>English: " . __('messages.currency') . " (UAE Dirham)</p>";

echo "<hr>";
echo "<p><strong>ملاحظة:</strong> هذا الملف للاختبار فقط. احذفه بعد التأكد من عمل الترجمة.</p>";
echo "<p><strong>Note:</strong> This file is for testing only. Delete it after confirming translation works.</p>";
?>
