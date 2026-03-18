<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DelSessionCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DelData:Session';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'hapus session yg sudah expired';

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
        $sessionTbl = DB::table('sessions')
        ->where('last_activity', '<', now()->subMinutes(ENV("SESSION_LIFETIME"))->timestamp)
        ->delete();

        return Command::SUCCESS;
    }
}
