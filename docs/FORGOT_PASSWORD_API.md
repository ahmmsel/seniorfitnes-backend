# Forgot Password API Documentation

## 🔐 Overview

Complete password reset flow with email notifications. Users can request a password reset link via email and set a new password using the token.

---

## 📍 API Endpoints

### 1. Request Password Reset

**Endpoint:** `POST /api/auth/forgot-password`

**Description:** Send a password reset link to the user's email.

**Request Body:**

```json
{
    "email": "user@example.com"
}
```

**Success Response (200):**

```json
{
    "status": "success",
    "message": "Password reset link has been sent to your email."
}
```

**Error Response (422):**

```json
{
    "status": "error",
    "message": "The email field is required.",
    "errors": {
        "email": ["No account found with this email address."]
    }
}
```

---

### 2. Reset Password

**Endpoint:** `POST /api/auth/reset-password`

**Description:** Reset user password using the token received via email.

**Request Body:**

```json
{
    "token": "abc123xyz...",
    "email": "user@example.com",
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
}
```

**Success Response (200):**

```json
{
    "status": "success",
    "message": "Password has been reset successfully. Please login with your new password."
}
```

**Error Responses:**

**Invalid/Expired Token (400):**

```json
{
    "status": "error",
    "message": "Invalid or expired reset token."
}
```

**Token Expired (400):**

```json
{
    "status": "error",
    "message": "Reset token has expired. Please request a new one."
}
```

**Validation Error (422):**

```json
{
    "status": "error",
    "message": "The password field is required.",
    "errors": {
        "password": [
            "Password must be at least 8 characters.",
            "Password confirmation does not match."
        ]
    }
}
```

---

## 🔧 Configuration

### Email Configuration

Update `.env` file with your email provider settings:

```env
# For production (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@suniorfit.com"
MAIL_FROM_NAME="SuniorFit"

# For development (log emails to file)
MAIL_MAILER=log
```

### Token Expiry

Set password reset token expiry time in `config/auth.php`:

```php
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60, // Minutes (default: 60)
        'throttle' => 60,
    ],
],
```

### Frontend URL

Set your Flutter app's deep link or web URL in `.env`:

```env
# For mobile app deep links
FRONTEND_URL=suniorfit://

# For web app
FRONTEND_URL=https://app.suniorfit.com
```

The reset link will be formatted as:

```
{FRONTEND_URL}/reset-password?token={token}&email={email}
```

---

## 📱 Flutter Integration

### Setup

Add HTTP package to `pubspec.yaml`:

```yaml
dependencies:
    http: ^1.1.0
    url_launcher: ^6.2.1 # For opening reset link from email
```

### 1. Forgot Password Screen

```dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class ForgotPasswordScreen extends StatefulWidget {
  @override
  _ForgotPasswordScreenState createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  bool _isLoading = false;
  String? _message;
  bool _isSuccess = false;

  Future<void> _submitForgotPassword() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _message = null;
    });

    try {
      final response = await http.post(
        Uri.parse('https://your-api.com/api/auth/forgot-password'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': _emailController.text.trim(),
        }),
      );

      final data = jsonDecode(response.body);

      setState(() {
        _isLoading = false;
        _message = data['message'];
        _isSuccess = response.statusCode == 200;
      });

      if (_isSuccess) {
        // Show success dialog
        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            title: Text('Check Your Email'),
            content: Text(
              'We\'ve sent a password reset link to ${_emailController.text}. '
              'Please check your email and follow the instructions.',
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.of(context).pop();
                  Navigator.of(context).pop(); // Back to login
                },
                child: Text('OK'),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      setState(() {
        _isLoading = false;
        _message = 'An error occurred. Please try again.';
        _isSuccess = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Forgot Password')),
      body: Padding(
        padding: EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Icon(
                Icons.lock_reset,
                size: 80,
                color: Theme.of(context).primaryColor,
              ),
              SizedBox(height: 32),
              Text(
                'Reset Your Password',
                style: Theme.of(context).textTheme.headlineSmall,
                textAlign: TextAlign.center,
              ),
              SizedBox(height: 16),
              Text(
                'Enter your email address and we\'ll send you a link to reset your password.',
                style: Theme.of(context).textTheme.bodyMedium,
                textAlign: TextAlign.center,
              ),
              SizedBox(height: 32),
              TextFormField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                  labelText: 'Email Address',
                  prefixIcon: Icon(Icons.email),
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter your email';
                  }
                  if (!value.contains('@')) {
                    return 'Please enter a valid email';
                  }
                  return null;
                },
              ),
              SizedBox(height: 16),
              if (_message != null)
                Container(
                  padding: EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: _isSuccess ? Colors.green.shade50 : Colors.red.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: _isSuccess ? Colors.green : Colors.red,
                    ),
                  ),
                  child: Text(
                    _message!,
                    style: TextStyle(
                      color: _isSuccess ? Colors.green.shade900 : Colors.red.shade900,
                    ),
                  ),
                ),
              SizedBox(height: 24),
              ElevatedButton(
                onPressed: _isLoading ? null : _submitForgotPassword,
                style: ElevatedButton.styleFrom(
                  padding: EdgeInsets.symmetric(vertical: 16),
                ),
                child: _isLoading
                    ? CircularProgressIndicator(color: Colors.white)
                    : Text('Send Reset Link'),
              ),
              SizedBox(height: 16),
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: Text('Back to Login'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    super.dispose();
  }
}
```

### 2. Reset Password Screen

```dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class ResetPasswordScreen extends StatefulWidget {
  final String token;
  final String email;

  const ResetPasswordScreen({
    required this.token,
    required this.email,
  });

  @override
  _ResetPasswordScreenState createState() => _ResetPasswordScreenState();
}

class _ResetPasswordScreenState extends State<ResetPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  Future<void> _submitResetPassword() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final response = await http.post(
        Uri.parse('https://your-api.com/api/auth/reset-password'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'token': widget.token,
          'email': widget.email,
          'password': _passwordController.text,
          'password_confirmation': _confirmPasswordController.text,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200) {
        // Success - show dialog and navigate to login
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => AlertDialog(
            title: Text('Success!'),
            content: Text('Your password has been reset successfully. Please login with your new password.'),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.of(context).pop();
                  // Navigate to login screen
                  Navigator.of(context).pushNamedAndRemoveUntil(
                    '/login',
                    (route) => false,
                  );
                },
                child: Text('Go to Login'),
              ),
            ],
          ),
        );
      } else {
        // Error
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(data['message'] ?? 'Failed to reset password'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('An error occurred. Please try again.'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Reset Password')),
      body: Padding(
        padding: EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Icon(
                Icons.lock_reset,
                size: 80,
                color: Theme.of(context).primaryColor,
              ),
              SizedBox(height: 32),
              Text(
                'Create New Password',
                style: Theme.of(context).textTheme.headlineSmall,
                textAlign: TextAlign.center,
              ),
              SizedBox(height: 32),
              TextFormField(
                controller: _passwordController,
                obscureText: _obscurePassword,
                decoration: InputDecoration(
                  labelText: 'New Password',
                  prefixIcon: Icon(Icons.lock),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscurePassword ? Icons.visibility : Icons.visibility_off,
                    ),
                    onPressed: () {
                      setState(() => _obscurePassword = !_obscurePassword);
                    },
                  ),
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter a password';
                  }
                  if (value.length < 8) {
                    return 'Password must be at least 8 characters';
                  }
                  return null;
                },
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _confirmPasswordController,
                obscureText: _obscureConfirmPassword,
                decoration: InputDecoration(
                  labelText: 'Confirm Password',
                  prefixIcon: Icon(Icons.lock_outline),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscureConfirmPassword ? Icons.visibility : Icons.visibility_off,
                    ),
                    onPressed: () {
                      setState(() => _obscureConfirmPassword = !_obscureConfirmPassword);
                    },
                  ),
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value != _passwordController.text) {
                    return 'Passwords do not match';
                  }
                  return null;
                },
              ),
              SizedBox(height: 24),
              ElevatedButton(
                onPressed: _isLoading ? null : _submitResetPassword,
                style: ElevatedButton.styleFrom(
                  padding: EdgeInsets.symmetric(vertical: 16),
                ),
                child: _isLoading
                    ? CircularProgressIndicator(color: Colors.white)
                    : Text('Reset Password'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }
}
```

### 3. Deep Link Handler

Configure deep links to open the reset password screen from email.

**AndroidManifest.xml:**

```xml
<intent-filter>
    <action android:name="android.intent.action.VIEW" />
    <category android:name="android.intent.category.DEFAULT" />
    <category android:name="android.intent.category.BROWSABLE" />
    <data
        android:scheme="suniorfit"
        android:host="reset-password" />
</intent-filter>
```

**Info.plist (iOS):**

```xml
<key>CFBundleURLTypes</key>
<array>
    <dict>
        <key>CFBundleTypeRole</key>
        <string>Editor</string>
        <key>CFBundleURLSchemes</key>
        <array>
            <string>suniorfit</string>
        </array>
    </dict>
</array>
```

**Main App Router:**

```dart
import 'package:uni_links/uni_links.dart';
import 'dart:async';

class MyApp extends StatefulWidget {
  @override
  _MyAppState createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  StreamSubscription? _sub;

  @override
  void initState() {
    super.initState();
    _handleIncomingLinks();
    _handleInitialLink();
  }

  void _handleIncomingLinks() {
    _sub = uriLinkStream.listen((Uri? uri) {
      if (uri != null) {
        _handleDeepLink(uri);
      }
    });
  }

  Future<void> _handleInitialLink() async {
    try {
      final uri = await getInitialUri();
      if (uri != null) {
        _handleDeepLink(uri);
      }
    } catch (e) {
      // Handle error
    }
  }

  void _handleDeepLink(Uri uri) {
    if (uri.path == '/reset-password') {
      final token = uri.queryParameters['token'];
      final email = uri.queryParameters['email'];

      if (token != null && email != null) {
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (context) => ResetPasswordScreen(
              token: token,
              email: email,
            ),
          ),
        );
      }
    }
  }

  @override
  void dispose() {
    _sub?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      // Your app configuration
    );
  }
}
```

---

## 🔒 Security Features

1. **Token Hashing**: Reset tokens are hashed before storing in database
2. **Expiration**: Tokens expire after 60 minutes (configurable)
3. **One-Time Use**: Tokens are deleted after successful password reset
4. **Token Revocation**: All user sessions (tokens) are revoked after password reset
5. **Email Validation**: Only existing emails can request password reset
6. **Rate Limiting**: Built-in Laravel throttling (1 request per 60 seconds)

---

## 📧 Email Template

The system sends a professional email with:

-   User's name greeting
-   Clear explanation
-   Prominent "Reset Password" button
-   Expiration time notice
-   Security disclaimer

The email is sent via the `ResetPasswordNotification` class using Laravel's notification system.

---

## 🧪 Testing

### Manual Testing with API Client (Postman/Insomnia)

**1. Request Reset:**

```bash
POST http://your-api.com/api/auth/forgot-password
Content-Type: application/json

{
  "email": "test@example.com"
}
```

**2. Check Email Logs:**

```bash
php artisan tinker

# View last email sent (if using log driver)
tail -f storage/logs/laravel.log
```

**3. Reset Password:**

```bash
POST http://your-api.com/api/auth/reset-password
Content-Type: application/json

{
  "token": "token-from-email",
  "email": "test@example.com",
  "password": "NewPassword123!",
  "password_confirmation": "NewPassword123!"
}
```

---

## ✅ Feature Complete

**Backend:**

-   ✅ Password reset tokens table
-   ✅ Request validation (ForgotPasswordRequest, ResetPasswordRequest)
-   ✅ Email notification with reset link
-   ✅ Token generation and verification
-   ✅ Password update logic
-   ✅ Token expiration handling
-   ✅ Session revocation after reset

**API Endpoints:**

-   ✅ POST `/api/auth/forgot-password`
-   ✅ POST `/api/auth/reset-password`

**Security:**

-   ✅ Token hashing
-   ✅ Expiration (60 minutes)
-   ✅ One-time use tokens
-   ✅ Email verification
-   ✅ Password strength validation

**Ready for production!** 🚀
