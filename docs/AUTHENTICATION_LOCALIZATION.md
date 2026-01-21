# Authentication Error Handling - Arabic Translation Guide

## Overview
All authentication error messages now support Arabic localization. The application automatically detects the user's preferred language from the `Accept-Language` header or defaults to Arabic.

## Language Files

### Arabic: `lang/ar/auth.php`
Contains all authentication messages in Arabic.

### English: `lang/en/auth.php`
Contains all authentication messages in English.

## How It Works

### 1. Locale Detection
The `SetLocale` middleware automatically sets the application locale based on:
- `Accept-Language` header (e.g., `ar`, `en`)
- Query parameter `?lang=ar` or `?lang=en`
- Default: Arabic (`ar`)

### 2. Usage in Code
Controllers and services use translation keys:
```php
__('auth.invalid_credentials')  // Returns message in current locale
```

## API Request Examples

### Setting Language via Header
```bash
# Request in Arabic (default)
curl -X POST https://api.suniorfit.com/api/auth/login \
  -H "Accept-Language: ar" \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "wrong"}'

# Response
{
  "message": "بيانات الاعتماد غير صحيحة."
}
```

```bash
# Request in English
curl -X POST https://api.suniorfit.com/api/auth/login \
  -H "Accept-Language: en" \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "wrong"}'

# Response
{
  "message": "Invalid credentials."
}
```

### Setting Language via Query Parameter
```bash
# Arabic
GET /api/tracking/history?lang=ar

# English  
GET /api/tracking/history?lang=en
```

## Available Translation Keys

### General Authentication
| Key | Arabic | English |
|-----|--------|---------|
| `signed_in_successfully` | تم تسجيل الدخول بنجاح. | Signed in successfully. |
| `registered_successfully` | تم التسجيل بنجاح. | Registered successfully. |
| `logged_out_successfully` | تم تسجيل الخروج بنجاح. | Logged out successfully. |
| `invalid_credentials` | بيانات الاعتماد غير صحيحة. | Invalid credentials. |
| `unauthenticated` | غير مصرح. | Unauthenticated. |
| `unauthorized` | غير مصرح لك بالوصول. | Unauthorized. |

### Password Reset
| Key | Arabic | English |
|-----|--------|---------|
| `password_reset_link_sent` | تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني. | Password reset link has been sent to your email. |
| `password_reset_successfully` | تم إعادة تعيين كلمة المرور بنجاح. يرجى تسجيل الدخول بكلمة المرور الجديدة. | Password has been reset successfully. Please login with your new password. |
| `invalid_reset_token` | رمز إعادة التعيين غير صالح أو منتهي الصلاحية. | Invalid or expired reset token. |
| `reset_token_expired` | انتهت صلاحية رمز إعادة التعيين. يرجى طلب رمز جديد. | Reset token has expired. Please request a new one. |

### Profile Errors
| Key | Arabic | English |
|-----|--------|---------|
| `profile_not_found` | الملف الشخصي غير موجود. | Profile not found. |
| `trainee_profile_not_found` | الملف الشخصي للمتدرب غير موجود. | Trainee profile not found. |
| `coach_profile_not_found` | الملف الشخصي للمدرب غير موجود. | Coach profile not found. |

### Validation Errors
| Key | Arabic | English |
|-----|--------|---------|
| `email_required` | البريد الإلكتروني مطلوب. | Email is required. |
| `email_invalid` | البريد الإلكتروني غير صالح. | Invalid email format. |
| `email_already_exists` | البريد الإلكتروني مسجل مسبقاً. | Email already exists. |
| `password_required` | كلمة المرور مطلوبة. | Password is required. |
| `password_min_length` | يجب أن تكون كلمة المرور 8 أحرف على الأقل. | Password must be at least 8 characters. |
| `name_required` | الاسم مطلوب. | Name is required. |

## Error Response Examples

### Login - Invalid Credentials

**Arabic:**
```json
{
  "message": "بيانات الاعتماد غير صحيحة."
}
```

**English:**
```json
{
  "message": "Invalid credentials."
}
```

### Password Reset - Success

**Arabic:**
```json
{
  "status": "success",
  "message": "تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني."
}
```

**English:**
```json
{
  "status": "success",
  "message": "Password reset link has been sent to your email."
}
```

### Password Reset - Invalid Token

**Arabic:**
```json
{
  "status": "error",
  "message": "رمز إعادة التعيين غير صالح أو منتهي الصلاحية."
}
```

**English:**
```json
{
  "status": "error",
  "message": "Invalid or expired reset token."
}
```

## Flutter Implementation

### Setting Language in API Requests

```dart
// In your API service
class ApiService {
  final String locale; // 'ar' or 'en'
  
  Future<Map<String, String>> _getHeaders() async {
    final token = await authService.getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Accept-Language': locale, // Set the language
      'Authorization': 'Bearer $token',
    };
  }
}
```

### Handling Localized Errors

```dart
try {
  await apiService.login(email, password);
} catch (e) {
  if (e is ApiException) {
    // Error message is already localized based on Accept-Language
    showErrorDialog(e.message); // Will show Arabic or English message
  }
}
```

### Dynamic Language Switching

```dart
class ApiService {
  String _locale = 'ar'; // Default to Arabic
  
  void setLocale(String locale) {
    _locale = locale;
  }
  
  Future<Response> _makeRequest() async {
    final headers = {
      'Accept-Language': _locale,
      // ... other headers
    };
    // ... make request
  }
}

// Usage
apiService.setLocale('en'); // Switch to English
apiService.setLocale('ar'); // Switch to Arabic
```

## Testing

### Test Language Detection
```bash
# Test Arabic (default)
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com", "password": "wrong"}'

# Test with English header
curl -X POST http://localhost:8000/api/auth/login \
  -H "Accept-Language: en" \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com", "password": "wrong"}'

# Test with query parameter
curl "http://localhost:8000/api/auth/login?lang=en" \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com", "password": "wrong"}'
```

## Adding New Translations

### 1. Add to Language Files
Add the key to both `lang/ar/auth.php` and `lang/en/auth.php`:

```php
// lang/ar/auth.php
'new_error_key' => 'رسالة الخطأ بالعربية',

// lang/en/auth.php
'new_error_key' => 'Error message in English',
```

### 2. Use in Code
```php
throw new Exception(__('auth.new_error_key'));
```

## Middleware Configuration

The `SetLocale` middleware is registered in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(prepend: [
        \App\Http\Middleware\SetLocale::class,
    ]);
})
```

This ensures every API request automatically sets the correct locale before processing.

## Default Locale

The default locale is set in `config/app.php`:

```php
'locale' => env('APP_LOCALE', 'ar'),
'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
```

## Best Practices

1. **Always use translation keys** instead of hardcoded messages
2. **Keep translations consistent** across both languages
3. **Test with both languages** before deploying
4. **Document new translation keys** when adding them
5. **Use meaningful key names** that describe the error

## Complete List of Files Modified

- ✅ `lang/ar/auth.php` - Arabic translations
- ✅ `lang/en/auth.php` - English translations
- ✅ `app/Http/Middleware/SetLocale.php` - Locale detection middleware
- ✅ `app/Http/Controllers/Api/AuthController.php` - Uses translation keys
- ✅ `app/Services/AuthService.php` - Uses translation keys
- ✅ `bootstrap/app.php` - Middleware registration
- ✅ `config/app.php` - Default locale (ar)

## Support

For adding new translations or reporting translation issues, please update the respective language files in `lang/ar/` and `lang/en/`.
