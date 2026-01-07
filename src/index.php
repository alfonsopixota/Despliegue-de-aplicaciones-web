<?php
$host = 'mysql';
$db   = getenv('MYSQL_DATABASE');
$user = getenv('MYSQL_USER');
$pass = getenv('MYSQL_PASSWORD');
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

echo "<h1>Prueba de Despliegue con Docker</h1>";

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     echo "<p style='color: green;'>¡Conexión a la base de datos establecida con éxito!</p>";
} catch (\PDOException $e) {
     echo "<p style='color: red;'>Error al conectar a la base de datos: " . $e->getMessage() . "</p>";
}

echo "<h3>Detalles del Entorno:</h3>";
echo "<ul>";
echo "<li>Servidor Web: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Versión de PHP: " . phpversion() . "</li>";
echo "<li>Base de Datos: " . $db . "</li>";
echo "</ul>";
?>
