<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['alumno'])) {
    echo json_encode(['success' => false, 'mensaje' => '⚠️ No has iniciado sesión.']);
    exit;
}

$matricula = $_SESSION['alumno']['id_matricula'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$hora = $_POST['hora'] ?? '';

if (!$fecha || !$hora) {
    echo json_encode(['success' => false, 'mensaje' => '⚠️ Faltan datos']);
    exit;
}

$fecha_hora = "$fecha $hora";
$conn = new mysqli("localhost", "root", "", "gimnasio");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'mensaje' => '❌ Error en la conexión']);
    exit;
}

// Verificar bloqueo
$stmt = $conn->prepare("SELECT fecha_bloqueo FROM bloqueos WHERE id_matricula = ?");
$stmt->bind_param("s", $matricula);
$stmt->execute();
$stmt->bind_result($fecha_bloqueo);
$stmt->fetch();
$stmt->close();

if ($fecha_bloqueo) {
    $hasta = new DateTime($fecha_bloqueo);
    $hasta->modify('+3 days');
    if (new DateTime() < $hasta) {
        echo json_encode(['success' => false, 'mensaje' => '🚫 Estás bloqueado hasta ' . $hasta->format('d/m/Y H:i')]);
        exit;
    } else {
        $stmt = $conn->prepare("DELETE FROM bloqueos WHERE id_matricula = ?");
        $stmt->bind_param("s", $matricula);
        $stmt->execute();
        $stmt->close();
    }
}

// Verificar máximo 3 reservas activas
$stmt = $conn->prepare("SELECT COUNT(*) FROM reservas WHERE id_matricula = ? AND fecha_reserva >= NOW()");
$stmt->bind_param("s", $matricula);
$stmt->execute();
$stmt->bind_result($totalReservas);
$stmt->fetch();
$stmt->close();

if ($totalReservas >= 3) {
    echo json_encode(['success' => false, 'mensaje' => '⚠️ Solo puedes tener 3 reservas activas']);
    exit;
}

// Verificar si ya tiene una reserva en ese horario
$stmt = $conn->prepare("SELECT COUNT(*) FROM reservas WHERE id_matricula = ? AND fecha_reserva = ?");
$stmt->bind_param("ss", $matricula, $fecha_hora);
$stmt->execute();
$stmt->bind_result($existe);
$stmt->fetch();
$stmt->close();

if ($existe > 0) {
    echo json_encode(['success' => false, 'mensaje' => '⚠️ Ya tienes una reserva en ese horario']);
    exit;
}

// Insertar reserva
$stmt = $conn->prepare("INSERT INTO reservas (fecha_reserva, id_matricula, asistio) VALUES (?, ?, 0)");
$stmt->bind_param("ss", $fecha_hora, $matricula);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => '✅ Reserva guardada correctamente']);
} else {
    echo json_encode(['success' => false, 'mensaje' => '❌ Error al guardar la reserva']);
}

$stmt->close();
$conn->close();
?>
