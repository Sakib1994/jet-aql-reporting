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
            if ($this->fetchFromApi($googleAdsClient, $account->accountId, $account->id)) {
                $this->info('Successfully command executed.');
            } else {
                $this->error('Already exist in the databsse or no data from api.');
            }
        }
        return 0;
    }
    public function fetchFromApi(GoogleAdsClient $googleAdsClient, int $customerId, int $accountId)
    {
        printf($customerId . " " . $accountId . "\n");
        $getTheNow = Carbon::now('+8:30');
        dump("Current time: ".$getTheNow->isoFormat('YYYY-MM-DD HH:mm:ss'));
        $date = $getTheNow->isoFormat('YYYY-MM-DD');
        $hournow = $getTheNow->isoFormat('YYYY-MM-DD HH:00:00');
        $hourtoSave = $getTheNow->sub(1, 'hour')->isoFormat('HH');
        print_r("Customer ID: $customerId Time: $hournow\n");
        // データがデータベースにすでに存在する場合は、データを確認する。
        // 存在する場合は、終了します。
        if ($hourtoSave != "24") {
            $isSaved = HourlyAdsData::where('time', 'like', $hournow)
                ->firstWhere('AdsAccountId', $accountId);
        }
        if ($isSaved) {
            return 0;
            // $this->error('Already exist in the databsse or no data from api.');
            // continue;
        }
        // 毎時データグーグルを取得します。
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
        // これは特別なクエリ言語ですGAQL.dataはこれを使用してグーグルからフェッチされます。
        $queryFromBuider = "SELECT customer.descriptive_name, segments.hour, metrics.clicks, metrics.ctr, metrics.impressions, metrics.conversions, metrics.average_cpc, metrics.cost_per_conversion, metrics.cost_micros, metrics.conversions_from_interactions_rate FROM customer WHERE segments.date = '$date' AND segments.hour = $hourtoSave";
        $response = $googleAdsServiceClient->search($customerId, $queryFromBuider, ['pageSize' => self::PAGE_SIZE]);
        // APIからデータをループして、データを取得します。
        foreach ($response->iterateAllElements() as $googleAdsRow) {
            if ($googleAdsRow->getMetrics()->getClicks() == 0) {
                continue;
            }
            printf(
                "Customer '%s', hour %s,  Clicks %d, Impressions %d, ctr %.2f%%, Cost %.2f, CPC %4.2f, Conversions %d conversion_rate %.2f and CostPerConversion %.2f\n",
                $googleAdsRow->getCustomer()->getDescriptiveName(),
                $googleAdsRow->getSegments()->getHour(),
                $googleAdsRow->getMetrics()->getClicks(),
                $googleAdsRow->getMetrics()->getImpressions(),
                round($googleAdsRow->getMetrics()->getCtr() * 100, 2),
                round($googleAdsRow->getMetrics()->getCostMicros() / 1000000),
                round($googleAdsRow->getMetrics()->getAverageCpc() / 1000000),
                $googleAdsRow->getMetrics()->getConversions(),
                round($googleAdsRow->getMetrics()->getConversionsFromInteractionsRate() * 100, 2),
                round($googleAdsRow->getMetrics()->getCostPerConversion() / 1000000),
                PHP_EOL
            );
            // グーグルから毎時データを取得し、データベースに保存します。
            $hourlyAdsData = new HourlyAdsData([
                "AdsAccountId" => $accountId,
                "time" => $hournow,
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
        return 1;
    }
}
