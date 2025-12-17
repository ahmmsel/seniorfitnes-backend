<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\CoachProfile;
use App\Models\TraineeProfile;

try {
    $u1 = User::first();
    if (! $u1) {
        $u1 = User::create(['name' => 't1', 'email' => 't1@example.com', 'password' => bcrypt('pass')]);
        echo "created1 {$u1->id}\n";
    } else {
        echo "u1 {$u1->id}\n";
    }

    $u2 = User::where('id', '!=', $u1->id)->first();
    if (! $u2) {
        $u2 = User::create(['name' => 't2', 'email' => 't2@example.com', 'password' => bcrypt('pass')]);
        echo "created2 {$u2->id}\n";
    } else {
        echo "u2 {$u2->id}\n";
    }

    if (! $u1->coachProfile) {
        CoachProfile::create([
            'user_id' => $u1->id,
            'description' => 't1',
            'specialty' => 'both',
            'years_of_experience' => 1,
            'nutrition_price' => 0,
            'workout_price' => 0,
            'full_package_price' => 0,
        ]);
        echo "cp1\n";
    }
    if (! $u2->traineeProfile) {
        TraineeProfile::create(['user_id' => $u2->id, 'height' => 170, 'weight' => 70, 'goal' => 'maintain_fitness', 'level' => 'active', 'body_type' => 'normal']);
        echo "tp2\n";
    }

    $chatService = $app->make(App\Services\ChatService::class);
    $chat = $chatService->startChatBetween($u1, $u2->id, 'trainee');
    echo "chat {$chat->id}\n";
    $message = $chatService->sendMessage($chat, $u1, 'hello from script');
    echo "msg {$message->id}\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
