<?php

namespace App\Http\Controllers;

use App\Models\adsAccount;
use App\Models\DailyAdsData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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
                'AdsAccountId' => $value->AdsAccountId,
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
        // ddd($formatedAllAccounts);
        $myCollectionObj = collect($formatedAllAccounts);
        $formatedAllAccounts = $this->paginate($myCollectionObj)->withPath(route('daily-ads.index'));
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
        $yahooAccounts = adsAccount::YahooAccounts()->get();
        // ddd($yahooAccounts);
        return Inertia::render('Ads/CreateDaily', [
            'yahooAccounts' => $yahooAccounts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'clicks' => 'required',
            'adsAccountId' => "required|integer",
            'impressions' => "required|integer",
            'ctr' => "required|regex:/^\d*(\.\d{2})?$/",
            'cost' => "required|integer",
            'cpc' => "required|integer",
        ]);
        $dailyAdsData = new DailyAdsData([
            "AdsAccountId" => $request->adsAccountId,
            "date" => $request->date,
            "clicks" => $request->clicks,
            "impressions" => $request->impressions,
            "ctr" => round($request->ctr, 2),
            "cost" => $request->cost,
            "cpc" => round($request->cpc),
            "conversions" => $request->conversions,
            "conversions_rate" => round($request->conversions_rate, 2),
            "cost_per_conversion" => $request->cost_per_conversion,
        ]);
        $isSaved = DailyAdsData::where("AdsAccountId", $request->adsAccountId)->where('date', $request->date)->first();
        if ($isSaved) {
            return Redirect::route('daily-ads.index')->with('error', 'Already in Database.');
        }
        $dailyAdsData->save();
        return Redirect::route('daily-ads.index')->with('success', 'New Yahoo ads data added.');
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
        $dailyAdsData = DailyAdsData::find($dailyAdsDataId);
        // ddd($dailyAdsData);
        return Inertia::render('Ads/DailyUpdate', [
            'dailyAdsData' => $dailyAdsData,
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
    public function fetch()
    {
        $yahooAccounts = adsAccount::YahooAccounts()->get();
        // dd($yahooAccounts);
        return Inertia::render('Ads/FetchDaily', [
            'yahooAccounts' => $yahooAccounts,
        ]);
    }
    public function savedailyfromcsv(Request $request)
    {
        $name = $request->file('csvFile')->getClientOriginalName();
        $accountId = $request->input('name');
        $request->file('csvFile')->storeAs('uploads', $name, 'public');
        // $path = $request->file('csvFile')->store('public');
        $response = $this->saveDaily($name, $accountId);
        if ($response == "Saved successfully") {
            return Redirect::route('daily-ads.index')->with('success', $response);
        } else {
            return Redirect::route('daily-ads.index')->with('error', $response);
        }
    }
    public function saveDaily($fileName, $accountId)
    {
        $file = mb_convert_encoding(Storage::disk('public')->get("uploads/$fileName"), "UTF-8", "utf-16le");
        $status = "Saved successfully";
        $rows = explode("\n", $file);
        $header = array_shift($rows);
        // The nested array to hold all the arrays
        $dailyAdsDataArray = [];
        $header = explode(',', $header);
        // dd($rows, $header);
        if (count($rows) > 0 && $rows[0] != "") {
            // print_r("more than one line");
            foreach ($rows as $row => $data) {
                //get row data
                if (strlen($data) > 0) {
                    $row_data = explode(',', $data);
                    $date = str_replace('"', '', $row_data[0]);
                    if ($date == "--") {
                        continue;
                    }
                    $dailyAdsData = new DailyAdsData([
                        "AdsAccountId" => $accountId,
                        "date" => $date,
                        "clicks" => $row_data[2],
                        "impressions" => $row_data[1],
                        "ctr" => round($row_data[3] * 100, 2),
                        "cost" => round($row_data[4]),
                        "cpc" => round($row_data[5]),
                        "conversions" => round($row_data[6]),
                        "conversions_rate" => round($row_data[7] * 100, 2),
                        "cost_per_conversion" => round($row_data[8]),
                    ]);
                    array_push($dailyAdsDataArray, $dailyAdsData);
                }
            }

        }
        // ddd($dailyAdsDataArray);
        foreach ($dailyAdsDataArray as $key => $value) {
            $isSaved = DailyAdsData::where('AdsAccountId', $accountId)->where('date', "$value->date")->first();
            if ($isSaved) {
                if ($status == "Saved successfully") {
                    $status = "Some or All Rows exist";
                }  
                continue;
            }
            $value->save();
        }
        Storage::disk('public')->delete("uploads/$fileName");
        return $status;
    }
}
