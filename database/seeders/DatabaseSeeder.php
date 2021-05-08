<?php

namespace Database\Seeders;

use App\Models\V1\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
        $roles = ["Cliente", "Gerente de sucursal", "Ejecutivo"];
        foreach ($roles as $role) {
            $newRole = new Role();
            $newRole->name = $role;
            $newRole->save();
        }
    }
}
