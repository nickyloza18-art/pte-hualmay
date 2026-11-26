<?php
include('auth.php');


$id = (int) $_SESSION['usuario'];

$sql = "SELECT dni, nombres, apellidos, usuario, rol
        FROM usuarios
        WHERE id = $id";

$f = mysqli_query($cn, $sql);
$r = mysqli_fetch_assoc($f);

if (!$r) {
    header('Location: cerrarsesion.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Principal - Responsable PTE</title>

    <link
        rel="stylesheet"
        href="assets/estilos.css"
    >
</head>

<body>

    <?php include('cabecera.php'); ?>

    <center>
        <h3>DATOS DEL RESPONSABLE</h3>
    </center>

    <table
        border="1"
        align="center"
        cellpadding="8"
    >

        <tr>
            <td>DNI</td>
            <td><?php echo e($r['dni']); ?></td>
        </tr>

        <tr>
            <td>Nombres</td>
            <td><?php echo e($r['nombres']); ?></td>
        </tr>

        <tr>
            <td>Apellidos</td>
            <td><?php echo e($r['apellidos']); ?></td>
        </tr>

        <tr>
            <td>Usuario</td>
            <td><?php echo e($r['usuario']); ?></td>
        </tr>

        <tr>
            <td>Rol</td>
            <td><?php echo e($r['rol']); ?></td>
        </tr>

    </table>

    <center>
        <p>
            Avance inicial: inicio de sesión
            y perfil del responsable.
        </p>
    </center>

</body>
</html>
