<?php

namespace Database\Seeders;

use App\Models\EducationalInstitution;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = [
            [
                'name'           => 'Georgia Northwestern Technical College',
                'type'           => 'technical_college',
                'address'        => 'One Maurice Culberson Dr, Rome, GA 30161',
                'phone'          => '706-295-6963',
                'contact_person' => 'Dr. Lisa Smith',
            ],
            [
                'name'           => 'Chattahoochee Technical College',
                'type'           => 'technical_college',
                'address'        => '980 South Cobb Dr SE, Marietta, GA 30060',
                'phone'          => '770-528-4545',
                'contact_person' => 'Dr. Ron Banks',
            ],
            [
                'name'           => 'Kennesaw State University',
                'type'           => 'university',
                'address'        => '1000 Chastain Rd, Kennesaw, GA 30144',
                'phone'          => '470-578-6000',
                'contact_person' => 'Prof. Angela Moore',
            ],
            [
                'name'           => 'University of North Georgia',
                'type'           => 'university',
                'address'        => '82 College Cir, Dahlonega, GA 30597',
                'phone'          => '706-864-1400',
                'contact_person' => 'Prof. James Carter',
            ],
        ];

        foreach ($institutions as $data) {
            EducationalInstitution::create($data);
        }
    }
}
