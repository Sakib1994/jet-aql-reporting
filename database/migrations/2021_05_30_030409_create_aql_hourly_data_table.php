<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAqlHourlyDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aql_hourly_data', function (Blueprint $table) {
            $table->id();
            $table->dateTime('time')->useCurrent();
            $table->string('accountName', 100);
            $table->string('prefecture', 100);
            $table->string('requestType', 100);
            $table->string('requestDetail', 100);
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
        Schema::dropIfExists('aql_hourly_data');
    }
}
