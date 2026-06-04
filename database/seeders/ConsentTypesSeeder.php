<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsentTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'slug' => 'personal-data',
                'title' => 'Согласие на обработку персональных данных',
                'description' => 'Нажимая на этот чекбокс, вы подтверждаете свое согласие на обработку ваших персональных данных в соответствии с Федеральным законом от 27 июля 2006 года номер 152-ФЗ "О персональных данных". Мы гарантируем конфиденциальность и безопасность ваших данных.',
                'is_required' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'newsletter',
                'title' => 'Согласие на рассылку',
                'description' => 'Нажимая на этот чекбокс, вы соглашаетесь получать информационную рассылку о событиях календаря на ваш электронный адрес. Вы можете отписаться от рассылки в любой момент.',
                'is_required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($types as $type) {
            DB::table('consent_types')->updateOrInsert(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}