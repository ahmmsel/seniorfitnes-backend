<?php

namespace Database\Factories;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    protected $exercises = [
        'chest' => [
            'ضغط بنش',
            'ضغط دمبل مستوي',
            'ضغط مائل عالي',
            'تفتيح دمبل',
            'تفتيح كيبل',
        ],
        'shoulders' => [
            'رفع جانبي',
            'ضغط كتف بار',
            'ضغط كتف دمبل',
            'رفع أمامي',
            'رفرفة خلفية',
        ],
        'biceps' => [
            'بار بايسبس',
            'دمبل بايسبس',
            'بايسبس تركيز',
            'بايسبس حبل',
            'بايسبس عكسي',
        ],
        'abs' => [
            'بلانك',
            'كرانش',
            'رفع أرجل',
            'تويست روسي',
            'تمرين الدراجة',
        ],
        'leg' => [
            'سكوات',
            'لانجز',
            'ليج برس',
            'رفعة رومانية',
            'إطالة خلفية',
        ],
        'back' => [
            'سحب أرضي',
            'سحب علوي',
            'دمبل رو',
            'بار رو',
            'سحب كيبل ضيق',
        ],
    ];

    protected $instructions = [
        'chest' =>
        'حافظ على ثبات الكتفين وادفع باستخدام عضلات الصدر فقط. ارفع الوزن ببطء وتحكم.',
        'shoulders' =>
        'ارفع الوزن دون تأرجح وتجنب استخدام الظهر. ركّز على حركة الكتف فقط.',
        'biceps' =>
        'ارفع الوزن مع عصر عضلة البايسبس في الأعلى، ثم أنزله ببطء للتحكم الكامل.',
        'abs' =>
        'حافظ على شد عضلات البطن وتجنب سحب الرقبة. ركّز على الحركة من البطن فقط.',
        'leg' =>
        'انزل بعمق مع إبقاء ظهرك مستقيمًا. ادفع للأعلى باستخدام الفخذين.',
        'back' =>
        'اسحب الوزن نحو جسمك مع إبقاء الظهر مستقيمًا وكتفيك للخلف لتفعيل عضلات الظهر.',
    ];

    public function definition()
    {
        // اختر مجموعة عضلية عشوائياً
        $group = array_rand($this->exercises);

        return [
            'name'         => $this->faker->randomElement($this->exercises[$group]),
            'instructions' => $this->instructions[$group],
        ];
    }
}
