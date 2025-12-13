<?php

namespace App\Services;

use App\Models\{Workout, WorkoutLog, Exercise, ExerciseLog};
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class WorkoutService
{
    /** Start a workout for the authenticated trainee */
    public function startWorkout(Workout $workout)
    {
        $trainee = $this->getTrainee();

        $this->closeOldSessions($trainee->id, $workout->id);

        $active = WorkoutLog::where([
            'trainee_id' => $trainee->id,
            'workout_id' => $workout->id,
            'status' => 'in_progress'
        ])->first();

        if ($active) {
            throw ValidationException::withMessages([
                'workout' => 'You already have this workout in progress.',
            ]);
        }

        return WorkoutLog::create([
            'trainee_id' => $trainee->id,
            'workout_id' => $workout->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /** Log exercise progress for the current workout */
    public function logExercise(Workout $workout, Exercise $exercise, array $data)
    {
        $trainee = $this->getTrainee();
        $log = $this->getActiveWorkoutLog($trainee->id, $workout->id);

        $pivot = $workout->exercises()->find($exercise->id)?->pivot;
        if (!$pivot) {
            throw ValidationException::withMessages(['exercise' => 'Exercise not part of this workout.']);
        }

        $requiredSets = $pivot->sets ?? 1;
        $loggedSets = ExerciseLog::where('workout_log_id', $log->id)
            ->where('exercise_id', $exercise->id)
            ->count();

        if ($loggedSets >= $requiredSets) {
            throw ValidationException::withMessages(['exercise' => 'All sets for this exercise are already logged.']);
        }

        ExerciseLog::create([
            'workout_log_id' => $log->id,
            'exercise_id' => $exercise->id,
            'reps' => $data['reps'],
            'weight' => $data['weight'] ?? null,
        ]);

        $this->markWorkoutIfCompleted($log);

        return [
            'message' => "Exercise set " . ($loggedSets + 1) . " logged successfully.",
            'set_number' => $loggedSets + 1,
            'remaining_sets' => max(0, $requiredSets - ($loggedSets + 1)),
        ];
    }

    /** Get detailed progress for the workout */
    public function progress(Workout $workout)
    {
        $trainee = $this->getTrainee();

        $log = WorkoutLog::with('exerciseLogs.exercise')
            ->where('workout_id', $workout->id)
            ->where('trainee_id', $trainee->id)
            ->latest()
            ->firstOrFail();

        $workout->load('exercises');

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

        return [
            'status' => $log->status,
            'started_at' => $log->started_at,
            'completed_at' => $log->completed_at,
            'completion_percent' => $total ? round(($completed / $total) * 100, 2) : 0,
            'can_restart' => $log->status === 'completed',
            'exercises' => $exercises,
        ];
    }

    /** List all workout logs for the trainee */
    public function logs()
    {
        $trainee = $this->getTrainee();

        return WorkoutLog::with('workout')
            ->where('trainee_id', $trainee->id)
            ->latest()
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'workout_name' => $log->workout->name,
                'status' => $log->status,
                'started_at' => $log->started_at,
                'completed_at' => $log->completed_at,
            ]);
    }

    /** List completed workout logs for the trainee */
    public function completedLogs()
    {
        $trainee = $this->getTrainee();

        return WorkoutLog::with('workout')
            ->where('trainee_id', $trainee->id)
            ->where('status', 'completed')
            ->latest()
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'workout_name' => $log->workout->name,
                'status' => $log->status,
                'started_at' => $log->started_at,
                'completed_at' => $log->completed_at,
            ]);
    }

    /** List completed logs for a specific workout (by id/slug resolved Workout) */
    public function completedForWorkout(Workout $workout)
    {
        $trainee = $this->getTrainee();

        return WorkoutLog::with(['workout', 'exerciseLogs.exercise'])
            ->where('trainee_id', $trainee->id)
            ->where('workout_id', $workout->id)
            ->where('status', 'completed')
            ->latest()
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'workout_name' => $log->workout->name,
                    'status' => $log->status,
                    'started_at' => $log->started_at,
                    'completed_at' => $log->completed_at,
                    'exercise_sets_logged' => $log->exerciseLogs->count(),
                ];
            });
    }

    public function isStarted(Workout $workout)
    {
        $trainee = $this->getTrainee();

        return WorkoutLog::where([
            'trainee_id' => $trainee->id,
            'workout_id' => $workout->id,
            'status' => 'in_progress',
        ])->exists();
    }

    // ===========================================================
    // PRIVATE HELPERS
    // ===========================================================

    /** Ensure user has trainee profile */
    private function getTrainee()
    {
        $trainee = Auth::user()?->traineeProfile;
        if (!$trainee) {
            throw new AuthorizationException('No trainee profile found.');
        }
        return $trainee;
    }

    /** Auto-close old sessions older than 24 hours */
    private function closeOldSessions(int $traineeId, int $workoutId): void
    {
        WorkoutLog::where('trainee_id', $traineeId)
            ->where('workout_id', $workoutId)
            ->where('status', 'in_progress')
            ->where('started_at', '<', now()->subHours(24))
            ->update(['status' => 'completed', 'completed_at' => now()]);
    }

    /** Get active log or fail */
    private function getActiveWorkoutLog(int $traineeId, int $workoutId): WorkoutLog
    {
        return WorkoutLog::where([
            'trainee_id' => $traineeId,
            'workout_id' => $workoutId,
            'status' => 'in_progress',
        ])->firstOr(function () {
            throw new ModelNotFoundException('No active workout session found.');
        });
    }

    /** Mark workout as completed if all sets logged */
    private function markWorkoutIfCompleted(WorkoutLog $log): void
    {
        $workout = $log->workout()->with('exercises')->first();

        $incomplete = $workout->exercises->first(function ($exercise) use ($log) {
            $required = $exercise->pivot->sets ?? 1;
            $logged = $log->exerciseLogs()->where('exercise_id', $exercise->id)->count();
            return $logged < $required;
        });

        if (!$incomplete) {
            $log->update(['status' => 'completed', 'completed_at' => now()]);
        }
    }
}
