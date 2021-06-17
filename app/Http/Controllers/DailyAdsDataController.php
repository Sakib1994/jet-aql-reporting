<?php

namespace App\Http\Controllers;

use App\Models\DailyAdsData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DailyAdsDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dailyAllAccounts = DailyAdsData::get();
        $formatedAllAccounts = [];
        foreach ($dailyAllAccounts as $key => $value) {
            $dailyAdsData = [
                'id' => $value->id,
                'name' => $value->account->name,
                '日付' => $value->date,
                '表示回数' => $value->impressions,
                'クリック数' => $value->clicks,
                'クリック率' => round($value->ctr, 2),
                'クリック単価' => $value->cpc,
                '費用' => $value->cost,
                'コンバージョン' => $value->conversions,
                'コンバージョン率' => round($value->conversions_rate, 2),
                'コンバージョン単価' => $value->cost_per_conversion,
            ];
            array_push($formatedAllAccounts, $dailyAdsData);
        }

        $myCollectionObj = collect($formatedAllAccounts);

        $formatedAllAccounts = $this->paginate($myCollectionObj)->withPath('/daily-ads');
        // ddd($formatedAllAccounts);
        return Inertia::render('Ads/DailyAds', [
            "dailyAds" => $formatedAllAccounts,
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
     * @param  \App\Models\DailyAdsData  $dailyAdsData
     * @return \Illuminate\Http\Response
     */
    public function show(DailyAdsData $dailyAdsData)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DailyAdsData  $dailyAdsData
     * @return \Illuminate\Http\Response
     */
    public function edit(int $dailyAdsDataId)
    {
        $dailyAdsData=DailyAdsData::find($dailyAdsDataId);
        // ddd($dailyAdsData);
        return Inertia::render('Ads/DailyUpdate', [
            'dailyAdsData' => $dailyAdsData
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DailyAdsData  $dailyAdsData
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DailyAdsData $dailyAdsData)
    {
        $dailyAdsData = DailyAdsData::find($request->id);
        if ($dailyAdsData) {
            $dailyAdsData->update($request->all());
        }
        return Redirect::route('daily-ads.index')->with('success', 'Daily Ads Info updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DailyAdsData  $dailyAdsData
     * @return \Illuminate\Http\Response
     */
    public function destroy(DailyAdsData $dailyAdsData)
    {
        //
    }
}
