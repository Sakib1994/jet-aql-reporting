<?php

namespace App\Http\Controllers;

use App\Models\adsAccount;
use App\Models\HourlyAdsData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HourlyAdsDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $begin = new Carbon('first day of this month');
        $begin = $begin->isoFormat('YYYY-MM-DD');
        $end = new Carbon('today');
        $end = $end->isoFormat('YYYY-MM-DD');
        $hourlyAdsData = HourlyAdsData::whereDate('time', '>=', $begin)->whereDate('time', '<=', $end)->latest()->get();
        if ($request->startDate && $request->endDate) {
            $begin = $request->startDate;
            $end = $request->endDate;
            $hourlyAdsData = HourlyAdsData::whereDate('time', '>=', $begin)->whereDate('time', '<=', $end)->latest()->get();
        }
        elseif ($request->startDate) {
            $begin = $request->startDate;
            $hourlyAdsData = HourlyAdsData::whereDate('time', '>=', $begin)->latest()->get();
        }
        elseif ($request->endDate) {
            $end = $request->endDate;
            $hourlyAdsData = HourlyAdsData::whereDate('time', '<=', $end)->latest()->get();
        }
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

        $formatedAllAccounts = $this->paginate($myCollectionObj)->withPath(route('hourly-ads.index'));
        if ($request->startDate && $request->endDate) {
            $formatedAllAccounts = $this->paginate($myCollectionObj)
            ->withPath(route('hourly-ads.index', [
                'startDate'=> $request->startDate,
                'endDate'=> $request->endDate,
            ]));
        }
        return Inertia::render('Ads/HourlyAds', [
            "hourlyAds" => $formatedAllAccounts,
            "begin"=>$begin,
            "end"=>$end 
        ]);
    }
    public function datewise(string $date, int $accountId)
    {
        $hourlyAdsData = HourlyAdsData::where('time', "like", "$date%")->where('AdsAccountId', $accountId)->orderBy('time')->get();
        // ddd($date, $accountId, $hourlyAdsData);
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
        $formatedAllAccounts = $this->paginate($myCollectionObj)->withPath("/hourly-ads/$date/$accountId");
        // ddd($formatedAllAccounts);
        return Inertia::render('Ads/HourlyAds', [
            "hourlyAds" => $formatedAllAccounts,
        ]);
    }
    public function paginate($items, $perPage = 20, $page = null, $options = [])
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
    public function edit(int $hourlyAdsDataId)
    {
        $hourlyAdsData = HourlyAdsData::find($hourlyAdsDataId);
        // ddd($hourlyAdsData);
        return Inertia::render('Ads/HourlyUpdate', [
            'hourlyAdsData' => $hourlyAdsData,
        ]);
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
        $hourlyAdsData = HourlyAdsData::find($request->id);
        if ($hourlyAdsData) {
            $hourlyAdsData->update($request->all());
        }
        return Redirect::route('hourly-ads.index')->with('success', 'Hourly Ads Info updated.');
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
    public function fetch()
    {
        $yahooAccounts = adsAccount::where('platform', 'yahoo')->get();
        return Inertia::render('Ads/FetchHourly', [
            'yahooAccounts' => $yahooAccounts,
        ]);
    }
    public function savehourlyfromcsv(Request $request)
    {
        $name = $request->file('csvFile')->getClientOriginalName();
        $accountId = $request->input('name');
        $request->file('csvFile')->storeAs('uploads', $name, 'public');
        // $path = $request->file('csvFile')->store('public');
        $response = $this->save($name, $accountId);
        if ($response == "Saved successfully") {
            return Redirect::route('hourly-ads.index')->with('success', $response);
        } else {
            return Redirect::route('hourly-ads.index')->with('error', $response);
        }
    }

    public function save($fileName, $accountId)
    {
        $file = mb_convert_encoding(Storage::disk('public')->get("uploads/$fileName"), "UTF-8", "utf-16le");
        // $file = str_replace('"""','',mb_convert_encoding($file, "UTF-8", "utf-16le"));
        $status = "Saved successfully";
        $rows = explode("\n", $file);
        $header = array_shift($rows);
        // The nested array to hold all the arrays
        $hourlyAdsDataArray = [];
        $header = explode(',', $header);
        // dump($header);
        if (count($rows) > 0 && $rows[0] != "") {
            // print_r("more than one line");
            foreach ($rows as $row => $data) {
                //get row data
                if (strlen($data) > 0) {
                    // dump($data);
                    $row_data = explode(',', $data);
                    $hour = str_replace('"', '', $row_data[0]);
                    $date = $row_data[1];
                    if (!is_numeric($hour)) {
                        continue;
                    }
                    $time = intval($hour) < 10 ? "$date 0$hour:00:00" : "$date $hour:00:00";
                    $hourlyAdsData = new HourlyAdsData([
                        "AdsAccountId" => $accountId,
                        "time" => $time,
                        "clicks" => $row_data[3],
                        "impressions" => $row_data[2],
                        "ctr" => round($row_data[4] * 100, 2),
                        "cost" => $row_data[5],
                        "cpc" => round($row_data[6]),
                        "conversions" => round($row_data[7]),
                        "conversions_rate" => round($row_data[8] * 100, 2),
                        "cost_per_conversion" => round($row_data[9]),
                    ]);
                    // dd($hourlyAdsData,$row_data);
                    array_push($hourlyAdsDataArray, $hourlyAdsData);
                }
            }

        }
        foreach ($hourlyAdsDataArray as $key => $value) {
            $isSaved = HourlyAdsData::where('AdsAccountId', $accountId)->where('time', 'like', "$value->time%")->first();
            // dd($value->time,$accountId,$isSaved);
            if (!$isSaved) {
                $value->save();
            } else if ($status == "Saved successfully") {
                // dump($value->time, $value->AdsAccountId);
                $status = "Some or All Rows exist";
            }
        }

        Storage::disk('public')->delete("uploads/$fileName");
        return $status;
    }
}
