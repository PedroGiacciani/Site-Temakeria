<?php
session_start();
require_once 'db.php';

$usuario = usuario_logado();
if (!$usuario) {
    header('Location: login.php');
    exit;
}
header('Location: home.php');
exit;
