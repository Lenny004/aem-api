<?php

namespace Database\Factories;

use App\Enums\CompanyStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'doc_number' => 'CO-'.fake()->unique()->numerify('####'),
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('####-####'),
            'companys_status' => CompanyStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['companys_status' => CompanyStatus::Inactive]);
    }
}
