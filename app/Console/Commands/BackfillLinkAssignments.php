<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserLinkAssignment;

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
    protected $description = 'Backfill current user link assignments.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        User::whereNotNull('link_id')->get()->each(function ($user) {
            UserLinkAssignment::firstOrCreate([
                'user_id' => $user->id,
                'link_id' => $user->link_id,
            ], [
                'assigned_at' => $user->created_at ?? now(),
            ]);
        });

        $this->info('Backfilled user link assignments.');
    }
}
