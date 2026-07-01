<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Usuario de prueba para poder loguearse inmediatamente después de
     * `docker-compose up --build`, sin pasos manuales adicionales.
     *
     * firstOrCreate() por email hace que correr este seeder en cada arranque
     * del contenedor (ver docker/entrypoint.sh) sea seguro: si el usuario ya
     * existe, no hace nada; nunca duplica ni falla por email repetido.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@aem.test'],
            [
                'name' => 'Admin AEM',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
