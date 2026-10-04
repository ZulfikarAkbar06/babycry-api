<?php

namespace Database\Seeders;

use App\Models\CryLog;
use Illuminate\Database\Seeder;

class CryLogSeeder extends Seeder
{
    public function run(): void
    {
        if (CryLog::query()->exists()) {
            return; // jangan duplikasi data setiap kali container restart
        }

        $types = ['hungry', 'tired', 'discomfort', 'pain', 'burping', 'lonely'];

        for ($i = 0; $i < 15; $i++) {
            CryLog::create([
                'device_id'        => 'BABYCRY-001',
                'is_crying'        => true,
                'cry_type'         => $types[array_rand($types)],
                'confidence'       => mt_rand(6000, 9900) / 100,
                'sound_level'      => mt_rand(5500, 9000) / 100,
                'temperature'      => mt_rand(2500, 3000) / 100,
                'humidity'         => mt_rand(5000, 8000) / 100,
                'duration_seconds' => mt_rand(5, 120),
                'notes'            => 'Data contoh dari seeder',
                'recorded_at'      => now()->subMinutes($i * 47),
            ]);
        }
    }
}
