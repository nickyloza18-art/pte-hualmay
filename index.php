<?php

/**
 * Portal público: búsqueda y descarga de resoluciones.
 */

require 'config.php';

/* ---------------------------------------------------------------------------
 * 1. DATOS: filtros, total y resoluciones de la página actual
 * ------------------------------------------------------------------------- */
$parametros = [];
$condicion = filtrar($_GET, $parametros, true);   // true = solo resoluciones publicadas
$porPagina = 10;
$pagina = page();
$desde = ($pagina - 1) * $porPagina;

$total = q(
    "SELECT COUNT(*) FROM resoluciones r WHERE $condicion",
    $parametros
)->fetchColumn();

$resoluciones = q(
    "SELECT r.*, t.nombre AS tipo
       FROM resoluciones r
       JOIN tipos_documento t ON t.id = r.tipo_id
      WHERE $condicion
      ORDER BY r.fecha_publicacion DESC, r.id DESC
      LIMIT $porPagina OFFSET $desde",
    $parametros
)->fetchAll();

/* ---------------------------------------------------------------------------
 * 2. VISTA
 * ------------------------------------------------------------------------- */
head('Portal de Transparencia · Resoluciones', true);

echo filtros_form(true);
?>

<div class="small text-muted mb-2"><?= $total ?> documento(s) encontrado(s)</div>

<?php foreach ($resoluciones as $r) : ?>
    <div class="card card-body mb-2">
        <div class="d-flex justify-content-between flex-wrap gap-2">

            <div>
                <div class="small text-muted">
                    <?= e($r['tipo']) ?> · N.º <?= e($r['numero']) ?> ·
                    Publicado el <?= date('d/m/Y', strtotime($r['fecha_publicacion'])) ?>
                </div>
                <div class="fw-semibold"><?= e($r['titulo']) ?></div>
                <div class="small"><?= e($r['descripcion']) ?></div>
            </div>

            <div class="text-end">
                <a class="btn btn-dark btn-sm" href="download.php?id=<?= $r['id'] ?>">Descargar PDF</a>
                <div class="small text-muted mt-1"><?= $r['descargas'] ?> descarga(s)</div>
            </div>

        </div>
    </div>
<?php endforeach; ?>

<?php if (!$resoluciones) : ?>
    <div class="alert alert-secondary">No encontramos documentos. Pruebe con otros filtros.</div>
<?php endif; ?>

<?= pager($total, $porPagina, $pagina) ?>

<?php
foot();
