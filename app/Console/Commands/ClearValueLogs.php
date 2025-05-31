<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearValueLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'valuelogs:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '毎日00:11にvalue_logsテーブルを全削除する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('value_logs')->truncate();
        
        \Log::info('valuelogs:clear');

        $this->info('value_logs テーブルのレコードを削除しました');
    }
}
