<?php

namespace App\Services;

use App\Models\CourtCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class CourtCaseSearch
{
    public function query(string $search): Builder
    {
        $search = trim(preg_replace('/\s+/', ' ', $search) ?? '');
        $parsed = RtftsCaseReference::parseIdentifier($search);

        if ($parsed) {
            return CourtCase::query()->where('permanent_barcode', $parsed['barcode']);
        }

        if (DB::getDriverName() === 'mysql') {
            $matches = $this->mysqlMatchingCaseIds($search);

            return CourtCase::query()
                ->joinSub($matches, 'search_matches', function ($join) {
                    $join->on('search_matches.case_id', '=', 'cases.id');
                })
                ->select('cases.*')
                ->distinct();
        }

        return $this->fallbackQuery($search);
    }

    private function mysqlMatchingCaseIds(string $search): QueryBuilder
    {
        $prefix = $this->escapeLike($search).'%';
        $booleanSearch = $this->booleanSearch($search);

        $barcodeMatches = DB::table('cases')
            ->selectRaw('id as case_id')
            ->where('permanent_barcode', 'like', $prefix)
            ->limit(100);
        $referenceMatches = DB::table('cases')
            ->selectRaw('id as case_id')
            ->where('final_case_number', 'like', $prefix)
            ->limit(100);

        $matches = $barcodeMatches->unionAll($referenceMatches);

        $looksLikeReference = preg_match(
            '/^(?:13\d{0,10}|(?:(?:WRPET|WRITPET)\s*)?\d{1,6}(?:\s*\/\s*\d{0,4})?)$/i',
            $search
        ) === 1;

        if ($booleanSearch === '' || $looksLikeReference) {
            return $matches;
        }

        $caseText = DB::table('cases')
            ->selectRaw('id as case_id')
            ->whereRaw(
                'MATCH(case_type, description) AGAINST (? IN BOOLEAN MODE)',
                [$booleanSearch]
            )
            ->limit(100);
        $petitionerText = DB::table('case_petitioners')
            ->select('case_id')
            ->whereRaw(
                'MATCH(name_or_organization, represented_by, designation, address) AGAINST (? IN BOOLEAN MODE)',
                [$booleanSearch]
            )
            ->limit(100);
        $respondentText = DB::table('case_respondents')
            ->select('case_id')
            ->whereRaw(
                'MATCH(name_or_organization, represented_by, designation, address) AGAINST (? IN BOOLEAN MODE)',
                [$booleanSearch]
            )
            ->limit(100);
        $lawyerText = DB::table('cases')
            ->join('lawyers', 'lawyers.id', '=', 'cases.lawyer_id')
            ->selectRaw('cases.id as case_id')
            ->whereRaw(
                'MATCH(lawyers.full_name, lawyers.bar_council_id, lawyers.phone) AGAINST (? IN BOOLEAN MODE)',
                [$booleanSearch]
            )
            ->limit(100);

        return $caseText
            ->unionAll($petitionerText)
            ->unionAll($respondentText)
            ->unionAll($lawyerText);
    }

    private function fallbackQuery(string $search): Builder
    {
        $like = '%'.$this->escapeLike($search).'%';

        return CourtCase::query()
            ->where(function ($query) use ($search, $like) {
                $query->where('permanent_barcode', $search)
                    ->orWhere('final_case_number', $search)
                    ->orWhere('permanent_barcode', 'like', $like)
                    ->orWhere('final_case_number', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('case_type', 'like', $like)
                    ->orWhereHas('petitioners', function ($party) use ($like) {
                        $party->where('name_or_organization', 'like', $like)
                            ->orWhere('represented_by', 'like', $like)
                            ->orWhere('designation', 'like', $like)
                            ->orWhere('address', 'like', $like);
                    })
                    ->orWhereHas('respondents', function ($party) use ($like) {
                        $party->where('name_or_organization', 'like', $like)
                            ->orWhere('represented_by', 'like', $like)
                            ->orWhere('designation', 'like', $like)
                            ->orWhere('address', 'like', $like);
                    })
                    ->orWhereHas('lawyer', function ($lawyer) use ($like) {
                        $lawyer->where('full_name', 'like', $like)
                            ->orWhere('bar_council_id', 'like', $like)
                            ->orWhere('phone', 'like', $like);
                    });

                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search);
                }
            });
    }

    private function booleanSearch(string $search): string
    {
        $tokens = preg_split('/[^\pL\pN]+/u', $search) ?: [];

        return collect($tokens)
            ->map(fn ($token) => trim((string) $token))
            ->filter(fn ($token) => mb_strlen($token) >= 3)
            ->take(8)
            ->map(fn ($token) => '+'.$token.'*')
            ->implode(' ');
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
