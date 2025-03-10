<?php
	$host = 'localhost';
	$dbname = 'Proyecto1';
	$user = 'postgres';
	$password = 'postgres';

	try {
		$pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		]);
	} catch (PDOException $e) {
		die("Error de conexión: " . $e->getMessage());
	}
?>
