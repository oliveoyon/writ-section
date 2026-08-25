<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class RtftsCaseReference
{
    public const PREFIX = '13';
    public const SERIAL_LENGTH = 6;
    public const MAX_SERIAL = 999999;

    public function issue(int|string $year): array
    {
        $year = $this->normalizeYear($year);

        return DB::transaction(function () use ($year) {
            DB::table('case_registration_sequences')->insertOrIgnore([
                'year' => $year,
                'last_serial' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = DB::table('case_registration_sequences')
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            $serial = ((int) $sequence->last_serial) + 1;
            if ($serial > self::MAX_SERIAL) {
                throw new RuntimeException("RTFTS registration serial limit reached for {$year}.");
            }

            DB::table('case_registration_sequences')
                ->where('year', $year)
                ->update([
                    'last_serial' => $serial,
                    'updated_at' => now(),
                ]);

            return [
                'year' => $year,
                'serial' => $serial,
                'barcode' => self::barcode($year, $serial),
                'reference' => self::humanReference($year, $serial),
            ];
        });
    }

    public static function barcode(int|string $year, int $serial): string
    {
        $year = (string) $year;
        if (!preg_match('/^\d{4}$/', $year)) {
            throw new InvalidArgumentException('Registration year must contain exactly four digits.');
        }

        if ($serial < 1 || $serial > self::MAX_SERIAL) {
            throw new InvalidArgumentException('Registration serial must be between 1 and 999999.');
        }

        return self::PREFIX . $year . str_pad((string) $serial, self::SERIAL_LENGTH, '0', STR_PAD_LEFT);
    }

    public static function humanReference(int|string $year, int $serial): string
    {
        return 'WRPET ' . $serial . '/' . $year;
    }

    public static function humanReferenceFromBarcode(?string $barcode): ?string
    {
        if (!$barcode || !preg_match('/^13(\d{4})(\d{6})$/', $barcode, $matches)) {
            return null;
        }

        return self::humanReference($matches[1], (int) $matches[2]);
    }

    public static function parseIdentifier(?string $value): ?array
    {
        $value = trim(preg_replace('/\s+/', ' ', (string) $value) ?? '');
        if ($value === '') {
            return null;
        }

        if (preg_match('/^13(\d{4})(\d{6})$/', $value, $matches)) {
            $year = $matches[1];
            $serial = (int) $matches[2];

            if (!self::isValidLegacyYear($year) || $serial < 1) {
                return null;
            }

            return [
                'input' => $value,
                'year' => $year,
                'serial' => $serial,
                'barcode' => self::barcode($year, $serial),
                'reference' => self::humanReference($year, $serial),
            ];
        }

        if (!preg_match('/^(?:(?:WRPET|WRITPET)\s*)?0*(\d{1,6})\s*\/\s*(\d{4})$/i', $value, $matches)) {
            return null;
        }

        $serial = (int) $matches[1];
        $year = $matches[2];

        if (!self::isValidLegacyYear($year) || $serial < 1) {
            return null;
        }

        return [
            'input' => $value,
            'year' => $year,
            'serial' => $serial,
            'barcode' => self::barcode($year, $serial),
            'reference' => self::humanReference($year, $serial),
        ];
    }

    public static function barcodeFromSearch(?string $value): ?string
    {
        return self::parseIdentifier($value)['barcode'] ?? null;
    }

    private function normalizeYear(int|string $year): string
    {
        $year = (string) $year;
        if (!preg_match('/^\d{4}$/', $year)) {
            throw new InvalidArgumentException('Registration year must contain exactly four digits.');
        }

        return $year;
    }

    private static function isValidLegacyYear(string $year): bool
    {
        $numericYear = (int) $year;

        return $numericYear >= 1971 && $numericYear <= ((int) date('Y') + 1);
    }
}
