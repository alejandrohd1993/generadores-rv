<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Provider;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'alejandrohd1993@gmail.com',
            'password' => Hash::make('123456'), // asegúrate de hashearlo
        ]);

        Provider::create([
            'nit' => '222222222222',
            'nombre' => 'Consumidor Final',
            'email' => 'consumidorfinal@mail.com',
            'telefono' => '1234567890',
            'direccion' => 'Calle 123',
            'tipo_persona' => 'Jurídica',          
        ]);

        Customer::create([
            'nit' => '222222222222',
            'nombre' => 'Consumidor Final',
            'email' => 'consumidorfinal@mail.com',
            'telefono' => '1234567890',
            'direccion' => 'Calle 123',
            'tipo_persona' => 'Jurídica',          
        ]);
    }
}
