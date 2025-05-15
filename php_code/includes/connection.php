<?php
    $host = 'db';
    $dbname = 'sample_d';
    $username = 'nigel';
    $password = 'passw0rd';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    } 
?>