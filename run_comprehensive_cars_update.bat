@echo off
echo ========================================
echo تحديث قاعدة بيانات السيارات الشاملة - Car Wash API
echo ========================================
echo.
echo ⚠️  سيتم الحفاظ على البيانات الموجودة
echo.

cd /d "%~dp0"

echo [1/3] إضافة البراندات والموديلات الشاملة...
php artisan db:seed --class=ComprehensiveCarSeeder

echo.
echo [2/3] إضافة سنوات الإنتاج (إذا لم تكن موجودة)...
php artisan db:seed --class=CarYearSeeder

echo.
echo [3/3] مسح الكاش...
php artisan cache:clear
php artisan config:clear
php artisan view:clear

echo.
echo ========================================
echo تم الانتهاء من التحديث بنجاح!
echo ========================================
echo.
echo 📊 الإحصائيات:
echo    - تم الحفاظ على البيانات الموجودة
echo    - تم إضافة البراندات والموديلات الجديدة
echo    - تم إضافة سنوات الإنتاج
echo.
echo يمكنك الآن اختبار النظام:
echo https://washluxuria.com/test_api_services_order.php
echo.
pause
