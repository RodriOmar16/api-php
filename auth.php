<?php
  require 'database.php';
  require 'common.php';
  require 'vendor/autoload.php'; // Carga la biblioteca de JWT
  use Firebase\JWT\JWT;
  use Firebase\JWT\Key;

  $secretKey = 'clave_super_segura'; // Clave secreta para firmar los tokens

  $data = json_decode(file_get_contents('php://input'), true);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($data['nombre']) || !isset($data['contrasenia'])) {
      sendResponse(0, 'Faltan datos obligatorios en la solicitud');
    }

    $username = $data['nombre'];
    $contrasenia = $data['contrasenia'];

    try {
      // Verificar usuario en la base de datos
      $sql = "SELECT * FROM esquema1.usuarios u WHERE u.nombre = :username OR u.username = :username";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(['username' => $username]);
      $user = $stmt->fetch();

      if (!$user || !password_verify($contrasenia, $user['password'])) {
        sendResponse(0, 'Credenciales inválidas');
      }

      // Crear el JWT
      $payload = [
        'user_id'  => $user['id'],
        'nombre'   => $user['nombre'],
        //'username' => $user['username'],
        'iat' => time(),
        'exp' => time() + 28800, // Expira en 8 hora
      ];
      $jwt = JWT::encode($payload, $secretKey, 'HS256');
      
      sendResponse(1, 'Bienvenido/a '.$username, ['token' => $jwt]);
    } catch (Exception $e) {
      error_log($e->getMessage());
      sendResponse(0, 'Error interno del servidor');
    }
  }
?>
