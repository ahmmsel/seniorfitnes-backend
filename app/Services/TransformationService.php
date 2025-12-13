<?php

namespace App\Services;

use App\Models\Transformation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransformationService
{
    public function getAll()
    {
        $profile = Auth::user()->coachProfile;

        if (!$profile) {
            throw new ModelNotFoundException('Coach profile not found.');
        }

        return $profile->transformations()
            ->with('media')
            ->latest()
            ->get()
            ->map(fn($t) => $this->format($t));
    }

    public function store(array $data, $request)
    {
        $profile = Auth::user()->coachProfile;

        if (!$profile) {
            throw new ModelNotFoundException('Coach profile not found.');
        }

        $transformation = $profile->transformations()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        if ($request->hasFile('image')) {
            $transformation->addMediaFromRequest('image')->toMediaCollection('transformations');
        }

        return $this->format($transformation);
    }

    public function show(Transformation $transformation)
    {
        $profile = Auth::user()->coachProfile;

        if (!$profile) {
            throw new ModelNotFoundException('Coach profile not found.');
        }

        if ($transformation->coach_profile_id !== $profile->id) {
            throw new AuthorizationException('Unauthorized access to transformation.');
        }

        return $this->format($transformation);
    }

    public function update(Transformation $transformation, array $data, $request)
    {
        $profile = Auth::user()->coachProfile;

        if (!$profile) {
            throw new ModelNotFoundException('Coach profile not found.');
        }

        if ($transformation->coach_profile_id !== $profile->id) {
            throw new AuthorizationException('Unauthorized access to transformation.');
        }

        $transformation->update(array_filter([
            'title' => $data['title'] ?? $transformation->title,
            'description' => $data['description'] ?? $transformation->description,
        ]));

        if ($request->hasFile('image')) {
            $transformation->clearMediaCollection('transformations');
            $transformation->addMediaFromRequest('image')->toMediaCollection('transformations');
        }

        return $this->format($transformation);
    }

    public function destroy(Transformation $transformation)
    {
        $profile = Auth::user()->coachProfile;

        if (!$profile) {
            throw new ModelNotFoundException('Coach profile not found.');
        }

        if ($transformation->coach_profile_id !== $profile->id) {
            throw new AuthorizationException('Unauthorized access to transformation.');
        }

        $transformation->clearMediaCollection('transformations');
        $transformation->delete();
    }

    private function format(Transformation $t): array
    {
        return [
            'id' => $t->id,
            'title' => $t->title,
            'description' => $t->description,
            'image_url' => $t->getFirstMediaUrl('transformations'),
        ];
    }
}
