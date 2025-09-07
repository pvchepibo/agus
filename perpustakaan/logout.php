<?php
require_once 'config.php';

// Hancurkan semua session
session_destroy();

// Arahkan ke halaman utama publik
redirect('index.php');
?>