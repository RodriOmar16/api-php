<?php
	require 'database.php';
	require 'common.php';
	require 'vendor/autoload.php';
	use Firebase\JWT\JWT;
	use Firebase\JWT\Key;

	$secretKey  = 'clave_super_segura';
	$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

	if(!str_starts_with($authHeader, 'Bearer ')){
		sendResponse(0, 'Token no válido o ausente');
	}

	$token = str_replace('Bearer ', '', $authHeader);

	try {
		$decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
	} catch (Exception $e) {
		sendResponse(0, 'Token inválido o expirado');
	}

	// Leer el método de la solicitud
	$method = $_SERVER['REQUEST_METHOD'];
	// Leer parámetros de la solicitud (para PUT o DELETE usamos el cuerpo en JSON)
	$input = json_decode(file_get_contents('php://input'), true);

	// Manejar las solicitudes HTTP
	switch ($method) {
		case 'GET': // Obtener usuarios con o sin filtros
			$nombre = $_GET['nombre'] ?? null;
			$email = $_GET['email'] ?? null;
			$rol = $_GET['rol'] ?? null;

			$sql = "SELECT * FROM esquema1.usuarios WHERE 1=1";
			$params = [];

			if ($nombre) {
				$sql .= " AND nombre ILIKE :nombre";
				$params[':nombre'] = "%$nombre%";
			}
			if ($email) {
				$sql .= " AND email ILIKE :email";
				$params[':email'] = "%$email%";
			}
			if ($rol) {
				$sql .= " AND rol = :rol";
				$params[':rol'] = $rol;
			}

			$stmt = $pdo->prepare($sql);
			$stmt->execute($params);
			$usuarios = $stmt->fetchAll();

			echo json_encode($usuarios);
			break;

		case 'POST': // Crear un nuevo usuario
			$nombre = $input['nombre'] ?? null;
			$email = $input['email'] ?? null;
			$rol = $input['rol'] ?? null;
			$contrasena = $input['contrasena'] ?? null;

			if (!$nombre || !$email || !$contrasena) {
				sendResponse(0, 'Faltan datos obligatorios');
			}

			// Validar si el usuario o email ya existen
			$sqlCheck = "SELECT * FROM esquema1.usuarios WHERE nombre = :nombre OR email = :email";
			$stmtCheck = $pdo->prepare($sqlCheck);
			$stmtCheck->execute(['nombre' => $nombre, 'email' => $email]);

			if ($stmtCheck->fetch()) {
				sendResponse(0, 'El nombre o el email ya están registrados');
			}

			// Insertar nuevo usuario
			$sqlInsert = "INSERT INTO esquema1.usuarios (nombre, email, rol, contrasena) VALUES (:nombre, :email, :rol, :contrasena)";
			$stmtInsert = $pdo->prepare($sqlInsert);
			$success = $stmtInsert->execute([
				'nombre' => $nombre,
				'email' => $email,
				'rol' => $rol,
				'contrasena' => password_hash($contrasena, PASSWORD_BCRYPT),
			]);
			sendResponse($success ? 1 : 0, $success ? 'Usuario creado' : 'Error al crear usuario');
			break;
		case 'PUT': // Editar un usuario existente
			$id = $_GET['id'] ?? null;

			if (!$id) {
				sendResponse(0, 'Se requiere un ID de usuario');
			}

			$nombre = $input['nombre'] ?? null;
			$email = $input['email'] ?? null;
			$rol = $input['rol'] ?? null;

			$sqlUpdate = "UPDATE esquema1.usuarios SET nombre = COALESCE(:nombre, nombre), email = COALESCE(:email, email), rol = COALESCE(:rol, rol) WHERE id = :id";
			$stmtUpdate = $pdo->prepare($sqlUpdate);
			$success = $stmtUpdate->execute([
				'nombre' => $nombre,
				'email' => $email,
				'rol' => $rol,
				'id' => $id,
			]);
			sendResponse($success ? 1 : 0, $success ? 'Usuario actualizado' : 'Error al actualizar usuario');
			break;
		case 'DELETE': // Eliminar un usuario
			$id = $_GET['id'] ?? null;

			if (!$id) {
				sendResponse(0, 'Se requiere un ID de usuario');
			}

			$sqlDelete = "DELETE FROM esquema1.usuarios WHERE id = :id";
			$stmtDelete = $pdo->prepare($sqlDelete);
			$success = $stmtDelete->execute(['id' => $id]);

			sendResponse($success ? 1 : 0, $success ? 'Usuario eliminado' : 'Error al eliminar usuario');
			break;

		default:
			sendResponse(0, 'Método no soportado');
			break;
	}
?>
