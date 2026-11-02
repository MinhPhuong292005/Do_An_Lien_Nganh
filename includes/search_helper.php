<?php

declare(strict_types=1);

/**
 * Cookio Search & Culinary Synonym Expansion Helper
 */

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

    // Ordered list of synonym groups (longer phrases first to avoid greedy matching)
    $synonymMap = [
        // Heo / Lợn variants
        'thịt heo'     => ['thịt heo', 'thịt lợn'],
        'thịt lợn'     => ['thịt lợn', 'thịt heo'],
        'sườn heo'     => ['sườn heo', 'sườn lợn'],
        'sườn lợn'     => ['sườn lợn', 'sườn heo'],
        'ba chỉ heo'   => ['ba chỉ heo', 'ba chỉ lợn', 'ba rọi', 'thịt ba chỉ', 'thịt ba rọi'],
        'ba chỉ lợn'   => ['ba chỉ lợn', 'ba chỉ heo', 'thịt ba chỉ', 'ba rọi', 'thịt ba rọi'],
        'ba rọi'       => ['ba rọi', 'ba chỉ', 'thịt ba rọi', 'thịt ba chỉ'],
        'ba chỉ'       => ['ba chỉ', 'ba rọi', 'thịt ba chỉ', 'thịt ba rọi'],
        'nạc heo'      => ['nạc heo', 'nạc lợn'],
        'nạc lợn'      => ['nạc lợn', 'nạc heo'],
        'giò heo'      => ['giò heo', 'giò lợn', 'chân giò'],
        'giò lợn'      => ['giò lợn', 'giò heo', 'chân giò'],
        'chân giò'     => ['chân giò', 'giò heo', 'giò lợn'],
        'tai heo'      => ['tai heo', 'tai lợn'],
        'tai lợn'      => ['tai lợn', 'tai heo'],
        'mỡ heo'       => ['mỡ heo', 'mỡ lợn'],
        'mỡ lợn'       => ['mỡ lợn', 'mỡ heo'],
        'gan heo'      => ['gan heo', 'gan lợn'],
        'gan lợn'      => ['gan lợn', 'gan heo'],
        'tim heo'      => ['tim heo', 'tim lợn'],
        'tim lợn'      => ['tim lợn', 'tim heo'],
        'lòng heo'     => ['lòng heo', 'lòng lợn'],
        'lòng lợn'     => ['lòng lợn', 'lòng heo'],
        'thịt xay'     => ['thịt xay', 'thịt băm'],
        'thịt băm'     => ['thịt băm', 'thịt xay'],
        'heo'          => ['heo', 'lợn'],
        'lợn'          => ['lợn', 'heo'],

        // Tofu / Chả giò / Nem
        'đậu hũ'       => ['đậu hũ', 'đậu phụ', 'tàu hũ'],
        'đậu phụ'      => ['đậu phụ', 'đậu hũ', 'tàu hũ'],
        'tàu hũ'       => ['tàu hũ', 'đậu hũ', 'đậu phụ'],
        'chả giò'      => ['chả giò', 'nem rán', 'nem'],
        'nem rán'      => ['nem rán', 'chả giò', 'nem'],
        'chả ram'      => ['chả ram', 'chả giò', 'nem rán'],

        // Herbs & Veggies
        'rau mùi'      => ['rau mùi', 'ngò rí', 'ngò'],
        'ngò rí'       => ['ngò rí', 'rau mùi', 'ngò'],
        'ngò gai'      => ['ngò gai', 'mùi tàu', 'ngò tây'],
        'mùi tàu'      => ['mùi tàu', 'ngò gai'],
        'thì là'       => ['thì là', 'thìa là'],
        'thìa là'      => ['thìa là', 'thì là'],
        'ngô'          => ['ngô', 'bắp'],
        'bắp'          => ['bắp', 'ngô'],
        'mè'           => ['mè', 'vừng'],
        'vừng'         => ['vừng', 'mè'],
        'dứa'          => ['dứa', 'khóm', 'thơm'],
        'khóm'         => ['khóm', 'dứa', 'thơm'],
        'thơm'         => ['thơm', 'dứa', 'khóm'],
        'mộc nhĩ'      => ['mộc nhĩ', 'nấm mèo'],
        'nấm mèo'      => ['nấm mèo', 'mộc nhĩ'],
        'ớt chuông'    => ['ớt chuông', 'ớt ngọt'],
        'ớt ngọt'      => ['ớt ngọt', 'ớt chuông'],
        'củ đậu'       => ['củ đậu', 'củ sắn'],
        'củ sắn'       => ['củ sắn', 'củ đậu'],
        'khoai mì'     => ['khoai mì', 'sắn'],
        'quất'         => ['quất', 'tắc', 'hạnh'],
        'tắc'          => ['tắc', 'quất'],

        // Seafood variants
        'hàu'          => ['hàu', 'hào'],
        'hào'          => ['hào', 'hàu'],
        'nghêu'        => ['nghêu', 'ngao'],
        'ngao'         => ['ngao', 'nghêu'],
        'tôm sú'       => ['tôm sú', 'tôm'],
        'mực ống'      => ['mực ống', 'mực'],
    ];

    $matchedSynonyms = [$token];
    if (isset($synonymMap[$lower])) {
        $matchedSynonyms = $synonymMap[$lower];
    } else {
        // Substring matching for phrases e.g. "thịt heo nướng" -> "(thịt heo|thịt lợn) nướng"
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

    // Format 1: Starts with number e.g. "350g Thịt bò..." or "1 củ Hành tây..." or "1/2 quả Chanh..."
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

    // Format 2: Colon separator e.g. "Thịt bò: 350g"
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

    // Format 3: General unquantified ingredient e.g. "Dầu ăn, gia vị..."
    return [
        'name' => $line,
        'amount' => 'vừa đủ',
        'qty' => null,
        'unit' => ''
    ];
}
