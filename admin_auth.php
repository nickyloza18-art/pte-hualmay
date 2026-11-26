<?php
require_once('funciones.php');

if (
    ($_SESSION['auth_admin'] ?? 0) !== 1 ||
    ($_SESSION['rol'] ?? '') !== 'admin'
) {
    header('Location: login.php');
    exit();
}
