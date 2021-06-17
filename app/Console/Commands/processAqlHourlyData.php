<?php

namespace App\Console\Commands;

use App\Models\aqlHourlyData;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class processAqlHourlyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aql:hourlyprocessdata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        
        $fileNameHead = "SSS" . Carbon::now('Asia/Tokyo')->isoFormat('YYYYMMDDHH');
        $fileName = $fileNameHead . "0500.csv";
        $fileName1 = $fileNameHead . "0459.csv";
        $fileName2 = $fileNameHead . "0501.csv";
        $fileName3 = $fileNameHead . "0502.csv";
        printf("file name: %s\n", $fileName);
        if (Storage::disk('sheets')->exists($fileName)) {
            $this->save($fileName);
        } else if (Storage::disk('sheets')->exists($fileName1)) {
            $this->save($fileName1);
        } else if (Storage::disk('sheets')->exists($fileName2)) {
            $this->save($fileName2);
        } else if (Storage::disk('sheets')->exists($fileName3)) {
            $this->save($fileName3);
        } else {
            $this->error('Command did not execute. No such file.');
            return 0;
            // continue;
        }
        if (Storage::disk('sheets')->exists($fileName1)) {
            Storage::disk('sheets')->delete($fileName1);
        }
        if (Storage::disk('sheets')->exists($fileName2)) {
            Storage::disk('sheets')->delete($fileName2);
        }
        if (Storage::disk('sheets')->exists($fileName3)) {
            Storage::disk('sheets')->delete($fileName3);
        }
        $this->info('Successfully command executed.');
        return 0;
    }
    public function save($fileName)
    {
        $file = mb_convert_encoding(Storage::disk('sheets')->get($fileName), "UTF-8", "SHIFT-JIS");
        $rows = explode("\r\n", $file);
        $header = array_shift($rows);
        $accounts = [];
        $header = explode(',', $header);
        if (count($rows) > 0 && $rows[0] != "") {
            foreach ($rows as $row => $data) {
                if (strlen($data) > 0) {
                    $row_data = str_getcsv($data, ',', $data);
                    array_push($accounts, $row_data[1]);
                    $aqlHourly = new aqlHourlyData([
                        'time' => date('Y-m-d H:i:s', strtotime($row_data[0])),
                        'accountName' => $row_data[1],
                        'prefecture' => $row_data[2],
                        'requestType' => $row_data[3],
                        'requestDetail' => $row_data[4],
                    ]);
                    $aqlHourly->save();
                }
            }
        }
        Storage::disk('sheets')->delete($fileName);
    }
}
