<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['alumno'])) {
    echo json_encode(['success' => false, 'error' => '⚠️ No has iniciado sesión']);
    exit;
}

$matricula = $_SESSION['alumno']['id_matricula'];
$fecha_hora = $_POST['fecha_hora'] ?? '';

if (!$fecha_hora) {
    echo json_encode(['success' => false, 'error' => 'Falta la fecha/hora']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "gimnasio");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión']);
    exit;
}

$stmt = $conn->prepare("UPDATE reservas SET asistio = 1 WHERE id_matricula = ? AND fecha_reserva = ?");
$stmt->bind_param("ss", $matricula, $fecha_hora);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No se pudo marcar la asistencia']);
}

$stmt->close();
$conn->close();
?>
