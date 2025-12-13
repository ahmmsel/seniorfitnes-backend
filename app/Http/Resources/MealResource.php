<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $typeTranslations = [
            'breakfast' => 'فطور',
            'lunch'     => 'غداء',
            'dinner'    => 'عشاء',
            'snack'     => 'وجبة خفيفة',
            'other'     => 'أخرى',
        ];

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'date'        => $this->date,
            'type'     => $typeTranslations[$this->type],
            'image_url'   => $this->absoluteMediaUrl($this->getFirstMediaUrl('meals')),
            'calories'    => $this->calories,
            'protein'     => $this->protein,
            'fats'        => $this->fats,
            'carbs'       => $this->carbs,
        ];
    }

    private function absoluteMediaUrl(?string $url): ?string
    {
        if (empty($url)) return null;

        // If URL already absolute, return as-is
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        // Otherwise prepend app url
        $base = rtrim(config('app.url') ?: env('APP_URL', ''), '/');
        return $base ? $base . '/' . ltrim($url, '/') : $url;
    }
}
