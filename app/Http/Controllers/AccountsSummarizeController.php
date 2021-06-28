<?php

namespace App\Http\Controllers;

use App\Models\AccountsSummarize;
use App\Models\adsAccount;
use App\Models\aqlHourlyData;
use App\Models\DailyAdsData;
use App\Models\HourlyAdsData;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Lib\V7\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V7\GoogleAdsClientBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class AccountsSummarizeController extends Controller
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
        $end = new Carbon('yesterday');
        $end = $end->isoFormat('YYYY-MM-DD');
        if ($request->startDate && $request->endDate) {
            $begin = $request->startDate;
            $end = $request->endDate;
        }
        $period = CarbonPeriod::create($begin, $end);
        $dailyDatas = [];
        $uniqueAccounts = array_unique(adsAccount::where('platform', '!=', 'all')->get()->pluck('name')->all());
        $allAccountName = adsAccount::where('platform', 'all')->first()->name;
        $accountsSummary = AccountsSummarize::where('accountName', $allAccountName)
            ->where('date', '>=', $begin)
            ->where('date', '<=', $end)->get();
        $allAccountDaily = [];
        foreach ($period as $date) {
            $filtered = $this->filter_by_value($accountsSummary, 'date', $date->format('Y-m-d'));
            if ($filtered) {
                $allAccountDaily[$date->format('Y-m-d')] = $filtered;
            }
        }
        $dailyDatas[$allAccountName] = $allAccountDaily;
        $uniqueAccounts = array_unique(adsAccount::where('platform', '!=', 'all')->get()->pluck('name')->all());
        foreach ($uniqueAccounts as $key => $value) {
            $accountwiseDaily = [];
            $accountsSummary = AccountsSummarize::where('accountName', $value)
                ->where('date', '>=', $begin)
                ->where('date', '<=', $end)->get();
            foreach ($period as $date) {
                $filtered = $this->filter_by_value($accountsSummary, 'date', $date->format('Y-m-d'));
                if ($filtered) {
                    $accountwiseDaily[$date->format('Y-m-d')] = $filtered;
                }
            }
            $dailyDatas[$value] = $accountwiseDaily;
        }
        return Inertia::render('DailySummary/Index', [
            "accounts" => $uniqueAccounts,
            "dailyDatas" => $dailyDatas,
        ]);
    }
    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
    public function filter_by_value($array, $index, $value)
    {
        if (count($array) > 0) {
            foreach ($array as $item) {
                if ($item->$index == $value) {
                    return $item;
                }
            }
        }
        return 0;
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
    public function processedAccountArray(Object $adsAccounts)
    {
        $occarances = [];
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
        return $occarances;
    }
    public function aqlProcress($startDate = null, $endDate = null)
    {
        $dailyGoogleDatas = [];
        $begin = new Carbon('first day of this month');
        $begin = $begin->isoFormat('YYYY-MM-DD');
        $end = new Carbon('today');
        $end = $end->isoFormat('YYYY-MM-DD');
        $today = Carbon::now()->isoFormat('YYYY-MM-DD');

        $adsAccounts = adsAccount::where('platform', '!=', 'all')->get();
        $occarances = $this->processedAccountArray($adsAccounts);
        // ddd($occarances);
        if ($today === $end) {
            if ($startDate && $endDate) {
                $begin = $startDate;
                $end = Carbon::now()->sub(1, 'day')->isoFormat('YYYY-MM-DD');
            }
        } else {
            $today = null;
            if ($startDate && $endDate) {
                $begin = $startDate;
                $end = $endDate;
            }
        }
        $period = CarbonPeriod::create($begin, $end);
        $dailyDatas = [];
        $uniqueAccounts = array_unique(AdsAccount::where('platform', '!=', 'all')->get()->pluck('name')->all());
        $allAccountName = adsAccount::where('platform', 'all')->first()->name;
        $accountsSummary = AccountsSummarize::where('accountName', $allAccountName)
            ->where('date', '>=', $begin)
            ->where('date', '<=', $end)->get();
        // ddd($accountsSummary);
        $allAccountDaily = [];
        foreach ($period as $date) {
            $filtered = $this->filter_by_value($accountsSummary, 'date', $date->format('Y-m-d'));
            if (!$filtered) {
                $filtered = new AccountsSummarize([
                    "date" => $date->format('Y-m-d'),
                    "accountName" => $allAccountName,
                    "yahooAds" => 0.0,
                    "googleAds" => 0.0,
                    "total" => 0.0,
                    "budget" => 2300000,
                    "numberOfCalls" => 0,
                    "costPerCall" => 0,
                ]);
            }
            $allAccountDaily[$date->format('Y-m-d')] = $filtered;
        }
        if ($today) {
            $googleTotalCost = 0;
            $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile('/var/www/html/jet/google_ads_php.ini')->build();
            // $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->build();

            $googleAdsClient = (new GoogleAdsClientBuilder())->fromFile('/var/www/html/jet/google_ads_php.ini')
            // $googleAdsClient = (new GoogleAdsClientBuilder())->fromFile()
                ->withOAuth2Credential($oAuth2Credential)
                ->withLoginCustomerId(4387461648)
                ->build();
            $accountIds = AdsAccount::select("id", "accountId")->where('platform', 'google')->get();
            foreach ($accountIds as $account) {
                if (self::fetchFromApi($googleAdsClient, $account->accountId, $account->id)) {
                    $cost = self::fetchFromApi($googleAdsClient, $account->accountId, $account->id)->cost;
                    array_push($dailyGoogleDatas, self::fetchFromApi($googleAdsClient, $account->accountId, $account->id));
                    $googleTotalCost += $cost;
                }
            }
            $dailyCallNumber = aqlHourlyData::where('time', 'LIKE', "$today%")->count();
            $filtered = new AccountsSummarize([
                "date" => $today,
                "accountName" => "全部",
                "yahooAds" => 0.0,
                "googleAds" => $googleTotalCost * 1.05 * 1.2,
                "total" => $googleTotalCost * 1.05 * 1.2,
                "budget" => 2300000,
                "numberOfCalls" => $dailyCallNumber > 0 ? $dailyCallNumber : 0,
                "costPerCall" => $dailyCallNumber > 0 ? round($googleTotalCost * 1.05 * 1.2 / $dailyCallNumber) : 0,
            ]);
            $allAccountDaily[$today] = $filtered;
        }
        $dailyDatas['全部'] = $allAccountDaily;
        // dd($dailyGoogleDatas);
        // dd($uniqueAccounts);
        foreach ($uniqueAccounts as $key => $value) {
            $accountwiseDaily = [];
            $accountsSummary = AccountsSummarize::where('accountName', $value)
                ->where('date', '>=', $begin)
                ->where('date', '<=', $end)->get();
            foreach ($period as $date) {
                $filtered = $this->filter_by_value($accountsSummary, 'date', $date->format('Y-m-d'));
                if (!$filtered) {
                    $filtered = new AccountsSummarize([
                        "date" => $date->format('Y-m-d'),
                        "accountName" => $value,
                        "yahooAds" => 0.0,
                        "googleAds" => 0.0,
                        "total" => 0.0,
                        "budget" => 600000.0,
                        "numberOfCalls" => 0,
                        "costPerCall" => 0,
                    ]);
                }
                $accountwiseDaily[$date->format('Y-m-d')] = $filtered;
            }
            if ($today) {
                $cost = 0;
                $numberOfCall = 0;
                $dailyCallNumber = aqlHourlyData::where('time', 'LIKE', "$today%")->count();
                foreach ($dailyGoogleDatas as $key => $googleData) {
                    if ($value == $googleData->AdsAccount) {
                        $cost = $googleData->cost;
                    }
                }
                if ($cost != 0 && intval($numberOfCall) != 0) {
                    $filtered = new AccountsSummarize([
                        "date" => $today,
                        "accountName" => $value,
                        "yahooAds" => 0,
                        "googleAds" => $cost * 1.05 * 1.2,
                        "total" => $cost * 1.05 * 1.2,
                        "budget" => 600000.0,
                        "numberOfCalls" => intval($numberOfCall),
                        "costPerCall" => round(($cost * 1.05 * 1.2) / intval($numberOfCall)),
                    ]);
                } else if ($cost != 0) {
                    $filtered = new AccountsSummarize([
                        "date" => $today,
                        "accountName" => $value,
                        "yahooAds" => 0,
                        "googleAds" => $cost * 1.05 * 1.2,
                        "total" => $cost * 1.05 * 1.2,
                        "budget" => 600000.0,
                        "numberOfCalls" => 0,
                        "costPerCall" => 0,
                    ]);
                } else {
                    $filtered = new AccountsSummarize([
                        "date" => $today,
                        "accountName" => $value,
                        "yahooAds" => 0.0,
                        "googleAds" => 0.0,
                        "total" => 0.0,
                        "budget" => 600000.0,
                        "numberOfCalls" => 0,
                        "costPerCall" => 0,
                    ]);
                }
                $accountwiseDaily[$today] = $filtered;
            }
            $dailyDatas[$value] = $accountwiseDaily;
        }
        // ddd($dailyDatas);
        return Inertia::render('DailySummary/Aql', [
            "accounts" => $uniqueAccounts,
            "dailyDatas" => $dailyDatas,
            'table_header' => ['日付', 'day', 'yahoo', 'google', '実績合計', '予算', '入電数', '入電単価'],
        ]);
    }
    private const PAGE_SIZE = 50;
    public static function fetchFromApi(GoogleAdsClient $googleAdsClient, int $customerId, int $accountId)
    {
        $date = Carbon::now('Asia/Tokyo')->isoFormat('YYYY-MM-DD');
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
        // Creates a query that retrieves hotel-ads statistics for each campaign and ad group.
        // Returned statistics will be segmented by the check-in day of week and length of stay.

        $queryFromBuider = "SELECT customer.descriptive_name, segments.date, metrics.clicks, metrics.ctr, metrics.impressions, metrics.conversions, metrics.average_cpc, metrics.cost_per_conversion, metrics.cost_micros, metrics.conversions_from_interactions_rate FROM customer WHERE segments.date = '$date'";
        // Issues a search request by specifying page size.
        $response = $googleAdsServiceClient->search($customerId, $queryFromBuider, ['pageSize' => self::PAGE_SIZE]);

        // Iterates over all rows in all pages and prints the requested field values for each row.
        foreach ($response->iterateAllElements() as $googleAdsRow) {
            if ($googleAdsRow->getMetrics()->getClicks() == 0) {
                continue;
            }
            $dailyAdsData = new DailyAdsData([
                "AdsAccount" => AdsAccount::find($accountId)->name,
                "date" => $googleAdsRow->getSegments()->getDate(),
                "cost" => intval($googleAdsRow->getMetrics()->getCostMicros() / 1000000),
            ]);
            return $dailyAdsData;
        }
        return 0;
    }
    public function processSSS($startDate = null, $endDate = null)
    {
        $allAccountName = adsAccount::where('platform', 'all')->first()->name;
        $dailyDatas = [];
        $begin = new Carbon('first day of this month');
        $begin = $begin->isoFormat('YYYY-MM-DD');
        $end = new Carbon('yesterday');
        $end = $end->isoFormat('YYYY-MM-DD');
        $today = Carbon::now()->isoFormat('YYYY-MM-DD');
        if ($today == $endDate) {
            if ($startDate && $endDate) {
                $begin = $startDate;
                $end = Carbon::now()->sub(1, 'day')->isoFormat('YYYY-MM-DD');
            }
        } else {
            $today = null;
            if ($startDate && $endDate) {
                $begin = $startDate;
                $end = $endDate;
            }
        }
        $period = CarbonPeriod::create($begin, $end);
        $allAccountDaily = [];
        foreach ($period as $date) {
            $allAccountData = AccountsSummarize::where('accountName', $allAccountName)->where('date', $date->format('Y-m-d'))->first();
            $dailyAllAdsInfo = DailyAdsData::where('date', $date->format('Y-m-d'));
            if (is_null($allAccountData)) {
                if (is_null($dailyAllAdsInfo->first())) {
                    $allAccountDaily[$date->format('Y-m-d')] = [
                        'date' => $date->format('Y-m-d'),
                        'accountName' => '全部',
                        '入電数' => 0,
                        '入電単価' => 0,
                        '表示回数' => 0,
                        'クリック数' => 0,
                        'クリック率' => 0,
                        'クリック単価' => 0,
                        '費用' => 0,
                        'コンバージョン' => 0,
                        'コンバージョン率' => 0,
                        'コンバージョン単価' => 0,
                    ];
                }
                $allAccountDaily[$date->format('Y-m-d')] = [
                    'date' => $date->format('Y-m-d'),
                    'accountName' => '全部',
                    '入電数' => 0,
                    '入電単価' => 0,
                    '表示回数' => $dailyAllAdsInfo->sum('impressions'),
                    'クリック数' => $dailyAllAdsInfo->sum('clicks'),
                    'クリック率' => round($dailyAllAdsInfo->avg('ctr'), 2),
                    'クリック単価' => round($dailyAllAdsInfo->avg('cpc')),
                    '費用' => round($dailyAllAdsInfo->sum('cost')),
                    'コンバージョン' => $dailyAllAdsInfo->sum('conversions'),
                    'コンバージョン率' => round($dailyAllAdsInfo->avg('conversions_rate'), 2),
                    'コンバージョン単価' => round($dailyAllAdsInfo->avg('cost_per_conversion')),
                ];
                continue;
            }
            $allAccountDaily[$date->format('Y-m-d')] = [
                'date' => $date->format('Y-m-d'),
                'accountName' => '全部',
                '入電数' => $allAccountData->numberOfCalls,
                '入電単価' => $allAccountData->costPerCall,
                '表示回数' => $dailyAllAdsInfo->sum('impressions'),
                'クリック数' => $dailyAllAdsInfo->sum('clicks'),
                'クリック率' => round($dailyAllAdsInfo->avg('ctr'), 2),
                'クリック単価' => round($dailyAllAdsInfo->avg('cpc')),
                '費用' => round($dailyAllAdsInfo->sum('cost')),
                'コンバージョン' => $dailyAllAdsInfo->sum('conversions'),
                'コンバージョン率' => round($dailyAllAdsInfo->avg('conversions_rate'), 2),
                'コンバージョン単価' => round($dailyAllAdsInfo->avg('cost_per_conversion')),
            ];
        }
        if ($today) {
            $todayAdsData = HourlyAdsData::where('time', 'LIKE', "$today%");
            $dailyCallNumber = aqlHourlyData::where('time', 'LIKE', "$today%")->count();
            $allAccountDaily[$today] = [
                'date' => $today,
                'accountName' => '全部',
                '入電数' => $dailyCallNumber,
                '入電単価' => round($todayAdsData->sum('cost') / $dailyCallNumber),
                '表示回数' => round($todayAdsData->sum('impressions')),
                'クリック数' => round($todayAdsData->sum('clicks')),
                'クリック率' => round($todayAdsData->avg('ctr'), 2),
                'クリック単価' => round($todayAdsData->avg('cpc')),
                '費用' => round($todayAdsData->sum('cost')),
                'コンバージョン' => round($todayAdsData->sum('conversions')),
                'コンバージョン率' => round($todayAdsData->avg('conversions_rate'), 2),
                'コンバージョン単価' => round($todayAdsData->avg('cost_per_conversion')),
            ];
        }
        $dailyDatas['全部'] = $allAccountDaily;
        $adsAccounts = AdsAccount::IndividualAccounts()->get();
        $uniqueAccounts = $this->processedAccountArray($adsAccounts);
        foreach ($uniqueAccounts as $key => $value) {
            $accountwiseDaily = [];
            $accountsSummary = AccountsSummarize::where('accountName', $value['name'])->get();
            $googleAccounts = isset($value['google']) ? AdsAccount::find($value['google'])->dailyAdsDatas()->get() : [];
            $yahooAccounts = isset($value['yahoo']) ? AdsAccount::find($value['yahoo'])->dailyAdsDatas()->get() : [];
            // dd($googleAccounts,$yahooAccounts);
            foreach ($period as $date) {
                $filtered = $this->filter_by_value($accountsSummary, 'date', $date->format('Y-m-d'));
                $googleDailyInfo = $this->filter_by_value($googleAccounts, 'date', $date->format('Y-m-d')) ? $this->filter_by_value($googleAccounts, 'date', $date->format('Y-m-d')) : null;
                $yahooDailyInfo = $this->filter_by_value($googleAccounts, 'date', $date->format('Y-m-d')) ? $this->filter_by_value($yahooAccounts, 'date', $date->format('Y-m-d')) : null;
                // dd($googleDailyInfo, $yahooDailyInfo);
                if (!$filtered) {
                    $filtered = [
                        'date' => $date->format('Y-m-d'),
                        'accountName' => $value['name'],
                        '入電数' => 0,
                        '入電単価' => 0,
                        '表示回数' => 0,
                        'クリック数' => 0,
                        'クリック率' => 0,
                        'クリック単価' => 0,
                        '費用' => 0,
                        'コンバージョン' => 0,
                        'コンバージョン率' => 0,
                        'コンバージョン単価' => 0,
                    ];
                    $accountwiseDaily[$date->format('Y-m-d')] = $filtered;
                    continue;
                }
                if ($googleDailyInfo && $yahooDailyInfo) {
                    $filtered = [
                        'date' => $date->format('Y-m-d'),
                        'accountName' => $value['name'],
                        '入電数' => $filtered->numberOfCalls,
                        '入電単価' => $filtered->costPerCall,
                        '表示回数' => $googleDailyInfo->impressions + $yahooDailyInfo->impressions,
                        'クリック数' => $googleDailyInfo->clicks + $yahooDailyInfo->clicks,
                        'クリック率' => round(($googleDailyInfo->ctr + $yahooDailyInfo->ctr) / 2, 2),
                        'クリック単価' => round(($googleDailyInfo->cpc + $yahooDailyInfo->cpc) / 2, 2),
                        '費用' => $googleDailyInfo->cost + $yahooDailyInfo->cost,
                        'コンバージョン' => $googleDailyInfo->conversions + $yahooDailyInfo->conversions,
                        'コンバージョン率' => round(($googleDailyInfo->conversions_rate + $yahooDailyInfo->conversions_rate) / 2, 2),
                        'コンバージョン単価' => round(($googleDailyInfo->cost_per_conversion + $yahooDailyInfo->cost_per_conversion) / 2, 2),
                    ];
                } else if ($googleDailyInfo) {
                    $filtered = [
                        'date' => $date->format('Y-m-d'),
                        'accountName' => $value['name'],
                        '入電数' => $filtered->numberOfCalls,
                        '入電単価' => $filtered->costPerCall,
                        '表示回数' => $googleDailyInfo->impressions,
                        'クリック数' => $googleDailyInfo->clicks,
                        'クリック率' => round($googleDailyInfo->ctr, 2),
                        'クリック単価' => round($googleDailyInfo->cpc, 2),
                        '費用' => $googleDailyInfo->cost,
                        'コンバージョン' => $googleDailyInfo->conversions,
                        'コンバージョン率' => round($googleDailyInfo->conversions_rate, 2),
                        'コンバージョン単価' => round($googleDailyInfo->cost_per_conversion, 2),
                    ];
                } else if ($yahooDailyInfo) {
                    $filtered = [
                        'date' => $date->format('Y-m-d'),
                        'accountName' => $value['name'],
                        '入電数' => $filtered->numberOfCalls,
                        '入電単価' => $filtered->costPerCall,
                        '表示回数' => $yahooDailyInfo->impressions,
                        'クリック数' => $yahooDailyInfo->clicks,
                        'クリック率' => round($yahooDailyInfo->ctr, 2),
                        'クリック単価' => round($yahooDailyInfo->cpc, 2),
                        '費用' => $yahooDailyInfo->cost,
                        'コンバージョン' => $yahooDailyInfo->conversions,
                        'コンバージョン率' => round($yahooDailyInfo->conversions_rate, 2),
                        'コンバージョン単価' => round($yahooDailyInfo->cost_per_conversion, 2),
                    ];
                } else {
                    $filtered = [
                        'date' => $date->format('Y-m-d'),
                        'accountName' => $value['name'],
                        '入電数' => $filtered->numberOfCalls,
                        '入電単価' => $filtered->costPerCall,
                        '表示回数' => 0,
                        'クリック数' => 0,
                        'クリック率' => 0,
                        'クリック単価' => 0,
                        '費用' => 0,
                        'コンバージョン' => 0,
                        'コンバージョン率' => 0,
                        'コンバージョン単価' => 0,
                    ];
                }
                $accountwiseDaily[$date->format('Y-m-d')] = $filtered;
            }
            if ($today) {
                $googleAccounts = isset($value['google']) ? HourlyAdsData::where('time', 'LIKE', "$today%")->where('AdsAccountId', $value['google']) : null;
                $yahooAccounts = isset($value['yahoo']) ? HourlyAdsData::where('time', 'LIKE', "$today%")->where('AdsAccountId', $value['yahoo']) : null;
                $hourlyCalls = $dailyCallNumber = aqlHourlyData::where('time', 'LIKE', "$today%")->where('accountName', $value['aqlName'])->count();

                if ($googleAccounts && $yahooAccounts) {
                    $accountwiseDaily[$today] = [
                        'date' => $today,
                        'accountName' => $value['name'],
                        '入電数' => $hourlyCalls > 0 ? $hourlyCalls : 0,
                        '入電単価' => $hourlyCalls > 0 ? round(($googleAccounts->sum('cost') + $yahooAccounts->sum('cost')) / $hourlyCalls) : 0,
                        '表示回数' => round($googleAccounts->sum('impressions') + $yahooAccounts->sum('impressions')),
                        'クリック数' => round($googleAccounts->sum('clicks') + $yahooAccounts->sum('clicks')),
                        'クリック率' => is_null($yahooAccounts->avg('ctr')) ? round($googleAccounts->avg('ctr'), 2) : round(($googleAccounts->avg('ctr') + $yahooAccounts->avg('ctr')) / 2, 2),
                        'クリック単価' => is_null($yahooAccounts->avg('cpc')) ? round($googleAccounts->avg('cpc'), 2) : round(($googleAccounts->avg('cpc') + $yahooAccounts->avg('cpc')) / 2, 2),
                        '費用' => round($googleAccounts->sum('cost') + $yahooAccounts->sum('cost')),
                        'コンバージョン' => $googleAccounts->sum('conversions') + $yahooAccounts->sum('conversions'),
                        'コンバージョン率' => is_null($yahooAccounts->avg('conversions_rate')) ? round($googleAccounts->avg('conversions_rate'), 2) : round(($googleAccounts->avg('conversions_rate') + $yahooAccounts->avg('conversions_rate')) / 2, 2),
                        'コンバージョン単価' => is_null($yahooAccounts->avg('cost_per_conversion')) ? round($googleAccounts->avg('cost_per_conversion'), 2) : round(($googleAccounts->avg('cost_per_conversion') + $yahooAccounts->avg('cost_per_conversion')) / 2, 2),
                    ];
                } elseif ($googleAccounts) {
                    $accountwiseDaily[$today] = [
                        'date' => $today,
                        'accountName' => $value['name'],
                        '入電数' => $hourlyCalls > 0 ? $hourlyCalls : 0,
                        '入電単価' => $hourlyCalls > 0 ? round($googleAccounts->sum('cost') / $hourlyCalls) : 0,
                        '表示回数' => $googleAccounts->sum('impressions'),
                        'クリック数' => $googleAccounts->sum('clicks'),
                        'クリック率' => is_null($googleAccounts->avg('ctr')) ? 0 : round($googleAccounts->avg('ctr'), 2),
                        'クリック単価' => is_null($googleAccounts->avg('ctr')) ? 0 : round($googleAccounts->avg('cpc'), 2),
                        '費用' => round($googleAccounts->sum('cost')),
                        'コンバージョン' => $googleAccounts->sum('conversions'),
                        'コンバージョン率' => round($googleAccounts->avg('conversions_rate'), 2),
                        'コンバージョン単価' => round($googleAccounts->avg('cost_per_conversion'), 2),
                    ];
                } elseif ($yahooAccounts) {
                    $accountwiseDaily[$today] = [
                        'date' => $today,
                        'accountName' => $value['name'],
                        '入電数' => $hourlyCalls > 0 ? $hourlyCalls : 0,
                        '入電単価' => $hourlyCalls > 0 ? round(($googleAccounts->sum('cost') + $yahooAccounts->sum('cost')) / $hourlyCalls) : 0,
                        '表示回数' => $yahooAccounts->sum('impressions'),
                        'クリック数' => $yahooAccounts->sum('clicks'),
                        'クリック率' => is_null($yahooAccounts->avg('ctr')) ? 0 : round($yahooAccounts->avg('ctr'), 2),
                        'クリック単価' => is_null($yahooAccounts->avg('cpc')) ? 0 : round($yahooAccounts->avg('cpc'), 2),
                        '費用' => $yahooAccounts->sum('cost'),
                        'コンバージョン' => $yahooAccounts->sum('conversions'),
                        'コンバージョン率' => is_null($yahooAccounts->avg('conversions_rate')) ? 0 : round($googleAccounts->avg('conversions_rate'), 2),
                        'コンバージョン単価' => is_null($yahooAccounts->avg('cost_per_conversion')) ? 0 : round($yahooAccounts->avg('cost_per_conversion'), 2),
                    ];
                } else {
                    $accountwiseDaily[$today] = [
                        'date' => $today,
                        'accountName' => $value['name'],
                        '入電数' => $hourlyCalls > 0 ? $hourlyCalls : 0,
                        '入電単価' => 0,
                        '表示回数' => 0,
                        'クリック数' => 0,
                        'クリック率' => 0,
                        'クリック単価' => 0,
                        '費用' => 0,
                        'コンバージョン' => 0,
                        'コンバージョン率' => 0,
                        'コンバージョン単価' => 0,
                    ];
                }
            }
            $dailyDatas[$value['name']] = $accountwiseDaily;
        }
        // ddd($dailyDatas);
        return Inertia::render('DailySummary/SSS', [
            "accounts" => array_unique($adsAccounts->pluck('name')->all()),
            "dailyDatas" => $dailyDatas,
            'table_header' => ['日付', 'day', '入電数', '入電単価', '表示回数', 'クリック数', 'クリック率', 'クリック単価', '費用', 'コンバージョン', 'コンバージョン率', 'コンバージョン単価'],
        ]);
    }
    public function datewiseExperiment(Request $request, $date)
    {
        $hourlyDatas = [];
        $adsAccounts = AdsAccount::IndividualAccounts()->get();
        $uniqueAccounts = $this->processedAccountArray($adsAccounts);
        $aqlHourlyDataQuery = aqlHourlyData::where('time', 'like', "$date%")->get();
        $prefectures = array_count_values($aqlHourlyDataQuery->pluck('prefecture')->all());
        $requestTypes = array_count_values($aqlHourlyDataQuery->pluck('requestType')->all());
        $requestDetails = array_count_values($aqlHourlyDataQuery->pluck('requestDetail')->all());
        $aqlHourlyData = [
            'prefectures' => $prefectures,
            'requestTypes' => $requestTypes,
            'requestDetails' => $requestDetails,
        ];
        $hourlyAdsQuery = HourlyAdsData::where('time', 'like', "$date%");
        if ($request->input('prefecture')) {
            $filteredAll = aqlHourlyData::select(DB::raw("count(*) as total"))
                ->where('time', 'LIKE', "$date%")
                ->where('prefecture', $request->input('prefecture'));
        } elseif ($request->input('type')) {
            $filteredAll = aqlHourlyData::select(DB::raw("hour(time) as hour, count(*) as total"))
                ->where('time', 'LIKE', "$date%")
                ->where('requestType', $request->input('type'));
        } elseif ($request->input('adsPlatform')) {
            $filteredAll = aqlHourlyData::select(DB::raw("hour(time) as hour, count(*) as total"))
                ->where('time', 'LIKE', "$date%");
            $selectedAccounts = AdsAccount::where('platform', $request->input('adsPlatform'))->get()->pluck('id')->all();
            $hourlyAdsQuery = HourlyAdsData::where('time', 'like', "$date%")->whereIn('AdsAccountId', $selectedAccounts);
        } else {
            $filteredAll = aqlHourlyData::select(DB::raw("hour(time) as hour, count(*) as total"))
                ->where('time', 'LIKE', "$date%")
                ->groupBy(DB::raw('hour(time)'));
        }
        $allAqlAccountsData = [];
        $aqlDataAllAccounts = $filteredAll->get()->pluck('total', 'hour')->all();
        // ddd($aqlDataAllAccounts,$date);
        for ($i = 0; $i < 24; $i++) {
            $hour = $i < 10 ? "0$i" : "$i";
            $hourUpperLimit = $i < 9 ? "0" . ($i + 1) : "" . ($i + 1);
            $cost = 0;
            $clicks = 0;
            $impressions = 0;
            $ctr = 0;
            $cpc = 0;
            $conv = 0;
            $convRate = 0;
            $costPerConv = 0;
            $count = 0;
            foreach ($hourlyAdsQuery->get() as $key => $value) {
                if (str_contains($value->time, "$date $hourUpperLimit")) {
                    $cost += $value->cost;
                    $clicks += $value->clicks;
                    $impressions += $value->impressions;
                    $ctr += $value->ctr;
                    $cpc += $value->cpc;
                    $conv += $value->conversions;
                    $convRate += $value->conversions_rate;
                    $costPerConv += $value->cost_per_conversion;
                    $count++;
                }
            }
            $ctr = $count > 0 ? round($ctr / $count, 2) : 0;
            $cpc = $count > 0 ? round($cpc / $count) : 0;
            $convRate = $count > 0 ? round($convRate / $count, 2) : 0;
            $costPerConv = $count > 0 ? round($costPerConv / $count) : 0;
            $hourlyData = [
                '時間' => "$hour:00 - $hourUpperLimit:00",
                '入電数' => isset($aqlDataAllAccounts[$i]) ? $aqlDataAllAccounts[$i] : 0,
                '入電単価' => isset($aqlDataAllAccounts[$i]) && $cost > 0 ? round($cost / $aqlDataAllAccounts[$i]) : 0,
                '表示回数' => $impressions > 0 ? $impressions : 0,
                'クリック数' => $clicks > 0 ? $clicks : 0,
                'クリック率' => $ctr > 0 ? round($ctr, 2) : 0,
                'クリック単価' => $cpc > 0 ? round($cpc) : 0,
                '費用' => $cost > 0 ? round($cost) : 0,
                'コンバージョン' => $conv > 0 ? $conv : 0,
                'コンバージョン率' => $convRate > 0 ? round($convRate, 2) : 0,
                'コンバージョン単価' => $costPerConv > 0 ? round($costPerConv) : 0,
            ];
            $allAqlAccountsData[$i] = $hourlyData;
        }
        $dailyDatas['全部'] = $allAqlAccountsData;
        $aqlHourlyCallCount = aqlHourlyData::select(DB::raw("hour(time) as hour, count(*) as total"))
            ->where('time', 'LIKE', "$date%")
            ->groupBy(DB::raw('hour(time)'));
        $allAqlAccountsDataHolder = [];
        $aqlDataAllAccounts = $aqlHourlyCallCount->get()->pluck('total', 'hour')->all();
        // ddd($uniqueAccounts);
        foreach ($uniqueAccounts as $key => $singleAccount) {
            $accountwiseHourly = [];
            $filtered = aqlHourlyData::select(DB::raw("hour(time) as hour, count(*) as total"))
                ->where('time', 'LIKE', "$date%")
                ->where('accountName', $singleAccount['aqlName'])
                ->groupBy(DB::raw('hour(time)'))
                ->get()->pluck('total', 'hour')->all();
            $googleHourlyInfoAll = isset($singleAccount['google']) ? AdsAccount::find($singleAccount['google'])->hourlyAdsDatas()->where('time', 'like', "$date%")->get() : null;
            $yahooHourlyInfoAll = isset($singleAccount['yahoo']) ? AdsAccount::find($singleAccount['yahoo'])->hourlyAdsDatas()->where('time', 'like', "$date%")->get() : null;
            for ($i = 0; $i < 24; $i++) {
                $hour = $i < 10 ? "0$i" : "$i";
                $googleHourlyInfo = null;
                $yahooHourlyInfo = null;
                $hourUpperLimit = $i < 9 ? "0" . ($i + 1) : "" . ($i + 1);
                if ($googleHourlyInfoAll) {
                    foreach ($googleHourlyInfoAll as $key => $value) {
                        if (str_contains($value->time, "$date $hourUpperLimit")) {
                            $googleHourlyInfo = $value;
                        }
                    }
                }
                if ($yahooHourlyInfoAll) {
                    foreach ($yahooHourlyInfoAll as $key => $value) {
                        if (str_contains($value->time, "$date $hour")) {
                            $yahooHourlyInfo = $value;
                        }
                    }
                }
                $aqlData = isset($filtered[$i]) ? $filtered[$i] : 0;
                $cost = 0;
                $clicks = 0;
                $impressions = 0;
                $ctr = 0;
                $cpc = 0;
                $conv = 0;
                $convRate = 0;
                $costPerConv = 0;
                if ($request->input('adsPlatform')) {
                    if ($googleHourlyInfo && $request->input('adsPlatform') == 'google') {
                        $cost = $googleHourlyInfo->cost;
                        $clicks = $googleHourlyInfo->clicks;
                        $impressions = $googleHourlyInfo->impressions;
                        $ctr = $googleHourlyInfo->ctr;
                        $cpc = $googleHourlyInfo->cpc;
                        $conv = $googleHourlyInfo->conversions;
                        $convRate = $googleHourlyInfo->conversions_rate;
                        $costPerConv = $googleHourlyInfo->cost_per_conversion;
                    } else if ($yahooHourlyInfo && $request->input('adsPlatform') == 'yahoo') {
                        $cost = $yahooHourlyInfo->cost;
                        $clicks = $yahooHourlyInfo->clicks;
                        $impressions = $yahooHourlyInfo->impressions;
                        $ctr = $yahooHourlyInfo->ctr;
                        $cpc = $yahooHourlyInfo->cpc;
                        $conv = $yahooHourlyInfo->conversions;
                        $convRate = $yahooHourlyInfo->conversions_rate;
                        $costPerConv = $yahooHourlyInfo->cost_per_conversion;
                    }
                } else {
                    if ($googleHourlyInfo && $yahooHourlyInfo) {
                        $cost = $googleHourlyInfo->cost + $yahooHourlyInfo->cost;
                        $clicks = $googleHourlyInfo->clicks + $yahooHourlyInfo->clicks;
                        $impressions = $googleHourlyInfo->impressions + $yahooHourlyInfo->impressions;
                        $ctr = ($googleHourlyInfo->ctr + $yahooHourlyInfo->ctr) / 2;
                        $cpc = ($googleHourlyInfo->cpc + $yahooHourlyInfo->cpc) / 2;
                        $conv = $googleHourlyInfo->conversions + $yahooHourlyInfo->conversions;
                        $convRate = ($googleHourlyInfo->conversions_rate + $yahooHourlyInfo->conversions_rate) / 2;
                        $costPerConv = ($googleHourlyInfo->cost_per_conversion + $yahooHourlyInfo->cost_per_conversion) / 2;
                    } else if ($googleHourlyInfo) {
                        $cost = $googleHourlyInfo->cost;
                        $clicks = $googleHourlyInfo->clicks;
                        $impressions = $googleHourlyInfo->impressions;
                        $ctr = $googleHourlyInfo->ctr;
                        $cpc = $googleHourlyInfo->cpc;
                        $conv = $googleHourlyInfo->conversions;
                        $convRate = $googleHourlyInfo->conversions_rate;
                        $costPerConv = $googleHourlyInfo->cost_per_conversion;
                    } else if ($yahooHourlyInfo) {
                        $cost = $yahooHourlyInfo->cost;
                        $clicks = $yahooHourlyInfo->clicks;
                        $impressions = $yahooHourlyInfo->impressions;
                        $ctr = $yahooHourlyInfo->ctr;
                        $cpc = $yahooHourlyInfo->cpc;
                        $conv = $yahooHourlyInfo->conversions;
                        $convRate = $yahooHourlyInfo->conversions_rate;
                        $costPerConv = $yahooHourlyInfo->cost_per_conversion;
                    }
                }
                $hourlyData = [
                    '時間' => "$hour:00 - $hourUpperLimit:00",
                    '入電数' => $aqlData,
                    '入電単価' => $aqlData > 0 ? round($cost / $aqlData, 2) : 0,
                    '表示回数' => $impressions,
                    'クリック数' => $clicks,
                    'クリック率' => round($ctr, 2),
                    'クリック単価' => round($cpc, 2),
                    '費用' => round($cost),
                    'コンバージョン' => round($conv),
                    'コンバージョン率' => round($convRate, 2),
                    'コンバージョン単価' => round($costPerConv),
                ];
                $accountwiseHourly[$i] = $hourlyData;
            }
            $dailyDatas[$singleAccount['name']] = $accountwiseHourly;
        }
        return Inertia::render('DailySummary/SSSdetailExp', [
            "aqlHourlyData" => $aqlHourlyData,
            "accounts" => array_unique($adsAccounts->pluck('name')->all()),
            "hourlyDatas" => $dailyDatas,
            'table_header' => ["$date", '入電数', '入電単価', '表示回数', 'クリック数', 'クリック率', 'クリック単価', '費用', 'コンバージョン', 'コンバージョン率', 'コンバージョン単価'],
        ]);
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
     * @param  \App\Models\AccountsSummarize  $accountsSummarize
     * @return \Illuminate\Http\Response
     */
    public function show(AccountsSummarize $accountsSummarize)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AccountsSummarize  $accountsSummarize
     * @return \Illuminate\Http\Response
     */
    public function edit(int $accountsSummarizeId)
    {
        $accountsSummarize = AccountsSummarize::find($accountsSummarizeId);
        // ddd($accountsSummarize);
        return Inertia::render('DailySummary/Edit', [
            'accountsSummarize' => $accountsSummarize,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountsSummarize  $accountsSummarize
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AccountsSummarize $accountsSummarize)
    {
        $hourlyAdsData = AccountsSummarize::find($request->id);
        if ($hourlyAdsData) {
            $hourlyAdsData->update($request->all());
        }
        return Redirect::route('daily-summary.index')->with('success', 'Account Summary Info updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountsSummarize  $accountsSummarize
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountsSummarize $accountsSummarize)
    {
        //
    }
    public function fetchHourlyFromApi(GoogleAdsClient $googleAdsClient, int $customerId, int $accountId)
    {
        $hourlyDatas = [];
        $getTheNow = Carbon::now('+8:30');
        $date = $getTheNow->isoFormat('YYYY-MM-DD');
        $hournow = $getTheNow->isoFormat('YYYY-MM-DD HH:00:00');
        $hourtoSave = $getTheNow->sub(1, 'hour')->isoFormat('HH');
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
        // これは特別なクエリ言語ですGAQL.dataはこれを使用してグーグルからフェッチされます。
        $queryFromBuider = "SELECT customer.descriptive_name, segments.hour, metrics.clicks, metrics.ctr, metrics.impressions, metrics.conversions, metrics.average_cpc, metrics.cost_per_conversion, metrics.cost_micros, metrics.conversions_from_interactions_rate FROM customer WHERE segments.date = '$date'";
        $response = $googleAdsServiceClient->search($customerId, $queryFromBuider, ['pageSize' => self::PAGE_SIZE]);
        // APIからデータをループして、データを取得します。
        foreach ($response->iterateAllElements() as $googleAdsRow) {
            if ($googleAdsRow->getMetrics()->getClicks() == 0) {
                continue;
            }
            $time = $googleAdsRow->getSegments()->getHour() < 9 ? "0" . ($googleAdsRow->getSegments()->getHour() + 1) : ($googleAdsRow->getSegments()->getHour() + 1);
            // グーグルから毎時データを取得し、データベースに保存します。
            $hourlyAdsData = new HourlyAdsData([
                "AdsAccountId" => $accountId,
                "time" => "$date $time:00",
                "clicks" => $googleAdsRow->getMetrics()->getClicks(),
                "impressions" => $googleAdsRow->getMetrics()->getImpressions(),
                "ctr" => round($googleAdsRow->getMetrics()->getCtr() * 100, 2),
                "cost" => round($googleAdsRow->getMetrics()->getCostMicros() / 1000000),
                "cpc" => round($googleAdsRow->getMetrics()->getAverageCpc() / 1000000),
                "conversions" => $googleAdsRow->getMetrics()->getConversions(),
                "conversions_rate" => round($googleAdsRow->getMetrics()->getConversionsFromInteractionsRate() * 100, 2),
                "cost_per_conversion" => round($googleAdsRow->getMetrics()->getCostPerConversion() / 1000000),
            ]);
            $hourlyDatas["$date $time:00:00"] = $hourlyAdsData;
        }
        return $hourlyDatas;
    }
    public function dateWise(Request $request, $date)
    {
        $adsAccounts = AdsAccount::IndividualAccounts()->get();
        $uniqueAccounts = $this->processedAccountArray($adsAccounts);
        $aqlHourlyDataQuery = aqlHourlyData::where('time', 'like', "$date%")->get();
        $prefectures = array_count_values($aqlHourlyDataQuery->pluck('prefecture')->all());
        $requestTypes = array_count_values($aqlHourlyDataQuery->pluck('requestType')->all());
        $requestDetails = array_count_values($aqlHourlyDataQuery->pluck('requestDetail')->all());
        $aqlHourlyData = [
            'prefectures' => $prefectures,
            'requestTypes' => $requestTypes,
            'requestDetails' => $requestDetails,
        ];
        $today = Carbon::now()->isoFormat('YYYY-MM-DD');
        $hourlyAdsQuery = HourlyAdsData::where('time', 'like', "$date%");
        $houlyAdsToday = [];
        if ($date == $today) {
            $googleTotalCost = 0;
            $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile('/var/www/html/jet/google_ads_php.ini')->build();

            $googleAdsClient = (new GoogleAdsClientBuilder())->fromFile('/var/www/html/jet/google_ads_php.ini')
                ->withOAuth2Credential($oAuth2Credential)
                ->withLoginCustomerId(4387461648)
                ->build();
            $accountIds = AdsAccount::select("id", "accountId", "name")->where('platform', 'google')->get();
            foreach ($accountIds as $account) {
                if ($this->fetchHourlyFromApi($googleAdsClient, $account->accountId, $account->id)) {
                    // ddd($this->fetchHourlyFromApi($googleAdsClient, $account->accountId, $account->id));
                    $houlyAdsToday[$account->name] = $this->fetchHourlyFromApi($googleAdsClient, $account->accountId, $account->id);
                }
            }
            // ddd($houlyAdsToday);
        }
        if ($request->input('prefecture')) {
            $filteredAll = aqlHourlyData::select(DB::raw("count(*) as total"))
                ->where('time', 'LIKE', "$date%")
                ->where('prefecture', $request->input('prefecture'));
        } elseif ($request->input('type')) {
            $filteredAll = aqlHourlyData::select(DB::raw("hour(time) as hour, count(*) as total"))
                ->where('time', 'LIKE', "$date%")
                ->where('requestType', $request->input('type'));
        } elseif ($request->input('adsPlatform')) {
            $filteredAll = aqlHourlyData::select(DB::raw("hour(time) as hour, count(*) as total"))
                ->where('time', 'LIKE', "$date%");
            $selectedAccounts = AdsAccount::where('platform', $request->input('adsPlatform'))->get()->pluck('id')->all();
            $hourlyAdsQuery = HourlyAdsData::where('time', 'like', "$date%")->whereIn('AdsAccountId', $selectedAccounts);
        } else {
            $filteredAll = aqlHourlyData::select(DB::raw("hour(time) as hour, count(*) as total"))
                ->where('time', 'LIKE', "$date%")
                ->groupBy(DB::raw('hour(time)'));
        }
        $allAqlAccountsData = [];
        $aqlDataAllAccounts = $filteredAll->get()->pluck('total', 'hour')->all();
        // ddd($aqlDataAllAccounts,$date);
        for ($i = 0; $i < 24; $i++) {
            $hour = $i < 10 ? "0$i" : "$i";
            $hourUpperLimit = $i < 9 ? "0" . ($i + 1) : "" . ($i + 1);
            $cost = 0;
            $clicks = 0;
            $impressions = 0;
            $ctr = 0;
            $cpc = 0;
            $conv = 0;
            $convRate = 0;
            $costPerConv = 0;
            $count = 0;
            if ($date == $today) {
                foreach ($houlyAdsToday as $key => $individualAccount) {
                    foreach ($individualAccount as $key => $value) {
                        if (str_contains($key, "$date $hourUpperLimit")) {
                            $cost += $value->cost;
                            $clicks += $value->clicks;
                            $impressions += $value->impressions;
                            $ctr += $value->ctr;
                            $cpc += $value->cpc;
                            $conv += $value->conversions;
                            $convRate += $value->conversions_rate;
                            $costPerConv += $value->cost_per_conversion;
                            $count++;
                        }
                    }
                }
                $ctr = $count > 0 ? round($ctr / $count, 2) : 0;
                $cpc = $count > 0 ? round($cpc / $count) : 0;
                $convRate = $count > 0 ? round($convRate / $count, 2) : 0;
                $costPerConv = $count > 0 ? round($costPerConv / $count) : 0;

            } else {
                foreach ($hourlyAdsQuery->get() as $key => $value) {
                    if (str_contains($value->time, "$date $hourUpperLimit")) {
                        $cost += $value->cost;
                        $clicks += $value->clicks;
                        $impressions += $value->impressions;
                        $ctr += $value->ctr;
                        $cpc += $value->cpc;
                        $conv += $value->conversions;
                        $convRate += $value->conversions_rate;
                        $costPerConv += $value->cost_per_conversion;
                        $count++;
                    }
                }
                $ctr = $count > 0 ? round($ctr / $count, 2) : 0;
                $cpc = $count > 0 ? round($cpc / $count) : 0;
                $convRate = $count > 0 ? round($convRate / $count, 2) : 0;
                $costPerConv = $count > 0 ? round($costPerConv / $count) : 0;
            }
            $hourlyData = [
                '時間' => "$hour:00 - $hourUpperLimit:00",
                '入電数' => isset($aqlDataAllAccounts[$i]) ? $aqlDataAllAccounts[$i] : 0,
                '入電単価' => isset($aqlDataAllAccounts[$i]) && $cost > 0 ? round($cost / $aqlDataAllAccounts[$i]) : 0,
                '表示回数' => $impressions > 0 ? $impressions : 0,
                'クリック数' => $clicks > 0 ? $clicks : 0,
                'クリック率' => $ctr > 0 ? round($ctr, 2) : 0,
                'クリック単価' => $cpc > 0 ? round($cpc) : 0,
                '費用' => $cost > 0 ? round($cost) : 0,
                'コンバージョン' => $conv > 0 ? $conv : 0,
                'コンバージョン率' => $convRate > 0 ? round($convRate, 2) : 0,
                'コンバージョン単価' => $costPerConv > 0 ? round($costPerConv) : 0,
            ];
            $allAqlAccountsData[$i] = $hourlyData;
        }
        $dailyDatas['全部'] = $allAqlAccountsData;
        $aqlHourlyCallCount = aqlHourlyData::select(DB::raw("hour(time) as hour, count(*) as total"))
            ->where('time', 'LIKE', "$date%")
            ->groupBy(DB::raw('hour(time)'));
        $allAqlAccountsDataHolder = [];
        $aqlDataAllAccounts = $aqlHourlyCallCount->get()->pluck('total', 'hour')->all();
        // ddd($uniqueAccounts);
        foreach ($uniqueAccounts as $key => $singleAccount) {
            $accountwiseHourly = [];
            $accountAdsInfo=null;
            $filtered = aqlHourlyData::select(DB::raw("hour(time) as hour, count(*) as total"))
                ->where('time', 'LIKE', "$date%")
                ->where('accountName', $singleAccount['aqlName'])
                ->groupBy(DB::raw('hour(time)'))
                ->get()->pluck('total', 'hour')->all();
            $googleHourlyInfoAll = isset($singleAccount['google']) ? AdsAccount::find($singleAccount['google'])->hourlyAdsDatas()->where('time', 'like', "$date%")->get() : null;
            $yahooHourlyInfoAll = isset($singleAccount['yahoo']) ? AdsAccount::find($singleAccount['yahoo'])->hourlyAdsDatas()->where('time', 'like', "$date%")->get() : null;
            if ($date == $today) {
                $accountAdsInfo = isset($houlyAdsToday[$singleAccount['name']])?$houlyAdsToday[$singleAccount['name']]:null;
            }
            for ($i = 0; $i < 24; $i++) {
                $hour = $i < 10 ? "0$i" : "$i";
                $googleHourlyInfo = null;
                $yahooHourlyInfo = null;
                $hourUpperLimit = $i < 9 ? "0" . ($i + 1) : "" . ($i + 1);
                if ($googleHourlyInfoAll) {
                    foreach ($googleHourlyInfoAll as $key => $value) {
                        if (str_contains($value->time, "$date $hourUpperLimit")) {
                            $googleHourlyInfo = $value;
                        }
                    }
                }
                if ($yahooHourlyInfoAll) {
                    foreach ($yahooHourlyInfoAll as $key => $value) {
                        if (str_contains($value->time, "$date $hour")) {
                            $yahooHourlyInfo = $value;
                        }
                    }
                }
                $aqlData = isset($filtered[$i]) ? $filtered[$i] : 0;
                $cost = 0;
                $clicks = 0;
                $impressions = 0;
                $ctr = 0;
                $cpc = 0;
                $conv = 0;
                $convRate = 0;
                $costPerConv = 0;
                if ($date == $today && $accountAdsInfo) {
                    foreach ($accountAdsInfo as $key => $value) {
                        // ddd($key);
                        if (str_contains($key, "$date $hourUpperLimit")) {
                            $cost += $value->cost;
                            $clicks += $value->clicks;
                            $impressions += $value->impressions;
                            $ctr += $value->ctr;
                            $cpc += $value->cpc;
                            $conv += $value->conversions;
                            $convRate += $value->conversions_rate;
                            $costPerConv += $value->cost_per_conversion;
                            $count++;
                        }
                    }
                    $ctr = $count > 0 ? round($ctr / $count, 2) : 0;
                    $cpc = $count > 0 ? round($cpc / $count) : 0;
                    $convRate = $count > 0 ? round($convRate / $count, 2) : 0;
                    $costPerConv = $count > 0 ? round($costPerConv / $count) : 0;
                } else if ($request->input('adsPlatform')) {
                    if ($googleHourlyInfo && $request->input('adsPlatform') == 'google') {
                        $cost = $googleHourlyInfo->cost;
                        $clicks = $googleHourlyInfo->clicks;
                        $impressions = $googleHourlyInfo->impressions;
                        $ctr = $googleHourlyInfo->ctr;
                        $cpc = $googleHourlyInfo->cpc;
                        $conv = $googleHourlyInfo->conversions;
                        $convRate = $googleHourlyInfo->conversions_rate;
                        $costPerConv = $googleHourlyInfo->cost_per_conversion;
                    } else if ($yahooHourlyInfo && $request->input('adsPlatform') == 'yahoo') {
                        $cost = $yahooHourlyInfo->cost;
                        $clicks = $yahooHourlyInfo->clicks;
                        $impressions = $yahooHourlyInfo->impressions;
                        $ctr = $yahooHourlyInfo->ctr;
                        $cpc = $yahooHourlyInfo->cpc;
                        $conv = $yahooHourlyInfo->conversions;
                        $convRate = $yahooHourlyInfo->conversions_rate;
                        $costPerConv = $yahooHourlyInfo->cost_per_conversion;
                    }
                } else {
                    if ($googleHourlyInfo && $yahooHourlyInfo) {
                        $cost = $googleHourlyInfo->cost + $yahooHourlyInfo->cost;
                        $clicks = $googleHourlyInfo->clicks + $yahooHourlyInfo->clicks;
                        $impressions = $googleHourlyInfo->impressions + $yahooHourlyInfo->impressions;
                        $ctr = ($googleHourlyInfo->ctr + $yahooHourlyInfo->ctr) / 2;
                        $cpc = ($googleHourlyInfo->cpc + $yahooHourlyInfo->cpc) / 2;
                        $conv = $googleHourlyInfo->conversions + $yahooHourlyInfo->conversions;
                        $convRate = ($googleHourlyInfo->conversions_rate + $yahooHourlyInfo->conversions_rate) / 2;
                        $costPerConv = ($googleHourlyInfo->cost_per_conversion + $yahooHourlyInfo->cost_per_conversion) / 2;
                    } else if ($googleHourlyInfo) {
                        $cost = $googleHourlyInfo->cost;
                        $clicks = $googleHourlyInfo->clicks;
                        $impressions = $googleHourlyInfo->impressions;
                        $ctr = $googleHourlyInfo->ctr;
                        $cpc = $googleHourlyInfo->cpc;
                        $conv = $googleHourlyInfo->conversions;
                        $convRate = $googleHourlyInfo->conversions_rate;
                        $costPerConv = $googleHourlyInfo->cost_per_conversion;
                    } else if ($yahooHourlyInfo) {
                        $cost = $yahooHourlyInfo->cost;
                        $clicks = $yahooHourlyInfo->clicks;
                        $impressions = $yahooHourlyInfo->impressions;
                        $ctr = $yahooHourlyInfo->ctr;
                        $cpc = $yahooHourlyInfo->cpc;
                        $conv = $yahooHourlyInfo->conversions;
                        $convRate = $yahooHourlyInfo->conversions_rate;
                        $costPerConv = $yahooHourlyInfo->cost_per_conversion;
                    }
                }
                $hourlyData = [
                    '時間' => "$hour:00 - $hourUpperLimit:00",
                    '入電数' => $aqlData,
                    '入電単価' => $aqlData > 0 ? round($cost / $aqlData, 2) : 0,
                    '表示回数' => $impressions,
                    'クリック数' => $clicks,
                    'クリック率' => round($ctr, 2),
                    'クリック単価' => round($cpc, 2),
                    '費用' => round($cost),
                    'コンバージョン' => round($conv),
                    'コンバージョン率' => round($convRate, 2),
                    'コンバージョン単価' => round($costPerConv),
                ];
                $accountwiseHourly[$i] = $hourlyData;
            }
            $dailyDatas[$singleAccount['name']] = $accountwiseHourly;
        }
        return Inertia::render('DailySummary/SSSdetailExp', [
            "aqlHourlyData" => $aqlHourlyData,
            "accounts" => array_unique($adsAccounts->pluck('name')->all()),
            "hourlyDatas" => $dailyDatas,
            'table_header' => ["$date", '入電数', '入電単価', '表示回数', 'クリック数', 'クリック率', 'クリック単価', '費用', 'コンバージョン', 'コンバージョン率', 'コンバージョン単価'],
        ]);
    }
}
