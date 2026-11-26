<?php
require_once('funciones.php');


$fila = consulta1(
    'SELECT COUNT(*) AS total FROM usuarios'
);

$hayUsuarios = (int) ($fila['total'] ?? 0);


if ($hayUsuarios === 0) {
    ejecutar(
        "INSERT INTO usuarios
        (dni, nombres, apellidos, usuario, clave, rol, estado)
        VALUES
        ('00000000', 'Administrador', 'General',
         'admin', ?, 'admin', 1)",
        [password_hash('Admin123*', PASSWORD_DEFAULT)]
    );
}

head('Iniciar sesión');
?>

<center>
    <h2>ACCESO AL PORTAL DE TRANSPARENCIA</h2>
</center>

<form
    action="p_login.php"
    method="post"
    class="card card-body mx-auto"
    style="max-width:400px"
>

    <?php echo csrf(); ?>

    <label>Usuario:</label>
    <input
        type="text"
        name="txtusuario"
        class="form-control mb-3"
        required
    >

    <label>Contraseña:</label>
    <input
        type="password"
        name="txtpassword"
        class="form-control mb-3"
        required
    >

    <input
        type="submit"
        value="Iniciar sesión"
        class="btn btn-dark"
    >

    <?php if (isset($_GET['error'])) { ?>
        <p class="text-danger mt-2">
            Usuario o contraseña incorrectos.
        </p>
    <?php } ?>

</form>

<?php foot(); ?>
