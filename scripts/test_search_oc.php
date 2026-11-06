<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/search_helper.php';

list($clauses, $params) = build_smart_search_conditions('ốc');
$sql = "SELECT r.id, r.title, r.image_url FROM recipes r WHERE r.status = 'approved' AND " . implode(' AND ', $clauses) . " LIMIT 30";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

echo "Total matching recipes for 'ốc': " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "ID: {$r['id']} | {$r['title']} | Image: {$r['image_url']}\n";
}
