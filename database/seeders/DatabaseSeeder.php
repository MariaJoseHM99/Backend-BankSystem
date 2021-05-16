<?php

namespace Database\Seeders;

use App\Models\V1\Role;
use DB;
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
        DB::table("configuration")->insert([
            "createdAt" => date("Y-m-d"),
            "surchargeRate" => 0.028,
            "minAmountRate" => 0.05
        ]);
        $newCreditCardType = new CreditCardType();
        $newCreditCardType->fundingLevel = "Classic";
        $newCreditCardType->interestRate = 0.018;
        $newCreditCardType->credit = 8000;
        $newCreditCardType->save();
    }
}
