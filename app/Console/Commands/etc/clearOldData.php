<?php

namespace App\Console\Commands\etc;

use Illuminate\Console\Command;

use App\Candidate as Candidate;
use \Carbon\Carbon as Carbon;
use App\Statistic as Statistic;
use App\Circle as Circle;

class clearOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear_old_data';

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
      
      
        // $now = Carbon::now()->add(1, 'day');
        // $circles = Circle::select('id')->where([['created_at', '<',  $now]]);
        // $circlesIds = $circles->pluck('id')->all();
       
        // $candidates = Candidate::whereIn('circle_id',$circlesIds);
        // $candidatesIds = $candidates->pluck('id')->all();


        // $statistics = Statistic::whereIn('candidate_id', $candidatesIds);
       
        // $circles->delete();
        // $candidates->delete();
        // $statistics ->delete();


        // //dd($candidatesIds);

        // return 0;
    }
}
