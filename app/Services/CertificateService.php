<?php

namespace App\Services;

use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CertificateService
{
    protected function profile()
    {
        $user = Auth::user();
        $profile = $user->coachProfile;

        if (!$profile) {
            throw new ModelNotFoundException('Coach profile not found.');
        }

        return $profile;
    }

    public function getAll(): array
    {
        $profile = $this->profile();

        $certificates = $profile->certificates()
            ->with('media')
            ->get()
            ->map(fn($c) => $this->formatCertificate($c));

        return ['certificates' => $certificates];
    }

    public function store(array $data, $request): array
    {
        $profile = $this->profile();

        $certificate = $profile->certificates()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if ($request->hasFile('image')) {
            $certificate->addMediaFromRequest('image')->toMediaCollection('certificates');
        }

        return [
            'message' => 'Certificate created successfully.',
            'certificate' => $this->formatCertificate($certificate),
        ];
    }

    public function show(Certificate $certificate): array
    {
        $profile = $this->profile();

        if ($certificate->profile_id !== $profile->id) {
            throw new AuthorizationException('Unauthorized access to this certificate.');
        }

        return ['certificate' => $this->formatCertificate($certificate)];
    }

    public function update(Certificate $certificate, array $data, $request): array
    {
        $profile = $this->profile();

        if ($certificate->profile_id !== $profile->id) {
            throw new AuthorizationException('Unauthorized access to this certificate.');
        }

        $certificate->update(array_filter([
            'name' => $data['name'] ?? $certificate->name,
            'description' => $data['description'] ?? $certificate->description,
        ]));

        if ($request->hasFile('image')) {
            $certificate->clearMediaCollection('certificates');
            $certificate->addMediaFromRequest('image')->toMediaCollection('certificates');
        }

        return [
            'message' => 'Certificate updated successfully.',
            'certificate' => $this->formatCertificate($certificate),
        ];
    }

    public function delete(Certificate $certificate): array
    {
        $profile = $this->profile();

        if ($certificate->profile_id !== $profile->id) {
            throw new AuthorizationException('Unauthorized access to this certificate.');
        }

        $certificate->clearMediaCollection('certificates');
        $certificate->delete();

        return ['message' => 'Certificate deleted successfully.'];
    }

    private function formatCertificate(Certificate $certificate): array
    {
        return [
            'id' => $certificate->id,
            'name' => $certificate->name,
            'description' => $certificate->description,
            'image_url' => $certificate->getFirstMediaUrl('certificates'),
        ];
    }
}
