<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Run custom Artisan commands
        Artisan::call('grants:download-xml');
        Artisan::call('db:seed', ['--class' => 'GrantsTableSeeder']);
        Artisan::call('db:seed', ['--class' => 'GrantVectorSeeder']);
    }
}
