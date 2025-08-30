Write-Host "========================================" -ForegroundColor Green
Write-Host "تحديث نظام ترتيب الخدمات - Car Wash API" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

Set-Location $PSScriptRoot

Write-Host "[1/4] تشغيل Migration لإضافة عمود sort_order..." -ForegroundColor Yellow
php artisan migrate

Write-Host ""
Write-Host "[2/4] تحديث الخدمات الموجودة بإضافة قيم الترتيب..." -ForegroundColor Yellow
php artisan db:seed --class=UpdateServicesSortOrderSeeder

Write-Host ""
Write-Host "[3/4] مسح الكاش..." -ForegroundColor Yellow
php artisan cache:clear
php artisan config:clear
php artisan view:clear

Write-Host ""
Write-Host "[4/4] مسح كاش الترجمة..." -ForegroundColor Yellow
php artisan lang:publish
php artisan lang:clear

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "تم الانتهاء من التحديث بنجاح!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "يمكنك الآن الوصول إلى:" -ForegroundColor Cyan
Write-Host "https://washluxuria.com/admin/services" -ForegroundColor White
Write-Host ""
Write-Host "للمزيد من المعلومات، راجع ملف:" -ForegroundColor Cyan
Write-Host "SERVICES_ORDERING_README.md" -ForegroundColor White
Write-Host ""
Write-Host "لاختبار الترجمة، راجع ملف:" -ForegroundColor Cyan
Write-Host "test_language_system.php" -ForegroundColor White
Write-Host ""
Read-Host "اضغط Enter للخروج"
