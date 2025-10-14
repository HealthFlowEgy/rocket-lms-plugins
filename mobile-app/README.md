# Rocket LMS Mobile App (Flutter)

A comprehensive mobile application for Rocket LMS built with Flutter, providing a native mobile experience for both Android and iOS platforms.

## Overview

This is the official Rocket LMS mobile application that allows students and instructors to access the learning management system on their mobile devices. The app is built using Flutter framework, ensuring cross-platform compatibility and native performance.

## Features

- **User Authentication**: Secure login and registration
- **Course Browsing**: Browse and search available courses
- **Course Enrollment**: Enroll in courses directly from the app
- **Video Lessons**: Watch course videos with native video player
- **Quizzes & Assignments**: Take quizzes and submit assignments
- **Progress Tracking**: Track learning progress
- **Certificates**: View and download certificates
- **Push Notifications**: Receive updates and notifications
- **Offline Mode**: Download content for offline viewing
- **Multi-language Support**: Support for multiple languages
- **Dark Mode**: Light and dark theme support

## Technology Stack

- **Framework**: Flutter
- **Language**: Dart
- **Platforms**: Android, iOS
- **State Management**: Provider/Bloc (check pubspec.yaml for exact implementation)
- **API Integration**: RESTful API communication with Rocket LMS backend

## Requirements

### Development Environment

- **Flutter SDK**: Latest stable version
- **Dart SDK**: Included with Flutter
- **Android Studio** or **VS Code** with Flutter extensions
- **Xcode** (for iOS development, macOS only)

### Minimum Platform Versions

- **Android**: API Level 21 (Android 5.0) or higher
- **iOS**: iOS 11.0 or higher

## Installation

### 1. Install Flutter

Follow the official Flutter installation guide:
- [Flutter Installation](https://flutter.dev/docs/get-started/install)

### 2. Clone the Repository

```bash
git clone https://github.com/HealthFlowEgy/rocket-lms-plugins.git
cd rocket-lms-plugins/mobile-app
```

### 3. Install Dependencies

```bash
flutter pub get
```

### 4. Configure API Endpoint

Update the API endpoint in the configuration file to point to your Rocket LMS backend:

```dart
// lib/config/api_config.dart (or similar)
const String API_BASE_URL = 'https://your-domain.com/api';
```

### 5. Run the App

#### Android

```bash
flutter run
```

#### iOS (macOS only)

```bash
cd ios
pod install
cd ..
flutter run
```

## Building for Production

### Android APK

```bash
flutter build apk --release
```

The APK will be generated at: `build/app/outputs/flutter-apk/app-release.apk`

### Android App Bundle (for Google Play)

```bash
flutter build appbundle --release
```

The bundle will be generated at: `build/app/outputs/bundle/release/app-release.aab`

### iOS (macOS only)

```bash
flutter build ios --release
```

Then use Xcode to archive and upload to App Store.

## Pre-built APK

A pre-built APK is available in the `builds/` directory:

- `builds/Rocket_LMS_Flutter_APP.apk` - Ready to install on Android devices

## Project Structure

```
mobile-app/
├── android/              # Android native code
├── ios/                  # iOS native code
├── lib/                  # Flutter/Dart source code
│   ├── main.dart        # App entry point
│   ├── models/          # Data models
│   ├── screens/         # UI screens
│   ├── widgets/         # Reusable widgets
│   ├── services/        # API services
│   └── utils/           # Utility functions
├── assets/              # Images, fonts, etc.
├── test/                # Unit and widget tests
├── web/                 # Web platform support
├── builds/              # Pre-built APK files
├── pubspec.yaml         # Dependencies
├── Documentation.pdf    # Complete documentation
└── README.md            # This file
```

## Configuration

### API Configuration

Update the API base URL and endpoints in your configuration file to match your Rocket LMS installation.

### App Branding

To customize the app branding:

1. **App Name**: Update in `android/app/src/main/AndroidManifest.xml` and `ios/Runner/Info.plist`
2. **App Icon**: Replace icons in `android/app/src/main/res/` and `ios/Runner/Assets.xcassets/`
3. **Splash Screen**: Update splash screen assets in respective platform folders
4. **Colors**: Modify theme colors in `lib/config/theme.dart` or similar

### Firebase Configuration (if applicable)

If the app uses Firebase for push notifications or analytics:

1. Add `google-services.json` to `android/app/`
2. Add `GoogleService-Info.plist` to `ios/Runner/`

## Testing

### Run Unit Tests

```bash
flutter test
```

### Run Integration Tests

```bash
flutter drive --target=test_driver/app.dart
```

## Troubleshooting

### Common Issues

#### 1. Dependencies Not Found

**Solution**:
```bash
flutter clean
flutter pub get
```

#### 2. Build Errors on iOS

**Solution**:
```bash
cd ios
pod deintegrate
pod install
cd ..
flutter clean
flutter build ios
```

#### 3. API Connection Issues

**Solution**:
- Verify API endpoint is correct
- Check network connectivity
- Ensure backend API is accessible
- Check CORS settings on backend

## Flutter Resources

A few resources to get you started with Flutter development:

- [Lab: Write your first Flutter app](https://docs.flutter.dev/get-started/codelab)
- [Cookbook: Useful Flutter samples](https://docs.flutter.dev/cookbook)
- [Online documentation](https://docs.flutter.dev/)

## Documentation

Complete documentation is available in `Documentation.pdf`.

## Version Compatibility

This mobile app is designed to work with:
- **Rocket LMS**: Version 2.0.0 or higher
- **API Version**: Check backend compatibility

## Support

For support and bug reports:
- Check the documentation PDF
- Visit CodeCanyon support forum
- Contact the development team

## License

This mobile app is part of the Rocket LMS package. Please refer to your license agreement for usage terms.

## Changelog

See the parent directory for version history and updates.

---

**Platform**: Flutter (Android & iOS)  
**Version**: Check pubspec.yaml  
**Last Updated**: October 14, 2025  
**Repository**: https://github.com/HealthFlowEgy/rocket-lms-plugins

