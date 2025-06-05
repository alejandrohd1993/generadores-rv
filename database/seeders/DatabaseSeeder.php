<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Provider;
use App\Models\Setting;
use App\Models\Suplly;
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

        // // Crear usuario operador
        // $operatorUser = User::firstOrCreate(
        //     ['email' => 'operador@mail.com'],
        //     [
        //         'name' => 'Operador Demo',
        //         'password' => Hash::make('123456'),
        //     ]
        // );

        // // Asignar rol operador
        // $operatorUser->assignRole($operatorRole);

        // Crear proveedor
        Provider::firstOrCreate(
            ['email' => 'consumidorfinal@mail.com'],
            [
                'nit' => '222222222222',
                'nombre' => 'Colaboradores RV',
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

        //Crear correo de prueba para notificaciones
        Setting::firstOrCreate(['id' => 1], [
            'accounting_email' => 'contabilidad@example.com',
            'maintenance_email' => 'mantenimiento@example.com',
        ]);

        Suplly::create([
            'tipo' => 'aceite',
            'nombre' => 'Aceite Mobil 350 horas',
            'horas' => 350,
        ]);

        Suplly::create([
            'tipo' => 'aceite',
            'nombre' => 'Aceite Antonio spath 200 horas',
            'horas' => 200,
        ]);

        Suplly::create([
            'tipo' => 'filtro',
            'nombre' => 'Filtro 100 horas',
            'horas' => 100,
        ]);
    }
}
