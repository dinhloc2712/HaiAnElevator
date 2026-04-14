<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IncidentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $elv1 = \App\Models\Elevator::where('code', 'HA-91226-EM')->first();
        if (!$elv1) $elv1 = \App\Models\Elevator::first();

        $elv2 = \App\Models\Elevator::where('code', 'HA-93120-EM')->first();
        if (!$elv2) $elv2 = \App\Models\Elevator::skip(1)->first() ?? $elv1;

        \App\Models\Incident::create([
            'code' => 'INC-2026-001',
            'elevator_id' => $elv1->id,
            'reporter_name' => 'Nguyễn Văn A',
            'reporter_phone' => '0912345678',
            'description' => 'Thang máy bị kẹt tại tầng 25, có người bên trong.',
            'priority' => 'emergency',
            'status' => 'processing',
            'reported_at' => '2026-03-25 08:30:00',
        ]);

        \App\Models\Incident::create([
            'code' => 'INC-2026-002',
            'elevator_id' => $elv2->id,
            'reporter_name' => 'Trần Thị B',
            'reporter_phone' => '0987654321',
            'description' => 'Cửa thang máy đóng mở không đều, phát ra tiếng kêu lạ.',
            'priority' => 'medium',
            'status' => 'new',
            'reported_at' => '2026-03-25 07:45:00',
        ]);
    }
}
