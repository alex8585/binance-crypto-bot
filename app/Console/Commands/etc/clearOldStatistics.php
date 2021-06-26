<?php

namespace App\Console\Commands\etc;

use App\GreenCount;

use \Carbon\Carbon as Carbon;
use App\Statistic as Statistic;
use Illuminate\Console\Command;

class clearOldStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear_old_statistics';

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
        $sub3m = Carbon::now()->subMonths(3);
        $sub1m = Carbon::now()->subMonths(1);

        Statistic::where([['created_at', '<',   $sub3m]])->delete();
        GreenCount::where([['created_at', '<',  $sub1m]])->delete();

        return 0;
    }
}
