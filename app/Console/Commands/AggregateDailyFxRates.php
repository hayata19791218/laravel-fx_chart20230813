<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Value;
use App\Models\ValueLog;

class AggregateDailyFxRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:aggregate-daily-fx-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'value_logsテーブルの同じ日の最高値と最低値をvaluesテーブルに登録';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = now()->subDay()->toDateString();

        $logs = ValueLog::whereDate('fetched_at', $date)
                        ->get();

        if ($logs->isEmpty()) {
            $this->warn("No logs found for $date");

            return;
        }

        $high = round($logs->max('rate'), 3);
        $row = round($logs->min('rate'), 3);
        $close = round($logs->last()->rate, 3);

        Value::Create([
            'high_value' => $high,
            'row_value' => $row,
            'date' => $date,
            'close_value' => $close,
        ]);

        \Log::info('aggregate-daily-fx-rates: 実行');

        $this->info("登録完了: high = $high, row = $row, 終値 = $close");
    }
}
