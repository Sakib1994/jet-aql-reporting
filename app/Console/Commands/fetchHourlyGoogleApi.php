<?php

namespace App\Console\Commands;

use App\Models\adsAccount;
use App\Models\HourlyAdsData;
use Carbon\Carbon;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Lib\V7\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V7\GoogleAdsClientBuilder;
use Illuminate\Console\Command;

class fetchHourlyGoogleApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:hourlyfetchfromapi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the hourly account data from google ads api.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    private const PAGE_SIZE = 50;
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // API接続を構成します。
        $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->build();

        $googleAdsClient = (new GoogleAdsClientBuilder())->fromFile()
            ->withOAuth2Credential($oAuth2Credential)
            ->withLoginCustomerId(4387461648)
            ->build();
        // データベースからすべてのGoogle広告アカウントの顧客IDを取得します。
        $accountIds = adsAccount::select("id", "accountId")->where('platform', 'google')->get();
        // 広告アカウントのグーグルから毎時データを取得し、データベースに保存します。
        foreach ($accountIds as $account) {
            if (self::fetchFromApi($googleAdsClient, $account->accountId, $account->id)) {
                $this->info('Successfully command executed.');
            } else {
                $this->error('Already exist in the databsse or no data from api.');
            }
        }
        return 0;
    }
    public static function fetchFromApi(GoogleAdsClient $googleAdsClient, int $customerId, int $accountId)
    {
        printf($customerId . " " . $accountId . "\n");
        $getTheNow = Carbon::now('Asia/Tokyo');
        $date = $getTheNow->isoFormat('YYYY-MM-DD');
        $hourtoSave = $getTheNow->sub(1, 'hour')->isoFormat('HH');
        $hour = $getTheNow->sub(1, 'hour')->isoFormat('HH');

        /*$date = "2021-06-01";
        // $hourlower = $getTheNow->sub(2,'hour')->isoFormat('HH');
        for ($hour = 0; $hour < 24; $hour++) {
            // $hourProcess = $hour < 10 ? "0$hour" : "$hour";
            $hourtoSave = $hour < 9 ? "0" . ($hour + 1) : "" . ($hour + 1);*/
            // $time = "$date $hourtosave:00:00";
            $time = "$date $hourtoSave:00:00";
            print_r("$customerId $time\n");
            // データがデータベースにすでに存在する場合は、データを確認する。
            // 存在する場合は、終了します。
            $isSaved = HourlyAdsData::where('time', $time)
                ->firstWhere('AdsAccountId', $accountId);
            if ($isSaved) {
                return 0;
                // continue;
            }
            // 毎時データグーグルを取得します。
            $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
            // これは特別なクエリ言語ですGAQL.dataはこれを使用してグーグルからフェッチされます。
            $queryFromBuider = "SELECT customer.descriptive_name, segments.hour, metrics.clicks, metrics.ctr, metrics.impressions, metrics.conversions, metrics.average_cpc, metrics.cost_per_conversion, metrics.cost_micros, metrics.conversions_from_interactions_rate FROM customer WHERE segments.date = '$date' AND segments.hour = $hour";
            $response = $googleAdsServiceClient->search($customerId, $queryFromBuider, ['pageSize' => self::PAGE_SIZE]);
            // APIからデータをループして、データを取得します。
            foreach ($response->iterateAllElements() as $googleAdsRow) {
                if ($googleAdsRow->getMetrics()->getClicks() == 0) {
                    // return 0;
                    continue;
                }
                printf(
                    "Customer '%s', hour %s,  Clicks %d, Impressions %d, ctr %.2f%%, Cost %.2f, CPC %4.2f, Conversions %d conversion_rate %.2f and CostPerConversion %.2f\n",
                    $googleAdsRow->getCustomer()->getDescriptiveName(),
                    $googleAdsRow->getSegments()->getHour(),
                    $googleAdsRow->getMetrics()->getClicks(),
                    $googleAdsRow->getMetrics()->getImpressions(),
                    round($googleAdsRow->getMetrics()->getCtr() * 100, 2),
                    round($googleAdsRow->getMetrics()->getCostMicros() / 1000000, 2),
                    round($googleAdsRow->getMetrics()->getAverageCpc() / 1000000, 2),
                    $googleAdsRow->getMetrics()->getConversions(),
                    round($googleAdsRow->getMetrics()->getConversionsFromInteractionsRate() * 100, 2),
                    round($googleAdsRow->getMetrics()->getCostPerConversion() / 1000000, 2),
                    PHP_EOL
                );
                // グーグルから毎時データを取得し、データベースに保存します。
                $hourlyAdsData = new HourlyAdsData([
                    "AdsAccountId" => $accountId,
                    "time" => $time,
                    "clicks" => $googleAdsRow->getMetrics()->getClicks(),
                    "impressions" => $googleAdsRow->getMetrics()->getImpressions(),
                    "ctr" => round($googleAdsRow->getMetrics()->getCtr() * 100, 2),
                    "cost" => round($googleAdsRow->getMetrics()->getCostMicros() / 1000000),
                    "cpc" => round($googleAdsRow->getMetrics()->getAverageCpc() / 1000000),
                    "conversions" => $googleAdsRow->getMetrics()->getConversions(),
                    "conversions_rate" => round($googleAdsRow->getMetrics()->getConversionsFromInteractionsRate() * 100, 2),
                    "cost_per_conversion" => round($googleAdsRow->getMetrics()->getCostPerConversion() / 1000000),
                ]);
                $hourlyAdsData->save();
            }
        // }
        return 1;
    }
}
