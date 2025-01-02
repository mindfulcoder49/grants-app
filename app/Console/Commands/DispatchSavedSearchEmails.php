<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Jobs\SendSavedSearchEmail;

class DispatchSavedSearchEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch jobs to perform saved searches and send email alerts for users with alerts enabled';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNotNull('alerts_setting')
            ->where('alerts_setting->frequency', '!=', 'disabled')
            ->get();

        foreach ($users as $user) {
            SendSavedSearchEmail::dispatch($user);
        }

        $this->info('Dispatched saved search jobs for all users with alerts enabled.');
    }
}
