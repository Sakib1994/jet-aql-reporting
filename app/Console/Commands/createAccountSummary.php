<?php

namespace App\Console\Commands;

use App\Models\AccountsSummarize;
use App\Models\adsAccount;
use App\Models\aqlHourlyData;
use App\Models\DailyAdsData;
use Carbon\Carbon;
use Illuminate\Console\Command;

class createAccountSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:accountsummary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create daily Account Summary.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    public function searchThrouhArray(array $array, string $name): int
    {
        foreach ($array as $key => $item) {
            if (strcmp($item["name"], $name) == 0) {
                return $key;
            }
        }
        return 1000;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = Carbon::now('Asia/Tokyo')->sub(1, 'day')->isoFormat('YYYY-MM-DD');
        // $date = '2021-06-23';
        $allAccountName = adsAccount::where('platform', 'all')->first();
        $adsAccounts = adsAccount::IndividualAccounts()->get();
        $occarances = [];
        $sumCallsTotal = 0;
        $sumGoogleCostTotal = 0;
        $sumYahooCostTotal = 0;

        $allAccountMonthlyBudget = $allAccountName->monthlyBudget;
        foreach ($adsAccounts as $key => $value) {
            $isSaved = $this->searchThrouhArray($occarances, $value->name);
            $platform = $value->platform;
            if ($isSaved != 1000) {
                if ($platform == "google") {
                    $occarances[$isSaved]['google'] = $value->id;
                } else {
                    $occarances[$isSaved]['yahoo'] = $value->id;
                }
                continue;
            }
            $occarance = [];
            $occarance['name'] = $value->name;
            $occarance['aqlName'] = $value->aqlName;
            if ($platform == "yahoo") {
                $occarance['yahoo'] = $value->id;
            } else {
                $occarance['google'] = $value->id;
            }
            array_push($occarances, $occarance);
        }
        // go through each account and fetch data from "dailyadsdata" and "aqlHourlyData" table
        
        foreach ($occarances as $key => $value) {
            $dailyCalls = 0;
            $dailyCalls = aqlHourlyData::where('accountName', $value['aqlName'])->where('time','like',"$date%")->count();
            $dailiesGoogle = isset($value['google'])?DailyAdsData::where('AdsAccountId', $value['google'])->where('date', $date)->first():null;
            $dailiesyahoo = isset($value['yahoo'])? DailyAdsData::where('AdsAccountId', $value['yahoo'])->where('date', $date)->first():null;
            if (isset($value['google']) && isset($value['yahoo'])) {
                $google = 0;
                $yahoo = 0;
                if ($dailiesGoogle && $dailiesyahoo) {
                    $google = $dailiesGoogle->cost;
                    $yahoo = $dailiesyahoo->cost;
                } else if ($dailiesyahoo) {
                    $yahoo = $dailiesyahoo->cost;
                } else if ($dailiesGoogle) {
                    $google = $dailiesGoogle->cost;
                }
                if ($dailyCalls == 0) {
                    $sumGoogleCostTotal += $google * 1.05 * 1.2;
                    $sumYahooCostTotal += $yahoo * 1.05 * 1.2;
                    $summary = new AccountsSummarize([
                        "date" => $date,
                        "accountName" => $value['name'],
                        "googleAds" => round($google * 1.05 * 1.2),
                        "yahooAds" => round($yahoo * 1.05 * 1.2),
                        "total" => round($google * 1.05 * 1.2 + $yahoo * 1.05 * 1.2),
                        "budget" => $allAccountName->dailyBudget,
                        "numberOfCalls" => 0,
                        "costPerCall" => 0,
                    ]);
                    $summary->save();
                    continue;
                }
                $sumCallsTotal += $dailyCalls;
                $sumGoogleCostTotal += $google * 1.05 * 1.2;
                $sumYahooCostTotal += $yahoo * 1.05 * 1.2;
                $summary = new AccountsSummarize([
                    "date" => $date,
                    "accountName" => $value['name'],
                    "googleAds" => round($google * 1.05 * 1.2),
                    "yahooAds" => round($yahoo * 1.05 * 1.2),
                    "total" => round($google * 1.05 * 1.2 + $yahoo * 1.05 * 1.2),
                    "budget" => $allAccountName->dailyBudget,
                    "numberOfCalls" => $dailyCalls,
                    "costPerCall" => round(($google * 1.05 * 1.2 + $yahoo * 1.05 * 1.2) / $dailyCalls),
                ]);
                $summary->save();
            } else if (isset($value['google'])) {
                $google = $dailiesGoogle?$dailiesGoogle->cost:0;
                if ($dailyCalls == 0) {
                    if ($google == 0) {
                        continue;
                    }
                    $sumGoogleCostTotal += $google * 1.05 * 1.2;
                    $summary = new AccountsSummarize([
                        "date" => $date,
                        "accountName" => $value['name'],
                        "googleAds" => round($google * 1.05 * 1.2),
                        "yahooAds" => 0,
                        "total" => round($google * 1.05 * 1.2),
                        "budget" => $allAccountName->dailyBudget,
                        "numberOfCalls" => 0,
                        "costPerCall" => 0,
                    ]);
                    $summary->save();
                    continue;
                }
                $sumCallsTotal += $dailyCalls;
                $sumGoogleCostTotal += $google * 1.05 * 1.2;
                $summary = new AccountsSummarize([
                    "date" => $date,
                    "accountName" => $value['name'],
                    "googleAds" => round($google * 1.05 * 1.2),
                    "yahooAds" => 0,
                    "total" => round($google * 1.05 * 1.2),
                    "budget" => $allAccountName->dailyBudget,
                    "numberOfCalls" => $dailyCalls,
                    "costPerCall" => round(($google * 1.05 * 1.2) / $dailyCalls),
                ]);
                $summary->save();
            } else if (isset($value['yahoo'])) {
                $yahoo = $dailiesyahoo?$dailiesyahoo->cost:0;
                if ($dailyCalls == 0) {
                    if ($yahoo == 0) {
                        continue;
                    }
                    $sumYahooCostTotal += $yahoo * 1.05 * 1.2;
                    $summary = new AccountsSummarize([
                        "date" => $date,
                        "accountName" => $value['name'],
                        "googleAds" => 0,
                        "yahooAds" => round($yahoo * 1.05 * 1.2),
                        "total" => round($yahoo * 1.05 * 1.2),
                        "budget" => $allAccountName->dailyBudget,
                        "numberOfCalls" => 0,
                        "costPerCall" => 0,
                    ]);
                    $summary->save();
                    continue;
                }
                $sumCallsTotal += $dailyCalls;
                $sumYahooCostTotal += $yahoo * 1.05 * 1.2;
                $summary = new AccountsSummarize([
                    "date" => $date,
                    "accountName" => $value['name'],
                    "googleAds" => 0,
                    "yahooAds" => round($yahoo * 1.05 * 1.2),
                    "total" => round($yahoo * 1.05 * 1.2),
                    "budget" => $allAccountName->dailyBudget,
                    "numberOfCalls" => $dailyCalls,
                    "costPerCall" => round(($yahoo * 1.05 * 1.2) / intval($dailyCalls)),
                ]);
                $summary->save();
            }
        }
        $summary = new AccountsSummarize([
            "date" => $date,
            "accountName" => $allAccountName->name,
            "googleAds" => round($sumGoogleCostTotal),
            "yahooAds" => round($sumYahooCostTotal),
            "total" => round($sumGoogleCostTotal + $sumYahooCostTotal),
            "budget" => $allAccountMonthlyBudget,
            "numberOfCalls" => $sumCallsTotal,
            "costPerCall" => round(($sumGoogleCostTotal + $sumYahooCostTotal) / intval($sumCallsTotal)),
        ]);
        $summary->save();
        return $this->info('Successfully command executed.');;
    }
}
