<?php

namespace Database\Seeders;

use App\Models\ApplianceType;
use App\Models\CommonProblem;
use Illuminate\Database\Seeder;

class ApplianceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $applianceTypes = [
            'Television' => [
                'No Power',
                'No Display/Black Screen',
                'Sound Issues',
                'Remote Not Working',
                'Screen Flickering',
                'Color Distortion',
                'Lines on Screen',
                'Not Turning On',
                'Overheating',
                'HDMI Port Issues',
            ],
            'Air Conditioner' => [
                'Not Cooling',
                'Leaking Water',
                'Making Strange Noises',
                'Not Turning On',
                'Bad Odor',
                'Remote Not Working',
                'Fan Not Spinning',
                'Compressor Issues',
                'Thermostat Problems',
                'Freezing Up',
            ],
            'Refrigerator' => [
                'Not Cooling',
                'Freezer Not Working',
                'Leaking Water',
                'Making Strange Noises',
                'Ice Maker Not Working',
                'Light Not Working',
                'Door Not Closing Properly',
                'Temperature Fluctuation',
                'Frost Buildup',
                'Compressor Issues',
            ],
            'Washing Machine' => [
                'Not Spinning',
                'Not Draining',
                'Not Turning On',
                'Leaking Water',
                'Making Strange Noises',
                'Not Agitating',
                'Door Not Locking',
                'Error Codes',
                'Not Filling with Water',
                'Vibrating Excessively',
            ],
            'Microwave' => [
                'Not Heating',
                'Not Turning On',
                'Turntable Not Spinning',
                'Making Strange Noises',
                'Spark Inside',
                'Door Not Closing',
                'Light Not Working',
                'Buttons Not Working',
                'Smell of Burning',
                'Display Not Working',
            ],
        ];

        foreach ($applianceTypes as $typeName => $problems) {
            $type = ApplianceType::firstOrCreate(
                ['name' => $typeName],
                ['slug' => strtolower(str_replace(' ', '-', $typeName))]
            );

            foreach ($problems as $problemName) {
                CommonProblem::firstOrCreate([
                    'appliance_type_id' => $type->id,
                    'problem_name' => $problemName,
                ]);
            }
        }
    }
}
