<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User\User;
use App\Models\User\Person;
use App\Models\Branch;

class CEOSeeder extends Seeder
{
    public function run(): void
    {
        // Primero creamos una sucursal base si no existe
        $branch = Branch::firstOrCreate(
            ['branchName' => 'Sucursal Central'],
            ['address' => 'Av. Principal 123, Torreón, Coahuila']
        );

        // Crear persona aleatoria (usando fake)
        $person = Person::create([
            'firstName' => fake()->firstName(),
            'lastName' => fake()->lastName(),
            'CURP' => strtoupper(fake()->bothify('????######???????#')),
            'phoneNumber' => fake()->numerify('##########'),
        ]);

        $user = User::create([
            'userType' => 'CEO',
            'email' => "ceo@gmail.com",
            'password' => Hash::make('Contraseña123'), // Contraseña segura por defecto
            'branch_id' => $branch->id,
            'person_id' => $person->id,
        ]);

        $this->command->info('✅ CEO creado exitosamente');
    }
}
