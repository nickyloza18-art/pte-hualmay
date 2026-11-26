<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const DB_HOST = 'localhost';
const DB_NAME = 'pte';
const DB_USER = 'root';
const DB_PASS = '';

const ENTIDAD = 'Municipalidad Distrital de Hualmay';


?>
