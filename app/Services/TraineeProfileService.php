<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\UploadedFile;
use App\Models\ExerciseLog;
use App\Models\TraineeProfile;
use App\Services\TargetService;

class TraineeProfileService
{
    public function __construct(protected TargetService $targetService) {}

    public function show(): array
    {
        $user = Auth::user();

        $profile = $user->traineeProfile;

        if (!$profile) {
            throw new ModelNotFoundException('Trainee profile not found.');
        }

        $profile->load(['target', 'badges']);

        // Total exercise sets/logs the trainee has completed across workouts
        $totalExercisesFinished = ExerciseLog::whereHas('workoutLog', function ($q) use ($profile) {
            $q->where('trainee_id', $profile->id);
        })->count();

        $badges = $profile->badges->map(function ($b) {
            return [
                'id' => $b->id,
                'name' => $b->name,
                'description' => $b->description,
                'image_url' => $b->image_url ?? null,
            ];
        })->toArray();

        return [
            'profile' => $profile,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'total_exercises_finished' => $totalExercisesFinished,
            'total_badges' => count($badges),
            'badges' => $badges,
            'today_target' => $this->targetService->createOrUpdate($profile),
        ];
    }
    /**
     * Return notifications for the authenticated trainee user.
     *
     * @param bool $unreadOnly
     * @return array
     */
    public function notifications(bool $unreadOnly = true): array
    {
        $user = Auth::user();
        if (!$user) {
            throw new ModelNotFoundException('User not found.');
        }

        // Use the DatabaseNotification model directly to avoid relying on the Notifiable trait
        // being present on the User model (which provides the notifications() relation).
        $query = DatabaseNotification::where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user));

        if ($unreadOnly) {
            $query = $query->whereNull('read_at');
        }

        $notes = $query->orderBy('created_at', 'desc')->take(50)->get()->map(function ($n) {
            $data = is_array($n->data) ? $n->data : (array) $n->data;

            // Prefer Arabic titles/messages if provided in payload
            $title = $data['title_ar'] ?? $data['title'] ?? $data['heading'] ?? null;
            $message = $data['message_ar'] ?? $data['message'] ?? $data['body'] ?? $data['text'] ?? null;

            // Ensure messages are Arabic; provide sensible Arabic fallbacks for known notification types
            if (empty($title) || empty($message)) {
                $type = class_basename($n->type);
                switch ($type) {
                    case 'PlanCreatedNotification':
                        $title = $title ?? 'تم إنشاء خطة جديدة';
                        $message = $message ?? 'لقد تم إنشاء خطة جديدة لك.';
                        break;
                    case 'TraineePurchaseNotification':
                        $traineeName = $data['trainee_name'] ?? ($data['trainee_id'] ?? 'المتدرب');
                        $planType = $data['plan_type'] ?? '';
                        $title = $title ?? "شراء جديد من {$traineeName}";
                        $message = $message ?? "{$traineeName} اشترى خطة {$planType}.";
                        break;
                    default:
                        $title = $title ?? 'إشعار جديد';
                        $message = $message ?? 'لديك إشعار جديد.';
                        break;
                }
            }

            return [
                'id' => $n->id,
                'type' => class_basename($n->type),
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'read_at' => $n->read_at ? $n->read_at->toDateTimeString() : null,
                'created_at' => $n->created_at->toIso8601String(),
                'time_ago' => $n->created_at->diffForHumans(),
            ];
        })->toArray();

        return [
            'count' => count($notes),
            'notifications' => $notes,
        ];
    }

    /**
     * Return all plans assigned to the authenticated trainee.
     * Includes plan relations and pivot purchase metadata.
     */
    public function plans(): array
    {
        $user = Auth::user();
        $profile = $user->traineeProfile;

        if (! $profile) {
            throw new ModelNotFoundException('Trainee profile not found.');
        }

        $plans = $profile->plans()->with(['workouts.exercises', 'meals', 'coach.user'])->get()->map(function ($plan) {
            return [
                'id' => $plan->id,
                'type' => $plan->type,
                'title' => $plan->title,
                'description' => $plan->description,
                'coach' => [
                    'id' => optional($plan->coach)->id,
                    'name' => optional($plan->coach->user)->name,
                    'profile_image_url' => optional($plan->coach)->profile_image_url ?? null,
                ],
                'workouts' => $plan->workouts->map(function ($w) {
                    return [
                        'id' => $w->id,
                        'slug' => $w->slug ?? null,
                        'name' => $w->name,
                        'description' => $w->description ?? null,
                        'image_url' => $w->image_url ?? null,
                        'exercises' => $w->exercises->map(function ($e) {
                            return [
                                'id' => $e->id,
                                'name' => $e->name,
                                'instructions' => $e->instructions ?? null,
                                'image_url' => $e->image_url ?? null,
                                'sets' => $e->pivot->sets ?? null,
                                'reps' => $e->pivot->reps ?? null,
                            ];
                        })->toArray(),
                    ];
                }),
                'meals' => $plan->meals->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'description' => $m->description ?? null,
                        'date' => $m->date ? (string) $m->date : null,
                        'type' => $m->type ?? null,
                        'calories' => $m->calories ?? null,
                        'protein' => $m->protein ?? null,
                        'carbs' => $m->carbs ?? null,
                        'fats' => $m->fats ?? null,
                        'image_url' => $m->image_url ?? null,
                    ];
                }),
                'pivot' => [
                    'tap_charge_id' => $plan->pivot->tap_charge_id ?? null,
                    'purchased_at' => $this->formatPivotPurchasedAt($plan->pivot->purchased_at ?? null),
                    'items' => is_string($plan->pivot->items) ? json_decode($plan->pivot->items, true) : $plan->pivot->items,
                ],
            ];
        });

        return ['plans' => $plans];
    }

    /**
     * Return detailed data for a single plan assigned to the authenticated trainee.
     * Throws ModelNotFoundException if the plan is not assigned to the trainee.
     *
     * @param int|\App\Models\Plan $planId
     * @return array
     */
    public function planDetails($planId): array
    {
        $user = Auth::user();
        $profile = $user->traineeProfile;

        if (! $profile) {
            throw new ModelNotFoundException('Trainee profile not found.');
        }

        // Use the relationship query so we can access pivot data
        $query = $profile->plans()->with(['workouts.exercises', 'workouts', 'meals', 'coach.user']);

        $plan = $query->where('plans.id', $planId)->first();

        if (! $plan) {
            throw new ModelNotFoundException('Plan not found for this trainee.');
        }

        // Format workouts and meals with full details (exercises, media, pivot sets/reps)
        $workouts = $plan->workouts->map(function ($w) {
            return [
                'id' => $w->id,
                'slug' => $w->slug ?? null,
                'name' => $w->name,
                'description' => $w->description ?? null,
                'image_url' => $w->image_url ?? $w->getFirstMediaUrl('workouts') ?: null,
                'exercises' => $w->exercises->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'name' => $e->name,
                        'instructions' => $e->instructions ?? null,
                        'image_url' => $e->image_url ?? $e->getFirstMediaUrl('exercises') ?: null,
                        'sets' => $e->pivot->sets ?? null,
                        'reps' => $e->pivot->reps ?? null,
                    ];
                })->toArray(),
            ];
        })->toArray();

        $meals = $plan->meals->map(function ($m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'description' => $m->description ?? null,
                'date' => $m->date ? (string) $m->date : null,
                'type' => $m->type ?? null,
                'calories' => $m->calories ?? null,
                'protein' => $m->protein ?? null,
                'carbs' => $m->carbs ?? null,
                'fats' => $m->fats ?? null,
                'image_url' => $m->image_url ?? $m->getFirstMediaUrl('meals') ?: null,
            ];
        })->toArray();

        $data = [
            'id' => $plan->id,
            'type' => $plan->type,
            'title' => $plan->title,
            'description' => $plan->description,
            'coach' => [
                'id' => optional($plan->coach)->id,
                'name' => optional($plan->coach->user)->name,
                'profile_image_url' => optional($plan->coach)->profile_image_url ?? null,
            ],
            'workouts' => $workouts,
            'meals' => $meals,
            'pivot' => [
                'tap_charge_id' => $plan->pivot->tap_charge_id ?? null,
                'purchased_at' => $this->formatPivotPurchasedAt($plan->pivot->purchased_at ?? null),
                'items' => is_string($plan->pivot->items) ? json_decode($plan->pivot->items, true) : $plan->pivot->items,
            ],
        ];

        return ['plan' => $data];
    }

    /**
     * Return the latest assigned plan for the authenticated trainee.
     * If none exists, returns ['plan' => null].
     */
    public function latestPlan(): array
    {
        $user = Auth::user();
        $profile = $user->traineeProfile;

        if (! $profile) {
            throw new ModelNotFoundException('Trainee profile not found.');
        }

        $plan = $profile->plans()
            ->with(['workouts', 'meals', 'coach.user'])
            ->orderByDesc('trainee_plan.purchased_at')
            ->orderByDesc('trainee_plan.created_at')
            ->first();

        if (! $plan) {
            return ['plan' => null];
        }

        // Build full workouts and meals payload (same structure as planDetails)
        $workouts = $plan->workouts->map(function ($w) {
            return [
                'id' => $w->id,
                'slug' => $w->slug ?? null,
                'name' => $w->name,
                'description' => $w->description ?? null,
                'image_url' => $w->image_url ?? $w->getFirstMediaUrl('workouts') ?: null,
                'exercises' => $w->exercises->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'name' => $e->name,
                        'instructions' => $e->instructions ?? null,
                        'image_url' => $e->image_url ?? $e->getFirstMediaUrl('exercises') ?: null,
                        'sets' => $e->pivot->sets ?? null,
                        'reps' => $e->pivot->reps ?? null,
                    ];
                })->toArray(),
            ];
        })->toArray();

        $meals = $plan->meals->map(function ($m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'description' => $m->description ?? null,
                'date' => $m->date ? (string) $m->date : null,
                'type' => $m->type ?? null,
                'calories' => $m->calories ?? null,
                'protein' => $m->protein ?? null,
                'carbs' => $m->carbs ?? null,
                'fats' => $m->fats ?? null,
                'image_url' => $m->image_url ?? $m->getFirstMediaUrl('meals') ?: null,
            ];
        })->toArray();

        $result = [
            'id' => $plan->id,
            'type' => $plan->type,
            'title' => $plan->title,
            'description' => $plan->description,
            'coach' => [
                'id' => optional($plan->coach)->id,
                'name' => optional($plan->coach->user)->name,
                'profile_image_url' => optional($plan->coach)->profile_image_url ?? null,
            ],
            'workouts' => $workouts,
            'meals' => $meals,
            'pivot' => [
                'tap_charge_id' => $plan->pivot->tap_charge_id ?? null,
                'purchased_at' => $this->formatPivotPurchasedAt($plan->pivot->purchased_at ?? null),
                'items' => is_string($plan->pivot->items) ? json_decode($plan->pivot->items, true) : $plan->pivot->items,
            ],
        ];

        return ['plan' => $result];
    }

    /**
     * Return the latest started workout log for the trainee with progress details.
     * If none exists, returns ['workout_log' => null].
     */
    public function latestWorkoutProgress(): array
    {
        $user = Auth::user();
        $profile = $user->traineeProfile;

        if (! $profile) {
            throw new ModelNotFoundException('Trainee profile not found.');
        }

        $log = \App\Models\WorkoutLog::with(['workout.exercises', 'exerciseLogs.exercise'])
            ->where('trainee_id', $profile->id)
            ->orderByDesc('started_at')
            ->first();

        if (! $log) {
            return ['workout_log' => null];
        }

        $workout = $log->workout;

        $exercises = $workout->exercises->map(function ($exercise) use ($log) {
            $required = $exercise->pivot->sets ?? 1;
            $logged = $log->exerciseLogs->where('exercise_id', $exercise->id)->count();

            return [
                'id' => $exercise->id,
                'name' => $exercise->name,
                'required_sets' => $required,
                'logged_sets' => $logged,
                'remaining_sets' => max(0, $required - $logged),
                'completed' => $logged >= $required,
            ];
        });

        $total = $exercises->count();
        $completed = $exercises->where('completed', true)->count();

        $result = [
            'id' => $log->id,
            'workout_id' => $workout->id,
            'workout_slug' => $workout->slug ?? null,
            'workout_name' => $workout->name,
            'status' => $log->status,
            'started_at' => $log->started_at ? $this->formatPivotPurchasedAt($log->started_at) : null,
            'completed_at' => $log->completed_at ? $this->formatPivotPurchasedAt($log->completed_at) : null,
            'completion_percent' => $total ? round(($completed / $total) * 100, 2) : 0,
            'exercises' => $exercises->toArray(),
        ];

        return ['workout_log' => $result];
    }

    public function store(array $data, ?UploadedFile $profileImage = null): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->traineeProfile) {
            throw ValidationException::withMessages(['profile' => 'Profile already exists.']);
        }

        $profile = $user->traineeProfile()->create($data);

        $this->syncProfileImage($profile, $profileImage);

        $todayTarget = $this->targetService->createOrUpdate($profile);

        return [
            'message' => 'Trainee profile created successfully.',
            'profile' => $profile->fresh()->load(['badges', 'target']),
            'today_target' => $todayTarget,
        ];
    }

    public function update(array $data, ?UploadedFile $profileImage = null): array
    {
        $profile = Auth::user()->traineeProfile;

        if (!$profile) {
            throw new ModelNotFoundException('Trainee profile not found.');
        }

        $profile->update(array_filter($data));

        $this->syncProfileImage($profile, $profileImage);

        $todayTarget = $this->targetService->createOrUpdate($profile);

        return [
            'message' => 'Trainee profile updated successfully.',
            'profile' => $profile->fresh()->load(['badges', 'target']),
            'today_target' => $todayTarget,
        ];
    }

    public function destroy(): array
    {
        $profile = Auth::user()->traineeProfile;

        if (!$profile) {
            throw new ModelNotFoundException('Trainee profile not found.');
        }

        $profile->clearMediaCollection('profile_picture');
        $profile->delete();

        return ['message' => 'Trainee profile deleted successfully.'];
    }

    private function syncProfileImage(TraineeProfile $profile, ?UploadedFile $profileImage): void
    {
        if (!$profileImage) {
            return;
        }

        $profile->clearMediaCollection('profile_picture');
        $profile->addMedia($profileImage)->toMediaCollection('profile_picture');
    }

    /**
     * Safely format the pivot purchased_at value.
     * Accepts null, string, or DateTime-like values and returns a Y-m-d H:i:s string or null.
     */
    private function formatPivotPurchasedAt($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        try {
            return \Carbon\Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable $e) {
            // Fallback to string cast if parsing fails
            return (string) $value;
        }
    }
}
