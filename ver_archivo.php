<?php
session_start();

if (!isset($_SESSION['alumno']['id_matricula'])) {
    http_response_code(403);
    die("No autorizado");
}

$permitidos = ['vigencia_ss', 'certificado_medico', 'credencial_estudiante', 'carta_responsiva'];
$campo = $_GET['campo'] ?? '';
if (!in_array($campo, $permitidos)) {
    http_response_code(400);
    die("Campo inválido.");
}

$matricula = $_SESSION['alumno']['id_matricula'];

$conn = new mysqli("localhost", "root", "", "gimnasio");
if ($conn->connect_error) {
    http_response_code(500);
    die("Error de conexión.");
}

$sql = "SELECT `$campo` FROM alumnos WHERE id_matricula = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $matricula);
$stmt->execute();
$stmt->bind_result($ruta);
$stmt->fetch();
$stmt->close();
$conn->close();

if (file_exists($ruta)) {
    header("Content-Type: application/pdf");
    header("Content-Disposition: inline; filename=\"" . basename($ruta) . "\"");
    readfile($ruta);
    exit;
} else {
    http_response_code(404);
    echo "Archivo no encontrado.";
}
?>
