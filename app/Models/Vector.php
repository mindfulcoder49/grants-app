<?php
// app/Models/Vector.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vector extends Model
{
    protected $fillable = ['vector', 'normalized_vector', 'magnitude', 'binary_code'];

    protected $casts = [
        'vector' => 'array',
        'normalized_vector' => 'array',
        'magnitude' => 'float',
    ];

    // Insert or update a vector
    public static function upsert(array $vector, ?int $id = null): int
    {
        $magnitude = self::getMagnitude($vector);
        $normalizedVector = self::normalize($vector, $magnitude);
        $binaryCode = self::vectorToBinary($normalizedVector);

        if ($id) {
            // Update existing vector
            self::where('id', $id)->update([
                'vector' => $vector,
                'normalized_vector' => $normalizedVector,
                'magnitude' => $magnitude,
                'binary_code' => $binaryCode,
            ]);
            return $id;
        } else {
            // Insert new vector
            $vectorModel = self::create([
                'vector' => $vector,
                'normalized_vector' => $normalizedVector,
                'magnitude' => $magnitude,
                'binary_code' => $binaryCode,
            ]);

            return $vectorModel->id;
        }
    }

    // Cosine similarity between two vectors
    public static function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $magnitudeA += $vectorA[$i] * $vectorA[$i];
            $magnitudeB += $vectorB[$i] * $vectorB[$i];
        }

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0;
        }

        return $dotProduct / (sqrt($magnitudeA) * sqrt($magnitudeB));
    }

    // Search for similar vectors
    public static function search(array $vector, int $topN = 10): array
    {
        $normalizedVector = self::normalize($vector);
        $allVectors = self::all();

        $results = $allVectors->map(function ($vectorData) use ($normalizedVector) {
            $storedNormalizedVector = $vectorData->normalized_vector;
            $similarity = self::cosineSimilarity($normalizedVector, $storedNormalizedVector);

            return [
                'id' => $vectorData->id,
                'similarity' => $similarity,
                'vector' => $vectorData->vector,
            ];
        })->toArray();

        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return array_slice($results, 0, $topN);
    }

    // Magnitude calculation
    public static function getMagnitude(array $vector): float
    {
        $sum = 0;
        foreach ($vector as $value) {
            $sum += $value * $value;
        }
        return sqrt($sum);
    }

    // Normalize vector
    public static function normalize(array $vector, float $magnitude = null, float $epsilon = 1e-10): array
    {
        $magnitude = $magnitude ?? self::getMagnitude($vector);
        if ($magnitude == 0) {
            $magnitude = $epsilon;
        }

        foreach ($vector as $key => $value) {
            $vector[$key] = $value / $magnitude;
        }

        return $vector;
    }

    // Convert vector to binary representation
    public static function vectorToBinary(array $vector): string
    {
        $binary = '';
        $bit = 0;
        $char = 0;

        foreach ($vector as $value) {
            if ($value > 0) {
                $char |= 1 << $bit;
            }
            $bit++;
            if ($bit === 8) {
                $binary .= chr($char);
                $bit = 0;
                $char = 0;
            }
        }

        if ($bit > 0) {
            $binary .= chr($char);
        }

        return $binary;
    }
}
