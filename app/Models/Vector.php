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

    // Hamming distance calculation between two binary strings
    public static function hammingDistance(string $binaryA, string $binaryB): int
    {
        $distance = 0;
        $length = min(strlen($binaryA), strlen($binaryB)); // Ensure we only compare the common length

        for ($i = 0; $i < $length; $i++) {
            $xor = ord($binaryA[$i]) ^ ord($binaryB[$i]); // XOR the bytes
            $distance += self::count_bits($xor); // Count the bits set to 1
        }

        // Account for any remaining characters in the longer string
        $distance += abs(strlen($binaryA) - strlen($binaryB)) * 8;

        return $distance;
    }

    // Helper function to count the number of bits set to 1 in a byte (popcount)
    private static function count_bits(int $byte): int
    {
        $count = 0;
        while ($byte) {
            $count += $byte & 1;
            $byte >>= 1;
        }
        return $count;
    }

}
