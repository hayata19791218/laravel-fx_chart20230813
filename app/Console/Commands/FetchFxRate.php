<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ValueLog;

class FetchFxRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fx:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FXのチャートの値を登録';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accessKey = 'fON2piTdHwscP1ACdBsQxvHZMGdojpZm';

        $response = Http::withHeaders([
            'apikey' => $accessKey
        ])->get('https://api.apilayer.com/exchangerates_data/latest', [
            'base' => 'USD',
            'symbols' => 'JPY',
        ]);

        $data = $response->json();

        $jpyRate = $data['rates']['JPY'] ?? null;

        if ($jpyRate) {
            $usdToJpy = $jpyRate;
            ValueLog::create([
                'rate' => $usdToJpy,
                'fetched_at' => now(),
            ]);

            $this->info("保存完了: 1 USD/JPY = {$usdToJpy}");

            \Log::info('fx:fetch 実行されました');
        } else {
            $this->error("為替レートの取得に失敗しました。");
        }
    }
}
