<?php
session_start();
if (!isset($_SESSION['alumno'])) {
    header("Location: login.html");
    exit();
}

$alumno = $_SESSION['alumno'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documentos del alumno</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color:rgb(0, 249, 67);
            color: #f0f0f0;
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
            color: #ffffff;
            margin-left: 15px;
            text-decoration: none;
            font-weight: bold;
        }
        nav a:hover {
            text-decoration: underline;
        }
        h1, h2 {
            text-align: center;
            margin: 20px 0;
        }
        .info-alumno {
            background-color:rgba(250, 96, 255, 0.66);
            max-width: 600px;
            margin: 20px auto;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.5);
        }
        .info-alumno h2 {
            font-weight: normal;
        }
        button {
            display: block;
            margin: 20px auto;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            background-color: #e63946;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #d62828;
        }
        a {
            color: #61dafb;
        }
    </style>
</head>
<body>
<nav>
    <img src="logo-removebg-preview.png" alt="Logo GYM">
    <div>
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </div>
</nav>

<div class="info-alumno">
    <h1>Bienvenido, <?= htmlspecialchars($alumno['nombre']) ?></h1>
    <h2>Matrícula: <?= htmlspecialchars($alumno['id_matricula']) ?></h2>
    <h2>NSS: <?= htmlspecialchars($alumno['NSS']) ?></h2>
    <h2>Vigencia S.S: <a href="ver_archivo.php?campo=vigencia_ss" target="_blank">Ver archivo</a></h2>
    <h2>Certificado médico: <a href="ver_archivo.php?campo=certificado_medico" target="_blank">Ver archivo</a></h2>
    <h2>Credencial estudiante: <a href="ver_archivo.php?campo=credencial_estudiante" target="_blank">Ver archivo</a></h2>
    <h2>Carta responsiva: <a href="ver_archivo.php?campo=carta_responsiva" target="_blank">Ver archivo</a></h2>
    <button onclick="location.href='horario.php'">Continuar</button>
</div>
</body>
</html>
