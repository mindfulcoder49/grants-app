<?php
// app/Http/Controllers/VectorController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Vector;
use Illuminate\Support\Facades\DB;
use App\Models\Grant;
use GuzzleHttp\Client;



class VectorController extends Controller
{
    protected $dimension;
    const OPENAI_API_URL = 'https://api.openai.com/v1/embeddings';
    private $apiKey;

    public function __construct()
    {
        $this->dimension = 256; // Dimension for vectors
        // Set your OpenAI API key (assumed to be stored in an environment variable)
        $this->apiKey = env('OPENAI_API_KEY');

        if (!$this->apiKey) {
            throw new \Exception("OpenAI API key is missing. Please set it in the environment.");
        }
    }

    // Insert a vector
    public function insertVector(Request $request)
    {
        Log::debug('Inserting a new vector.');
        $vector = $request->input('vector', []); // Expecting a vector with a defined dimension
        $vectorId = Vector::upsert($vector);

        Log::debug('Vector inserted successfully.', ['vector_id' => $vectorId]);
        return response()->json(['vector_id' => $vectorId]);
    }

    // Update an existing vector
    public function updateVector(Request $request, $id)
    {
        $vector = $request->input('vector', []);
        Vector::upsert($vector, $id);

        return response()->json(['message' => 'Vector updated successfully.']);
    }

    // Delete a vector
    public function deleteVector($id)
    {
        Vector::where('id', $id)->delete();

        return response()->json(['message' => 'Vector deleted successfully.']);
    }

    // Calculate cosine similarity between two vectors
    public function calculateCosineSimilarity(Request $request)
    {
        $vector1 = $request->input('vector1', []);
        $vector2 = $request->input('vector2', []);

        $similarity = Vector::cosineSimilarity($vector1, $vector2);

        return response()->json(['cosine_similarity' => $similarity]);
    }

    // Search for similar vectors
    public function searchSimilarVectors(Request $request)
    {
        Log::info('searchSimilarVectors: Searching for similar vectors.');
        $vector = $request->input('vector', []);
        $topN = $request->input('topN', 5);
        $useHamming = $request->input('useHamming', 'hybrid');
        $percentageToRefine = $request->input('percentageToRefine', 1);
        $chunkSize = $request->input('chunkSize', 1000);
        

        Log::info('searchSimilarVectors: Vector received.');
        Log::info('searchSimilarVectors: Top N value.', ['topN' => $topN]);

        switch ($useHamming) {
            case 'cosine':
                $similarVectors = $this->searchByCosineSimilarity($vector, $topN);
                break;
            case 'hamming':
                $similarVectors = $this->searchByHammingDistance($vector, $topN, $chunkSize);
                break;
            case 'hybrid':
                $similarVectors = $this->searchHybrid($vector, $topN, $percentageToRefine, $chunkSize);
                break;
            default:
                $similarVectors = $this->searchHybrid($vector, $topN, $percentageToRefine, $chunkSize);
        }

        Log::info('searchSimilarVectors: Found similar vectors.', ['count' => count($similarVectors)]);
        return response()->json(['similar_vectors' => $similarVectors]);
    }

    public function searchSimilarVectorsWithGrants(Request $request)
    {
        // Log the function name and initial inputs
        Log::info('searchSimilarVectorsWithGrants: Searching for similar vectors with grants.');
        $vector = $request->input('vector', []);
        $topN = $request->input('topN', 5);

        Log::info('searchSimilarVectorsWithGrants: Vector received.');
        Log::info('searchSimilarVectorsWithGrants: Top N value.', ['topN' => $topN]);

        // Step 1: Search for similar vectors using the search method
        $similarVectors = $this->search($vector, $topN);
        Log::info('searchSimilarVectorsWithGrants: Found similar vectors.', ['count' => count($similarVectors)]);

        // Step 2: Extract vector IDs from the similar vectors
        $vectorIds = array_column($similarVectors, 'id');
        Log::info('searchSimilarVectorsWithGrants: Extracted vector IDs.', ['count' => count($vectorIds)]);

        // Step 3: Fetch the vector models
        $vectors = Vector::whereIn('id', $vectorIds)->get()->keyBy('id');

        // Step 4: Fetch related grant IDs from the 'grant_vector' pivot table
        $pivotRecords = DB::table('grant_vector')
            ->whereIn('vector_id', $vectorIds)
            ->get(['grant_id', 'vector_id']);
        Log::info('searchSimilarVectorsWithGrants: Fetched pivot records.', ['count' => $pivotRecords->count()]);

        // Step 5: Use grant IDs to fetch grant data from the 'grants' table
        $grantIds = $pivotRecords->pluck('grant_id')->unique()->toArray();
        $grants = Grant::whereIn('id', $grantIds)->get()->keyBy('id');
        Log::info('searchSimilarVectorsWithGrants: Fetched grants.', ['count' => $grants->count()]);

        // Step 6: Map the similar vectors to their corresponding grants
        $formattedVectors = collect($similarVectors)->map(function ($vectorData) use ($vectors, $pivotRecords, $grants) {
            $vectorId = $vectorData['id'];
            $vectorModel = $vectors->get($vectorId);

            // Find the matching pivot record for this vector
            $pivot = $pivotRecords->firstWhere('vector_id', $vectorId);

            if ($pivot) {
                // Find the corresponding grant
                $grant = $grants->get($pivot->grant_id);

                if ($grant) {
                    $textToEmbed = "Title: " . $grant->opportunity_title . ' Description: ' . $grant->description;
                    $textToEmbed .= ' Category: ' . $grant->opportunity_category . ' Funding Instrument: ' . $grant->funding_instrument_type;
                    $textToEmbed .= ' Eligible Applicants: ' . $grant->eligible_applicants . ' Agency: ' . $grant->agency_name;

                    return [
                        'id'         => $vectorId,
                        'similarity' => $vectorData['similarity'],
                        'text'       => $textToEmbed,
                        'vector'     => $vectorModel->vector, // Assuming 'vector' is a field on your Vector model
                    ];
                }
            }

            return null; // Return null if no grant found
        })->filter()->values(); // Filter out nulls and reset keys

        Log::info('searchSimilarVectorsWithGrants: Formatted similar vectors for response.', ['vector_count' => $formattedVectors->count()]);

        // Return the formatted similar vectors along with their grant data
        return response()->json(['similar_vectors' => $formattedVectors]);
    }


    /**
     * Search using only cosine similarity.
     */
    public static function searchByCosineSimilarity(array $vector, int $topN = 10): array
    {
        $normalizedVector = Vector::normalize($vector);

        // Initialize an empty array to store the top N results
        $topResults = [];

        // Process vectors in chunks to avoid loading all into memory
        Vector::chunk(1000, function ($vectors) use ($normalizedVector, &$topResults, $topN) {
            foreach ($vectors as $vectorData) {
                $storedNormalizedVector = $vectorData->normalized_vector; // Assuming this is stored in your database
                $similarity = Vector::cosineSimilarity($normalizedVector, $storedNormalizedVector);

                // If we have fewer than topN results, add the current result
                if (count($topResults) < $topN) {
                    $topResults[] = [
                        'id'         => $vectorData->id,
                        'similarity' => $similarity,
                    ];
                    // If we have exactly topN results, sort them ascending by similarity
                    if (count($topResults) == $topN) {
                        usort($topResults, fn($a, $b) => $a['similarity'] <=> $b['similarity']);
                    }
                } else {
                    // Check if the current similarity is greater than the smallest in topResults
                    if ($similarity > $topResults[0]['similarity']) {
                        // Replace the smallest similarity with the current one
                        $topResults[0] = [
                            'id'         => $vectorData->id,
                            'similarity' => $similarity,
                        ];
                        // Re-sort the topResults array
                        usort($topResults, fn($a, $b) => $a['similarity'] <=> $b['similarity']);
                    }
                }
            }
        });

        // After processing all chunks, sort the topResults in descending order
        usort($topResults, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        // Return the top N results
        return $topResults;
    }


    /**
     * Search using only Hamming distance.
     */
    public static function searchByHammingDistance(array $vector, int $topN = 10, int $chunkSize = 1000): array
    {
        $normalizedVector = Vector::normalize($vector);
        $binaryVector = Vector::vectorToBinary($normalizedVector);

        // Perform the Hamming distance-based search
        $hammingResults = self::hammingSearch($binaryVector, $topN, $chunkSize);

        // Convert Hamming distance to similarity (lower distance means higher similarity)
        $results = array_map(function ($result) {
            $vectorData = Vector::find($result['id']);
            $maxDistance = 8 * strlen($vectorData->binary_code); // Maximum possible Hamming distance
            $similarity = $maxDistance - $result['hamming_distance'];

            return [
                'id'         => $vectorData->id,
                'similarity' => $similarity,
                'vector'     => $vectorData->vector,
            ];
        }, $hammingResults);

        // Sort the results by similarity in descending order
        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        // Return the top N results
        return array_slice($results, 0, $topN);
    }

    /**
     * Hybrid search using Hamming distance to narrow down candidates and refining with cosine similarity.
     */
    public static function searchHybrid(array $vector, int $topN = 10, float $percentageToRefine = 1.0, int $chunkSize = 1000): array
    {
        // Normalize input vector and convert it to binary
        $normalizedVector = Vector::normalize($vector);
        $binaryVector = Vector::vectorToBinary($normalizedVector);

        // Step 1: Perform the Hamming distance-based search
        $hammingResults = self::hammingSearch($binaryVector, $topN, $chunkSize);

        // Step 2: Determine the number of closest matches to perform cosine similarity on
        $numCosineChecks = max(1, floor($topN * $percentageToRefine));

        $cosineResults = [];

        // Step 3: Perform cosine similarity on the top N percentage
        foreach (array_slice($hammingResults, 0, $numCosineChecks) as $result) {
            $vectorData = Vector::find($result['id']); // Fetch the full vector data
            $storedNormalizedVector = $vectorData->normalized_vector;
            $similarity = Vector::cosineSimilarity($normalizedVector, $storedNormalizedVector);

            $cosineResults[] = [
                'id'         => $vectorData->id,
                'similarity' => $similarity,
                'vector'     => $vectorData->vector,
            ];
        }

        // Sort the refined cosine results by similarity
        usort($cosineResults, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        // Return the refined top N cosine similarity results
        return array_slice($cosineResults, 0, $topN);
    }

    // Chunked binary search using Hamming distance
    public static function hammingSearch(string $binaryVector, int $topN = 10, int $chunkSize = 1000): array
    {
        $page = 0;
        $results = [];

        do {
            $vectorsBatch = Vector::query()
                ->select(['id', 'binary_code'])
                ->offset($page * $chunkSize)
                ->limit($chunkSize)
                ->get();

            if ($vectorsBatch->isEmpty()) {
                break;
            }

            foreach ($vectorsBatch as $vectorData) {
                $hammingDist = Vector::hammingDistance($binaryVector, $vectorData->binary_code);
                $results[] = [
                    'id' => $vectorData->id,
                    'hamming_distance' => $hammingDist
                ];
            }

            $page++;

        } while (count($vectorsBatch) === $chunkSize);

        // Sort by Hamming distance
        usort($results, fn($a, $b) => $a['hamming_distance'] <=> $b['hamming_distance']);

        return array_slice($results, 0, $topN);
    }




    // Embed text and get vector representation
    public function embedText(Request $request)
    {
        $texts = $request->input('texts', []);
        Log::info('Embedding text.', ['text_count' => count($texts)]);
        $embeddings = $this->embed($texts[0]);

        return response()->json(['embeddings' => $embeddings]);
    }


    public function listVectors(Request $request)
    {
        // Step 1: Fetch the first 100 vectors from the 'vectors' table
        Log::info('Fetching the first 100 vectors.');
        $vectors = Vector::limit(100)->get();

        // Step 2: Extract vector IDs and fetch related grant IDs from the 'grant_vector' pivot table
        $vectorIds = $vectors->pluck('id')->toArray();

        $pivotRecords = DB::table('grant_vector')
            ->whereIn('vector_id', $vectorIds)
            ->get(['grant_id', 'vector_id']);

        // Step 3: Use grant IDs to fetch grant data from the 'grants' table
        $grantIds = $pivotRecords->pluck('grant_id')->unique()->toArray();
        $grants = Grant::whereIn('id', $grantIds)->get();

        // Step 4: Map the vectors to their corresponding grants
        $formattedVectors = $vectors->map(function ($vector) use ($pivotRecords, $grants) {
            // Find the matching pivot record for this vector
            $pivot = $pivotRecords->firstWhere('vector_id', $vector->id);

            if ($pivot) {
                // Find the corresponding grant
                $grant = $grants->firstWhere('id', $pivot->grant_id);

                if ($grant) {
                    $textToEmbed = "Title: " . $grant->opportunity_title . ' Description: ' . $grant->description;
                    $textToEmbed .= ' Category: ' . $grant->opportunity_category . ' Funding Instrument: ' . $grant->funding_instrument_type;
                    $textToEmbed .= ' Eligible Applicants: ' . $grant->eligible_applicants . ' Agency: ' . $grant->agency_name;

                    return [
                        'id'     => $vector->id,
                        'text'   => $textToEmbed,
                        'vector' => $vector->vector, // Assuming 'vector' is a field on your Vector model
                    ];
                }
            }

            return null; // Return null if no grant found
        })->filter()->values(); // Filter out nulls and reset keys

        Log::info('Formatted vectors for response.', ['vector_count' => $formattedVectors->count()]);

        // Return the formatted vectors
        return response()->json(['vectors' => $formattedVectors]);
    }




        /**
     * Calculates the embedding of a text using OpenAI Embeddings API.
     * @param array $text Batch of text to embed
     * @return array Batch of embeddings
     * @throws \Exception
     */
    public function embed(string $text): array
    {
        $client = new Client();



        // Prepare the request body
        $body = [
            'model' => 'text-embedding-3-small', // OpenAI embedding model
            'input' => $text,
            'dimensions' => $this->dimension,
        ];

        // Make the API request
        $response = $client->post(self::OPENAI_API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $body
        ]);

        // Parse the response
        $responseBody = json_decode($response->getBody(), true);

        if (isset($responseBody['error'])) {
            Log::error('OpenAI API error: ' . $responseBody['error']['message']);
            throw new \Exception('OpenAI API error: ' . $responseBody['error']['message']);
        }

        // Extract embeddings
        $embeddings = array_map(function($result) {
            return $result['embedding'];
        }, $responseBody['data']);

        return $embeddings;
    }

    
    
}

