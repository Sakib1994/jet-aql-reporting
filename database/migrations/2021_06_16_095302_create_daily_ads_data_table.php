<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyAdsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_ads_data', function (Blueprint $table) {
            $table->id();
            $table->integer('AdsAccountId');
            $table->date('date')->useCurrent();
            $table->integer('clicks');
            $table->integer('impressions');
            $table->float('ctr', 5, 2);
            $table->integer('cost');
            $table->integer('cpc');
            $table->integer('conversions');
            $table->float('conversions_rate', 5, 2);
            $table->integer('cost_per_conversion');
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
        Schema::dropIfExists('daily_ads_data');
    }
}
