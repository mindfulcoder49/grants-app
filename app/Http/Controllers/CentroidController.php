<?php

namespace App\Http\Controllers;

use App\Models\Centroid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CentroidController extends Controller
{
    // Method to create centroids
    public function createCentroid(Request $request)
    {
        $validated = $request->validate([
            'vector' => 'required|array',  // Expecting a JSON array for the vector
        ]);

        $centroid = Centroid::create([
            'vector' => $validated['vector']
        ]);

        return response()->json(['centroid' => $centroid], 201);
    }

    // Method to find the closest centroid (using cosine similarity)
    public function findClosestCentroid(Request $request)
    {
        $validated = $request->validate([
            'query_vector' => 'required|array',  // Expecting a JSON array
        ]);

        $queryVector = $validated['query_vector'];

        // Get all centroids from the database
        $centroids = Centroid::all();

        $bestMatch = null;
        $bestDistance = PHP_FLOAT_MAX;

        // Iterate through centroids to find the closest one
        foreach ($centroids as $centroid) {
            $centroidVector = $centroid->vector;
            $distance = $this->cosineSimilarity($queryVector, $centroidVector);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $centroid;
            }
        }

        return response()->json([
            'closest_centroid' => $bestMatch,
            'distance' => $bestDistance
        ]);
    }

    // Cosine similarity function
    private function cosineSimilarity($vectorA, $vectorB)
    {
        $dotProduct = array_sum(array_map(fn($a, $b) => $a * $b, $vectorA, $vectorB));
        $magnitudeA = sqrt(array_sum(array_map(fn($a) => $a * $a, $vectorA)));
        $magnitudeB = sqrt(array_sum(array_map(fn($b) => $b * $b, $vectorB)));

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return PHP_FLOAT_MAX;  // Return max value if one vector is zero (no similarity)
        }

        return 1 - ($dotProduct / ($magnitudeA * $magnitudeB));  // 1 - cosine similarity to treat as distance
    }
}
