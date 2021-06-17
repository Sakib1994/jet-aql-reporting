<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

class adsAccount extends Model
{
    use HasFactory;
    public function hourlyAdsDatas()
    {
        return $this->hasMany(HourlyAdsData::class, 'AdsAccountId');
    }
    public function dailyAdsDatas()
    {
        return $this->hasMany(DailyAdsData::class, 'AdsAccountId');
    }
    public function scopeIndividualAccounts($query)
    {
        return $query->where('platform', '!=', 'all');
    }
}
