<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsSummarizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts_summarizes', function (Blueprint $table) {
            $table->id();
            $table->date('date')->useCurrent();
            $table->string('accountName', 60);
            $table->integer('googleAds');
            $table->integer('yahooAds');
            $table->integer('total');
            $table->integer('budget');
            $table->integer('numberOfCalls');
            $table->integer('costPerCall');
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
        Schema::dropIfExists('accounts_summarizes');
    }
}
