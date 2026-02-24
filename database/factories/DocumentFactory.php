<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'entity_type'   => 'Institution',
            'entity_id'     => 0,
            'uploaded_by'   => User::factory(),
            'uuid'          => (string) Str::uuid(),
            'original_name' => $this->faker->word() . '.pdf',
            'stored_path'   => 'documents/Institution/0/' . Str::uuid() . '_test.pdf',
            'mime_type'     => 'application/pdf',
            'size_bytes'    => $this->faker->numberBetween(1_024, 5_242_880),
            'category'      => $this->faker->optional()->randomElement(['Policy', 'Contract', 'IEP', 'Other']),
            'description'   => $this->faker->optional()->sentence(),
        ];
    }

    public function forStudent(int $studentId): static
    {
        return $this->state([
            'entity_type' => 'Student',
            'entity_id'   => $studentId,
            'stored_path' => 'documents/Student/' . $studentId . '/' . Str::uuid() . '_test.pdf',
        ]);
    }

    public function forEmployee(int $employeeId): static
    {
        return $this->state([
            'entity_type' => 'Employee',
            'entity_id'   => $employeeId,
            'stored_path' => 'documents/Employee/' . $employeeId . '/' . Str::uuid() . '_test.pdf',
        ]);
    }
}
