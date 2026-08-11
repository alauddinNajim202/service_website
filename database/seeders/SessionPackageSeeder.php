<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SessionPackage;

class SessionPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate the table before seeding to avoid duplicates during testing
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        DB::table('session_packages')->truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $packages = [
            [
                // VIP Quiz
                'type' => 'vip_access',
                'name' => '1 Month VIP Access',
                'name_en' => '1 Month VIP Access',
                'name_fr' => 'Accès VIP d\'un mois',
                'name_es' => 'Acceso VIP de 1 mes',
                'price' => 29.00,
                'duration' => '1 full month Access',
                'duration_en' => '1 full month Access',
                'duration_fr' => 'Accès d\'un mois complet',
                'duration_es' => 'Acceso de un mes completo',
                'badge' => 'Most Popular',
                'badge_en' => 'Most Popular',
                'badge_fr' => 'Le plus populaire',
                'badge_es' => 'Más popular',
                'description' => 'Get full VIP access to our experts for an entire month.',
                'description_en' => 'Get full VIP access to our experts for an entire month.',
                'description_fr' => 'Obtenez un accès VIP complet à nos experts pendant un mois entier.',
                'description_es' => 'Obtenga acceso VIP completo a nuestros expertos durante todo un mes.',
                'status' => 'active',
                'is_feature' => 1,
                'feature_text' => 'Best value for long-term guidance!',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                // Quick Advice
                'type' => 'quick_advice',
                'name' => 'Personalized Advice',
                'name_en' => 'Personalized Advice',
                'name_fr' => 'Conseils personnalisés',
                'name_es' => 'Consejos personalizados',
                'price' => 19.00,
                'duration' => '25 minutes of exchange',
                'duration_en' => '25 minutes of exchange',
                'duration_fr' => '25 minutes d\'échange',
                'duration_es' => '25 minutos de intercambio',
                'badge' => null,
                'badge_en' => null,
                'badge_fr' => null,
                'badge_es' => null,
                'description' => 'A 25-minute one-on-one session tailored to your needs.',
                'description_en' => 'A 25-minute one-on-one session tailored to your needs.',
                'description_fr' => 'Une session individuelle de 25 minutes adaptée à vos besoins.',
                'description_es' => 'Una sesión individual de 25 minutos adaptada a sus necesidades.',
                'status' => 'active',
                'is_feature' => 0,
                'feature_text' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                // Personal Advice
                'type' => 'personal_advice',
                'name' => 'Quick advice',
                'name_en' => 'Quick advice',
                'name_fr' => 'Conseil rapide',
                'name_es' => 'Consejo rápido',
                'price' => 9.00,
                'duration' => '10 minutes of chat',
                'duration_en' => '10 minutes of chat',
                'duration_fr' => '10 minutes de chat',
                'duration_es' => '10 minutos de chat',
                'badge' => 'Most Popular',
                'badge_en' => 'Most Popular',
                'badge_fr' => 'Le plus populaire',
                'badge_es' => 'Más popular',
                'description' => 'A quick 10-minute chat to get fast answers.',
                'description_en' => 'A quick 10-minute chat to get fast answers.',
                'description_fr' => 'Un chat rapide de 10 minutes pour obtenir des réponses rapides.',
                'description_es' => 'Un chat rápido de 10 minutos para obtener respuestas rápidas.',
                'status' => 'active',
                'is_feature' => 0,
                'feature_text' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        SessionPackage::insert($packages);
    }
}
