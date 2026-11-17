
CREATE DATABASE IF NOT EXISTS pte_huaura
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pte_huaura;

CREATE TABLE usuarios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    dni        CHAR(8)      NOT NULL UNIQUE,
    nombres    VARCHAR(80)  NOT NULL,
    apellidos  VARCHAR(80)  NOT NULL,
    email      VARCHAR(120),
    telefono   VARCHAR(20),
    usuario    VARCHAR(40)  NOT NULL UNIQUE,
    clave      VARCHAR(255) NOT NULL,
    rol        ENUM('admin', 'responsable') NOT NULL DEFAULT 'responsable',
    estado     TINYINT      NOT NULL DEFAULT 1, 
    foto       VARCHAR(120),
    creado     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tipos_documento (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    nombre  VARCHAR(100) NOT NULL UNIQUE,
    estado  TINYINT      NOT NULL DEFAULT 1
);

CREATE TABLE resoluciones (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    tipo_id            INT          NOT NULL,
    numero             VARCHAR(50)  NOT NULL,
    titulo             VARCHAR(200) NOT NULL,
    descripcion        TEXT,
    fecha_publicacion  DATE         NOT NULL,
    fecha_subida       DATETIME     DEFAULT CURRENT_TIMESTAMP,
    usuario_id         INT          NOT NULL,
    archivo            VARCHAR(120) NOT NULL,
    estado             ENUM('publicado', 'borrador', 'eliminado') NOT NULL DEFAULT 'publicado',
    descargas          INT          NOT NULL DEFAULT 0,

    FOREIGN KEY (tipo_id)    REFERENCES tipos_documento (id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
);

CREATE TABLE auditoria (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    resolucion_id  INT,
    numero         VARCHAR(50),
    accion         ENUM('creada', 'editada', 'eliminada') NOT NULL,
    usuario_id     INT          NOT NULL,
    detalle        VARCHAR(255),
    fecha          DATETIME     DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO tipos_documento (nombre) VALUES
    ('ACTA DE SESIÓN EXTRAORDINARIA'),
    ('ACTA DE SESIÓN ORDINARIA'),
    ('ACUERDO DE CONCEJO'),
    ('ORDENANZAS MUNICIPALES'),
    ('DECRETO DE ALCALDÍA'),
    ('RESOLUCIÓN DE ALCALDÍA'),
    ('RESOLUCIÓN EXTRAORDINARIA'),
    ('RESOLUCIÓN GERENCIAL'),
    ('RESOLUCIÓN SUB GERENCIAL'),
    ('FE DE ERRATAS'),
    ('RESOLUCIÓN JEFATURAL');
