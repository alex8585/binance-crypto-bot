<?php

namespace App\Jobs;
use Artisan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessGetStatistics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $candidatesIds;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($candidatesIds)
    {
        $this->candidatesIds = $candidatesIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('get_statistics',[
            'candidates_ids' =>  $this->candidatesIds,
        ]);
    }
}
