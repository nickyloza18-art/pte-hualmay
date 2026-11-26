<?php
require_once('config.php');

try {
    $cn = mysqli_connect(
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME
    );

    mysqli_set_charset($cn, 'utf8mb4');

} catch (mysqli_sql_exception $e) {
    die('No se pudo conectar a la base de datos.');
}
