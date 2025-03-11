<?php
	require 'common.php';
	require 'database.php';
	require 'vendor/autoload.php';
	use Firebase\JWT\JWT;
	use Firebase\JWT\Key;

	$secretKey = 'clave_super_segura';

	// Obtenemos los datos del cuerpo de la solicitud
	$data = json_decode(file_get_contents('php://input'), true);

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		// Eliminar espacios y validar campos
		$nombre			= trim($data['nombre'] ?? '');
		$username   = trim($data['username'] ?? '');
		$email      = trim($data['email'] ?? '');
		$contrasena = trim($data['contrasenia'] ?? '');

		// Validar campos
		if (empty($nombre) || empty($username) || empty($email) || empty($contrasena)) {
			sendResponse(0, 'Todos los campos son requeridos');
		}

		// Validar formato del email
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			sendResponse(0, 'Email no válido');
		}

		try {
			// Validar si el usuario ya existe
			$sqlCheck = " SELECT COUNT(*) 
										FROM esquema1.usuarios 
										WHERE username = :username
											 OR nombre   = :nombre 
											 OR email    = :email";
			$stmtCheck = $pdo->prepare($sqlCheck);
			$stmtCheck->execute(['username' => $username ,
													 'nombre'   => $nombre, 
													 'email'    => $email ]);

			if ($stmtCheck->fetchColumn() > 0) {
				sendResponse(0, 'El usuario o el email ya están registrados');
			}

			// Registrar un nuevo usuario
			$sqlInsert = "INSERT INTO esquema1.usuarios (nombre, email, password, username) 
										VALUES (:nombre, :email, :contrasena, :username)";
			$stmtInsert = $pdo->prepare($sqlInsert);
			$success = $stmtInsert->execute([
				'nombre'	   => $nombre,
				'username'   => $username,
				'email'      => $email,
				'contrasena' => password_hash($contrasena, PASSWORD_BCRYPT),
			]);

			if ($success) {
				//Obtiene el ultimo id, el del nuevo usuario
				$userId = $pdo->lastInsertId();
				
				//Creamos el JWT
				$payload = [
					'user_id' => $userId,
					'nombre'  => $nombre,
					'iat'     => time(),
					'exp'			=> time() + 28800 //8 horas
				];

				$jwt = JWT::encode($payload,$secretKey,'HS256');

				sendResponse(1, 'Usuario registrado con exito', ['token' => $jwt]);
			} else {
				sendResponse(0, 'Error al registrar el usuario');
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
			sendResponse(0, 'Error interno del servidor');
		}
	}
?>
