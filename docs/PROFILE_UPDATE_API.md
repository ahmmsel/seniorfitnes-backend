# Profile Update API Endpoints

## 📋 Overview

Complete API documentation for updating trainee and coach profiles with image upload support using POST requests.

---

## 👤 Trainee Profile Update

### **POST /api/trainee/profile/update**

Update authenticated trainee profile information with optional profile image.

#### **Headers**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
Accept: application/json
```

#### **Request Body (Form Data)**

| Field           | Type   | Required | Description    | Validation                                                          |
| --------------- | ------ | -------- | -------------- | ------------------------------------------------------------------- |
| `height`        | number | No       | Height in cm   | Min: 50, Max: 300                                                   |
| `weight`        | number | No       | Weight in kg   | Min: 20, Max: 500                                                   |
| `goal`          | string | No       | Fitness goal   | `lose_weight`, `build_muscle`, `improve_cardio`, `maintain_fitness` |
| `level`         | string | No       | Activity level | `sedentary`, `lightly_active`, `active`, `very_active`              |
| `body_type`     | string | No       | Body type      | `underweight`, `normal`, `overweight`, `obese`                      |
| `profile_image` | file   | No       | Profile image  | jpeg, jpg, png, webp (max 4MB)                                      |

#### **Example Request (Flutter)**

```dart
import 'package:http/http.dart' as http;
import 'dart:io';

Future<void> updateTraineeProfile({
  required String token,
  double? height,
  double? weight,
  String? goal,
  String? level,
  String? bodyType,
  File? profileImage,
}) async {
  final request = http.MultipartRequest(
    'POST',
    Uri.parse('https://your-api.com/api/trainee/profile/update'),
  );

  // Headers
  request.headers['Authorization'] = 'Bearer $token';
  request.headers['Accept'] = 'application/json';

  // Fields
  if (height != null) request.fields['height'] = height.toString();
  if (weight != null) request.fields['weight'] = weight.toString();
  if (goal != null) request.fields['goal'] = goal;
  if (level != null) request.fields['level'] = level;
  if (bodyType != null) request.fields['body_type'] = bodyType;

  // Profile image
  if (profileImage != null) {
    request.files.add(
      await http.MultipartFile.fromPath(
        'profile_image',
        profileImage.path,
      ),
    );
  }

  final response = await request.send();
  final responseData = await response.stream.bytesToString();

  if (response.statusCode == 200) {
    print('Profile updated successfully');
    print(responseData);
  } else {
    print('Error: ${response.statusCode}');
    print(responseData);
  }
}

// Usage
await updateTraineeProfile(
  token: userToken,
  height: 175.0,
  weight: 70.5,
  goal: 'build_muscle',
  level: 'active',
  bodyType: 'normal',
  profileImage: File('/path/to/image.jpg'),
);
```

#### **Example Request (cURL)**

```bash
curl -X POST https://your-api.com/api/trainee/profile/update \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "height=175" \
  -F "weight=70.5" \
  -F "goal=build_muscle" \
  -F "level=active" \
  -F "body_type=normal" \
  -F "profile_image=@/path/to/image.jpg"
```

#### **Success Response (200)**

```json
{
    "id": 1,
    "user_id": 1,
    "height": 175,
    "weight": 70.5,
    "goal": "build_muscle",
    "level": "active",
    "body_type": "normal",
    "profile_image_url": "https://your-api.com/storage/trainee/1/profile.jpg",
    "created_at": "2025-12-11T10:00:00.000000Z",
    "updated_at": "2025-12-11T10:30:00.000000Z"
}
```

#### **Validation Error Response (422)**

```json
{
    "message": "The height field must be between 50 and 300.",
    "errors": {
        "height": ["The height field must be between 50 and 300."],
        "profile_image": [
            "The profile image field must be a file of type: jpeg, jpg, png, webp."
        ]
    }
}
```

---

## 🎓 Coach Profile Update

### **POST /api/coach/profile/update**

Update authenticated coach profile information with optional profile image.

#### **Headers**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
Accept: application/json
```

#### **Request Body (Form Data)**

| Field                 | Type   | Required | Description           | Validation                     |
| --------------------- | ------ | -------- | --------------------- | ------------------------------ |
| `description`         | string | No       | Coach bio/description | Text                           |
| `specialty`           | string | No       | Coach specialty       | `nutrition`, `workout`, `both` |
| `years_of_experience` | number | No       | Years of experience   | Min: 0                         |
| `nutrition_price`     | number | No       | Nutrition plan price  | Min: 0                         |
| `workout_price`       | number | No       | Workout plan price    | Min: 0                         |
| `full_package_price`  | number | No       | Full package price    | Min: 0                         |
| `profile_image`       | file   | No       | Profile image         | jpeg, jpg, png, webp (max 4MB) |

#### **Example Request (Flutter)**

```dart
import 'package:http/http.dart' as http;
import 'dart:io';

Future<void> updateCoachProfile({
  required String token,
  String? description,
  String? specialty,
  int? yearsOfExperience,
  double? nutritionPrice,
  double? workoutPrice,
  double? fullPackagePrice,
  File? profileImage,
}) async {
  final request = http.MultipartRequest(
    'POST',
    Uri.parse('https://your-api.com/api/coach/profile/update'),
  );

  // Headers
  request.headers['Authorization'] = 'Bearer $token';
  request.headers['Accept'] = 'application/json';

  // Fields
  if (description != null) request.fields['description'] = description;
  if (specialty != null) request.fields['specialty'] = specialty;
  if (yearsOfExperience != null) {
    request.fields['years_of_experience'] = yearsOfExperience.toString();
  }
  if (nutritionPrice != null) {
    request.fields['nutrition_price'] = nutritionPrice.toString();
  }
  if (workoutPrice != null) {
    request.fields['workout_price'] = workoutPrice.toString();
  }
  if (fullPackagePrice != null) {
    request.fields['full_package_price'] = fullPackagePrice.toString();
  }

  // Profile image
  if (profileImage != null) {
    request.files.add(
      await http.MultipartFile.fromPath(
        'profile_image',
        profileImage.path,
      ),
    );
  }

  final response = await request.send();
  final responseData = await response.stream.bytesToString();

  if (response.statusCode == 200) {
    print('Coach profile updated successfully');
    print(responseData);
  } else {
    print('Error: ${response.statusCode}');
    print(responseData);
  }
}

// Usage
await updateCoachProfile(
  token: userToken,
  description: 'Certified fitness coach with passion for helping clients achieve their goals',
  specialty: 'both',
  yearsOfExperience: 5,
  nutritionPrice: 50.0,
  workoutPrice: 75.0,
  fullPackagePrice: 100.0,
  profileImage: File('/path/to/coach-photo.jpg'),
);
```

#### **Example Request (cURL)**

```bash
curl -X POST https://your-api.com/api/coach/profile/update \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "description=Certified fitness coach" \
  -F "specialty=both" \
  -F "years_of_experience=5" \
  -F "nutrition_price=50" \
  -F "workout_price=75" \
  -F "full_package_price=100" \
  -F "profile_image=@/path/to/coach-photo.jpg"
```

#### **Success Response (200)**

```json
{
    "id": 1,
    "user_id": 2,
    "description": "Certified fitness coach with passion for helping clients achieve their goals",
    "specialty": "both",
    "years_of_experience": 5,
    "nutrition_price": 50.0,
    "workout_price": 75.0,
    "full_package_price": 100.0,
    "profile_image_url": "https://your-api.com/storage/coach/2/profile.jpg",
    "created_at": "2025-12-11T10:00:00.000000Z",
    "updated_at": "2025-12-11T10:30:00.000000Z"
}
```

#### **Validation Error Response (422)**

```json
{
    "message": "The specialty field must be one of: nutrition, workout, both.",
    "errors": {
        "specialty": [
            "The specialty field must be one of: nutrition, workout, both."
        ],
        "nutrition_price": ["The nutrition price field must be at least 0."]
    }
}
```

---

## 📸 Image Upload Guidelines

### **Supported Formats**

-   JPEG (.jpeg, .jpg)
-   PNG (.png)
-   WebP (.webp)

### **Size Limits**

-   Maximum file size: **4MB** (4096 KB)

### **Recommendations**

-   Optimal resolution: **500x500** to **1000x1000** pixels
-   Square images work best for profile pictures
-   Compress images before upload for better performance

### **Error Handling**

**File too large:**

```json
{
    "message": "The profile image field must not be greater than 4096 kilobytes.",
    "errors": {
        "profile_image": [
            "The profile image field must not be greater than 4096 kilobytes."
        ]
    }
}
```

**Invalid format:**

```json
{
    "message": "The profile image field must be a file of type: jpeg, jpg, png, webp.",
    "errors": {
        "profile_image": [
            "The profile image field must be a file of type: jpeg, jpg, png, webp."
        ]
    }
}
```

---

## 🔐 Authentication

All profile update endpoints require authentication via **Sanctum Bearer token**.

```dart
// Get token after login
final loginResponse = await http.post(
  Uri.parse('https://your-api.com/api/auth/login'),
  body: {'email': 'user@example.com', 'password': 'password'},
);

final token = jsonDecode(loginResponse.body)['data']['token'];

// Use token in profile update
request.headers['Authorization'] = 'Bearer $token';
```

---

## ✅ Complete Flutter Example

### **Trainee Profile Update Screen**

```dart
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:http/http.dart' as http;
import 'dart:io';
import 'dart:convert';

class UpdateTraineeProfileScreen extends StatefulWidget {
  final String token;

  const UpdateTraineeProfileScreen({Key? key, required this.token}) : super(key: key);

  @override
  State<UpdateTraineeProfileScreen> createState() => _UpdateTraineeProfileScreenState();
}

class _UpdateTraineeProfileScreenState extends State<UpdateTraineeProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _heightController = TextEditingController();
  final _weightController = TextEditingController();

  String? _goal;
  String? _level;
  String? _bodyType;
  File? _profileImage;
  bool _isLoading = false;

  final _goals = [
    {'value': 'lose_weight', 'label': 'Lose Weight'},
    {'value': 'build_muscle', 'label': 'Build Muscle'},
    {'value': 'improve_cardio', 'label': 'Improve Cardio'},
    {'value': 'maintain_fitness', 'label': 'Maintain Fitness'},
  ];

  final _levels = [
    {'value': 'sedentary', 'label': 'Sedentary'},
    {'value': 'lightly_active', 'label': 'Lightly Active'},
    {'value': 'active', 'label': 'Active'},
    {'value': 'very_active', 'label': 'Very Active'},
  ];

  final _bodyTypes = [
    {'value': 'underweight', 'label': 'Underweight'},
    {'value': 'normal', 'label': 'Normal'},
    {'value': 'overweight', 'label': 'Overweight'},
    {'value': 'obese', 'label': 'Obese'},
  ];

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final pickedFile = await picker.pickImage(source: ImageSource.gallery);

    if (pickedFile != null) {
      setState(() {
        _profileImage = File(pickedFile.path);
      });
    }
  }

  Future<void> _updateProfile() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final request = http.MultipartRequest(
        'POST',
        Uri.parse('https://your-api.com/api/trainee/profile/update'),
      );

      request.headers['Authorization'] = 'Bearer ${widget.token}';
      request.headers['Accept'] = 'application/json';

      if (_heightController.text.isNotEmpty) {
        request.fields['height'] = _heightController.text;
      }
      if (_weightController.text.isNotEmpty) {
        request.fields['weight'] = _weightController.text;
      }
      if (_goal != null) request.fields['goal'] = _goal!;
      if (_level != null) request.fields['level'] = _level!;
      if (_bodyType != null) request.fields['body_type'] = _bodyType!;

      if (_profileImage != null) {
        request.files.add(
          await http.MultipartFile.fromPath('profile_image', _profileImage!.path),
        );
      }

      final response = await request.send();
      final responseData = await response.stream.bytesToString();

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Profile updated successfully!')),
        );
        Navigator.pop(context);
      } else {
        final error = jsonDecode(responseData);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(error['message'] ?? 'Update failed')),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Update Profile')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: EdgeInsets.all(16),
          children: [
            // Profile Image
            Center(
              child: GestureDetector(
                onTap: _pickImage,
                child: CircleAvatar(
                  radius: 60,
                  backgroundImage: _profileImage != null
                      ? FileImage(_profileImage!)
                      : null,
                  child: _profileImage == null
                      ? Icon(Icons.camera_alt, size: 40)
                      : null,
                ),
              ),
            ),
            SizedBox(height: 24),

            // Height
            TextFormField(
              controller: _heightController,
              decoration: InputDecoration(labelText: 'Height (cm)'),
              keyboardType: TextInputType.number,
            ),
            SizedBox(height: 16),

            // Weight
            TextFormField(
              controller: _weightController,
              decoration: InputDecoration(labelText: 'Weight (kg)'),
              keyboardType: TextInputType.number,
            ),
            SizedBox(height: 16),

            // Goal
            DropdownButtonFormField<String>(
              value: _goal,
              decoration: InputDecoration(labelText: 'Goal'),
              items: _goals.map((goal) {
                return DropdownMenuItem(
                  value: goal['value'],
                  child: Text(goal['label']!),
                );
              }).toList(),
              onChanged: (value) => setState(() => _goal = value),
            ),
            SizedBox(height: 16),

            // Activity Level
            DropdownButtonFormField<String>(
              value: _level,
              decoration: InputDecoration(labelText: 'Activity Level'),
              items: _levels.map((level) {
                return DropdownMenuItem(
                  value: level['value'],
                  child: Text(level['label']!),
                );
              }).toList(),
              onChanged: (value) => setState(() => _level = value),
            ),
            SizedBox(height: 16),

            // Body Type
            DropdownButtonFormField<String>(
              value: _bodyType,
              decoration: InputDecoration(labelText: 'Body Type'),
              items: _bodyTypes.map((type) {
                return DropdownMenuItem(
                  value: type['value'],
                  child: Text(type['label']!),
                );
              }).toList(),
              onChanged: (value) => setState(() => _bodyType = value),
            ),
            SizedBox(height: 32),

            // Update Button
            ElevatedButton(
              onPressed: _isLoading ? null : _updateProfile,
              child: _isLoading
                  ? CircularProgressIndicator()
                  : Text('Update Profile'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    _heightController.dispose();
    _weightController.dispose();
    super.dispose();
  }
}
```

---

## 🎯 Summary

### **Trainee Profile Update**

-   **Endpoint**: `POST /api/trainee/profile/update`
-   **Fields**: height, weight, goal, level, body_type, profile_image
-   **Image**: Optional, max 4MB

### **Coach Profile Update**

-   **Endpoint**: `POST /api/coach/profile/update`
-   **Fields**: description, specialty, years_of_experience, prices, profile_image
-   **Image**: Optional, max 4MB

### **Key Points**

-   ✅ Use POST instead of PUT for multipart/form-data
-   ✅ All fields are optional (partial updates supported)
-   ✅ Profile images stored and returned as full URLs
-   ✅ Authentication required via Bearer token
-   ✅ Comprehensive validation with clear error messages

Your profile update endpoints are ready for Flutter integration! 🚀
