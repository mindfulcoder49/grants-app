<?php

namespace Database\Seeders;

use App\Models\Vector;
use App\Models\Centroid;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignNewVectorsToCentroidsSeeder extends Seeder
{
    public function run()
    {
        Log::info('Starting centroid assignment for new vectors without a centroid.');

        // Step 1: Fetch all centroids
        $centroids = Centroid::all();

        // Step 2: Get the count of vectors that do not have an assigned centroid
        $totalVectors = DB::table('grant_vector')
            ->whereNull('centroid_id')
            ->count();
        Log::info("Total new vectors to process: {$totalVectors}");

        // Step 3: Process only vectors without a centroid in chunks
        $processedCount = 0;
        DB::table('grant_vector')
            ->whereNull('centroid_id')
            ->chunkById(100, function ($grantVectors) use ($centroids, $totalVectors, &$processedCount) {
                foreach ($grantVectors as $grantVector) {
                    // Fetch the vector model by ID
                    $vector = Vector::find($grantVector->vector_id);
                    if (!$vector) {
                        Log::warning("Vector ID {$grantVector->vector_id} not found, skipping.");
                        continue;
                    }

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
                            ->where('id', $grantVector->id)
                            ->update(['centroid_id' => $bestMatch->id]);

                        Log::info("Assigned centroid {$bestMatch->id} to vector ID {$vector->id}.");
                    }

                    // Increment processed count and log progress
                    $processedCount++;
                    if ($processedCount % 10 == 0 || $processedCount == $totalVectors) {
                        Log::info("Processed {$processedCount} out of {$totalVectors} new vectors.");
                    }
                }
            });

        Log::info('Centroid assignment for new vectors completed.');
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
