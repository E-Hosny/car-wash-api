# نظام ترتيب الخدمات - Car Wash API

## نظرة عامة
تم إضافة نظام جديد للتحكم في ترتيب ظهور الخدمات في التطبيق. هذا النظام يسمح للمديرين بتحديد أولوية ظهور كل خدمة.

## الميزات الجديدة

### 1. حقل ترتيب الظهور
- تم إضافة عمود `sort_order` إلى جدول `services`
- الخدمات تظهر حسب هذا الترتيب (الأقل = الأول)

### 2. واجهة إدارة الترتيب
- عرض الخدمات في بطاقات قابلة للسحب والإفلات
- أزرار نقل لأعلى/لأسفل
- زر حفظ الترتيب
- تحديث فوري للترتيب

### 3. طرق التحكم
- **السحب والإفلات**: اسحب الخدمة من أيقونة القبضة
- **أزرار النقل**: استخدم الأسهم لأعلى/لأسفل
- **تحديث يدوي**: أدخل رقم الترتيب في نماذج الإنشاء/التعديل

### 4. دعم متعدد اللغات
- **العربية**: واجهة كاملة باللغة العربية
- **الإنجليزية**: واجهة كاملة باللغة الإنجليزية
- تبديل تلقائي حسب اللغة المختارة

### 5. العملة
- **العربية**: د.إ (درهم إماراتي)
- **English**: AED (UAE Dirham)

### 6. تطبيق الترتيب في التطبيق
- **API Endpoint**: `/api/services` يستخدم الترتيب الجديد
- **لوحة الإدارة**: عرض الخدمات مرتبة
- **التطبيق**: Flutter app يحصل على الخدمات مرتبة

## التثبيت والتشغيل

### 1. تشغيل Migration
```bash
php artisan migrate
```

### 2. تحديث الخدمات الموجودة
```bash
php artisan db:seed --class=UpdateServicesSortOrderSeeder
```

### 3. الوصول للوحة الإدارة
```
https://washluxuria.com/admin/services
```

## كيفية الاستخدام

### إضافة خدمة جديدة
1. اذهب إلى "إضافة خدمة"
2. أدخل اسم الخدمة والوصف والسعر
3. أدخل رقم الترتيب (1 للأول، 2 للثاني، إلخ)
4. احفظ الخدمة

### تغيير ترتيب الخدمات
1. اذهب إلى صفحة الخدمات
2. استخدم إحدى الطرق التالية:
   - **السحب والإفلات**: اسحب الخدمة من أيقونة القبضة
   - **أزرار النقل**: اضغط على الأسهم لأعلى/لأسفل
3. اضغط "حفظ الترتيب" لتطبيق التغييرات

### تعديل خدمة موجودة
1. اضغط على زر التعديل
2. عدّل البيانات المطلوبة
3. عدّل رقم الترتيب إذا أردت
4. احفظ التغييرات

### تغيير اللغة
- استخدم أزرار تغيير اللغة في القائمة الجانبية
- العربية | English
- جميع النصوص والمصطلحات تتغير تلقائياً

## الملفات المحدثة

### Controllers
- `app/Http/Controllers/Admin/ServiceController.php`
- `app/Http/Controllers/Admin/PackageController.php`
- `app/Http/Controllers/API/ServiceController.php`

### Models
- `app/Models/Service.php`

### Views
- `resources/views/admin/services/index.blade.php`
- `resources/views/admin/services/create.blade.php`
- `resources/views/admin/services/edit.blade.php`
- `resources/views/admin/layout.blade.php`

### Routes
- `routes/web.php` (إضافة مسارات جديدة)

### Database
- `database/migrations/2025_01_20_000000_add_sort_order_to_services_table.php`
- `database/seeders/UpdateServicesSortOrderSeeder.php`

### Language Files
- `resources/lang/ar/messages.php` (العربية)
- `resources/lang/en/messages.php` (الإنجليزية)

## المسارات الجديدة

```php
POST /admin/services/update-order          // تحديث ترتيب الخدمات
POST /admin/services/{id}/move-up         // نقل خدمة لأعلى
POST /admin/services/{id}/move-down       // نقل خدمة لأسفل
```

## API Endpoints

### الخدمات (مرتبة)
```php
GET /api/services                          // الحصول على الخدمات مرتبة
GET /api/services/{id}                    // الحصول على خدمة محددة
```

### تطبيق الترتيب
- **API ServiceController**: يستخدم `Service::ordered()->get()`
- **Admin ServiceController**: يستخدم `Service::ordered()->get()`
- **PackageController**: يستخدم `Service::ordered()->get()`

## API Response

### تحديث الترتيب
```json
{
    "success": true,
    "message": "تم تحديث ترتيب الخدمات بنجاح"
}
```

### الحصول على الخدمات
```json
[
    {
        "id": 1,
        "name": "غسيل خارجي",
        "description": "غسيل خارجي للسيارة",
        "price": "50.00",
        "sort_order": 1
    },
    {
        "id": 2,
        "name": "غسيل داخلي",
        "description": "غسيل داخلي للسيارة",
        "price": "30.00",
        "sort_order": 2
    }
]
```

## المصطلحات المدعومة

### العربية
- ترتيب الخدمات
- ترتيب الظهور
- حفظ الترتيب
- نقل لأعلى/لأسفل
- اسحب من هنا
- جاري الحفظ...
- العملة: د.إ (درهم إماراتي)

### English
- Service Ordering
- Display Order
- Save Order
- Move Up/Down
- Drag from here
- Saving...
- Currency: AED (UAE Dirham)

## ملاحظات مهمة

1. **الترتيب**: الخدمات تظهر حسب `sort_order` تصاعدياً
2. **القيم**: يجب أن تكون أرقام صحيحة موجبة
3. **التحديث**: التغييرات تطبق فوراً على التطبيق
4. **الأمان**: جميع العمليات محمية بـ CSRF token
5. **اللغات**: النظام يدعم العربية والإنجليزية تلقائياً
6. **العملة**: النظام يستخدم الدرهم الإماراتي (AED)
7. **التطبيق**: Flutter app يحصل على الخدمات مرتبة تلقائياً

## كيفية عمل الترتيب

### 1. في لوحة الإدارة
- المدير يغير ترتيب الخدمات
- يتم حفظ الترتيب في قاعدة البيانات
- التغييرات تطبق فوراً

### 2. في API
- `/api/services` يستخدم `Service::ordered()->get()`
- الخدمات ترجع مرتبة حسب `sort_order`

### 3. في التطبيق
- Flutter app يستدعي `/api/services`
- يحصل على الخدمات مرتبة
- يعرضها بالترتيب الصحيح

## استكشاف الأخطاء

### مشكلة في حفظ الترتيب
- تأكد من وجود CSRF token
- تحقق من صحة البيانات المرسلة
- راجع سجلات الخطأ في Laravel

### مشكلة في عرض الترتيب
- تأكد من تشغيل migration
- تحقق من وجود قيم `sort_order` في قاعدة البيانات
- راجع scope `ordered()` في نموذج Service

### مشكلة في الترجمة
- تأكد من وجود ملفات اللغة
- تحقق من صحة أسماء المفاتيح
- راجع `SetLocale` middleware

### مشكلة في التطبيق
- تأكد من أن API يعيد الخدمات مرتبة
- تحقق من أن التطبيق يستدعي `/api/services`
- راجع سجلات API في Laravel

## الدعم

للمساعدة أو الإبلاغ عن مشاكل، يرجى التواصل مع فريق التطوير.
