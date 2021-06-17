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
            $table->float('ctr', 4, 2);
            $table->float('cost', 8, 2);
            $table->float('cpc', 6, 2);
            $table->integer('conversions');
            $table->float('conversions_rate', 5, 2);
            $table->float('cost_per_conversion', 8, 2);
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
