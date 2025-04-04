<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackfillLinkAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backfill:link-assignments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \App\Models\NetworkProfile::whereNotNull('link_id')->get()->each(function ($networkProfile) {
            \App\Models\NetworkProfileLinkAssignment::firstOrCreate([
                'profile_id' => $networkProfile->id,
                'link_id' => $networkProfile->link_id,
            ], [
                'assigned_at' => $networkProfile->created_at ?? now(),
            ]);
        });
    
        $this->info('Backfilled current link assignments.');
    }
}
