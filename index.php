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

foreach ($passwords as $id => $password) {
    echo "UPDATE esquema1.usuarios SET password = '" . password_hash($password, PASSWORD_BCRYPT) . "' WHERE id = $id;\n";
}


?>
