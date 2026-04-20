<?php

namespace Database\Seeders;

use App\Models\Segment;
use Illuminate\Database\Seeder;

class SegmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $segments = [
            ['name' => 'Beleza e Estética'],
            ['name' => 'Saúde e Bem-estar'],
            ['name' => 'Educação'],
            ['name' => 'Consultoria'],
            ['name' => 'Tecnologia'],
            ['name' => 'Serviços Gerais'],
        ];

        foreach ($segments as $segment) {
            Segment::firstOrCreate(
                ['name' => $segment['name']],
                $segment
            );
        }
    }
}
