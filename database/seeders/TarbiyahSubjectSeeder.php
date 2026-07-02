<?php

namespace Database\Seeders;

use App\Models\TarbiyahSubject;
use App\Support\TarbiyahClass;
use Illuminate\Database\Seeder;

class TarbiyahSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'القرآن',
            'التفسير',
            'الحديث',
            'التوحيد',
            'فقه العبادة',
            'فقه المعاملة',
            'النحو',
            'الصرف',
            'الاعراب',
            'تطبيق القراءة',
            'الأخلاق',
            'المحاورة',
            'العربية',
        ];

        foreach (TarbiyahClass::levels() as $classLevel) {
            foreach ($subjects as $index => $subject) {
                TarbiyahSubject::updateOrCreate(
                    ['class_level' => $classLevel, 'name' => $subject],
                    ['sort_order' => ($index + 1) * 10, 'is_active' => true]
                );
            }
        }
    }
}
