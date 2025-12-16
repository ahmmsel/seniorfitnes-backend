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
            font-weight: bold;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            border: none;
            width: 100%;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: #6c757d;
            margin-top: 10px;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #999;
        }

        .charge-id {
            font-family: 'Courier New', monospace;
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            display: inline-block;
            margin-top: 10px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .icon {
                width: 80px;
                height: 80px;
                font-size: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @if($status === 'CAPTURED')
            <div class="icon success">✓</div>
            <h1>تم الدفع بنجاح!</h1>
            <p class="status-message">
                تمت معالجة عملية الدفع بنجاح. شكراً لك على استخدام Suniorfit!
            </p>
        @elseif($status === 'INITIATED' || $status === 'PENDING')
            <div class="icon pending">⏳</div>
            <h1>الدفع قيد المعالجة</h1>
            <p class="status-message">
                عملية الدفع الخاصة بك قيد المعالجة. سنقوم بإخطارك فور اكتمالها.
            </p>
        @else
            <div class="icon failed">✕</div>
            <h1>فشل الدفع</h1>
            <p class="status-message">
                عذراً، لم نتمكن من إتمام عملية الدفع. يرجى المحاولة مرة أخرى أو التواصل مع الدعم الفني.
            </p>
        @endif

        <div class="details">
            <div class="detail-row">
                <span class="detail-label">حالة الدفع:</span>
                <span class="detail-value">
                    @if($status === 'CAPTURED')
                        <span style="color: #28a745;">مكتمل</span>
                    @elseif($status === 'INITIATED' || $status === 'PENDING')
                        <span style="color: #ffc107;">قيد المعالجة</span>
                    @else
                        <span style="color: #dc3545;">فشل</span>
                    @endif
                </span>
            </div>
            
            @if(isset($amount) && $amount)
            <div class="detail-row">
                <span class="detail-label">المبلغ:</span>
                <span class="detail-value">{{ $amount }} {{ $currency ?? 'KWD' }}</span>
            </div>
            @endif

            @if(isset($reference) && $reference)
            <div class="detail-row">
                <span class="detail-label">رقم المرجع:</span>
                <span class="detail-value">{{ $reference }}</span>
            </div>
            @endif
        </div>

        @if($status === 'INITIATED' || $status === 'PENDING')
            <p style="color: #666; font-size: 14px; margin-top: 15px;">
                يمكنك متابعة حالة الدفع من خلال حسابك على التطبيق
            </p>
        @endif

        @if(isset($chargeId) && $chargeId)
        <div class="charge-id">
            معرف العملية: {{ $chargeId }}
        </div>
        @endif

        <div class="footer">
            <p>© {{ date('Y') }} Suniorfit. جميع الحقوق محفوظة.</p>
        </div>
    </div>

    <script>
        // No app deep-linking or auto-redirects — links go back to the website.
        // Keep this empty to avoid attempting to open the native app from the browser.
    </script>
</body>
</html>
