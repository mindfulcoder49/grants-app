<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Vector;

class CleanDuplicateGrantVectors extends Command
{
    protected $signature = 'grantvector:clean-duplicates';
    protected $description = 'Remove duplicate grant_vector entries and their vectors, keeping the one with the highest grant_id.';

    public function handle()
    {
        // Find duplicates based on opportunity_id
        $duplicates = DB::table('grant_vector')
            ->select('opportunity_id', DB::raw('MAX(grant_id) as max_grant_id'))
            ->groupBy('opportunity_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            // Get all entries for this opportunity_id
            $entries = DB::table('grant_vector')
                ->where('opportunity_id', $duplicate->opportunity_id)
                ->get();

            // Get the entry to keep
            $entryToKeep = $entries->firstWhere('grant_id', $duplicate->max_grant_id);

            // Delete the other entries and their corresponding vectors
            foreach ($entries as $entry) {
                if ($entry->id !== $entryToKeep->id) {
                    // Delete the vector associated with this entry
                    Vector::destroy($entry->vector_id);

                    // Delete the duplicate grant_vector entry
                    DB::table('grant_vector')->where('id', $entry->id)->delete();
                    $this->info("Deleted duplicate grant_vector entry for opportunity_id: {$entry->opportunity_id}");
                }
            }
        }

        $this->info('Finished cleaning up duplicate grant_vector entries.');
    }
}
