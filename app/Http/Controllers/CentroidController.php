<?php

namespace App\Http\Controllers;

use App\Models\Centroid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vector;
use Illuminate\Support\Facades\Log;

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

    /**
     * Search similar vectors using cosine similarity within centroids.
     */
    public function searchByCosineSimilarity(Request $request)
    {
        $validated = $request->validate([
            'vector'        => 'required|array',
            'top_centroids' => 'integer|min:1',
            'topN'          => 'integer|min:1',
            'single_centroid' => 'integer|nullable', // Optional single centroid ID
            'scope'         => 'string|nullable', // Optional scope for filtering vectors
        ]);


        $queryVector = $validated['vector'];
        $normalizedVector = Vector::normalize($queryVector);
        $top_centroids = $validated['top_centroids'] ?? 5;
        $topN = $validated['topN'] ?? 10;
        $single_centroid = $validated['single_centroid'] ?? -1;
        $vectorsInCentroids = [];
        $scope = $validated['scope'] ?? 'all';

        if ($single_centroid > -1 ) {
            Log::info('Retrieving vectors from a single centroid: ' . $single_centroid);
            $my_centroid = Centroid::find($single_centroid);
            $vectorsInCentroids = $this->getVectorsFromCentroids([$my_centroid], $scope);
        } else {
            // Step 1: Find the closest centroids
            Log::info('Finding closest centroids');
            $closestCentroids = $this->findClosestCentroids($normalizedVector, $top_centroids);

            // Step 2: Retrieve vectors from these centroids
            Log::info('Retrieving vectors from centroids');
            $vectorsInCentroids = $this->getVectorsFromCentroids($closestCentroids, $scope);
        }

        // Step 3: Calculate cosine similarity
        Log::info('Calculating cosine similarity');
        $similarVectors = $this->calculateSimilarities($normalizedVector, $vectorsInCentroids, 'cosine');

        // Step 4: Return top N similar vectors
        $topVectors = array_slice($similarVectors, 0, $topN);

        return response()->json(['similar_vectors' => $topVectors]);
    }

    /**
     * Search similar vectors using Hamming distance within centroids.
     */
    public function searchByHammingDistance(Request $request)
    {
        $validated = $request->validate([
            'vector'        => 'required|array',
            'top_centroids' => 'integer|min:1',
            'topN'          => 'integer|min:1',
            'chunkSize'     => 'integer|min:1',
        ]);

        $queryVector = $validated['vector'];
        $top_centroids = $validated['top_centroids'] ?? 5;
        $topN = $validated['topN'] ?? 10;
        $chunkSize = $validated['chunkSize'] ?? 100000;

        // Step 1: Convert query vector to binary
        $normalizedVector = Vector::normalize($queryVector);
        $binaryQueryVector = Vector::vectorToBinary($normalizedVector);

        // Step 2: Find the closest centroids
        $closestCentroids = $this->findClosestCentroids($normalizedVector, $top_centroids);

        // Step 3: Retrieve vectors from these centroids
        $vectorsInCentroids = $this->getVectorsFromCentroids($closestCentroids);

        // Step 4: Calculate Hamming distance
        $similarVectors = $this->calculateSimilarities($binaryQueryVector, $vectorsInCentroids, 'hamming', $chunkSize);

        // Step 5: Return top N similar vectors
        $topVectors = array_slice($similarVectors, 0, $topN);

        return response()->json(['similar_vectors' => $topVectors]);
    }

    /**
     * Hybrid search using both Hamming distance and cosine similarity within centroids.
     */
    public function searchHybrid(Request $request)
    {
        $validated = $request->validate([
            'vector'             => 'required|array',
            'top_centroids'      => 'integer|min:1',
            'topN'               => 'integer|min:1',
            'percentageToRefine' => 'numeric|min:0|max:1',
            'chunkSize'          => 'integer|min:1',
        ]);

        $queryVector = $validated['vector'];
        $top_centroids = $validated['top_centroids'] ?? 5;
        $topN = $validated['topN'] ?? 10;
        $percentageToRefine = $validated['percentageToRefine'] ?? 0.1;
        $chunkSize = $validated['chunkSize'] ?? 100000;

        // Step 1: Convert query vector to binary
        $normalizedVector = Vector::normalize($queryVector);
        $binaryQueryVector = Vector::vectorToBinary($normalizedVector);

        // Step 2: Find the closest centroids
        $closestCentroids = $this->findClosestCentroids($normalizedVector, $top_centroids);

        // Step 3: Retrieve vectors from these centroids
        $vectorsInCentroids = $this->getVectorsFromCentroids($closestCentroids);

        // Step 4: Calculate Hamming distance
        $hammingResults = $this->calculateSimilarities($binaryQueryVector, $vectorsInCentroids, 'hamming', $chunkSize);

        // Step 5: Refine top percentage using cosine similarity
        $numToRefine = max(1, floor(count($hammingResults) * $percentageToRefine));
        $vectorsToRefine = array_slice($hammingResults, 0, $numToRefine);

        // Extract vector IDs to fetch full vector data
        $vectorIdsToRefine = array_column($vectorsToRefine, 'id');
        $vectorsDataToRefine = $this->getVectorsByIds($vectorIdsToRefine);

        $refinedResults = $this->calculateSimilarities($normalizedVector, $vectorsDataToRefine, 'cosine');

        // Step 6: Merge and sort results
        $finalResults = array_merge($refinedResults, array_slice($hammingResults, $numToRefine));
        usort($finalResults, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        // Step 7: Return top N similar vectors
        $topVectors = array_slice($finalResults, 0, $topN);

        return response()->json(['similar_vectors' => $topVectors]);
    }

    /**
     * Helper method to retrieve vectors from given centroids.
     * The getVectorsFromCentroids function retrieves Vector models based 
     * on specified centroids and optional filtering criteria (scope) on 
     * associated grants data. This allows for fetching only relevant vectors 
     * depending on grant conditions, such as open opportunities or keyword 
     * matching in titles.
     */
    private function getVectorsFromCentroids($centroids, $scope = 'all')
    {
        $centroidIds = array_column($centroids, 'id');

        // Step 1: Initialize the query for vector IDs associated with the centroids
        $vectorQuery = DB::table('grant_vector')
            ->join('grants', 'grant_vector.grant_id', '=', 'grants.id')
            ->whereIn('grant_vector.centroid_id', $centroidIds);

        // Step 2: Apply scope filters
        if ($scope === 'open') {
            Log::info('Filtering by open opportunities');
            
            // Exclude grants with null close dates and filter for dates in the future
            $vectorQuery->whereNotNull('grants.close_date')
                        ->where('grants.close_date', '>', now());
        } elseif ($scope === 'keyword') {
            // Example: filter by keyword in the opportunity title
            $vectorQuery->where('grants.opportunity_title', 'LIKE', '%your_keyword%');
        }

        // Step 3: Retrieve the unique vector IDs after filtering
        $vectorIds = $vectorQuery->pluck('vector_id')->unique();

        // Step 4: Retrieve the Vector models
        $vectors = Vector::whereIn('id', $vectorIds)->get();

        return $vectors;
    }

    


    /**
     * Helper method to retrieve vectors by their IDs.
     */
    private function getVectorsByIds($vectorIds)
    {
        return Vector::whereIn('id', $vectorIds)->get();
    }

    /**
     * Calculate similarities between the query vector and the vectors in the centroid.
     */
    private function calculateSimilarities($queryVector, $vectorsInCentroid, $method = 'cosine', $chunkSize = 1000)
    {
        $similarVectors = [];

        foreach ($vectorsInCentroid->chunk($chunkSize) as $vectorChunk) {
            foreach ($vectorChunk as $vectorRecord) {
                if ($method === 'cosine') {
                    //Log::info('Calculating cosine similarity');
                    $vector = $vectorRecord->normalized_vector; // Convert JSON string to array
                    $similarity = Vector::cosineSimilarity($queryVector, $vector);
                } elseif ($method === 'hamming') {
                    $similarity = Vector::hammingDistance($queryVector, $vectorRecord->binary_code);
                    // Invert similarity for Hamming distance (lower distance means higher similarity)
                    $similarity = 256-$similarity;
                }

                $similarVectors[] = [
                    'id'         => $vectorRecord->id,
                    'similarity' => $similarity,
                    'vector'     => $vectorRecord->vector,
                ];
            }
        }

        // Sort by similarity (higher is more similar)
        usort($similarVectors, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return $similarVectors;
    }

    /**
     * Find the top N closest centroids to the given vector.
     */
    public function findClosestCentroids($vector, $top_centroids)
    {
        $centroids = Centroid::all();
        $centroidSimilarities = [];

        foreach ($centroids as $centroid) {
            $similarity = Vector::cosineSimilarity($vector, $centroid->vector);
            $centroidSimilarities[] = [
                'id'         => $centroid->id,
                'similarity' => $similarity
            ];
        }

        // Sort centroids by similarity (higher similarity is closer)
        usort($centroidSimilarities, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        // Return the top N closest centroids
        return array_slice($centroidSimilarities, 0, $top_centroids);
    }
}
