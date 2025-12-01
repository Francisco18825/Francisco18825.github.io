<?php
$conn = new mysqli("localhost", "root", "", "gimnasio");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Validación de campos
$requeridos = ['nombre', 'matricula', 'nss', 'contrasena'];
foreach ($requeridos as $campo) {
    if (empty($_POST[$campo])) {
        die("Falta el campo: $campo");
    }
}

$archivos = ['vigencia', 'certificado', 'credencial', 'carta'];
foreach ($archivos as $archivo) {
    if ($_FILES[$archivo]['error'] !== UPLOAD_ERR_OK || $_FILES[$archivo]['size'] > 2 * 1024 * 1024) {
        die("Archivo inválido: $archivo");
    }
}

// Sanitización
$nombre = $conn->real_escape_string($_POST['nombre']);
$matricula = $conn->real_escape_string($_POST['matricula']);
$nss = $conn->real_escape_string($_POST['nss']);
$contrasena = $conn->real_escape_string($_POST['contrasena']);

// Verifica si ya existe la matrícula
$check = $conn->prepare("SELECT 1 FROM alumnos WHERE id_matricula = ?");
$check->bind_param("s", $matricula);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    die("duplicado");
}
$check->close();

// Guardar archivos
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function guardarArchivo($archivo, $nombre, $matricula, $dir) {
    $nombre_final = $dir . $nombre . "_" . $matricula . ".pdf";
    move_uploaded_file($_FILES[$archivo]['tmp_name'], $nombre_final);
    return $nombre_final;
}

$vigenciaPath    = guardarArchivo('vigencia', 'vigencia', $matricula, $uploadDir);
$certificadoPath = guardarArchivo('certificado', 'certificado', $matricula, $uploadDir);
$credencialPath  = guardarArchivo('credencial', 'credencial', $matricula, $uploadDir);
$cartaPath       = guardarArchivo('carta', 'carta', $matricula, $uploadDir);

// Guardar en base de datos
$stmt = $conn->prepare("INSERT INTO alumnos (id_matricula, nombre, NSS, contrasena, vigencia_ss, certificado_medico, credencial_estudiante, carta_responsiva) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", $matricula, $nombre, $nss, $contrasena, $vigenciaPath, $certificadoPath, $credencialPath, $cartaPath);

if ($stmt->execute()) {
    echo "Registro exitoso.";
} else {
    echo "Error al registrar: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
