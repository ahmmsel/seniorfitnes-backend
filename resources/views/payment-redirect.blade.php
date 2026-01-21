<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حالة الدفع - Suniorfit</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
        }

        .icon.success {
            background: #d4edda;
            color: #28a745;
            animation: scaleIn 0.5s ease-out 0.3s both;
        }

        .icon.failed {
            background: #f8d7da;
            color: #dc3545;
            animation: scaleIn 0.5s ease-out 0.3s both;
        }

        .icon.pending {
            background: #fff3cd;
            color: #ffc107;
            animation: scaleIn 0.5s ease-out 0.3s both;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        h1 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #333;
        }

        .status-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: right;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: bold;
            color: #495057;
        }

        .detail-value {
            color: #6c757d;
        }

        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 16px;
            < !DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>حالة الدفع - Suniorfit</title><style>

            /* Dark mode layout with primary color #A7D849 */
            :root {
                --bg: #0b0f12;
                /* dark background */
                --card: #0f1416;
                --muted: #9aa3a6;
                --primary: #A7D849;
                /* provided primary color */
                --danger: #dc3545;
                --success: #28a745;
                --accent-on-primary: #000000;
                /* black text when using primary background */
            }

            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            html,
            body {
                height: 100%;
            }

            body {
                background: var(--bg);
                color: #e6eef0;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .container {
                width: 100%;
                max-width: 560px;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
                border-radius: 14px;
                padding: 28px;
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
                text-align: center;
            }

            .icon {
                width: 84px;
                height: 84px;
                border-radius: 20px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 44px;
                margin-bottom: 18px;
            }

            .icon.success {
                background: var(--primary);
                color: var(--accent-on-primary);
            }

            .icon.pending {
                background: #f1c40f;
                color: #000;
            }

            .icon.failed {
                background: var(--danger);
                color: #fff;
            }

            h1 {
                font-size: 22px;
                margin-bottom: 10px;
                color: #ffffff;
            }

            .status-message {
                color: var(--muted);
                font-size: 15px;
                margin-bottom: 18px;
                line-height: 1.5;
            }

            .details {
                background: rgba(255, 255, 255, 0.02);
                border-radius: 10px;
                padding: 16px;
                text-align: right;
                margin-bottom: 18px;
                color: #c7d0d2;
            }

            .detail-row {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            }

            .detail-row:last-child {
                border-bottom: none;
            }

            .detail-label {
                color: #b9c3c5;
                font-weight: 600;
            }

            .detail-value {
                color: #e6eef0;
            }

            .charge-id {
                display: inline-block;
                margin-top: 12px;
                padding: 8px 12px;
                border-radius: 8px;
                background: rgba(255, 255, 255, 0.02);
                font-family: 'Courier New', monospace;
                color: #e6eef0;
            }

            .btn {
                display: block;
                width: 100%;
                padding: 14px 18px;
                border-radius: 12px;
                background: var(--primary);
                color: var(--accent-on-primary);
                font-weight: 700;
                border: none;
                margin-top: 16px;
                cursor: default;
                /* informational only */
                text-decoration: none;
            }

            .footer {
                margin-top: 20px;
                color: var(--muted);
                font-size: 13px;
            }

            @media (max-width: 480px) {
                .container {
                    padding: 20px;
                }

                .icon {
                    width: 72px;
                    height: 72px;
                    font-size: 36px;
                }
            }
    </style>
</head>

<body>
    <div class="container">
        @if ($status === 'CAPTURED')
            <div class="icon success">✓</div>
            <h1>تم الدفع بنجاح</h1>
            <p class="status-message">لقد تم تأكيد عملية الدفع بنجاح. يمكنك العودة للتطبيق لمتابعة حسابك.</p>
        @elseif($status === 'INITIATED' || $status === 'PENDING')
            <div class="icon pending">⏳</div>
            <h1>الدفع قيد المعالجة</h1>
            <p class="status-message">عملية الدفع حالياً قيد المعالجة. سنقوم بتحديث حالتها تلقائياً عند اكتمالها.</p>
        @else
            <div class="icon failed">✕</div>
            <h1>فشل الدفع</h1>
            <p class="status-message">تعذر إتمام عملية الدفع. الرجاء المحاولة مرة أخرى أو التواصل مع الدعم.</p>
        @endif

        <div class="details" role="status" aria-live="polite">
            <div class="detail-row">
                <div class="detail-label">حالة الدفع</div>
                <div class="detail-value">
                    @if ($status === 'CAPTURED')
                        مكتمل
                    @elseif($status === 'INITIATED' || $status === 'PENDING')
                        قيد المعالجة
                    @else
                        فشل
                    @endif
                </div>
            </div>

            @if (isset($amount) && $amount)
                <div class="detail-row">
                    <div class="detail-label">المبلغ</div>
                    <div class="detail-value">{{ $amount }} {{ $currency ?? 'KWD' }}</div>
                </div>
            @endif

            @if (isset($reference) && $reference)
                <div class="detail-row">
                    <div class="detail-label">المرجع</div>
                    <div class="detail-value">{{ $reference }}</div>
                </div>
            @endif
        </div>

        @if (isset($chargeId) && $chargeId)
            <div class="charge-id">معرف العملية: {{ $chargeId }}</div>
        @endif

        <button class="btn" disabled>معلومات الدفع فقط — لا يتم إعادة توجيه</button>

        <div class="footer">© {{ date('Y') }} Suniorfit. جميع الحقوق محفوظة.</div>
    </div>

</body>

</html>
