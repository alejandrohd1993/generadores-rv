<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Provider;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'administrador']);
        $operatorRole = Role::firstOrCreate(['name' => 'operador']);

        // Crear usuario administrador
        $adminUser = User::firstOrCreate(
            ['email' => 'alejandrohd1993@gmail.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('123456'),
            ]
        );

        // Asignar rol administrador
        $adminUser->assignRole($adminRole);

        // Crear usuario operador
        $operatorUser = User::firstOrCreate(
            ['email' => 'operador@mail.com'],
            [
                'name' => 'Operador Demo',
                'password' => Hash::make('123456'),
            ]
        );

        // Asignar rol operador
        $operatorUser->assignRole($operatorRole);

        // Crear proveedor
        Provider::firstOrCreate(
            ['email' => 'consumidorfinal@mail.com'],
            [
                'nit' => '222222222222',
                'nombre' => 'Consumidor Final',
                'telefono' => '1234567890',
                'direccion' => 'Calle 123',
                'tipo_persona' => 'Jurídica',
            ]
        );

        // Crear cliente
        Customer::firstOrCreate(
            ['email' => 'consumidorfinal@mail.com'],
            [
                'nit' => '222222222222',
                'nombre' => 'Consumidor Final',
                'telefono' => '1234567890',
                'direccion' => 'Calle 123',
                'tipo_persona' => 'Jurídica',
            ]
        );
    }
}
