# صفحة إدارة المواعيد والساعات - لوحة تحكم الأدمن

## نظرة عامة
تم إنشاء صفحة متقدمة لإدارة المواعيد والساعات في لوحة تحكم الأدمن، تعرض الساعات المتاحة والمحجوزة بطريقة مشابهة لتطبيق الهاتف المحمول.

## المميزات الرئيسية

### 🎯 **عرض الساعات التفاعلي**
- عرض الساعات من 10 AM إلى 11 PM
- تمييز الساعات المحجوزة والمتاحة بالألوان
- عرض تفاصيل العميل عند التمرير على الساعة المحجوزة
- تصميم مشابه لتطبيق الهاتف المحمول

### 📅 **نظام التواريخ**
- **Today**: اليوم الحالي
- **Tomorrow**: غداً
- **Day After**: بعد غد
- عرض عدد المواعيد المحجوزة لكل يوم

### 📊 **الإحصائيات السريعة**
- عدد المواعيد المحجوزة لكل يوم
- عرض إجمالي المواعيد المتاحة
- بطاقات إحصائيات ملونة وجذابة

### 🔄 **التحديث المباشر**
- تحديث تلقائي كل 30 ثانية
- تحديث يدوي عند الضغط على زر التحديث
- AJAX للتحديث بدون إعادة تحميل الصفحة

### 📋 **جدول تفصيلي للطلبات**
- جميع الطلبات المحجوزة مع التفاصيل
- معلومات العميل والخدمات المطلوبة
- فلترة حسب حالة الطلب
- بحث في أسماء العملاء
- إجراءات سريعة (عرض، تعديل، إلغاء)

### 🎨 **تصميم متقدم**
- تصميم Bootstrap 5 حديث
- تأثيرات بصرية جميلة
- تصميم متجاوب لجميع الأجهزة
- ألوان متدرجة وجذابة
- أيقونات Bootstrap Icons

## الملفات المضافة/المحدثة

### 1. Routes
```php
// routes/web.php
Route::get('/orders/time-slots', [OrderController::class, 'timeSlots'])->name('admin.orders.time-slots');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
Route::get('/orders/{order}/status', [OrderController::class, 'getStatus'])->name('admin.orders.status');
Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('admin.orders.cancel');
```

### 2. Controller
```php
// app/Http/Controllers/Admin/OrderController.php
- timeSlots() // عرض صفحة المواعيد
- show() // عرض تفاصيل الطلب
- getStatus() // الحصول على حالة الطلب
- updateStatus() // تحديث حالة الطلب
- cancel() // إلغاء الطلب
```

### 3. Views
- `resources/views/admin/orders/time-slots.blade.php` - الصفحة الرئيسية
- `resources/views/admin/orders/details.blade.php` - تفاصيل الطلب
- تحديث `resources/views/admin/layout.blade.php` - إضافة رابط القائمة

### 4. Database
```php
// Migration: add_admin_fields_to_orders_table
- admin_notes (text, nullable) // ملاحظات الأدمن
- cancelled_at (timestamp, nullable) // تاريخ الإلغاء
```

### 5. Model
```php
// app/Models/Order.php
- إضافة admin_notes و cancelled_at للـ fillable
```

## كيفية الاستخدام

### الوصول للصفحة
```
http://localhost/car-wash-api/public/admin/orders/time-slots
```

### من القائمة الجانبية
- اذهب إلى لوحة تحكم الأدمن
- اضغط على "المواعيد والساعات" في القائمة الجانبية

### الميزات التفاعلية

#### 1. عرض الساعات
- الساعات الخضراء: متاحة للحجز
- الساعات الحمراء: محجوزة
- اضغط على الساعة المحجوزة لعرض تفاصيل الطلب

#### 2. إدارة الطلبات
- **عرض التفاصيل**: اضغط على أيقونة العين
- **تعديل الحالة**: اضغط على أيقونة القلم
- **إلغاء الطلب**: اضغط على أيقونة X

#### 3. الفلترة والبحث
- استخدم قائمة الفلترة لتصفية الطلبات حسب الحالة
- استخدم مربع البحث للبحث في أسماء العملاء

#### 4. التصدير
- اضغط على زر "تصدير" لحفظ البيانات في ملف CSV

## التقنيات المستخدمة

### Frontend
- **Bootstrap 5**: للتصميم المتجاوب
- **Bootstrap Icons**: للأيقونات
- **JavaScript ES6**: للتفاعل
- **AJAX**: للتحديث المباشر
- **CSS3**: للتأثيرات البصرية

### Backend
- **Laravel 10**: إطار العمل
- **PHP 8.1+**: لغة البرمجة
- **MySQL**: قاعدة البيانات
- **Carbon**: لإدارة التواريخ

## الميزات المتقدمة

### 1. التحديث التلقائي
```javascript
// تحديث تلقائي كل 30 ثانية
setInterval(function() {
    refreshTimeSlots(true);
}, 30000);
```

### 2. AJAX للتفاعل
```javascript
// تحديث حالة الطلب
fetch(`/admin/orders/${orderId}/status`, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: formData
})
```

### 3. التصدير للـ CSV
```javascript
// تصدير البيانات
const csvContent = generateCSV(data);
const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
```

## الأمان

### CSRF Protection
- جميع الطلبات محمية بـ CSRF Token
- التحقق من صحة البيانات المدخلة

### Authorization
- الوصول مقيد للمديرين فقط
- التحقق من صلاحيات المستخدم

## الأداء

### تحسينات قاعدة البيانات
- استخدام Eager Loading للعلاقات
- فهرسة على الحقول المهمة
- استعلامات محسنة

### تحسينات Frontend
- تحميل البيانات بشكل تدريجي
- استخدام AJAX لتجنب إعادة تحميل الصفحة
- تحسين الصور والأيقونات

## الصيانة والتطوير

### إضافة ميزات جديدة
1. أضف الـ Route الجديد في `web.php`
2. أضف الدالة في `OrderController`
3. أنشئ الـ View المطلوب
4. حدث الـ JavaScript إذا لزم الأمر

### تخصيص التصميم
- عدّل ملف `time-slots.blade.php`
- غيّر الألوان في قسم `<style>`
- أضف أيقونات جديدة من Bootstrap Icons

## الدعم والمساعدة

### استكشاف الأخطاء
1. تحقق من سجلات Laravel
2. تأكد من صحة قاعدة البيانات
3. تحقق من صلاحيات المستخدم

### التحسينات المقترحة
- إضافة إشعارات فورية
- دعم التقويم الشهري
- إضافة تقارير مفصلة
- دعم الطباعة

---

**تم التطوير بواسطة**: AI Assistant  
**تاريخ الإنشاء**: 17 سبتمبر 2025  
**الإصدار**: 1.0.0
