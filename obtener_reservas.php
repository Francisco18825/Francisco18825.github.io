<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['alumno'])) {
    echo json_encode([]);
    exit;
}

$matricula = $_SESSION['alumno']['id_matricula'];

$conn = new mysqli("localhost", "root", "", "gimnasio");
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT fecha_reserva, asistio FROM reservas WHERE id_matricula = ? AND fecha_reserva >= NOW() ORDER BY fecha_reserva ASC");
$stmt->bind_param("s", $matricula);
$stmt->execute();
$resultado = $stmt->get_result();

$reservas = [];
while ($row = $resultado->fetch_assoc()) {
    $reservas[] = [
        'fecha_hora' => $row['fecha_reserva'],
        'fecha_hora_formateada' => date("d/m/Y H:i", strtotime($row['fecha_reserva'])),
        'asistio' => $row['asistio']
    ];
}

$stmt->close();
$conn->close();

echo json_encode($reservas);
?>
