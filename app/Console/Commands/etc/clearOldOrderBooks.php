<?php

namespace App\Console\Commands\etc;

use Illuminate\Console\Command;

use \Carbon\Carbon as Carbon;
use App\OrderBook;

class clearOldOrderBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear_old_order_books';

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
        $sub2m = Carbon::now()->subMonths(3);

        $order_books = OrderBook::where([['created_at', '<',  $sub2m]])->delete();
        //dd(count($order_books));
        return 0;
    }
}
