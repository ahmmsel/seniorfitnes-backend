<?php

namespace App\Services;

use App\Models\CoachProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\UploadedFile;

class CoachProfileService
{
    protected function profile()
    {
        $user = Auth::user();
        $profile = $user->coachProfile;

        return $profile;
    }

    public function show(): array
    {
        $profile = $this->profile();

        if (!$profile) {
            throw new ModelNotFoundException('Profile not found.');
        }

        $profile->load('user:id,name,email');

        return ['profile' => $profile];
    }

    /**
     * Return notifications for the authenticated coach user.
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
     * Return coach analytics for the authenticated coach.
     *
     * Basic metrics provided:
     * - total_plans: total TraineePlan records for this coach
     * - unique_trainees: number of unique trainees served
     * - revenue: sum of payments linked to trainee plans (if any)
     * - recent_purchases: last 5 purchases with basic meta
     *
     * @return array
     */
    public function analytics(): array
    {
        $profile = $this->profile();

        if (! $profile) {
            throw new ModelNotFoundException('Profile not found.');
        }

        $coachId = $profile->id;

        $plansQuery = \App\Models\TraineePlan::where('coach_profile_id', $coachId);

        $totalPlans = $plansQuery->count();

        $uniqueTrainees = \App\Models\TraineePlan::where('coach_profile_id', $coachId)
            ->distinct('trainee_id')
            ->count('trainee_id');

        $chargeIds = \App\Models\TraineePlan::where('coach_profile_id', $coachId)
            ->pluck('tap_charge_id')
            ->filter()
            ->values()
            ->all();

        $revenue = 0.0;
        if (!empty($chargeIds)) {
            $revenue = (float) \App\Models\Payment::whereIn('charge_id', $chargeIds)->sum('amount');
        }

        $recent = \App\Models\TraineePlan::where('coach_profile_id', $coachId)
            ->with(['trainee.user:id,name'])
            ->orderByDesc('purchased_at')
            ->limit(5)
            ->get()
            ->map(fn($tp) => [
                'id' => $tp->id,
                'trainee_id' => $tp->trainee_id,
                'trainee_name' => optional($tp->trainee->user)->name,
                'plan_id' => $tp->plan_id,
                'plan_type' => $tp->plan_type,
                'purchased_at' => $tp->purchased_at ? $tp->purchased_at->toDateTimeString() : null,
            ])
            ->toArray();

        return [
            'total_plans' => $totalPlans,
            'unique_trainees' => $uniqueTrainees,
            'revenue' => $revenue,
            'recent_purchases' => $recent,
        ];
    }

    public function store(array $data, ?UploadedFile $profileImage = null): array
    {
        $user = Auth::user();

        // Ensure user is authenticated (safety check)
        if (!$user) {
            throw ValidationException::withMessages([
                'auth' => 'You must be logged in to create a coach profile.'
            ]);
        }

        // Prevent duplicate profiles
        if ($user->coachProfile) {
            throw ValidationException::withMessages([
                'profile' => 'A profile already exists for this user.'
            ]);
        }

        // Create the coach profile by setting the user_id on the data array
        $data['user_id'] = $user->id;
        $profile = CoachProfile::create($data);

        $this->syncProfileImage($profile, $profileImage);

        return [
            'message' => 'Profile created successfully.',
            'profile' => $profile->fresh('user'),
        ];
    }


    public function update(array $data, ?UploadedFile $profileImage = null): array
    {
        $profile = $this->profile();

        if (!$profile) {
            throw new ModelNotFoundException('Profile not found.');
        }

        $profile->update($data);

        $this->syncProfileImage($profile, $profileImage);

        return [
            'message' => 'Profile updated successfully.',
            'profile' => $profile->fresh('user'),
        ];
    }

    public function destroy(): array
    {
        $profile = $this->profile();

        if (!$profile) {
            throw new ModelNotFoundException('Profile not found.');
        }

        $profile->clearMediaCollection('profile_picture');
        $profile->delete();

        return ['message' => 'Profile deleted successfully.'];
    }

    private function syncProfileImage(CoachProfile $profile, ?UploadedFile $profileImage): void
    {
        if (!$profileImage) {
            return;
        }

        $profile->clearMediaCollection('profile_picture');
        $profile->addMedia($profileImage)->toMediaCollection('profile_picture');
    }
}
