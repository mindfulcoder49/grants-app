<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MHz\MysqlVector\VectorTable;
use MHz\MysqlVector\Nlp\Embedder;
use Illuminate\Support\Facades\Log;
use App\Models\Grant;
use Illuminate\Support\Facades\DB;

class VectorController extends Controller
{
    protected $vectorTable;
    protected $embedder;

    public function __construct()
    {
        // Initialize MySQLi connection
        Log::info('Initializing MySQLi connection.');
        $mysqli = new \mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'));
        $tableName = 'my_vector_table';
        $dimension = 256;
        $engine = 'InnoDB';

        // Initialize VectorTable
        Log::info('Initializing VectorTable.');
        $this->vectorTable = new VectorTable($mysqli, $tableName, $dimension, $engine);

        // Initialize Embedder for text embedding
        Log::info('Initializing Embedder.');
        $this->embedder = new Embedder();
    }

    // Insert a vector
    public function insertVector(Request $request)
    {
        Log::debug('Inserting a new vector.');
        $vector = $request->input('vector', []); // Expecting a 384-dimensional vector
        $vectorId = $this->vectorTable->upsert($vector);

        Log::debug('Vector inserted successfully.', ['vector_id' => $vectorId]);
        return response()->json(['vector_id' => $vectorId]);
    }

    // Update an existing vector
    public function updateVector(Request $request, $id)
    {
        $vector = $request->input('vector', []);
        $this->vectorTable->upsert($vector, $id);

        return response()->json(['message' => 'Vector updated successfully.']);
    }

    // Delete a vector
    public function deleteVector($id)
    {
        $this->vectorTable->delete($id);

        return response()->json(['message' => 'Vector deleted successfully.']);
    }

    // Calculate cosine similarity between two vectors
    public function calculateCosineSimilarity(Request $request)
    {
        $vector1 = $request->input('vector1', []);
        $vector2 = $request->input('vector2', []);

        $similarity = $this->vectorTable->cosim($vector1, $vector2);

        return response()->json(['cosine_similarity' => $similarity]);
    }

    // Search for similar vectors
    public function searchSimilarVectors(Request $request)
    {
        //put function name in logging 
        Log::info('searchSimilarVectors: Searching for similar vectors.');
        $vector = $request->input('vector', []);
        $topN = $request->input('topN', 5);

        Log::info('searchSimilarVectors: Vector received.');
        Log::info('searchSimilarVectors: Top N value.', ['topN' => $topN]);

        $similarVectors = $this->vectorTable->search($vector, 2000);

        Log::info('searchSimilarVectors: Found similar vectors.', ['count' => count($similarVectors)]);
        return response()->json(['similar_vectors' => $similarVectors]);
    }

    public function searchSimilarVectorsWithGrants(Request $request)
    {
        // Log the function name and initial inputs
        Log::info('searchSimilarVectors: Searching for similar vectors.');
        $vector = $request->input('vector', []);
        $topN = $request->input('topN', 5);

        Log::info('searchSimilarVectors: Vector received.');
        Log::info('searchSimilarVectors: Top N value.', ['topN' => $topN]);

        // Step 1: Search for similar vectors using your vector table
        $similarVectors = $this->vectorTable->search($vector, 2000); // Assuming 2000 dimensions or distance measure
        Log::info('searchSimilarVectors: Found similar vectors.', ['count' => count($similarVectors)]);

        // Step 1.5: Limit the number of similar vectors to the top N
        $similarVectors = array_slice($similarVectors, 0, $topN);
        Log::info('searchSimilarVectors: Limited similar vectors to top N.', ['topN' => $topN]);

        // Step 2: Extract vector IDs from the similar vectors
        $vectorIds = array_column($similarVectors, 'id'); // Assuming 'id' is part of the vector data
        Log::info('searchSimilarVectors: Extracted vector IDs.', ['count' => count($vectorIds)]);

        // Step 3: Fetch related grant IDs from the 'grant_vector' pivot table
        $pivotRecords = DB::table('grant_vector')
            ->whereIn('vector_id', $vectorIds)
            ->get(['grant_id', 'vector_id']);
        Log::info('searchSimilarVectors: Fetched pivot records.', ['count' => $pivotRecords->count()]);

        // Step 4: Use grant IDs to fetch grant data from the 'grants' table
        $grantIds = $pivotRecords->pluck('grant_id')->unique();
        $grants = Grant::whereIn('id', $grantIds)->get();
        Log::info('searchSimilarVectors: Fetched grants.', ['count' => $grants->count()]);

        // Step 5: Map the similar vectors to their corresponding grants
        $formattedVectors = collect($similarVectors)->map(function ($vector) use ($pivotRecords, $grants) {
            // Find the matching pivot record and grant for this vector
            $pivot = $pivotRecords->firstWhere('vector_id', $vector['id']);
            $grant = $grants->firstWhere('id', $pivot->grant_id);

            if ($grant) {
                $textToEmbed = "Title: " . $grant->opportunity_title . ' Description: ' . $grant->description;
                $textToEmbed .= ' Category: ' . $grant->opportunity_category . ' Funding Instrument: ' . $grant->funding_instrument_type;
                $textToEmbed .= ' Eligible Applicants: ' . $grant->eligible_applicants . ' Agency: ' . $grant->agency_name;

                return [
                    'id' => $vector['id'],
                    'text' => $textToEmbed,
                    'vector' => $vector['vector'], // Assuming vector has a 'vector' field
                ];
            }

            return null; // Return null if no grant found
        })->filter(); // Filter out nulls if any vectors don’t have matching grants

        Log::info('searchSimilarVectors: Formatted similar vectors for response.', ['vector_count' => $formattedVectors->count()]);

        // Return the formatted similar vectors along with their grant data
        return response()->json(['similar_vectors' => $formattedVectors]);
    }


    // Embed text and get vector representation
    public function embedText(Request $request)
    {
        $texts = $request->input('texts', []);

        $embeddings = $this->embedder->embed($texts);

        return response()->json(['embeddings' => $embeddings]);
    }


    public function listVectors(Request $request)
    {
        // Step 1: Fetch vectors from the vector table
        Log::info('Fetching the first 100 vectors using vectorTable->selectAll().');
        $vectors = $this->vectorTable->selectAll(); // Assuming this returns a collection or array of vectors
        $vectors = array_slice($vectors, 0, 100); // Limit to the first 100 vectors

        // Step 2: Extract vector IDs and fetch related grant IDs from the grant_vector table
        $vectorIds = array_column($vectors, 'id'); // Assuming vectors have an 'id' field
        $pivotRecords = DB::table('grant_vector')
            ->whereIn('vector_id', $vectorIds)
            ->get(['grant_id', 'vector_id']);

        // Step 3: Use grant IDs to fetch grant data from the grants table
        $grantIds = $pivotRecords->pluck('grant_id')->unique();
        $grants = Grant::whereIn('id', $grantIds)->get();

        // Step 4: Map the vectors to their corresponding grants
        $formattedVectors = collect($vectors)->map(function ($vector) use ($pivotRecords, $grants) {
            // Find the matching pivot record and grant for this vector
            $pivot = $pivotRecords->firstWhere('vector_id', $vector['id']);
            $grant = $grants->firstWhere('id', $pivot->grant_id);

            if ($grant) {
                $textToEmbed = "Title: " . $grant->opportunity_title . ' Description: ' . $grant->description;
                $textToEmbed .= ' Category: ' . $grant->opportunity_category . ' Funding Instrument: ' . $grant->funding_instrument_type;
                $textToEmbed .= ' Eligible Applicants: ' . $grant->eligible_applicants . ' Agency: ' . $grant->agency_name;

                return [
                    'id' => $vector['id'],
                    'text' => $textToEmbed,
                    'vector' => $vector['vector'], // Assuming vector has a 'vector' field
                ];
            }

            return null; // Return null if no grant found
        })->filter(); // Filter out nulls if any vectors don’t have matching grants

        Log::info('Formatted vectors for response.', ['vector_count' => $formattedVectors->count()]);

        // Return the formatted vectors
        return response()->json(['vectors' => $formattedVectors]);
    }





    
    
}

