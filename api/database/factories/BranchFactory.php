<?php

namespace Database\Factories;

use App\Enums\BranchStatus;
use App\Models\Enterprise;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'enterprise_id' => Enterprise::factory(),
            'name' => 'Sucursal '.fake()->city(),
            'address' => fake()->address(),
            // SS-01 siempre existe en el catálogo de 44 municipios (config/municipalities.php).
            'municipality_codigo' => 'SS-01',
            'phone' => fake()->numerify('####-####'),
            'branchs_status' => BranchStatus::Active,
        ];
    }
}
