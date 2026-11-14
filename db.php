<?php
$host = 'localhost';
$db   = 'classwall';
$user = 'postgres'; // Change if your PostgreSQL username is different
$pass = 'my_password'; //eplace with your actual PostgreSQL password/ R
$port = '5433';

$dsn = "pgsql:host=$host;port=$port;dbname=$db;";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>