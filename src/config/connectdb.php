<?php
$host = 'db';
$db   = 'pacientes_db';
$user = 'root';
$pass = 'root';

$reintentos = 5;
$conexion = false;

while ($reintentos > 0 && !$conexion) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conexion = true;
    } catch (Exception $e) {
        $reintentos--;
        sleep(2); // espera 2 segundos
    }
}

if (!$conexion) {
    die("Error de conexión: No se pudo conectar a la base de datos");
}