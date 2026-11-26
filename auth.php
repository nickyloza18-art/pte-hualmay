<?php
require_once('funciones.php');

if (
    ($_SESSION['auth'] ?? 0) !== 1 ||
    ($_SESSION['rol'] ?? '') !== 'responsable'
) {
    header('Location: login.php');
    exit();
}
