<?php
header('Content-Type: application/json; charset=utf-8');
$slug = $_GET['slug'] ?? '';
if (!$slug) { http_response_code(400); echo json_encode(['error'=>'Falta slug']); exit; }
$storePath = __DIR__ . '/../data/movies.json';
if (!file_exists($storePath)) { echo json_encode(null); exit; }
$movies = json_decode(file_get_contents($storePath), true) ?: [];
foreach ($movies as $m) {
  if ($m['slug'] === $slug) { echo json_encode($m, JSON_UNESCAPED_UNICODE); exit; }
}
echo json_encode(null);
