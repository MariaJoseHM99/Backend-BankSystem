<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Init extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create("account", function (Blueprint $table) {
            $table->increments("accountId");
            $table->integer("role")->unsigned(); // ROLE_TYPE
            $table->string("username", 20)->unique();
            $table->string("password");
            $table->string("name", 25);
            $table->string("lastName", 40);
            $table->string("email")->unique();
            $table->date("birthdate");
            $table->string("street", 100);
            $table->string("extNumber", 6);
            $table->string("intNumber", 6)->nullable();
            $table->string("colony", 50);
            $table->string("zipCode", 9);
            $table->string("cellphoneNumber", 10)->nullable();
            $table->string("homePhone", 10);
            $table->dateTime("createdAt");
        });
        Schema::create("card", function (Blueprint $table) {
            $table->increments("cardId");
            $table->integer("accountId")->unsigned();
            $table->foreign("accountId")->references("accountId")->on("account");
            $table->string("cardNumber", 16)->unique();
            $table->integer("cvv");
            $table->date("expirationDate");
            $table->string("pin");
            $table->dateTime("createdAt");
            $table->integer("type"); // 0 -> debit | 1 -> credit
            $table->integer("status"); // CARD_STATUS
        });
        Schema::create("debit_card", function (Blueprint $table) {
            $table->integer("cardId")->unsigned();
            $table->foreign("cardId")->references("cardId")->on("card");
            $table->primary("cardId");
            $table->float("balance");
        });
        Schema::create("credit_card_type", function (Blueprint $table) {
            $table->increments("creditCardTypeId");
            $table->string("fundingLevel", 15)->unique();
            $table->float("interestRate", 8, 3);
            $table->float("credit");
        });
        Schema::create("credit_card", function (Blueprint $table) {
            $table->integer("cardId")->unsigned();
            $table->foreign("cardId")->references("cardId")->on("card");
            $table->primary("cardId");
            $table->integer("creditCardTypeId")->unsigned();
            $table->foreign("creditCardTypeId")->references("creditCardTypeId")->on("credit_card_type");
            $table->float("credit");
            $table->float("positiveBalance");
        });
        Schema::create("transaction", function (Blueprint $table) {
            $table->increments("transactionId");
            $table->integer("destinationCardId")->unsigned();
            $table->foreign("destinationCardId")->references("cardId")->on("card");
            $table->integer("originCardId")->unsigned()->nullable();
            $table->foreign("originCardId")->references("cardId")->on("card");
            $table->integer("type"); // TRANSACTION_TYPE
            $table->dateTime("createdAt");
            $table->float("amount");
            $table->string("reference", 6);
            $table->string("concept", 25)->nullable();
            $table->float("interestRate", 8, 3)->nullable();
            $table->float("surchargeRate", 8, 3)->nullable();
        });
        Schema::create("configuration", function (Blueprint $table) {
            $table->date("createdAt")->primary();
            $table->float("surchargeRate", 8, 3);
            $table->float("minAmountRate", 8, 3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists("transaction");
        Schema::dropIfExists("creditCard");
        Schema::dropIfExists("creditCardType");
        Schema::dropIfExists("debitCard");
        Schema::dropIfExists("card");
        Schema::dropIfExists("role");
        Schema::dropIfExists("account");
    }
}
