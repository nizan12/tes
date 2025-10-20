<?php
require_once __DIR__ . '/../../includes/auth.php'; // login + koneksi + session
require_once __DIR__ . '/auth.php';  
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . " | Notulen App" : "Notulen App" ?></title>
    <link rel="stylesheet" href="/include/assets/css/style.css">
</head>
<body>
<header class="header">
    <h1>📋 Notulen Rapat</h1>
    <div class="user-info">
        <span><?= $_SESSION['nama'] ?? 'User' ?> (<?= ucfirst($_SESSION['role']) ?>)</span> |
        <a href="./../public/logout.php">Logout</a>
    </div>
</header>
<div class="container">
    
