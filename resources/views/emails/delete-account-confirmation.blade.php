<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد حذف الحساب</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.8em;
        }
        .content {
            padding: 30px;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .warning-box h3 {
            color: #856404;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 0.9em;
        }
        .data-list {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .data-list h4 {
            color: #495057;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .data-list ul {
            margin: 0;
            padding-right: 20px;
        }
        .data-list li {
            margin-bottom: 8px;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>تأكيد حذف الحساب</h1>
        </div>

        <div class="content">
            <p>مرحباً {{ $user->name ?? 'عزيزي المستخدم' }}،</p>

            <p>لقد تلقينا طلباً لحذف حسابك من تطبيق Car Wash App. قبل المتابعة، يرجى قراءة المعلومات التالية بعناية:</p>

            <div class="warning-box">
                <h3>⚠️ تنبيه مهم</h3>
                <p>عند تأكيد حذف الحساب، سيتم حذف جميع البيانات المرتبطة بك نهائياً ولا يمكن استردادها.</p>
            </div>

            <div class="data-list">
                <h4>البيانات التي سيتم حذفها:</h4>
                <ul>
                    <li>معلومات الحساب الشخصية (الاسم، البريد الإلكتروني، رقم الهاتف)</li>
                    <li>معلومات السيارات المسجلة</li>
                    <li>سجل الطلبات والخدمات السابقة</li>
                    <li>العناوين المحفوظة</li>
                    <li>تفضيلات الخدمة</li>
                    <li>جميع البيانات المرتبطة بالحساب</li>
                </ul>
            </div>

            @if($reason)
                <p><strong>سبب الحذف المذكور:</strong> {{ $reason }}</p>
            @endif

            <p>إذا كنت متأكداً من رغبتك في حذف الحساب، يرجى النقر على الزر أدناه:</p>

            <div style="text-align: center;">
                <a href="{{ $confirmationUrl }}" class="btn">
                    تأكيد حذف الحساب
                </a>
            </div>

            <p style="margin-top: 30px; color: #6c757d; font-size: 0.9em;">
                <strong>ملاحظة:</strong> هذا الرابط صالح لمدة 24 ساعة فقط. إذا لم تقم بتأكيد الحذف خلال هذه المدة، ستحتاج إلى تقديم طلب جديد.
            </p>

            <p style="color: #6c757d; font-size: 0.9em;">
                إذا لم تقم بطلب حذف الحساب، يرجى تجاهل هذا البريد الإلكتروني أو التواصل معنا فوراً.
            </p>
        </div>

        <div class="footer">
            <p>هذا البريد الإلكتروني تم إرساله من تطبيق Car Wash App</p>
            <p>للمساعدة: info@washluxuria.com | +971502711549</p>
        </div>
    </div>
</body>
</html> 