<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user in Arabic.
    |
    */

    // General messages
    'signed_in_successfully' => 'تم تسجيل الدخول بنجاح.',
    'registered_successfully' => 'تم التسجيل بنجاح.',
    'logged_out_successfully' => 'تم تسجيل الخروج بنجاح.',

    // Login/Register errors
    'invalid_credentials' => 'بيانات الاعتماد غير صحيحة.',
    'unauthenticated' => 'غير مصرح.',
    'unauthorized' => 'غير مصرح لك بالوصول.',
    'account_disabled' => 'تم تعطيل حسابك.',

    // Email verification
    'email_already_verified' => 'البريد الإلكتروني مؤكد مسبقاً.',
    'email_verification_sent' => 'تم إرسال رابط التحقق إلى بريدك الإلكتروني.',
    'email_verified_successfully' => 'تم التحقق من البريد الإلكتروني بنجاح.',

    // Password reset
    'password_reset_link_sent' => 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.',
    'password_reset_successfully' => 'تم إعادة تعيين كلمة المرور بنجاح. يرجى تسجيل الدخول بكلمة المرور الجديدة.',
    'invalid_reset_token' => 'رمز إعادة التعيين غير صالح أو منتهي الصلاحية.',
    'reset_token_expired' => 'انتهت صلاحية رمز إعادة التعيين. يرجى طلب رمز جديد.',
    'password_changed_successfully' => 'تم تغيير كلمة المرور بنجاح.',

    // Social authentication
    'social_login_failed' => 'فشل تسجيل الدخول عبر :provider.',
    'invalid_social_token' => 'رمز :provider غير صالح.',
    'social_account_not_linked' => 'حساب :provider غير مرتبط.',

    // Profile-related
    'profile_not_found' => 'الملف الشخصي غير موجود.',
    'trainee_profile_not_found' => 'الملف الشخصي للمتدرب غير موجود.',
    'coach_profile_not_found' => 'الملف الشخصي للمدرب غير موجود.',

    // Token errors
    'token_expired' => 'انتهت صلاحية الرمز.',
    'token_invalid' => 'الرمز غير صالح.',
    'token_not_provided' => 'لم يتم توفير الرمز.',

    // Validation messages
    'email_required' => 'البريد الإلكتروني مطلوب.',
    'email_invalid' => 'البريد الإلكتروني غير صالح.',
    'email_already_exists' => 'البريد الإلكتروني مسجل مسبقاً.',
    'password_required' => 'كلمة المرور مطلوبة.',
    'password_min_length' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل.',
    'password_confirmation_mismatch' => 'كلمة المرور وتأكيدها غير متطابقين.',
    'name_required' => 'الاسم مطلوب.',

    // Device/Session
    'device_name_required' => 'اسم الجهاز مطلوب.',
    'session_expired' => 'انتهت صلاحية الجلسة.',
    'too_many_attempts' => 'محاولات كثيرة جداً. يرجى المحاولة مرة أخرى بعد :seconds ثانية.',
];
