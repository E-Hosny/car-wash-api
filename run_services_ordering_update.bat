@echo off
echo ========================================
echo تحديث نظام ترتيب الخدمات - Car Wash API
echo ========================================
echo.

cd /d "%~dp0"

echo [1/4] تشغيل Migration لإضافة عمود sort_order...
php artisan migrate

echo.
echo [2/4] تحديث الخدمات الموجودة بإضافة قيم الترتيب...
php artisan db:seed --class=UpdateServicesSortOrderSeeder

echo.
echo [3/4] مسح الكاش...
php artisan cache:clear
php artisan config:clear
php artisan view:clear

echo.
echo [4/4] مسح كاش الترجمة...
php artisan lang:publish
php artisan lang:clear

echo.
echo ========================================
echo تم الانتهاء من التحديث بنجاح!
echo ========================================
echo.
echo يمكنك الآن الوصول إلى:
echo https://washluxuria.com/admin/services
echo.
echo للمزيد من المعلومات، راجع ملف:
echo SERVICES_ORDERING_README.md
echo.
echo لاختبار الترجمة، راجع ملف:
echo test_language_system.php
echo.
pause
