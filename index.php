<?php
  echo "API funcionando -- ";
  echo password_hash('sofiapass', PASSWORD_BCRYPT);
  echo "<br>" ;
  $passwords = [
    2 => 'abcdef',
    3 => 'qwerty',
    4 => 'password123',
    5 => 'securepass',
    6 => 'mypassword',
    7 => '123abc',
  ];

  try {
    $pdo = new PDO("pgsql:host=127.0.0.1;port=5432;dbname=Proyecto1", "postgres", "postgres");
    echo "Conexión exitosa.";
  } catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
  }


?>
