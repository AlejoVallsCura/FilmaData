<?php
header('Content-Type: application/json; charset=utf-8');

// Validación básica
$required = ['title','genre','review'];
foreach ($required as $r) {
  if (empty($_POST[$r])) { http_response_code(400); echo json_encode(['error'=>"Falta $r"]); exit; }
}

// Helpers
function slugify($str){
  $t = function_exists('iconv') ? iconv('UTF-8', 'ASCII//TRANSLIT', $str) : $str;
  $t = preg_replace('/[^A-Za-z0-9]+/', '-', strtolower($t));
  return trim($t,'-');
}
function readStore($path){
  if (!file_exists($path)) return [];
  $json = file_get_contents($path);
  $data = json_decode($json, true);
  return is_array($data) ? $data : [];
}

// Inputs
$title   = trim($_POST['title']);
$genre   = trim($_POST['genre']);
$year    = isset($_POST['year']) && $_POST['year'] !== '' ? intval($_POST['year']) : null;
$country = isset($_POST['country']) ? trim($_POST['country']) : '';
$review  = trim($_POST['review']);

// Ruta física para el JSON
$storePath = __DIR__ . '/../data/movies.json';
@mkdir(dirname($storePath), 0775, true);
$movies = readStore($storePath);

// Slug único
$base = slugify($title);
$slug = $base;
$i = 2;
$exists = function($s) use ($movies) {
  foreach($movies as $m){ if ($m['slug'] === $s) return true; }
  return false;
};
while ($exists($slug)) { $slug = $base . '-' . $i++; }

// Imagen (opcional)
$imageUrl = '';
if (!empty($_FILES['imageFile']['name'])) {
  $extAllow = ['jpg','jpeg','png','webp','gif'];
  $ext = strtolower(pathinfo($_FILES['imageFile']['name'], PATHINFO_EXTENSION));
  if (!in_array($ext,$extAllow)) { http_response_code(400); echo json_encode(['error'=>'Extensión no permitida']); exit; }

  @mkdir(__DIR__ . '/../uploads', 0775, true);
  $fname = $slug . '-' . uniqid() . '.' . $ext;
  $dest = __DIR__ . '/../uploads/' . $fname;
  if (!move_uploaded_file($_FILES['imageFile']['tmp_name'], $dest)) {
    http_response_code(500); echo json_encode(['error'=>'No se pudo guardar la imagen']); exit;
  }

  // Construye URL pública automáticamente según tu proyecto
  $project = 'filmdataproyecto'; // ← si tu carpeta se llama distinto, cambiá esto
  $imageUrl = '/' . $project . '/uploads/' . $fname;
}

// Crear y guardar
$movie = [
  'id'        => bin2hex(random_bytes(16)),
  'slug'      => $slug,
  'title'     => $title,
  'genre'     => $genre,
  'year'      => $year,
  'country'   => $country,
  'review'    => $review,
  'image'     => $imageUrl,
  'createdAt' => date('c')
];

$movies[] = $movie;
$ok = file_put_contents($storePath, json_encode($movies, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
if ($ok === false) { http_response_code(500); echo json_encode(['error'=>'No se pudo escribir movies.json']); exit; }

echo json_encode(['ok'=>true,'slug'=>$slug,'movie'=>$movie]);
