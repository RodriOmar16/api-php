<?php
  // Configuración de encabezados para JSON y CORS
  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *'); // Permitir solicitudes desde el frontend
  header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // Métodos permitidos
  header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Encabezados permitidos

  // Manejo global de excepciones
  function handleException($exception) {
    error_log($exception->getMessage());
    sendResponse(0, 'Error interno del servidor');
  }

  // Registrar manejador de excepciones
  set_exception_handler('handleException');

  // Función genérica para enviar respuestas JSON
  function sendResponse($resultado, $msj, $data = []) {
    $response = [
      'resultado' => $resultado, 'msj' => $msj
    ];
    if(!empty($data)){
      $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit;
  }
  // Manejar solicitudes OPTIONS (Preflight CORS)
  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
  }
?>
