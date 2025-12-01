<?php
session_start();

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "gimnasio");
if ($conn->connect_error) {
    http_response_code(500);
    exit("Error de conexión: " . $conn->connect_error);
}

// Validación de datos enviados
if (empty($_POST['matricula']) || empty($_POST['contrasena'])) {
    http_response_code(400);
    exit("Faltan datos.");
}

$matricula = trim($_POST['matricula']);
$contrasena = trim($_POST['contrasena']);

// Consulta del alumno
$sql = "SELECT * FROM alumnos WHERE id_matricula = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $matricula);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $alumno = $resultado->fetch_assoc();

    // Aquí compara la contraseña directamente; si tienes password_hash, usa password_verify()
    if ($contrasena === $alumno['contrasena']) {
        $_SESSION['alumno'] = $alumno;
        http_response_code(200); // Éxito
        exit();
    } else {
        http_response_code(401);
        exit("Contraseña incorrecta.");
    }
} else {
    http_response_code(404);
    exit("Matrícula no encontrada.");
}

$stmt->close();
$conn->close();
?>
