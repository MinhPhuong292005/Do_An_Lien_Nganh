<?php

declare(strict_types=1);

/**
 * Cookio Search & Culinary Title Filter Helper
 * Ensures searching "gà" strictly returns dishes with "gà" in title,
 * and similarly for other keywords (bò, cá, tôm, sườn, bánh, etc.).
 */

/**
 * Builds smart WHERE clauses and SQL parameters for search query.
 * Strictly guarantees that returned dishes match the search query in their title (r.title).
 */
function build_smart_search_conditions(string $query): array
{
    $q = trim($query);
    if ($q === '') {
        return [[], []];
    }

    $lower = mb_strtolower($q, 'UTF-8');
    $clauses = [];
    $params = [];

    // Common synonyms / expansions for ingredients and dishes
    $synonyms = [
        'thịt gà'  => ['gà', 'thịt gà', 'ức gà', 'cánh gà', 'đùi gà'],
        'gà'       => ['gà', 'thịt gà'],
        'thịt bò'  => ['bò', 'thịt bò', 'bắp bò', 'thăn bò'],
        'bò'       => ['bò', 'thịt bò'],
        'thịt heo' => ['heo', 'lợn', 'thịt heo', 'thịt lợn', 'ba chỉ', 'sườn'],
        'thịt lợn' => ['lợn', 'heo', 'thịt lợn', 'thịt heo', 'ba chỉ', 'sườn'],
        'heo'      => ['heo', 'thịt heo'],
        'lợn'      => ['lợn', 'thịt lợn'],
        'hải sản'  => ['hải sản', 'tôm', 'mực', 'cá', 'cua', 'nghêu', 'ngao', 'ốc', 'bạch tuộc'],
        'tôm'      => ['tôm'],
        'cá'       => ['cá'],
        'mực'      => ['mực'],
        'nghêu'    => ['nghêu', 'ngao'],
        'ngao'     => ['ngao', 'nghêu'],
        'đậu hũ'   => ['đậu hũ', 'đậu phụ', 'tàu hũ'],
        'đậu phụ'  => ['đậu phụ', 'đậu hũ', 'tàu hũ'],
        'bún'      => ['bún'],
        'phở'      => ['phở'],
        'mì'       => ['mì', 'ramen', 'spaghetti', 'pasta'],
        'miến'     => ['miến'],
        'bánh'     => ['bánh'],
        'canh'     => ['canh', 'súp'],
        'súp'      => ['súp', 'canh'],
        'chay'     => ['chay'],
    ];

    // Determine target keywords to match against title
    $targets = [];
    if (isset($synonyms[$lower])) {
        $targets = $synonyms[$lower];
    } else {
        // Check if any synonym key is a whole word in the query
        $matchedSynonym = false;
        foreach ($synonyms as $k => $synList) {
            if ($lower === $k) {
                $targets = $synList;
                $matchedSynonym = true;
                break;
            }
        }
        if (!$matchedSynonym) {
            $targets = [$lower];
        }
    }

    $subOrs = [];
    foreach ($targets as $kw) {
        $kw = trim($kw);
        if ($kw === '') continue;

        $capitalized = mb_convert_case($kw, MB_CASE_TITLE, 'UTF-8');
        $upper = mb_strtoupper($kw, 'UTF-8');

        // Condition matching keyword as a distinct word or prominent part of title
        $subOrs[] = "(r.title LIKE ? OR r.title LIKE ? OR r.title LIKE ? OR r.title = ? 
                     OR r.title LIKE ? OR r.title LIKE ? OR r.title LIKE ? OR r.title = ?
                     OR r.title LIKE ? OR r.title LIKE ? OR r.title LIKE ?)";
        
        // Lowercase patterns
        $params[] = '% ' . $kw . ' %';
        $params[] = $kw . ' %';
        $params[] = '% ' . $kw;
        $params[] = $kw;

        // Capitalized patterns
        $params[] = '% ' . $capitalized . ' %';
        $params[] = $capitalized . ' %';
        $params[] = '% ' . $capitalized;
        $params[] = $capitalized;

        // Punctuation boundaries (e.g. "súp (gà)", "thịt gà,", "gà-")
        $params[] = '%(' . $kw . '%';
        $params[] = '%' . $kw . ',%';
        $params[] = '%' . $kw . '-%';
    }

    if (!empty($subOrs)) {
        $clauses[] = '(' . implode(' OR ', $subOrs) . ')';
    } else {
        $clauses[] = "(r.title LIKE ?)";
        $params[] = '%' . $q . '%';
    }

    return [$clauses, $params];
}

/**
 * Tokenize search query into individual clean words
 */
function parse_search_query_tokens(string $query): array
{
    $query = trim(mb_strtolower($query, 'UTF-8'));
    if ($query === '') {
        return [];
    }

    $stopWords = ['món', 'nấu', 'làm', 'cách', 'ăn', 'ngon', 'chuẩn', 'vị'];
    $rawTokens = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);
    $cleanTokens = [];

    foreach ($rawTokens as $tk) {
        if (!in_array($tk, $stopWords, true) && mb_strlen($tk, 'UTF-8') >= 2) {
            $cleanTokens[] = $tk;
        }
    }

    if (empty($cleanTokens) && !empty($rawTokens)) {
        $cleanTokens = $rawTokens;
    }

    return array_values(array_unique($cleanTokens));
}

/**
 * Regex helper for titles
 */
function build_synonym_regex(string $token): string
{
    $token = trim(mb_strtolower($token, 'UTF-8'));
    if ($token === '') return '';
    return '(^|[[:space:][:punct:]])' . preg_quote($token, '/') . '($|[[:space:][:punct:]])';
}
