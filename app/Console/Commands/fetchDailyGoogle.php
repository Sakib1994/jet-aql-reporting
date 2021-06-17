<?php

namespace App\Console\Commands;

use App\Models\adsAccount;
use App\Models\DailyAdsData;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Lib\V7\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V7\GoogleAdsClientBuilder;
use Illuminate\Console\Command;
use Carbon\Carbon;

class fetchDailyGoogle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:dailyfetchfromapi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the daily account data from google ads api.';

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
        $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->build();

        // Construct a Google Ads client configured from a properties file and the
        // OAuth2 credentials above.
        $googleAdsClient = (new GoogleAdsClientBuilder())->fromFile()
            ->withOAuth2Credential($oAuth2Credential)
            ->withLoginCustomerId(4387461648)
            ->build();
        $accountIds = adsAccount::select("id", "accountId")->where('platform','google')->get();
        foreach ($accountIds as $account) {
            print_r($account->id . "\n");
            if (self::fetchFromApi( $googleAdsClient, $account->accountId, $account->id)) {
                $this->info('Successfully command executed.');
            }else{
                $this->error('Already exist in the databsse.');
            }
        }
        return 0;
    }
    public static function fetchFromApi(GoogleAdsClient $googleAdsClient, int $customerId, int $accountId)
    {
        print_r($customerId, $accountId . "\n");
        $date = '2021-06-03';
        $date = Carbon::now('Asia/Tokyo')->sub(1, 'day')->isoFormat('YYYY-MM-DD');
        $isSaved = DailyAdsData::where('date', $date)->firstWhere('AdsAccountId', $accountId);
        if ($isSaved) {
            return 0;
        }
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
        // Creates a query that retrieves hotel-ads statistics for each campaign and ad group.
        // Returned statistics will be segmented by the check-in day of week and length of stay.

        // $query = "SELECT customer.descriptive_name, segments.date, metrics.clicks, metrics.impressions, metrics.ctr, metrics.average_cpc, metrics.cost_micros, metrics.conversions, metrics.cost_per_conversion, metrics.conversions_from_interactions_rate FROM customer WHERE segments.date >= '2021-05-01' AND segments.date <= '2021-05-15'";
        $queryFromBuider = "SELECT customer.descriptive_name, segments.date, metrics.clicks, metrics.ctr, metrics.impressions, metrics.conversions, metrics.average_cpc, metrics.cost_per_conversion, metrics.cost_micros, metrics.conversions_from_interactions_rate FROM customer WHERE segments.date = '$date'";
        // Issues a search request by specifying page size.
        $response = $googleAdsServiceClient->search($customerId, $queryFromBuider, ['pageSize' => self::PAGE_SIZE]);

        // Iterates over all rows in all pages and prints the requested field values for each row.
        foreach ($response->iterateAllElements() as $googleAdsRow) {
            if ($googleAdsRow->getMetrics()->getClicks() == 0) {
                continue;
            }
            printf(
                "Customer '%s', date %s,  Clicks %d, Impressions %d, ctr %.2f%%, Cost %.2f, CPC %4.2f, Conversions %d conversion_rate %.2f and CostPerConversion %.2f\n",
                $googleAdsRow->getCustomer()->getDescriptiveName(),
                $googleAdsRow->getSegments()->getDate(),
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
            $dailyAdsData = new DailyAdsData([
                "AdsAccountId" => $accountId,
                "date" => $googleAdsRow->getSegments()->getDate(),
                "clicks" => $googleAdsRow->getMetrics()->getClicks(),
                "impressions" => $googleAdsRow->getMetrics()->getImpressions(),
                "ctr" => round($googleAdsRow->getMetrics()->getCtr() * 100, 2),
                "cost" => round($googleAdsRow->getMetrics()->getCostMicros() / 1000000),
                "cpc" => round($googleAdsRow->getMetrics()->getAverageCpc() / 1000000),
                "conversions" => $googleAdsRow->getMetrics()->getConversions(),
                "conversions_rate" => round($googleAdsRow->getMetrics()->getConversionsFromInteractionsRate() * 100, 2),
                "cost_per_conversion" => round($googleAdsRow->getMetrics()->getCostPerConversion() / 1000000),
            ]);
            $dailyAdsData->save();
        }
        return 1;
    }
}
