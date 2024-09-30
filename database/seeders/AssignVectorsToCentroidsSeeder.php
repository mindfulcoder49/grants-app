<?php
namespace Database\Seeders;

use App\Models\Vector;
use App\Models\Centroid;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignVectorsToCentroidsSeeder extends Seeder
{
    public function run()
    {
        Log::info('Starting centroid assignment forvectors.');

        // Step 1: Fetch all centroids
        $centroids = Centroid::all();

        // Step 2: Process grant vectors in chunks to avoid memory overload
        DB::table('grant_vector')->orderBy('id')->chunk(100, function ($grantVectorRecords) use ($centroids) {
            foreach ($grantVectorRecords as $grantVectorRecord) {
                // Fetch the vector data
                $vector = Vector::find($grantVectorRecord->vector_id);

                if (!$vector) {
                    Log::warning("Vector ID {$grantVectorRecord->vector_id} not found, skipping.");
                    continue;
                }

                $grantVectorData = $vector->vector;

                // Step 3: Find the closest centroid for this vector
                $bestMatch = null;
                $bestDistance = PHP_FLOAT_MAX;

                foreach ($centroids as $centroid) {
                    $centroidVector = $centroid->vector;
                    $distance = $this->cosineSimilarity($grantVectorData, $centroidVector);

                    if ($distance < $bestDistance) {
                        $bestDistance = $distance;
                        $bestMatch = $centroid;
                    }
                }

                // Step 4: Assign the closest centroid to the grant vector
                if ($bestMatch) {
                    DB::table('grant_vector')
                        ->where('id', $grantVectorRecord->id)
                        ->update(['centroid_id' => $bestMatch->id]);

                    Log::info("Assigned centroid {$bestMatch->id} to vector ID {$vector->id}.");
                }
            }
        });

        Log::info('Centroid assignment for grant vectors completed.');
    }

    /**
     * Cosine similarity function (1 - cosine similarity is used as distance)
     */
    private function cosineSimilarity($vectorA, $vectorB)
    {
        $dotProduct = array_sum(array_map(fn($a, $b) => $a * $b, $vectorA, $vectorB));
        $magnitudeA = sqrt(array_sum(array_map(fn($a) => $a * $a, $vectorA)));
        $magnitudeB = sqrt(array_sum(array_map(fn($b) => $b * $b, $vectorB)));

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return PHP_FLOAT_MAX;
        }

        return 1 - ($dotProduct / ($magnitudeA * $magnitudeB));  // 1 - cosine similarity as a distance metric
    }
}
