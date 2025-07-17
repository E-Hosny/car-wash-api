<?php

return [
    // General
    'packages' => 'الباقات',
    'package' => 'الباقة',
    'add_package' => 'إضافة باقة',
    'edit_package' => 'تعديل الباقة',
    'package_details' => 'تفاصيل الباقة',
    'package_name' => 'اسم الباقة',
    'package_description' => 'وصف الباقة',
    'package_price' => 'سعر الباقة',
    'package_points' => 'نقاط الباقة',
    'package_image' => 'صورة الباقة',
    'package_status' => 'حالة الباقة',
    'active' => 'نشط',
    'inactive' => 'غير نشط',
    'expired' => 'منتهي الصلاحية',
    'cancelled' => 'ملغي',

    // Actions
    'create_package' => 'إنشاء باقة',
    'update_package' => 'تحديث الباقة',
    'delete_package' => 'حذف الباقة',
    'toggle_status' => 'تغيير الحالة',
    'view_package' => 'عرض الباقة',
    'purchase_package' => 'شراء الباقة',
    'use_package' => 'استخدام الباقة',

    // Messages
    'package_created_successfully' => 'تم إنشاء الباقة بنجاح',
    'package_updated_successfully' => 'تم تحديث الباقة بنجاح',
    'package_deleted_successfully' => 'تم حذف الباقة بنجاح',
    'package_status_updated' => 'تم تحديث حالة الباقة بنجاح',
    'package_purchased_successfully' => 'تم شراء الباقة بنجاح',
    'no_active_package' => 'لا توجد باقة نشطة',
    'insufficient_points' => 'النقاط غير كافية',
    'package_expired' => 'الباقة منتهية الصلاحية',
    'package_already_active' => 'لديك باقة نشطة بالفعل',

    // Service Points
    'service_points' => 'نقاط الخدمات',
    'points_required' => 'النقاط المطلوبة',
    'remaining_points' => 'النقاط المتبقية',
    'total_points' => 'إجمالي النقاط',
    'points_used' => 'النقاط المستخدمة',

    // User Package
    'my_package' => 'باقتي',
    'current_package' => 'الباقة الحالية',
    'package_history' => 'سجل الباقات',
    'available_services' => 'الخدمات المتاحة',
    'purchased_at' => 'تاريخ الشراء',
    'expires_at' => 'تاريخ الانتهاء',

    // Statistics
    'package_statistics' => 'إحصائيات الباقات',
    'total_packages' => 'إجمالي الباقات',
    'active_packages' => 'الباقات النشطة',
    'total_purchases' => 'إجمالي المشتريات',
    'active_subscriptions' => 'الاشتراكات النشطة',
    'total_revenue' => 'إجمالي الإيرادات',
    'top_packages' => 'أفضل الباقات مبيعاً',

    // Validation
    'name_required' => 'اسم الباقة مطلوب',
    'price_required' => 'سعر الباقة مطلوب',
    'price_numeric' => 'السعر يجب أن يكون رقم',
    'price_min' => 'السعر يجب أن يكون أكبر من صفر',
    'points_required' => 'عدد النقاط مطلوب',
    'points_integer' => 'النقاط يجب أن تكون رقم صحيح',
    'points_min' => 'النقاط يجب أن تكون أكبر من صفر',
    'image_image' => 'الملف يجب أن يكون صورة',
    'image_max' => 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت',

    // Errors
    'package_not_found' => 'الباقة غير موجودة',
    'cannot_delete_active_package' => 'لا يمكن حذف الباقة لوجود مستخدمين نشطين',
    'error_creating_package' => 'حدث خطأ أثناء إنشاء الباقة',
    'error_updating_package' => 'حدث خطأ أثناء تحديث الباقة',
    'error_deleting_package' => 'حدث خطأ أثناء حذف الباقة',
    'error_purchasing_package' => 'حدث خطأ أثناء شراء الباقة',

    // Table Headers
    'id' => '#',
    'image' => 'الصورة',
    'name' => 'الاسم',
    'description' => 'الوصف',
    'price' => 'السعر',
    'points' => 'النقاط',
    'subscribers_count' => 'عدد المشتركين',
    'status' => 'الحالة',
    'actions' => 'الإجراءات',

    // Buttons
    'save' => 'حفظ',
    'cancel' => 'إلغاء',
    'back' => 'رجوع',
    'edit' => 'تعديل',
    'delete' => 'حذف',
    'activate' => 'تفعيل',
    'deactivate' => 'إلغاء التفعيل',
    'view_statistics' => 'عرض الإحصائيات',
    'confirm_purchase' => 'تأكيد الشراء',
    'select_services' => 'اختيار الخدمات',

    // Placeholders
    'enter_package_name' => 'أدخل اسم الباقة',
    'enter_package_description' => 'أدخل وصف الباقة',
    'enter_package_price' => 'أدخل سعر الباقة',
    'enter_package_points' => 'أدخل عدد النقاط',
    'select_package_image' => 'اختر صورة الباقة',

    // Info Messages
    'package_info' => 'معلومات الباقة',
    'package_benefits' => 'مزايا الباقة',
    'package_terms' => 'شروط الباقة',
    'package_duration' => 'مدة الباقة',
    'package_services' => 'خدمات الباقة',

    // Currency
    'currency' => 'درهم',
    'points_unit' => 'نقطة',
    'points_unit_plural' => 'نقاط',
    
    // Additional
    'no_packages_found' => 'لا توجد باقات',
    'confirm_delete' => 'هل أنت متأكد من حذف هذه الباقة؟',
    
    // User Package Subscriptions
    'user_package_subscriptions' => 'اشتراكات الباقات',
    'add_new_subscription' => 'إضافة اشتراك جديد',
    'all_packages' => 'جميع الباقات',
    'all_status' => 'جميع الحالات',
    'all_users' => 'جميع المستخدمين',
    'from_date' => 'من تاريخ',
    'to_date' => 'إلى تاريخ',
    'filter' => 'تصفية',
    'clear' => 'مسح',
    'total_subscriptions' => 'إجمالي الاشتراكات',
    'active_subscriptions' => 'الاشتراكات النشطة',
    'expired_subscriptions' => 'الاشتراكات المنتهية',
    'no_expiry' => 'لا يوجد تاريخ انتهاء',
    'extend' => 'تمديد',
    'confirm_delete_package' => 'هل أنت متأكد من حذف الباقة',
    'delete_warning' => 'تحذير: لا يمكن التراجع عن هذا الإجراء!',
    'loading' => 'جاري التحميل',
    'no_packages_available' => 'لا توجد باقات متاحة حالياً',
    'add_first_package' => 'إضافة أول باقة',
    'view_detailed_statistics' => 'عرض الإحصائيات التفصيلية',
    'confirm_activate' => 'هل تريد تفعيل هذه الباقة؟',
    'confirm_deactivate' => 'هل تريد إلغاء تفعيل هذه الباقة؟',
    'edit_package' => 'تعديل الباقة',
    'delete_package' => 'حذف الباقة نهائياً',
    
    // Create/Edit Form
    'correct_errors' => 'يرجى تصحيح الأخطاء التالية',
    'optional' => 'اختياري',
    'max_size_2mb' => 'الحد الأقصى 2 ميجابايت',
    'set_points_per_service' => 'حدد عدد النقاط المطلوبة لكل خدمة',
    'not_available' => 'غير متاح',
    'available' => 'متاح',
    'preview' => 'معاينة',
    'preview_package' => 'معاينة الباقة',
    'no_image' => 'لا توجد صورة',
    'saving' => 'جاري الحفظ',
    
    // Edit Form
    'current_image' => 'الصورة الحالية',
    'select_new_image_or_keep_current' => 'يمكنك اختيار صورة جديدة أو تركها فارغة للاحتفاظ بالصورة الحالية',
    'updating_package' => 'جاري التحديث',

    // Statistics
    'back_to_packages' => 'رجوع للباقات',
    'percentage' => 'النسبة',
    'no_data' => 'لا توجد بيانات',
    'no_purchases_yet' => 'لم يتم تسجيل أي مشتريات بعد',
    'additional_info' => 'معلومات إضافية',
    'active_packages_percentage' => 'نسبة الباقات النشطة',
    'active_subscriptions_percentage' => 'نسبة الاشتراكات النشطة',
    'average_revenue_per_subscriber' => 'متوسط الإيراد لكل مشتركة',
    'last_update_date' => 'تاريخ آخر تحديث',
    'view_all_packages' => 'عرض جميع الباقات',
    'add_new_package' => 'إضافة باقة جديدة',
    'print_report' => 'طباعة التقرير',
]; 