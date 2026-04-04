<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS repairsystem_laravel");
    echo "Database 'repairsystem_laravel' created or already exists.\n";
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
