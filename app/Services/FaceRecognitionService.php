<?php

namespace App\Services;

class FaceRecognitionService
{
    /**
     * @param array<int, mixed> $descriptorA
     * @param array<int, mixed> $descriptorB
     */
    public function distance(array $descriptorA, array $descriptorB): ?float
    {
        $a = $this->normalize($descriptorA);
        $b = $this->normalize($descriptorB);

        if ($a === null || $b === null) {
            return null;
        }

        $sum = 0.0;

        for ($i = 0; $i < 128; $i++) {
            $delta = $a[$i] - $b[$i];
            $sum += $delta * $delta;
        }

        return sqrt($sum);
    }

    /**
     * @param array<int, mixed> $descriptor
     * @return array<int, float>|null
     */
    public function normalize(array $descriptor): ?array
    {
        if (count($descriptor) !== 128) {
            return null;
        }

        $normalized = [];
        foreach ($descriptor as $value) {
            if (!is_numeric($value)) {
                return null;
            }
            $normalized[] = (float) $value;
        }

        return $normalized;
    }
}

