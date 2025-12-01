<?php
session_start();

if (!isset($_SESSION['alumno'])) {
    header("Location: login.html");
    exit();
}

$alumno = $_SESSION['alumno'];
$mensaje = "";

function conectar() {
    $conn = new mysqli("localhost", "root", "", "gimnasio");
    if ($conn->connect_error) {
        die("Error de conexión a la base de datos: " . $conn->connect_error);
    }
    return $conn;
}

$conn = conectar();

// Verificar si está bloqueado
$stmt = $conn->prepare("SELECT fecha_bloqueo FROM bloqueos WHERE id_matricula = ?");
$stmt->bind_param("s", $alumno['id_matricula']);
$stmt->execute();
$stmt->bind_result($fecha_bloqueo);
$stmt->fetch();
$stmt->close();

$bloqueado = false;
$ahora = new DateTime();

if ($fecha_bloqueo) {
    $hasta = new DateTime($fecha_bloqueo);
    $hasta->modify('+3 days');
    if ($ahora < $hasta) {
        $bloqueado = true;
        $mensaje = "🚫 Bloqueado por faltas (son 3 días de bloqueo) hasta " . $hasta->format('d/m/Y H:i');
    } else {
        $stmt = $conn->prepare("DELETE FROM bloqueos WHERE id_matricula = ?");
        $stmt->bind_param("s", $alumno['id_matricula']);
        $stmt->execute();
        $stmt->close();
    }
}

// Contar faltas
$stmt = $conn->prepare("SELECT COUNT(*) FROM reservas WHERE id_matricula = ? AND fecha_reserva < NOW() AND asistio = 0");
$stmt->bind_param("s", $alumno['id_matricula']);
$stmt->execute();
$stmt->bind_result($faltas);
$stmt->fetch();
$stmt->close();

if ($faltas == 2 && !$bloqueado) {
    $mensaje = "⚠️ Tienes 2 inasistencias. Si faltas una más, serás bloqueado 3 días.";
}

if ($faltas >= 3 && !$bloqueado) {
    $stmt = $conn->prepare("REPLACE INTO bloqueos (id_matricula, fecha_bloqueo) VALUES (?, NOW())");
    $stmt->bind_param("s", $alumno['id_matricula']);
    $stmt->execute();
    $stmt->close();
    $mensaje = "🚫 Has sido bloqueado por acumular 3 faltas.";
    $bloqueado = true;
}

// Obtener reservas activas
function obtenerReservas($conn, $matricula) {
    $reservas = [];
    $stmt = $conn->prepare("SELECT fecha_reserva, asistio FROM reservas WHERE id_matricula = ? AND fecha_reserva >= NOW() ORDER BY fecha_reserva ASC");
    $stmt->bind_param("s", $matricula);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($row = $resultado->fetch_assoc()) {
        $reservas[] = [
            'fecha_reserva' => $row['fecha_reserva'],
            'asistio' => $row['asistio']
        ];
    }
    $stmt->close();
    return $reservas;
}

$reservas = obtenerReservas($conn, $alumno['id_matricula']);
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Reserva el gym</title>
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color:rgba(123, 223, 149, 0.54);
    color:rgb(0, 0, 0);
}
nav {
    background-color:#1aa5b8;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
}
nav img {
    height: 80px;
}
nav a {
    color: #f8f0f0ff;
    margin-left: 15px;
    text-decoration: none;
    font-weight: bold;
}
nav a:hover {
    text-decoration: underline;
}
h1 {
    text-align: center;
    margin-top: 30px;
}
form {
    background-color:rgba(160, 68, 221, 0.66);
    max-width: 500px;
    margin: 30px auto;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 0 12px rgba(39, 109, 82, 0.5);
}
label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
}
input[type="date"],
input[type="time"],
button {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border-radius: 6px;
    border: none;
    font-size: 16px;
    background-color: #333;
    color: #fff;
}
button {
    background-color: #e63946;
    cursor: pointer;
}
button:hover {
    background-color: #d62828;
}
.reservas {
    max-width: 600px;
    margin: 20px auto;
    padding: 20px;
    background-color:rgba(153, 0, 255, 0.2);
    border-radius: 12px;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.5);
}
.reservas h2 {
    text-align: center;
    color: #fca311;
}
.reservas ul {
    list-style: none;
    padding: 0;
}
.reservas li {
    padding: 10px;
    background-color: #e0e0e0;
    margin: 10px 0;
    border-radius: 6px;
    text-align: center;
    color: #000;
}
footer {
    text-align: center;
    padding: 20px;
    color: #aaa;
    margin-top: 40px;
}
h4 {
    color:rgb(248, 73, 42)
}
</style>
</head>
<body>
<nav>
    <img src="logo-removebg-preview.png" alt="Logo GYM" />
    <div>
        <a href="reserva.php">Atrás</a>
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </div>
</nav>

<h1><strong> Horario de servicio:</strong></h1><br>
<h1><strong>9:30 AM - 5:00 PM</strong></h1><br>
<h1><strong>De lunes a viernes.</strong></h1><br>
<h1><strong>NOTA IMPORTANTE: Una vez que acomulen 3 inasistencias se bloqueara el acceso a reservar en el GYM.</strong></h1>
<h1><strong>Reserva el día y la hora:UNA VEZ RESERVADO REFRESCA LA PÁGINA</strong></h1><br>

<?php if (!empty($reservas)): ?>
<div class="reservas" id="lista-reservas">
    <h2>Reservas activas</h2>
    <ul>
        <?php foreach ($reservas as $r): ?>
            <li>
                <?= date("d/m/Y H:i", strtotime($r['fecha_reserva'])) ?>
                <?php if ($r['asistio']): ?>
                    ✅
                <?php else: ?>
                    <button class="btn-asistencia" data-fecha="<?= htmlspecialchars($r['fecha_reserva']) ?>" style="margin-left: 10px; color: green; font-weight: bold; cursor: pointer;">Marcar asistencia</button>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php if (!$bloqueado): ?>
<form id="form-reserva" method="POST" action="">
    <h1><label for="fecha"><strong>Selecciona día:</strong></label></h1> 
    <input type="date" id="fecha" name="fecha" required min="<?= date('Y-m-d') ?>" />

    <h1><label for="hora">Selecciona hora: <h4> DEBE SER CADA MEDIA HORA O BIEN CADA HORA <br>
RECUERDA QUE SOLO ES 1 HORA Y MEDIA </h4> (9:30, 10:30, 11:30 o tambien puede ser 10:00, 11:00, 12:00) AL REGISTRAR REFRESQUE LA PÁGINA.</label></h1>
    <input type="time" id="hora" name="hora" required min="09:00" max="17:00" step="1800" />

    <button type="submit">Reservar</button>
</form>
<?php endif; ?>

<footer>
    <p>GYM, UPT. (2025)</p>
</footer>

<script>
document.getElementById('form-reserva').addEventListener('submit', function(e) {
    e.preventDefault();

    const fecha = document.getElementById('fecha').value;
    const hora = document.getElementById('hora').value;

    if (!fecha || !hora) {
        alert('❌ Selecciona fecha y hora.');
        return;
    }

    const data = new URLSearchParams();
    data.append('fecha', fecha);
    data.append('hora', hora);

    fetch('procesar_reserva.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: data.toString()
    })
    .then(resp => resp.json())
    .then(res => {
        alert(res.mensaje);
        if (res.success) {
            actualizarReservas();
            document.getElementById('form-reserva').reset();
        }
    })
    .catch(() => alert('❌ Error en la conexión'));
});

function actualizarReservas() {
    fetch('obtener_reservas.php')
    .then(resp => resp.json())
    .then(data => {
        const lista = document.getElementById('lista-reservas');
        if (!lista) return;

        let html = '<h2>Reservas activas</h2><ul>';
        if (data.length === 0) {
            html += '<li>No tienes reservas activas.</li>';
        } else {
            data.forEach(r => {
                html += '<li>' + r.fecha_hora_formateada;
                if (r.asistio) {
                    html += ' ✅';
                } else {
                    html += ` <button class="btn-asistencia" data-fecha="${r.fecha_hora}" style="margin-left: 10px; color: green; font-weight: bold; cursor: pointer;">Marcar asistencia</button>`;
                }
                html += '</li>';
            });
        }
        html += '</ul>';
        lista.innerHTML = html;
        agregarEventosAsistencia();
    });
}

function agregarEventosAsistencia() {
    document.querySelectorAll('.btn-asistencia').forEach(button => {
        button.addEventListener('click', () => {
            const fecha_hora = button.getAttribute('data-fecha');
            const data = new URLSearchParams();
            data.append('fecha_hora', fecha_hora);

            fetch('marcar_asistencia.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: data.toString()
            })
            .then(resp => resp.json())
            .then(res => {
                if (res.success) {
                    button.closest('li').remove();
                } else {
                    alert('Error: ' + (res.error || 'No se pudo marcar asistencia'));
                }
            })
            .catch(() => alert('Error en la conexión'));
        });
    });
}

agregarEventosAsistencia();
</script>

<?php if ($mensaje): ?>
<script>
    alert("<?= htmlspecialchars($mensaje) ?>");
</script>
<?php endif; ?>
</body>
</html>
