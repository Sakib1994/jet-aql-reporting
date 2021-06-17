<?php

namespace App\Http\Controllers;

use App\Models\HourlyAdsData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Inertia\Inertia;

class HourlyAdsDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $hourlyAdsData = HourlyAdsData::get();
        
        $formatedAllAccounts = [];
        foreach ($hourlyAdsData as $key => $value) {
            $dailyAdsData = [
                'id' => $value->id,
                'name' => $value->account->name,
                'time' => $value->time,
                'impressions' => $value->impressions,
                'clicks' => $value->clicks,
                'ctr' => round($value->ctr, 2),
                'cpc' => $value->cpc,
                'cost' => $value->cost,
                'conversions' => $value->conversions,
                'conversions_rate' => round($value->conversions_rate, 2),
                'cost_per_conversion' => $value->cost_per_conversion,
            ];
            array_push($formatedAllAccounts, $dailyAdsData);
        }
        $myCollectionObj = collect($formatedAllAccounts);

        $formatedAllAccounts = $this->paginate($myCollectionObj)->withPath('/hourly-ads');
        // ddd($formatedAllAccounts);
        return Inertia::render('Ads/HourlyAds', [
            "hourlyAds" => $formatedAllAccounts,
        ]);
    }
    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HourlyAdsData  $hourlyAdsData
     * @return \Illuminate\Http\Response
     */
    public function show(HourlyAdsData $hourlyAdsData)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HourlyAdsData  $hourlyAdsData
     * @return \Illuminate\Http\Response
     */
    public function edit(HourlyAdsData $hourlyAdsData)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HourlyAdsData  $hourlyAdsData
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HourlyAdsData $hourlyAdsData)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HourlyAdsData  $hourlyAdsData
     * @return \Illuminate\Http\Response
     */
    public function destroy(HourlyAdsData $hourlyAdsData)
    {
        //
    }
}
