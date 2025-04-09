<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class MigrateUserLinkId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:user-link-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy deprecated_link_id from latest network profile to user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::with(['networkProfiles' => function ($q) {
            $q->orderByDesc('id'); // Or orderByDesc('created_at')
        }])->get();

        foreach ($users as $user) {
            $latestProfile = $user->networkProfiles->first();
            if ($latestProfile && $latestProfile->deprecated_link_id && is_null($user->link_id)) {
                $user->link_id = $latestProfile->deprecated_link_id;
                $user->save();

                $this->info("User #{$user->id} assigned link_id: {$user->link_id}");
            }
        }

        $this->info('Migration completed.');
    }
}
