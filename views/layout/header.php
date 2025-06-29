<?php // views/layout/header.php ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Les recettes de Queen Mum</title>
    <link rel="icon" type="image/x-icon" href="/public/images/chef_7168283.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/public/images/chef_7168283.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/public/images/chef_7168283.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/public/images/chef_7168283.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/public/css/style.css" rel="stylesheet">
</head>
<body>
<header class="header">
    <div class="container">
        <nav class="navbar">
            <div class="nav-brand">
                <a href="/">
                    <i class="fas fa-utensils"></i>
                    Les recettes de Queen Mum
                </a>
            </div>
            
            <div class="nav-menu">
                <a href="/" class="nav-link">
                    <i class="fas fa-home"></i> Accueil
                </a>
                <a href="/recipes" class="nav-link">
                    <i class="fas fa-book"></i> Recettes
                </a>
                <a href="/categories" class="nav-link">
                    <i class="fas fa-tags"></i> Catégories
                </a>
                <a href="/blog" class="nav-link">
                    <i class="fas fa-blog"></i> La Cuisine
                </a>
                <a href="/recipes/create" class="nav-link btn-primary">
                    <i class="fas fa-plus"></i> Nouvelle Recette
                </a>
            </div>
            
            <div class="nav-search">
                <form action="/recipes" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Rechercher une recette..."
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <div class="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </div>
</header>

<main class="main">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <ul>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>