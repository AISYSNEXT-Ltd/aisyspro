<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate(
            ['slug' => 'administrateur'],
            ['name' => 'Administrateur'],
        );

        Role::query()->firstOrCreate(['slug' => 'commercial'], ['name' => 'Commercial']);
        Role::query()->firstOrCreate(['slug' => 'editeur'], ['name' => 'Éditeur']);

        if (config('aisyspro.admin.password')) {
            User::query()->updateOrCreate(
                ['login' => config('aisyspro.admin.login')],
                [
                    'name' => config('aisyspro.admin.name'),
                    'email' => config('aisyspro.admin.email') ?: 'admin@staging.aisyspro.tn',
                    'password' => config('aisyspro.admin.password'),
                    'role_id' => $adminRole->id,
                    'is_active' => true,
                ],
            );
        }
    }
}
