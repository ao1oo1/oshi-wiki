<?php
require __DIR__ . '/../app/helpers.php';
$_SESSION = [];
session_destroy();
header('Location: ' . base_url('index.php'));
exit;
