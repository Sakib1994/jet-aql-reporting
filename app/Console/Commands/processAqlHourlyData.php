<?php

namespace App\Console\Commands;

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
        $fileNameHead = "outSSS2021050109";
        // $fileNameHead = "SSS20210509";

        $fileName = $fileNameHead . "0500.csv";
        $fileName1 = $fileNameHead . "0459.csv";
        $fileName2 = $fileNameHead . "0501.csv";
        printf("file name: %s\n", $fileName);
            if (Storage::disk('sheets')->exists($fileName)) {
                $this->save($fileName);
            } else if (Storage::disk('sheets')->exists($fileName1)) {
                $this->save($fileName1);
            } else if (Storage::disk('sheets')->exists($fileName2)) {
                $this->save($fileName2);
            } else {
                $this->error('Command did not execute. No such file.');
                return 0;
                // continue;
            }
            if (Storage::disk('sheets')->exists($fileName1)) {
                Storage::disk('sheets')->delete($fileName1);
                // File::move(storage_path("sheets/$fileName1"), storage_path("sheets/done/$fileName1"));
            }
            if (Storage::disk('sheets')->exists($fileName2)) {
                Storage::disk('sheets')->delete($fileName2);
                // File::move(storage_path("sheets/$fileName2"), storage_path("sheets/done/$fileName2"));
            }
            $this->info('Successfully command executed.');
        return 0;
    }
}
