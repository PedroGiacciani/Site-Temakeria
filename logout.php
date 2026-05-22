<?php
session_start();
require_once 'db.php';

usuario_logout();
header('Location: home.php');
exit;
