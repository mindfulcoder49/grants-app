<?php

namespace Database\Seeders;

use App\Models\Vector;
use App\Models\Centroid;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CentroidSeeder extends Seeder
{
    protected $numCentroids = 200;  // Number of centroids to find

    public function run()
    {
        Log::info('Starting Centroid Seeder: Fetching vectors from database');

        // Step 1: Get all vectors from the Vector table
        $vectors = Vector::all()->pluck('vector')->toArray();
        $vectorCount = count($vectors);
        
        Log::info("Fetched {$vectorCount} vectors from database.");

        // Step 2: Initialize or resume k-means algorithm
        Log::info("Running k-means algorithm with {$this->numCentroids} centroids.");
        
        // If centroids already exist in the DB, use them to resume
        $existingCentroids = Centroid::all()->pluck('vector')->toArray();
        $centroids = count($existingCentroids) > 0 ? $existingCentroids : $this->initializeCentroids($vectors, $this->numCentroids);

        // Step 3: Perform k-means clustering, save centroids after each iteration
        $this->kMeans($vectors, $centroids, $this->numCentroids);

        Log::info('Centroid Seeder completed successfully.');
    }

    /**
     * Initialize centroids randomly from the existing vectors
     */
    private function initializeCentroids(array $vectors, int $k)
    {
        Log::info("Initializing centroids randomly.");
        return array_map(fn($i) => $vectors[array_rand($vectors)], range(0, $k - 1));
    }

    /**
     * K-means clustering algorithm to find centroids, saving after each iteration
     */
    private function kMeans(array $vectors, array $centroids, int $k, int $maxIterations = 100)
    {
        $previousCentroids = [];

        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            Log::info("K-means iteration {$iteration} started.");

            // Step 1: Assign each vector to the closest centroid
            $clusters = array_fill(0, $k, []);
            foreach ($vectors as $vector) {
                $closestIndex = $this->findClosestCentroid($vector, $centroids);
                $clusters[$closestIndex][] = $vector;
            }

            // Step 2: Recalculate the centroids of each cluster
            $previousCentroids = $centroids;
            foreach ($clusters as $index => $cluster) {
                if (count($cluster) > 0) {
                    $centroids[$index] = $this->calculateCentroid($cluster);
                    Log::info("Centroid {$index} recalculated.");
                }
            }

            // Step 3: Save centroids after each iteration
            $this->saveCentroids($centroids);

            // Step 4: Check if centroids have converged
            $convergenceMetric = $this->calculateConvergenceMetric($centroids, $previousCentroids);
            Log::info("Convergence metric (SSD): {$convergenceMetric}");

            if ($convergenceMetric < 0.001) {
                Log::info("Centroids converged with metric {$convergenceMetric} after {$iteration} iterations.");
                break;
            }
        }
    }

    /**
     * Save centroids to the database
     */
    private function saveCentroids(array $centroids)
    {
        foreach ($centroids as $index => $centroid) {
            // Check if centroid already exists, update or create new entry
            $existingCentroid = Centroid::find($index + 1); // Assuming ID is 1-based

            if ($existingCentroid) {
                $existingCentroid->update(['vector' => $centroid]);
                Log::info("Updated Centroid {$index} in the database.");
            } else {
                Centroid::create(['vector' => $centroid]);
                Log::info("Created Centroid {$index} in the database.");
            }
        }
    }

    /**
     * Find the index of the closest centroid to the given vector
     */
    private function findClosestCentroid(array $vector, array $centroids)
    {
        $bestDistance = PHP_FLOAT_MAX;
        $bestIndex = 0;

        foreach ($centroids as $index => $centroid) {
            $distance = $this->cosineSimilarity($vector, $centroid);
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestIndex = $index;
            }
        }

        return $bestIndex;
    }

    /**
     * Calculate the centroid of a given cluster of vectors
     */
    private function calculateCentroid(array $vectors)
    {
        $centroid = [];
        $numVectors = count($vectors);
        $numDimensions = count($vectors[0]);

        for ($i = 0; $i < $numDimensions; $i++) {
            $sum = 0;
            foreach ($vectors as $vector) {
                $sum += $vector[$i];
            }
            $centroid[] = $sum / $numVectors;
        }

        return $centroid;
    }

    /**
     * Calculate the convergence metric (sum of squared differences between centroids)
     */
    private function calculateConvergenceMetric(array $centroids, array $previousCentroids)
    {
        $sumSquaredDifference = 0;

        for ($i = 0; $i < count($centroids); $i++) {
            for ($j = 0; $j < count($centroids[$i]); $j++) {
                $difference = $centroids[$i][$j] - $previousCentroids[$i][$j];
                $sumSquaredDifference += $difference * $difference;
            }
        }

        return $sumSquaredDifference;
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

        return 1 - ($dotProduct / ($magnitudeA * $magnitudeB));
    }
}
