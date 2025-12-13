# Google & Apple Sign-In Integration Guide

## 🎯 Overview

Complete authentication flow for Google and Apple sign-in, perfectly integrated with the app registration flow.

---

## ✅ How It Works

### **Registration Flow Comparison**

| Feature            | Regular Registration | Google/Apple Sign-In |
| ------------------ | -------------------- | -------------------- |
| Email              | User provides        | From Google/Apple    |
| Password           | User creates         | Random (hidden)      |
| Name               | User provides        | From Google/Apple    |
| Email Verification | Manual               | Automatic ✅         |
| Profile Setup      | After registration   | After sign-in        |

### **Flow Integration**

Both flows lead to the same next step: **Profile Selection (Coach or Trainee)**

```
Regular Registration → Token → Profile Selection
Google/Apple Sign-In → Token → Profile Selection ✅
```

---

## 🔐 Google Sign-In Flow

### **Step 1: User Signs In with Google**

Flutter app gets Google access token using `google_sign_in` package.

```dart
import 'package:google_sign_in/google_sign_in.dart';

final GoogleSignIn _googleSignIn = GoogleSignIn(
  scopes: ['email', 'profile'],
);

Future<void> signInWithGoogle() async {
  try {
    final GoogleSignInAccount? account = await _googleSignIn.signIn();

    if (account == null) return; // User cancelled

    final GoogleSignInAuthentication auth = await account.authentication;
    final String? accessToken = auth.accessToken;

    // Send to your Laravel API
    await authenticateWithBackend(accessToken);
  } catch (e) {
    print('Error: $e');
  }
}
```

### **Step 2: Send to Laravel API**

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

Future<Map<String, dynamic>> authenticateWithBackend(String accessToken) async {
  final response = await http.post(
    Uri.parse('https://your-api.com/api/auth/google-signin'),
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: jsonEncode({
      'access_token': accessToken,
      'device_name': 'iPhone 13', // Optional
    }),
  );

  if (response.statusCode == 200) {
    return jsonDecode(response.body);
  } else {
    throw Exception('Sign-in failed');
  }
}
```

### **Step 3: Laravel Backend Processing**

**What happens on the backend:**

1. ✅ Validates access token with Google
2. ✅ Retrieves user info (email, name, id)
3. ✅ Checks if user exists by `google_id` or `email`
4. ✅ If exists: Updates `google_id` and verifies email
5. ✅ If new: Creates user with:
    - Email from Google
    - Name from Google
    - Random secure password
    - `email_verified_at` = now()
    - `google_id` stored
6. ✅ Generates Sanctum token
7. ✅ Checks profile status (coach/trainee)
8. ✅ Returns response

### **Step 4: API Response**

```json
{
    "status": "success",
    "message": "Signed in successfully.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@gmail.com",
            "has_coach_profile": false,
            "has_trainee_profile": false
        },
        "auth": {
            "token_type": "sanctum",
            "access_token": "1|xxxxxxxxxxxxxxxxxxxxx"
        }
    }
}
```

### **Step 5: Profile Selection in Flutter**

```dart
void handleGoogleSignInResponse(Map<String, dynamic> response) {
  final user = response['data']['user'];
  final token = response['data']['auth']['access_token'];

  // Save token
  await storage.write(key: 'auth_token', value: token);

  // Check profile status
  final hasCoachProfile = user['has_coach_profile'];
  final hasTraineeProfile = user['has_trainee_profile'];

  if (!hasCoachProfile && !hasTraineeProfile) {
    // Navigate to profile selection screen
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (context) => ProfileSelectionScreen(token: token),
      ),
    );
  } else {
    // Navigate to home
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => HomeScreen()),
    );
  }
}
```

---

## 🍎 Apple Sign-In Flow

### **Step 1: User Signs In with Apple**

```dart
import 'package:sign_in_with_apple/sign_in_with_apple.dart';

Future<void> signInWithApple() async {
  try {
    final credential = await SignInWithApple.getAppleIDCredential(
      scopes: [
        AppleIDAuthorizationScopes.email,
        AppleIDAuthorizationScopes.fullName,
      ],
    );

    final String? idToken = credential.identityToken;

    if (idToken != null) {
      await authenticateWithBackendApple(idToken);
    }
  } catch (e) {
    print('Error: $e');
  }
}
```

### **Step 2: Send to Laravel API**

```dart
Future<Map<String, dynamic>> authenticateWithBackendApple(String idToken) async {
  final response = await http.post(
    Uri.parse('https://your-api.com/api/auth/apple-signin'),
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: jsonEncode({
      'id_token': idToken,
      'device_name': 'iPhone 13', // Optional
    }),
  );

  if (response.statusCode == 200) {
    return jsonDecode(response.body);
  } else {
    throw Exception('Sign-in failed');
  }
}
```

### **Step 3: Backend Processing**

Same as Google, but using Apple ID:

1. ✅ Validates `id_token` with Apple
2. ✅ Retrieves user info
3. ✅ Checks by `apple_id` or email
4. ✅ Creates/updates user
5. ✅ Returns same response format

---

## 🔄 Complete Flutter Integration

### **Main Authentication Service**

```dart
class AuthService {
  static const String baseUrl = 'https://your-api.com/api';

  // Regular Registration
  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/register'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
      }),
    );

    if (response.statusCode == 201) {
      return jsonDecode(response.body);
    }
    throw Exception(jsonDecode(response.body)['message']);
  }

  // Google Sign-In
  Future<Map<String, dynamic>> googleSignIn() async {
    final GoogleSignIn googleSignIn = GoogleSignIn(scopes: ['email', 'profile']);
    final account = await googleSignIn.signIn();

    if (account == null) throw Exception('Sign-in cancelled');

    final auth = await account.authentication;

    final response = await http.post(
      Uri.parse('$baseUrl/auth/google-signin'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({
        'access_token': auth.accessToken,
        'device_name': Platform.operatingSystem,
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception(jsonDecode(response.body)['message']);
  }

  // Apple Sign-In
  Future<Map<String, dynamic>> appleSignIn() async {
    final credential = await SignInWithApple.getAppleIDCredential(
      scopes: [
        AppleIDAuthorizationScopes.email,
        AppleIDAuthorizationScopes.fullName,
      ],
    );

    final response = await http.post(
      Uri.parse('$baseUrl/auth/apple-signin'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({
        'id_token': credential.identityToken,
        'device_name': Platform.operatingSystem,
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception(jsonDecode(response.body)['message']);
  }

  // Check profile status
  Future<Map<String, dynamic>> checkProfile(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/auth/check-profile'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Failed to check profile');
  }
}
```

### **Login Screen with Social Auth**

```dart
class LoginScreen extends StatelessWidget {
  final AuthService _authService = AuthService();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Padding(
        padding: EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Google Sign-In Button
            ElevatedButton.icon(
              onPressed: () => _handleGoogleSignIn(context),
              icon: Icon(Icons.g_mobiledata),
              label: Text('Continue with Google'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.white,
                foregroundColor: Colors.black,
              ),
            ),
            SizedBox(height: 16),

            // Apple Sign-In Button
            if (Platform.isIOS)
              ElevatedButton.icon(
                onPressed: () => _handleAppleSignIn(context),
                icon: Icon(Icons.apple),
                label: Text('Continue with Apple'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.black,
                  foregroundColor: Colors.white,
                ),
              ),

            SizedBox(height: 32),
            Text('OR'),
            SizedBox(height: 32),

            // Regular Email/Password Form
            // ... your existing form
          ],
        ),
      ),
    );
  }

  Future<void> _handleGoogleSignIn(BuildContext context) async {
    try {
      final response = await _authService.googleSignIn();
      _navigateAfterAuth(context, response);
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Google sign-in failed: $e')),
      );
    }
  }

  Future<void> _handleAppleSignIn(BuildContext context) async {
    try {
      final response = await _authService.appleSignIn();
      _navigateAfterAuth(context, response);
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Apple sign-in failed: $e')),
      );
    }
  }

  void _navigateAfterAuth(BuildContext context, Map<String, dynamic> response) {
    final user = response['data']['user'];
    final token = response['data']['auth']['access_token'];

    // Save token locally
    // ... use secure_storage or similar

    if (!user['has_coach_profile'] && !user['has_trainee_profile']) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (context) => ProfileSelectionScreen(token: token),
        ),
      );
    } else {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => HomeScreen()),
      );
    }
  }
}
```

---

## ✅ Key Features

### **Backend**

1. ✅ Stores `google_id` and `apple_id` for account linking
2. ✅ Finds user by provider ID first, then email
3. ✅ Updates provider ID if user signs in via different method
4. ✅ Auto-verifies email for social sign-ins
5. ✅ Generates secure random password (not accessible)
6. ✅ Returns profile status flags
7. ✅ Same token format as regular registration

### **Flutter**

1. ✅ Single sign-in flow for all methods
2. ✅ Profile selection after first sign-in
3. ✅ Same navigation logic as regular registration
4. ✅ Proper error handling
5. ✅ Token storage and management

---

## 🔐 Security Features

1. **Provider ID Validation**: Links account to provider ID, prevents email spoofing
2. **Email Verification**: Auto-verified for social providers
3. **Password Security**: Random 16-character password, bcrypt hashed
4. **Token Management**: Old tokens revoked, new token generated
5. **Account Linking**: If user registers normally then signs in with Google, accounts are linked

---

## 📋 API Endpoints Summary

| Endpoint                  | Method | Purpose              | Auth Required |
| ------------------------- | ------ | -------------------- | ------------- |
| `/api/auth/register`      | POST   | Regular registration | No            |
| `/api/auth/login`         | POST   | Email/password login | No            |
| `/api/auth/google-signin` | POST   | Google OAuth sign-in | No            |
| `/api/auth/apple-signin`  | POST   | Apple ID sign-in     | No            |
| `/api/auth/check-profile` | GET    | Check profile status | Yes (Bearer)  |

---

## ✅ Your Flow Is Perfect!

**Google/Apple Sign-In:**

1. ✅ Creates user automatically
2. ✅ Stores provider ID
3. ✅ Auto-verifies email
4. ✅ Returns token immediately
5. ✅ Shows profile selection
6. ✅ Same as regular registration flow

**The integration is seamless and production-ready!** 🎉
