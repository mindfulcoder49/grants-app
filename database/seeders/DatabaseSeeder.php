<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Console\Commands\DownloadGrantsXML;  

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //run custom command grants:download-xml
        $this->call([
            DownloadGrantsXML::class,
        ]);

        //Chain other seeders together
        $this->call([
            GrantsTableSeeder::class,
            GrantVectorSeeder::class,
            CentroidSeeder::class,
            AssignVectorsToCentroidsSeeder::class,
        ]);
    }
}
