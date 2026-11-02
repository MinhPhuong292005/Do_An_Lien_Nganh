<?php

declare(strict_types=1);

/**
 * Danh sách từ khóa tiêu cực, xúc phạm, thô tục vi phạm tiêu chuẩn cộng đồng Cookio
 */
function get_toxic_keywords(): array
{
    return [
        // Chửi bậy, thô tục tiếng Việt
        'đụ', 'đụ má', 'đụ mẹ', 'địt', 'địt mẹ', 'địt cụ', 'đĩ', 'con đĩ', 'thằng chó', 'đồ chó',
        'chó chết', 'chết tiệt', 'mất dạy', 'khốn nạn', 'súc vật', 'óc chó', 'não tàn',
        'lồn', 'buồi', 'cặc', 'dái', 'cút mẹ', 'cút đi', 'biến đi', 'đm', 'đcm', 'dmm',
        'dcm', 'vcl', 'vcc', 'clgt', 'đéo', 'đíu', 'cc', 'cl', 'lol', 'ml',
        'rác rưởi', 'kinh tởm', 'dơ dáy', 'gớm ghiếc', 'tởm lợm', 'lừa đảo', 'lừa tiền', 'scam',
        // Tiếng Anh thô tục
        'fuck', 'fucking', 'shit', 'bitch', 'asshole', 'bastard', 'cunt', 'dick', 'pussy'
    ];
}

/**
 * Kiểm tra xem một chuỗi có chứa từ khóa vi phạm tiêu chuẩn cộng đồng hay không
 * @return array{is_toxic: bool, matched: string}
 */
function check_toxic_comment(string $text): array
{
    $cleanText = mb_strtolower(trim($text), 'UTF-8');
    if ($cleanText === '') {
        return ['is_toxic' => false, 'matched' => ''];
    }

    $normalized = ' ' . preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $cleanText) . ' ';
    $normalized = (string) preg_replace('/\s+/', ' ', $normalized);

    foreach (get_toxic_keywords() as $kw) {
        $kwLower = mb_strtolower($kw, 'UTF-8');
        $pattern = '/(?<=^|\s|[,.\-!?])' . preg_quote($kwLower, '/') . '(?=$|\s|[,.\-!?])/iu';
        if (preg_match($pattern, $cleanText) || str_contains($normalized, ' ' . $kwLower . ' ')) {
            return [
                'is_toxic' => true,
                'matched' => $kw
            ];
        }
    }

    return [
        'is_toxic' => false,
        'matched' => ''
    ];
}

