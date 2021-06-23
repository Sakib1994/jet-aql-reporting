<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyAdsData extends Model
{
    use HasFactory;
    public function account()
    {
        return $this->belongsTo(adsAccount::class, 'AdsAccountId');
    }
}
