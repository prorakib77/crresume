<?php

namespace Database\Seeders;

use App\Models\CustomizationSetting;
use Illuminate\Database\Seeder;

class CustomizationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (CustomizationSetting::defaultDefinitions() as $settingKey => $definition) {
            CustomizationSetting::updateOrCreate(
                ['setting_key' => $settingKey],
                array_merge($definition, ['setting_key' => $settingKey])
            );
        }

        $this->command->info('Customization settings seeded successfully.');
    }
}
