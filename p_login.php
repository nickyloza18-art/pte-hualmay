<?php
require_once('funciones.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

chk();

$usu = trim($_POST['txtusuario'] ?? '');
$pass = (string) ($_POST['txtpassword'] ?? '');

$sql = "SELECT id, usuario, clave, rol
        FROM usuarios
        WHERE usuario = ?
        AND estado = 1
        LIMIT 1";

$r = consulta1($sql, [$usu]);

if (!$r || !password_verify($pass, $r['clave'])) {
    header('Location: login.php?error=1');
    exit();
}

if (!in_array($r['rol'], ['admin', 'responsable'], true)) {
    header('Location: login.php?error=1');
    exit();
}

session_regenerate_id(true);

unset($_SESSION['auth'], $_SESSION['auth_admin']);

$_SESSION['usuario'] = (int) $r['id'];
$_SESSION['rol'] = $r['rol'];

$_SESSION['u'] = [
    'id' => (int) $r['id'],
    'rol' => $r['rol']
];

if ($r['rol'] === 'admin') {

    $_SESSION['auth_admin'] = 1;

    header('Location: admin_principal.php');

} else {

    $_SESSION['auth'] = 1;

    header('Location: principal.php');
}

exit();
