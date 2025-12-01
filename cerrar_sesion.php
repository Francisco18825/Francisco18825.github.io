<?php
session_start();
session_unset();
session_destroy();
header("Location: inicio_de_sesion.html");
exit();
?>
