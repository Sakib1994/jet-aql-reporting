<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHourlyAdsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hourly_ads_data', function (Blueprint $table) {
            $table->id();
            $table->integer('AdsAccountId');
            $table->datetime('time')->useCurrent();
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
        Schema::dropIfExists('hourly_ads_data');
    }
}
