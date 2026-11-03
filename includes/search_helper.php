<?php

declare(strict_types=1);

/**
 * Cookio Search & Culinary Synonym Expansion Helper
 * Upgraded to support comprehensive Vietnamese culinary search,
 * tokenization, synonym expansion, and smart ranking.
 */

/**
 * Common culinary synonym dictionary
 */
function get_culinary_synonym_map(): array
{
    return [
        // Gà
        'thịt gà'      => ['gà', 'thịt gà', 'ức gà', 'cánh gà', 'đùi gà', 'gà ta'],
        'gà'           => ['gà', 'thịt gà', 'ức gà', 'cánh gà', 'đùi gà'],
        'ức gà'        => ['ức gà', 'thịt gà', 'gà'],
        'cánh gà'      => ['cánh gà', 'gà'],
        'đùi gà'       => ['đùi gà', 'gà'],

        // Bò
        'thịt bò'      => ['bò', 'thịt bò', 'bắp bò', 'thăn bò', 'gầu bò', 'nạm bò'],
        'bò'           => ['bò', 'thịt bò', 'bắp bò'],
        'bắp bò'       => ['bắp bò', 'thịt bò', 'bò'],
        'thăn bò'      => ['thăn bò', 'thịt bò', 'bò'],

        // Heo / Lợn
        'thịt heo'     => ['thịt heo', 'thịt lợn', 'heo', 'lợn', 'ba chỉ', 'ba rọi', 'sườn'],
        'thịt lợn'     => ['thịt lợn', 'thịt heo', 'lợn', 'heo', 'ba chỉ', 'sườn'],
        'heo'          => ['heo', 'lợn', 'thịt heo', 'thịt lợn'],
        'lợn'          => ['lợn', 'heo', 'thịt lợn', 'thịt heo'],
        'sườn heo'     => ['sườn heo', 'sườn lợn', 'sườn', 'sườn non'],
        'sườn lợn'     => ['sườn lợn', 'sườn heo', 'sườn', 'sườn non'],
        'sườn'         => ['sườn', 'sườn heo', 'sườn lợn', 'sườn non', 'sườn cừu'],
        'ba chỉ heo'   => ['ba chỉ heo', 'ba chỉ lợn', 'ba rọi', 'thịt ba chỉ', 'thịt ba rọi'],
        'ba chỉ lợn'   => ['ba chỉ lợn', 'ba chỉ heo', 'thịt ba chỉ', 'ba rọi', 'thịt ba rọi'],
        'ba rọi'       => ['ba rọi', 'ba chỉ', 'thịt ba rọi', 'thịt ba chỉ'],
        'ba chỉ'       => ['ba chỉ', 'ba rọi', 'thịt ba chỉ', 'thịt ba rọi'],
        'thịt xay'     => ['thịt xay', 'thịt băm'],
        'thịt băm'     => ['thịt băm', 'thịt xay'],
        'lòng heo'     => ['lòng heo', 'lòng lợn', 'lòng'],
        'lòng lợn'     => ['lòng lợn', 'lòng heo', 'lòng'],

        // Hải sản
        'hải sản'      => ['hải sản', 'tôm', 'cua', 'mực', 'cá', 'nghêu', 'ngao', 'ốc', 'sò'],
        'tôm'          => ['tôm', 'tôm sú', 'tôm nõn', 'tôm đồng', 'tôm rim'],
        'tôm sú'       => ['tôm sú', 'tôm'],
        'mực'          => ['mực', 'mực ống', 'mực xào', 'mực chiên', 'mực sa tế'],
        'mực ống'      => ['mực ống', 'mực'],
        'cá'           => ['cá', 'cá lóc', 'cá chép', 'cá thu', 'cá hồi', 'cá bống', 'cá trắm', 'cá diêu hồng', 'cá tai tượng'],
        'nghêu'        => ['nghêu', 'ngao'],
        'ngao'         => ['ngao', 'nghêu'],
        'hàu'          => ['hàu', 'hào'],
        'hào'          => ['hào', 'hàu'],
        'ốc'           => ['ốc', 'ốc hương', 'ốc bươu'],

        // Đậu / Tofu
        'đậu hũ'       => ['đậu hũ', 'đậu phụ', 'tàu hũ'],
        'đậu phụ'      => ['đậu phụ', 'đậu hũ', 'tàu hũ'],
        'tàu hũ'       => ['tàu hũ', 'đậu hũ', 'đậu phụ'],

        // Món bún / phở / mì
        'bún'          => ['bún', 'bún bò', 'bún chả', 'bún cá', 'bún riêu'],
        'phở'          => ['phở', 'phở bò', 'phở gà'],
        'mì xào'       => ['mì xào', 'mì xào giòn'],
        'mì'           => ['mì', 'mì xào', 'mì ý', 'ramen', 'spaghetti'],
        'miến'         => ['miến', 'miến xào', 'miến trộn'],

        // Chả giò / Nem
        'chả giò'      => ['chả giò', 'nem rán', 'nem'],
        'nem rán'      => ['nem rán', 'chả giò', 'nem'],
        'chả ram'      => ['chả ram', 'chả giò', 'nem rán'],

        // Món gỏi / nộm
        'nộm'          => ['nộm', 'gỏi', 'salad'],
        'gỏi'          => ['gỏi', 'nộm', 'salad'],
        'salad'        => ['salad', 'gỏi', 'nộm'],

        // Canh / súp
        'canh'         => ['canh', 'súp', 'soup'],
        'súp'          => ['súp', 'canh', 'soup'],

        // Rau củ & gia vị
        'ngô'          => ['ngô', 'bắp'],
        'bắp'          => ['bắp', 'ngô'],
        'dứa'          => ['dứa', 'khóm', 'thơm'],
        'khóm'         => ['khóm', 'dứa', 'thơm'],
        'thơm'         => ['thơm', 'dứa', 'khóm'],
        'mộc nhĩ'      => ['mộc nhĩ', 'nấm mèo'],
        'nấm mèo'      => ['nấm mèo', 'mộc nhĩ'],
        'mè'           => ['mè', 'vừng'],
        'vừng'         => ['vừng', 'mè'],
    ];
}

/**
 * Builds smart WHERE clauses and SQL parameters for search query.
 * Expands synonyms like 'thịt gà' -> 'gà', 'thịt gà', 'ức gà'...
 */
function build_smart_search_conditions(string $query): array
{
    $q = trim($query);
    if ($q === '') {
        return [[], []];
    }

    $lower = mb_strtolower($q, 'UTF-8');
    $synonymMap = get_culinary_synonym_map();

    $clauses = [];
    $params = [];

    // 1. Direct synonym match for full query
    if (isset($synonymMap[$lower])) {
        $syns = $synonymMap[$lower];
        $sub = [];
        foreach ($syns as $syn) {
            $sub[] = "(r.title LIKE ? OR r.ingredients LIKE ? OR r.description LIKE ? OR r.category LIKE ?)";
            $like = '%' . $syn . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $clauses[] = '(' . implode(' OR ', $sub) . ')';
        return [$clauses, $params];
    }

    // 2. Tokenize and identify multi-word phrases
    $tokens = preg_split('/\s+/', $lower, -1, PREG_SPLIT_NO_EMPTY);
    $handledWords = [];

    foreach ($synonymMap as $key => $syns) {
        if (str_contains($lower, $key)) {
            $sub = [];
            foreach ($syns as $syn) {
                $sub[] = "(r.title LIKE ? OR r.ingredients LIKE ? OR r.description LIKE ? OR r.category LIKE ?)";
                $like = '%' . $syn . '%';
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }
            $clauses[] = '(' . implode(' OR ', $sub) . ')';

            foreach (explode(' ', $key) as $w) {
                $handledWords[$w] = true;
            }
            break;
        }
    }

    // 3. Process remaining tokens
    $stopWords = ['món', 'nấu', 'làm', 'cách', 'ăn', 'ngon', 'chuẩn', 'vị'];
    foreach ($tokens as $t) {
        if (isset($handledWords[$t]) || in_array($t, $stopWords, true) || mb_strlen($t, 'UTF-8') < 2) {
            continue;
        }

        $tokenSyns = [$t];
        if (isset($synonymMap[$t])) {
            $tokenSyns = array_unique(array_merge($tokenSyns, $synonymMap[$t]));
        }

        $sub = [];
        foreach ($tokenSyns as $ts) {
            $sub[] = "(r.title LIKE ? OR r.ingredients LIKE ? OR r.description LIKE ? OR r.category LIKE ?)";
            $like = '%' . $ts . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $clauses[] = '(' . implode(' OR ', $sub) . ')';
    }

    // Fallback if no clauses generated
    if (empty($clauses)) {
        $clauses[] = "(r.title LIKE ? OR r.ingredients LIKE ? OR r.description LIKE ?)";
        $like = '%' . $q . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    return [$clauses, $params];
}

/**
 * Returns an expanded regex pattern for a single search token
 * taking into account North/South Vietnamese culinary synonyms.
 */
function build_synonym_regex(string $token): string
{
    $token = trim($token);
    if ($token === '') {
        return '';
    }

    $lower = mb_strtolower($token, 'UTF-8');
    $synonymMap = get_culinary_synonym_map();

    $matchedSynonyms = [$token];
    if (isset($synonymMap[$lower])) {
        $matchedSynonyms = $synonymMap[$lower];
    } else {
        foreach ($synonymMap as $key => $syns) {
            if (mb_strpos($lower, $key) !== false) {
                $matchedSynonyms = $syns;
                break;
            }
        }
    }

    $escaped = array_map(function ($s) {
        return preg_quote($s, '/');
    }, array_unique($matchedSynonyms));

    $inner = implode('|', $escaped);
    return '(^|[^[:alnum:]])(' . $inner . ')([^[:alnum:]]|$)';
}

/**
 * Splits user query by comma, semicolon, plus sign into distinct search tokens.
 */
function parse_search_query_tokens(string $query): array
{
    $query = trim($query);
    if ($query === '') {
        return [];
    }

    if (str_contains($query, ',') || str_contains($query, ';') || str_contains($query, '+')) {
        $raw = preg_split('/[,;+]+/', $query);
    } else {
        $raw = [$query];
    }

    return array_values(array_filter(array_map('trim', $raw), fn($t) => $t !== ''));
}

/**
 * Parses a recipe ingredient line into name, amount, numeric quantity, and unit.
 */
function parse_ingredient_line(string $line): array
{
    $line = trim($line);
    if ($line === '') {
        return ['name' => '', 'amount' => '', 'qty' => null, 'unit' => ''];
    }

    $units = 'kg|g|gam|ml|l|lít|củ|nhánh|tép|trái|quả|con|bát|chén|miếng|cây|lát|gói|búp|khoanh|bìa|muỗng canh|thìa canh|muỗng cà phê|thìa cà phê|muỗng|thìa|lon|hộp|khúc';

    if (preg_match('/^(\d+(?:[\.,\/]\d+)?)\s*(' . $units . ')?\s+(.+)$/iu', $line, $m)) {
        $rawQty = $m[1];
        $unit = trim($m[2] ?? '');
        $name = trim($m[3]);

        if (str_contains($rawQty, '/')) {
            $fParts = explode('/', $rawQty);
            $qty = (float)$fParts[0] / (float)($fParts[1] ?: 1);
        } else {
            $qty = (float)str_replace(',', '.', $rawQty);
        }

        $amount = $rawQty . ($unit !== '' ? ' ' . $unit : '');
        return [
            'name' => $name,
            'amount' => trim($amount),
            'qty' => $qty,
            'unit' => $unit
        ];
    }

    if (preg_match('/^([^:\-]+)[:\-]\s*(.+)$/u', $line, $m)) {
        $name = trim($m[1]);
        $amountPart = trim($m[2]);

        $qty = null;
        $unit = '';
        if (preg_match('/^(\d+(?:[\.,\/]\d+)?)\s*(' . $units . ')?$/iu', $amountPart, $mq)) {
            $rawQty = $mq[1];
            $unit = trim($mq[2] ?? '');
            if (str_contains($rawQty, '/')) {
                $fParts = explode('/', $rawQty);
                $qty = (float)$fParts[0] / (float)($fParts[1] ?: 1);
            } else {
                $qty = (float)str_replace(',', '.', $rawQty);
            }
        }

        return [
            'name' => $name,
            'amount' => $amountPart,
            'qty' => $qty,
            'unit' => $unit
        ];
    }

    return [
        'name' => $line,
        'amount' => 'vừa đủ',
        'qty' => null,
        'unit' => ''
    ];
}
