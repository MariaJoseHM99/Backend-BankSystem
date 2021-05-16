<?php

namespace Database\Seeders;

use App\Models\V1\CreditCardType;
use DB;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
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
