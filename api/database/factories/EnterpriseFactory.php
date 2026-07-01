<?php

namespace Database\Factories;

use App\Enums\EnterpriseStatus;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnterpriseFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Crea una Company real si el test no pasa una explícita con ->for()/company_id.
            'company_id' => Company::factory(),
            'name' => fake()->company().' Asociada',
            'doc_number' => 'EN-'.fake()->unique()->numerify('####'),
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('####-####'),
            'enterprises_status' => EnterpriseStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['enterprises_status' => EnterpriseStatus::Inactive]);
    }
}
