<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        try {
            $accessKey = 'fON2piTdHwscP1ACdBsQxvHZMGdojpZm';
            $url = 'https://api.apilayer.com/exchangerates_data/latest?base=USD&symbols=JPY';

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "apikey: {$accessKey}\r\n",
                    'timeout' => 10,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                $error = error_get_last();
                throw new \Exception("APIリクエストに失敗しました（file_get_contents）: " . $error['message']);
            }

            $data = json_decode($response, true);
            $jpyRate = $data['rates']['JPY'] ?? null;

            if ($jpyRate) {
                ValueLog::create([
                    'rate' => $jpyRate,
                    'fetched_at' => now(),
                ]);

                $this->info("保存完了: 1 USD/JPY = {$jpyRate}");
                Log::info('fx:fetch 成功: 1 USD = ' . $jpyRate . ' JPY');
            } else {
                Log::warning('fx:fetch 失敗: JPYレートが取得できませんでした');
            }
        } catch (\Throwable $e) {
            Log::error('[fx:fetch] 通信エラー: ' . $e->getMessage());
        }
    }
}
