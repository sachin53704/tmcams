<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'id' => 1,
                'configurable_type' => 'App\Models\Tenant',
                'configurable_id' => '1',
                'key' => 'PAYROLL_DATE',
                'value' => '16',
                'group' => 'attendance',
                'type' => 'text'
            ],
            [
                'id' => 2,
                'configurable_type' => 'App\Models\Tenant',
                'configurable_id' => '1',
                'key' => 'LATE_MARK_TIMING',
                'value' => '15',
                'group' => 'attendance',
                'type' => 'text'
            ],
            [
                'id' => 3,
                'configurable_type' => 'App\Models\Tenant',
                'configurable_id' => '1',
                'key' => 'LATE_MARK_TIMING_DIVYANG',
                'value' => '60',
                'group' => 'attendance',
                'type' => 'text'
            ],
            [
                'id' => 4,
                'configurable_type' => 'App\Models\Tenant',
                'configurable_id' => '1',
                'key' => 'HALF_DAY_DURATION',
                'value' => '7200',
                'group' => 'attendance',
                'type' => 'text'
            ],
            [
                'id' => 5,
                'configurable_type' => 'App\Models\Tenant',
                'configurable_id' => '1',
                'key' => 'MIN_COMPLETION_HOUR',
                'value' => '28800',
                'group' => 'attendance',
                'type' => 'text'
            ],
            [
                'id' => 6,
                'configurable_type' => 'App\Models\Tenant',
                'configurable_id' => '2',
                'key' => 'PAYROLL_DATE',
                'value' => '16',
                'group' => 'attendance',
                'type' => 'text'
            ],
            [
                'id' => 7,
                'configurable_type' => 'App\Models\Tenant',
                'configurable_id' => '2',
                'key' => 'LATE_MARK_TIMING',
                'value' => '15',
                'group' => 'attendance',
                'type' => 'text'
            ],
            [
                'id' => 8,
                'configurable_type' => 'App\Models\Tenant',
                'configurable_id' => '2',
                'key' => 'LATE_MARK_TIMING_DIVYANG',
                'value' => '60',
                'group' => 'attendance',
                'type' => 'text'
            ],
            [
                'id' => 9,
                'configurable_type' => 'App\Models\Tenant',
                'configurable_id' => '2',
                'key' => 'HALF_DAY_DURATION',
                'value' => '16200',
                'group' => 'attendance',
                'type' => 'text'
            ],
            [
                'id' => 10,
                'configurable_type' => 'App\Models\Tenant',
                'configurable_id' => '2',
                'key' => 'MIN_COMPLETION_HOUR',
                'value' => '32400',
                'group' => 'attendance',
                'type' => 'text'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate([
                'id' => $setting['id']
            ], [
                'id' => $setting['id'],
                'configurable_type' => $setting['configurable_type'],
                'configurable_id' => $setting['configurable_id'],
                'key' => $setting['key'],
                'value' => $setting['value'],
                'group' => $setting['group'],
                'type' => $setting['type'],
            ]);
        }
    }
}
