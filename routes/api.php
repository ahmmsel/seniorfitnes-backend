<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CoachProfileController;
use App\Http\Controllers\Api\CoachWorkoutController;
use App\Http\Controllers\Api\CoachExerciseController;
use App\Http\Controllers\Api\CoachTraineePlanController;
use App\Http\Controllers\Api\TransformationController;
use App\Http\Controllers\Api\ChallengeController;
use App\Http\Controllers\Api\CoachMealController;
use App\Http\Controllers\Api\DiscoverCoachController;
use App\Http\Controllers\Api\MealController;
use App\Http\Controllers\Api\TraineeProfileController;
use App\Http\Controllers\Api\WorkoutController;
use App\Http\Controllers\Api\WorkoutCatalogController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\TapWebhookController;
use App\Http\Controllers\Api\TapRedirectController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\CommunityController;
use Illuminate\Support\Facades\Route;

// ---------- Public Routes ----------

// Authentication (public endpoints)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('google-signin', [AuthController::class, 'googleSignIn']);
    Route::post('apple-signin', [AuthController::class, 'appleSignIn']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::get('check-profile', [AuthController::class, 'check'])->middleware('auth:sanctum');
});

Route::post('payment/tap/webhook', [TapWebhookController::class, 'webhook'])->name('tap.webhook');

Route::get('payment/tap/redirect', [TapRedirectController::class, 'redirect'])->name('tap.redirect');

// ---------- Protected API (requires authentication) ----------
Route::middleware('auth:sanctum')->group(function () {
    // Generic payment endpoints removed: use the strict purchase flow (POST /api/purchases)

    // Trainee routes (profile CRUD)
    Route::prefix('trainee')->group(function () {
        Route::get('profile', [TraineeProfileController::class, 'show']);
        Route::post('profile', [TraineeProfileController::class, 'store']);
        Route::post('profile/update', [TraineeProfileController::class, 'update']);
        Route::delete('profile', [TraineeProfileController::class, 'destroy']);
        // Notifications for trainee profile
        Route::get('notifications', [TraineeProfileController::class, 'notifications']);
        // List plans assigned to the trainee
        Route::get('plans', [TraineeProfileController::class, 'plans']);
        // Quick access: latest assigned plan
        Route::get('plans/latest', [TraineeProfileController::class, 'latestPlan']);
        // Plan detail for assigned trainee
        Route::get('plans/{plan}', [TraineeProfileController::class, 'planDetail']);
        // Quick access: latest started workout with progress
        Route::get('workouts/latest/progress', [TraineeProfileController::class, 'latestWorkoutProgress']);
    });

    // Purchases: create a charge for the authenticated trainee
    Route::post('purchases', [PurchaseController::class, 'purchase']);

    // Discover
    Route::get('discover/coaches', [DiscoverCoachController::class, 'coaches']);
    Route::get('discover/coaches/{coach}', [DiscoverCoachController::class, 'show']);

    // Meals
    Route::apiResource('meals', MealController::class)->only(['index', 'show']);

    // Plans
    Route::apiResource('plans', PlanController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    // Assign route removed: coach must create plan from pending purchase only.

    // Workouts
    Route::prefix('workouts')->group(function () {
        Route::get('/', [WorkoutCatalogController::class, 'index']);
        Route::get('/completed', [WorkoutController::class, 'completed']);
        Route::get('/{workout}/completed', [WorkoutController::class, 'completedFor']);
        Route::get('/{workout}', [WorkoutCatalogController::class, 'show']);
        Route::get('/{workout}/progress', [WorkoutController::class, 'progress']);
        Route::post('/{workout}/start', [WorkoutController::class, 'start']);
        Route::post('/{workout}/exercises/{exercise}/log', [WorkoutController::class, 'logExercise']);
    });

    // Challenges
    Route::prefix('challenges')->group(function () {
        Route::get('/', [ChallengeController::class, 'index']);
        Route::get('/{challenge}', [ChallengeController::class, 'show']);
        Route::post('/{challenge}/join', [ChallengeController::class, 'join']);
        Route::post('/{challenge}/complete-day', [ChallengeController::class, 'markDayCompleted']);
        Route::get('/{challenge}/leaderboard', [ChallengeController::class, 'leaderboard']);
    });

    // Exercises (list and detail) - accessible to authenticated users (coach or trainee)
    Route::apiResource('exercises', ExerciseController::class)->only(['index', 'show']);

    // Tracking (Walking/Running)
    Route::prefix('tracking')->group(function () {
        Route::post('start', [TrackingController::class, 'start']);
        Route::post('finish', [TrackingController::class, 'finish']);
        Route::get('history', [TrackingController::class, 'history']);
    });

    // Progress Sharing
    Route::post('progress/share/{session}', [TrackingController::class, 'share']);

    // Community Feed
    Route::get('community/feed', [CommunityController::class, 'feed']);
    Route::post('progress/{post}/like', [CommunityController::class, 'like']);
    Route::post('progress/{post}/comment', [CommunityController::class, 'comment']);
    Route::get('progress/{post}/comments', [CommunityController::class, 'comments']);

    // Coach
    Route::prefix('coach')->name('coach.')->group(function () {
        Route::get('profile', [CoachProfileController::class, 'show']);
        Route::post('profile', [CoachProfileController::class, 'store']);
        Route::post('profile/update', [CoachProfileController::class, 'update']);
        Route::delete('profile', [CoachProfileController::class, 'destroy']);
        // Notifications for coach profile
        Route::get('notifications', [CoachProfileController::class, 'notifications']);
        // Coach analytics
        Route::get('analytics', [CoachProfileController::class, 'analytics']);
        Route::get('trainee-plans/pending', [CoachTraineePlanController::class, 'index']);
        Route::get('trainee-plans/completed', [CoachTraineePlanController::class, 'completed']);
        Route::apiResource('certificates', CertificateController::class);
        Route::apiResource('transformations', TransformationController::class);
        // Coach content management
        Route::apiResource('workouts', CoachWorkoutController::class);
        Route::apiResource('exercises', CoachExerciseController::class);
        Route::apiResource('meals', CoachMealController::class);
    });

    // Coach resources aliases without /coach prefix (backward compatibility)
    Route::apiResource('certificates', CertificateController::class);
    Route::apiResource('transformations', TransformationController::class);

    // Chat endpoints (realtime via Pusher)
    Route::get('chats', [\App\Http\Controllers\Api\ChatController::class, 'index']);
    Route::get('chats/latest', [\App\Http\Controllers\Api\ChatController::class, 'latest']);
    Route::post('chats/start', [\App\Http\Controllers\Api\ChatController::class, 'store']);
    Route::get('chats/{chat}', [\App\Http\Controllers\Api\ChatController::class, 'show']);
    Route::post('chats/{chat}/message', [\App\Http\Controllers\Api\ChatController::class, 'sendMessage']);
    Route::post('chats/{chat}/read', [\App\Http\Controllers\Api\ChatController::class, 'markAsRead']);

    // Broadcasting endpoints for real-time events
    Route::prefix('broadcast')->group(function () {
        Route::post('/', [\App\Http\Controllers\Api\BroadcastController::class, 'trigger']);
        Route::get('info', [\App\Http\Controllers\Api\BroadcastController::class, 'info']);
        Route::get('test', [\App\Http\Controllers\Api\BroadcastController::class, 'test']);
    });
});
