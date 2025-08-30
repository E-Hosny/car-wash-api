Write-Host "========================================" -ForegroundColor Green
Write-Host "تحديث قاعدة بيانات السيارات الشاملة - Car Wash API" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "⚠️  سيتم الحفاظ على البيانات الموجودة" -ForegroundColor Yellow
Write-Host ""

Set-Location $PSScriptRoot

Write-Host "[1/3] إضافة البراندات والموديلات الشاملة..." -ForegroundColor Yellow
php artisan db:seed --class=ComprehensiveCarSeeder

Write-Host ""
Write-Host "[2/3] إضافة سنوات الإنتاج (إذا لم تكن موجودة)..." -ForegroundColor Yellow
php artisan db:seed --class=CarYearSeeder

Write-Host ""
Write-Host "[3/3] مسح الكاش..." -ForegroundColor Yellow
php artisan cache:clear
php artisan config:clear
php artisan view:clear

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "تم الانتهاء من التحديث بنجاح!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "📊 الإحصائيات:" -ForegroundColor Cyan
Write-Host "   - تم الحفاظ على البيانات الموجودة" -ForegroundColor White
Write-Host "   - تم إضافة البراندات والموديلات الجديدة" -ForegroundColor White
Write-Host "   - تم إضافة سنوات الإنتاج" -ForegroundColor White
Write-Host ""
Write-Host "يمكنك الآن اختبار النظام:" -ForegroundColor Cyan
Write-Host "https://washluxuria.com/test_api_services_order.php" -ForegroundColor White
Write-Host ""
Read-Host "اضغط Enter للخروج"
