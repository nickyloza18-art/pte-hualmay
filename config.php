<?php

session_start();

const DB_HOST = 'localhost';
const DB_NAME = 'pte_hualmay';
const DB_USER = 'root';
const DB_PASS = '';
const ENTIDAD = 'Municipalidad Distrital de Hualmay';

function db()
{
    static $conexion;

    if (!$conexion) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $conexion = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    return $conexion;
}

function q($sql, $parametros = [])
{
    $consulta = db()->prepare($sql);
    $consulta->execute($parametros);

    return $consulta;
}

function e($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

function flash($mensaje = null)
{
    if ($mensaje) {
        $_SESSION['flash'] = $mensaje;

        return;
    }

    $texto = $_SESSION['flash'] ?? '';
    unset($_SESSION['flash']);

    return $texto;
}

function page()
{
    return max(1, (int) ($_GET['p'] ?? 1));
}

function pager($total, $porPagina, $paginaActual)
{
    $paginas = max(1, (int) ceil($total / $porPagina));

    if ($paginas < 2) {
        return '';
    }

    $parametros = $_GET;
    $html = '<nav><ul class="pagination pagination-sm flex-wrap">';

    for ($i = 1; $i <= $paginas; $i++) {
        $parametros['p'] = $i;
        $activo = ($i == $paginaActual) ? ' active' : '';
        $url = e(http_build_query($parametros));
        $html .= '<li class="page-item' . $activo . '"><a class="page-link" href="?' . $url . '">' . $i . '</a></li>';
    }

    return $html . '</ul></nav>';
}

function user()
{
    return $_SESSION['u'] ?? null;
}

function auth($roles = ['admin', 'responsable'])
{
    if (!user() || !in_array(user()['rol'], $roles)) {
        redirect('login.php');
    }
}

function csrf()
{
    $_SESSION['t'] = $_SESSION['t'] ?? bin2hex(random_bytes(16));

    return '<input type="hidden" name="t" value="' . $_SESSION['t'] . '">';
}

function chk()
{
    $valido = hash_equals($_SESSION['t'] ?? '', $_POST['t'] ?? '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$valido) {
        die('Token inválido');
    }
}

function audit($resolucionId, $numero, $accion, $detalle = '')
{
    q(
        'INSERT INTO auditoria(resolucion_id, numero, accion, usuario_id, detalle) VALUES(?,?,?,?,?)',
        [$resolucionId, $numero, $accion, user()['id'], $detalle]
    );
}

function filtrar($filtros, &$parametros, $publico)
{
    $parametros = [];
    $condiciones = [$publico ? "r.estado = 'publicado'" : "r.estado <> 'eliminado'"];

    $numero = trim($filtros['numero'] ?? '');

    if ($numero !== '') {
        $condiciones[] = 'r.numero LIKE ?';
        $parametros[] = "%$numero%";
    }

    $texto = trim($filtros['texto'] ?? '');

    if ($texto !== '') {
        $condiciones[] = '(r.titulo LIKE ? OR r.descripcion LIKE ?)';
        $parametros[] = "%$texto%";
        $parametros[] = "%$texto%";
    }

    if (!empty($filtros['tipo'])) {
        $condiciones[] = 'r.tipo_id = ?';
        $parametros[] = (int) $filtros['tipo'];
    }

    if (!$publico && !empty($filtros['usuario'])) {
        $condiciones[] = 'r.usuario_id = ?';
        $parametros[] = (int) $filtros['usuario'];
    }

    $fechas = [
        'pd' => ['r.fecha_publicacion', '>='],
        'ph' => ['r.fecha_publicacion', '<='],
        'sd' => ['DATE(r.fecha_subida)', '>='],
        'sh' => ['DATE(r.fecha_subida)', '<='],
    ];

    foreach ($fechas as $campo => [$columna, $operador]) {
        $esFechaSubida = ($campo[0] === 's');

        if (!empty($filtros[$campo]) && !($publico && $esFechaSubida)) {
            $condiciones[] = "$columna $operador ?";
            $parametros[] = $filtros[$campo];
        }
    }

    return implode(' AND ', $condiciones);
}

function select_filtro($nombre, $opciones, $etiqueta)
{
    $html = '<select name="' . $nombre . '" class="form-select form-select-sm">';
    $html .= '<option value="">' . $etiqueta . '</option>';

    foreach ($opciones as $opcion) {
        $marcado = (($_GET[$nombre] ?? '') == $opcion['id']) ? ' selected' : '';
        $html .= '<option value="' . $opcion['id'] . '"' . $marcado . '>' . e($opcion['nombre']) . '</option>';
    }

    return $html . '</select>';
}

function filtros_form($publico = false)
{
    $valor = fn ($campo) => e($_GET[$campo] ?? '');
    $tipos = q('SELECT id, nombre FROM tipos_documento WHERE estado = 1 ORDER BY id')->fetchAll();

    $html = '<form class="row g-2 mb-3 p-3 bg-white border rounded" method="get">';
    $html .= '<div class="col-md-2"><input name="numero" value="' . $valor('numero') . '" class="form-control form-control-sm" placeholder="N.º de resolución"></div>';
    $html .= '<div class="col-md-3"><input name="texto" value="' . $valor('texto') . '" class="form-control form-control-sm" placeholder="Palabras de la descripción"></div>';
    $html .= '<div class="col-md-3">' . select_filtro('tipo', $tipos, 'SELECCIONE TIPO DE DOCUMENTO') . '</div>';

    if (!$publico) {
        $usuarios = q("SELECT id, CONCAT(nombres, ' ', apellidos) AS nombre FROM usuarios ORDER BY nombres")->fetchAll();
        $html .= '<div class="col-md-4">' . select_filtro('usuario', $usuarios, 'Todos los usuarios') . '</div>';
    }

    $fechas = ['pd' => 'Publicada desde', 'ph' => 'Publicada hasta'];

    if (!$publico) {
        $fechas += ['sd' => 'Subida desde', 'sh' => 'Subida hasta'];
    }

    foreach ($fechas as $campo => $etiqueta) {
        $html .= '<div class="col-6 col-md-3"><label class="form-label small mb-0">' . $etiqueta . '</label>'
            . '<input type="date" name="' . $campo . '" value="' . $valor($campo) . '" class="form-control form-control-sm"></div>';
    }

    $html .= '<div class="col-auto align-self-end">'
        . '<button class="btn btn-sm btn-dark">Buscar</button> '
        . '<a class="btn btn-sm btn-outline-secondary" href="?">Limpiar</a></div>';

    return $html . '</form>';
}

function menu_para($usuario)
{
    if (!$usuario) {
        return [];
    }

    $esAdmin = ($usuario['rol'] === 'admin');
    $menu = ['panel.php' => 'Inicio', 'perfil.php' => 'Mi perfil'];

    if ($esAdmin) {
        $menu['usuarios.php'] = 'Usuarios PTE';
        $menu['tipos.php'] = 'Tipos de documento';
    }

    $menu['resoluciones.php'] = 'Nueva resolución';
    $menu['reportes.php'] = 'Resoluciones';
    $menu['estadisticas.php'] = 'Estadísticas';

    if ($esAdmin) {
        $menu['auditoria.php'] = 'Editadas o eliminadas';
    }

    $menu['logout.php'] = 'Cerrar sesión';

    return $menu;
}

function head($titulo, $hero = false)
{
    $usuario = user();
    $menu = menu_para($usuario);
    $paginaActual = basename($_SERVER['SCRIPT_NAME']);
    $inicio = $usuario ? 'panel.php' : 'index.php';
    ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · PTE Hualmay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/estilos.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= $inicio ?>">
            <img src="assets/logo.png" alt="Escudo">PTE · <?= ENTIDAD ?>
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div id="menu" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <?php foreach ($menu as $enlace => $texto) : ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $paginaActual === $enlace ? ' active' : '' ?>" href="<?= $enlace ?>"><?= $texto ?></a>
                    </li>
                <?php endforeach; ?>
                <?php if (!$usuario) : ?>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-warning fw-semibold" href="login.php">Acceso administrador</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
    <?php if ($hero) : ?>
<section class="hero">
    <div class="container">
        <h1 class="display-6">Portal de Transparencia</h1>
        <p>Consulte y descargue las resoluciones, acuerdos y ordenanzas de <?= ENTIDAD ?>.</p>
    </div>
</section>
    <?php endif; ?>
<main class="container py-4">
    <?php if (!$hero) : ?>
    <h1 class="h4 mb-3"><?= e($titulo) ?></h1>
    <?php endif; ?>
    <?php if ($mensaje = flash()) : ?>
    <div class="alert alert-info"><?= e($mensaje) ?></div>
    <?php endif; ?>
    <?php
}

function foot()
{
    ?>
</main>
<footer class="text-center py-4">
    Portal de Transparencia · Ley N.º 27806 · <?= ENTIDAD ?><br>
    Desarrollado por Sandro Loza · CEO y Director de Tecnología e Innovación DigitalHuacho, Perú
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
    <?php
}
