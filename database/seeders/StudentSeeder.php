<?php

namespace Database\Seeders;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample students
        $student1 = Student::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'Michael',
            'date_of_birth' => '2010-05-15',
            'gender' => 'male',
            'email' => 'john.doe@example.com',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62701',
            'country' => 'USA',
            'emergency_contact_name' => 'Jane Doe',
            'enrollment_date' => '2024-09-01',
            'status' => 'active',
        ]);
        $student1->phoneNumbers()->create(['area_code' => '404', 'number' => '5550101', 'type' => 'primary', 'is_primary' => true]);
        $student1->phoneNumbers()->create(['area_code' => '404', 'number' => '5550102', 'type' => 'emergency']);

        $student2 = Student::create([
            'first_name' => 'Emma',
            'last_name' => 'Smith',
            'date_of_birth' => '2011-08-22',
            'gender' => 'female',
            'email' => 'emma.smith@example.com',
            'address' => '456 Oak Ave',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62702',
            'country' => 'USA',
            'emergency_contact_name' => 'Robert Smith',
            'enrollment_date' => '2024-09-01',
            'status' => 'active',
        ]);
        $student2->phoneNumbers()->create(['area_code' => '404', 'number' => '5550201', 'type' => 'primary', 'is_primary' => true]);
        $student2->phoneNumbers()->create(['area_code' => '404', 'number' => '5550202', 'type' => 'emergency']);

        $student3 = Student::create([
            'first_name' => 'Michael',
            'last_name' => 'Johnson',
            'middle_name' => 'David',
            'date_of_birth' => '2009-12-10',
            'gender' => 'male',
            'email' => 'michael.johnson@example.com',
            'address' => '789 Elm St',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62703',
            'country' => 'USA',
            'emergency_contact_name' => 'Sarah Johnson',
            'enrollment_date' => '2023-09-01',
            'status' => 'active',
        ]);
        $student3->phoneNumbers()->create(['area_code' => '404', 'number' => '5550301', 'type' => 'primary', 'is_primary' => true]);
        $student3->phoneNumbers()->create(['area_code' => '404', 'number' => '5550302', 'type' => 'emergency']);

        // Create guardians
        $guardian1 = Guardian::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'relationship' => 'mother',
            'email' => 'jane.doe@example.com',
            'address' => '123 Main St',
            'occupation' => 'Teacher',
        ]);
        $guardian1->phoneNumbers()->create(['area_code' => '404', 'number' => '5550102', 'type' => 'primary', 'is_primary' => true]);

        $guardian2 = Guardian::create([
            'first_name' => 'Robert',
            'last_name' => 'Smith',
            'relationship' => 'father',
            'email' => 'robert.smith@example.com',
            'address' => '456 Oak Ave',
            'occupation' => 'Engineer',
        ]);
        $guardian2->phoneNumbers()->create(['area_code' => '404', 'number' => '5550202', 'type' => 'primary', 'is_primary' => true]);

        // Link guardians to students
        $student1->guardians()->attach($guardian1->id, ['is_primary' => true]);
        $student2->guardians()->attach($guardian2->id, ['is_primary' => true]);
    }
}
