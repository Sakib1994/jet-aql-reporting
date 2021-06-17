<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdsAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ads_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('accountId', 100);
            $table->string('name', 100);
            $table->string('aqlName', 100);
            $table->string('platform', 50);
            $table->integer('monthlyBudget');
            $table->integer('dailyBudget');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ads_accounts');
    }
}
