<?php

namespace Database\Factories;

use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkoutFactory extends Factory
{
    protected $model = Workout::class;

    protected $groups = [
        'chest',     // الصدر
        'shoulders', // الأكتاف
        'biceps',    // البايسبس
        'abs',       // البطن
        'leg',       // الأرجل
        'back',      // الظهر
    ];

    protected $descriptions = [
        'chest' =>
        'برنامج تدريبي يستهدف عضلات الصدر العلوية والوسطى والسفلية من خلال تمارين الضغط والدفع لزيادة القوة والكتلة العضلية.',
        'shoulders' =>
        'برنامج لتمرين الأكتاف الأمامية والجانبية والخلفية لتحسين القوة والثبات وتحقيق توازن في الجزء العلوي من الجسم.',
        'biceps' =>
        'خطة تدريب مركّزة على تضخيم وتقوية عضلات البايسبس عبر تمارين العزل باستخدام الدمبل والبار.',
        'abs' =>
        'برنامج قوي لشد وتقوية عضلات البطن العلوية والسفلية والجوانب، يساعد على تحسين الثبات والبنية الأساسية للجسم.',
        'leg' =>
        'برنامج شامل لعضلات الأرجل يستهدف الفخذين والسمنة وأوتار الركبة لزيادة القوة والتحمل.',
        'back' =>
        'برنامج تدريبي متكامل لعضلات الظهر العلوية والسفلية يهدف لزيادة القوة وتحسين وضع الجسم.',
    ];

    public function definition()
    {
        $group = $this->faker->randomElement($this->groups);

        return [
            'name'        => 'تمارين ' . $this->getArabicGroupName($group),
            'slug'        => $group,
            'description' => $this->descriptions[$group],
        ];
    }

    private function getArabicGroupName($group)
    {
        return [
            'chest' => 'الصدر',
            'shoulders' => 'الأكتاف',
            'biceps' => 'البايسبس',
            'abs' => 'البطن',
            'leg' => 'الأرجل',
            'back' => 'الظهر',
        ][$group];
    }
}
