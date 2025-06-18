<?php
$claves = [
    "SISTEMA2025",
    "SISTEMA2025",
    "SISTEMA2025",
    "SENATI2025",
    "clave123",
    "otraClave2025"
];

foreach ($claves as $clave) {
    echo "<hr>";
    echo "Clave: " . $clave . "<br>";
    echo "Encriptada: " . password_hash($clave, PASSWORD_BCRYPT) . "<br>";
}
?>
