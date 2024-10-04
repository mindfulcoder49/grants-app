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
        Log::info('Starting centroid assignment for vectors.');

        // Step 1: Fetch all centroids
        $centroids = Centroid::all();

        // Step 2: Get the total count of vectors
        $totalVectors = Vector::count();
        Log::info("Total vectors to process: {$totalVectors}");

        
        // Step 3: Process vectors in chunks to avoid memory overload
        $processedCount = 0;
        Vector::chunk(100, function ($vectors) use ($centroids, $totalVectors, &$processedCount) {
            foreach ($vectors as $vector) {
                // Fetch the normalized vector data
                $grantVectorData = $vector->normalized_vector;

                if (!$grantVectorData) {
                    Log::warning("Vector ID {$vector->id} has invalid normalized_vector data, skipping.");
                    continue;
                }

                // Step 4: Find the closest centroid for this vector
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

                // Step 5: Assign the closest centroid to the vector
                if ($bestMatch) {
                    DB::table('grant_vector')
                        ->where('vector_id', $vector->id)
                        ->update(['centroid_id' => $bestMatch->id]);

                    Log::info("Assigned centroid {$bestMatch->id} to vector ID {$vector->id}.");
                }

                // Increment processed count and log progress
                $processedCount++;
                if ($processedCount % 10 == 0 || $processedCount == $totalVectors) {
                    Log::info("Processed {$processedCount} out of {$totalVectors} vectors.");
                }
            }
        });

        Log::info('Centroid assignment for vectors completed.');
    }

    /**
     * Cosine similarity function (1 - cosine similarity is used as distance)
     */
    private function cosineSimilarity($vectorA, $vectorB)
    {
        if (!$vectorA || !$vectorB) {
            return PHP_FLOAT_MAX;
        }

        $dotProduct = array_sum(array_map(fn($a, $b) => $a * $b, $vectorA, $vectorB));
        $magnitudeA = sqrt(array_sum(array_map(fn($a) => $a * $a, $vectorA)));
        $magnitudeB = sqrt(array_sum(array_map(fn($b) => $b * $b, $vectorB)));

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return PHP_FLOAT_MAX;
        }

        return 1 - ($dotProduct / ($magnitudeA * $magnitudeB));  // 1 - cosine similarity as a distance metric
    }
}
