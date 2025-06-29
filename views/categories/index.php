<?php
// views/categories/index.php
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-tags"></i> Catégories de Recettes</h1>
        <p>Explorez nos recettes organisées par catégories pour trouver facilement ce que vous cherchez</p>
    </div>

    <!-- Statistiques générales -->
    <div class="categories-stats">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-content">
                    <h3><?= count($categoriesByType['saison'] ?? []) ?></h3>
                    <p>Saisons</p>
                </div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="stat-content">
                    <h3><?= count($categoriesByType['type_plat']['categories'] ?? []) ?></h3>
                    <p>Types de plats</p>
                </div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="stat-content">
                    <h3><?= count($categoriesByType['origine']['continents'] ?? []) ?></h3>
                    <p>Continents</p>
                </div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <div class="stat-content">
                    <h3><?= count($categoriesByType['regime'] ?? []) ?></h3>
                    <p>Régimes spéciaux</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation rapide -->
    <div class="quick-nav">
        <h2>Navigation rapide</h2>
        <div class="nav-buttons">
            <a href="#saisons" class="nav-btn">
                <i class="fas fa-calendar"></i>
                <span>Saisons</span>
            </a>
            <a href="#types-plats" class="nav-btn">
                <i class="fas fa-utensils"></i>
                <span>Types de plats</span>
            </a>
            <a href="#origines" class="nav-btn">
                <i class="fas fa-globe"></i>
                <span>Origines</span>
            </a>
            <a href="#regimes" class="nav-btn">
                <i class="fas fa-leaf"></i>
                <span>Régimes</span>
            </a>
        </div>
    </div>

    <!-- Catégories par type -->
    <?php
    $typeConfig = [
        'saison' => [
            'title' => 'Par saison',
            'icon' => 'fas fa-calendar',
            'description' => 'Découvrez les recettes parfaites pour chaque saison',
            'anchor' => 'saisons',
            'color' => '#28a745'
        ],
        'type_plat' => [
            'title' => 'Types de plats',
            'icon' => 'fas fa-utensils',
            'description' => 'Organisez vos repas par type de plat',
            'anchor' => 'types-plats',
            'color' => '#667eea'
        ],
        'origine' => [
            'title' => 'Origines culinaires',
            'icon' => 'fas fa-globe',
            'description' => 'Voyagez à travers les saveurs du monde',
            'anchor' => 'origines',
            'color' => '#fd7e14'
        ],
        'regime' => [
            'title' => 'Régimes alimentaires',
            'icon' => 'fas fa-leaf',
            'description' => 'Recettes adaptées à vos besoins alimentaires',
            'anchor' => 'regimes',
            'color' => '#20c997'
        ]
    ];
    ?>

    <?php foreach ($categoriesByType as $type => $categories): ?>
        <?php if (empty($categories)) continue; ?>

        <section class="category-section" id="<?= $typeConfig[$type]['anchor'] ?? $type ?>">
            <div class="section-header" style="border-color: <?= $typeConfig[$type]['color'] ?>;">
                <div class="section-title">
                    <h2>
                        <i class="<?= $typeConfig[$type]['icon'] ?>" style="color: <?= $typeConfig[$type]['color'] ?>;"></i>
                        <?= $typeConfig[$type]['title'] ?? ucfirst($type) ?>
                    </h2>
                    <p class="section-description">
                        <?= $typeConfig[$type]['description'] ?? '' ?>
                    </p>
                </div>

                <div class="section-stats">
                    <?php if ($type === 'origine'): ?>
                        <span class="total-categories">
                            <?= count($categories['continents'] ?? []) ?> continent<?= count($categories['continents'] ?? []) > 1 ? 's' : '' ?>
                        </span>
                        <span class="total-recipes">
                            <?php 
                            $totalRecipes = 0;
                            if (isset($categories['continents'])) {
                                foreach ($categories['continents'] as $continent) {
                                    $totalRecipes += $continent['nb_recettes'] ?? 0;
                                    if (isset($continent['pays'])) {
                                        foreach ($continent['pays'] as $pays) {
                                            $totalRecipes += $pays['nb_recettes'] ?? 0;
                                        }
                                    }
                                }
                            }
                            echo $totalRecipes;
                            ?> recette<?= $totalRecipes > 1 ? 's' : '' ?>
                        </span>
                    <?php elseif ($type === 'type_plat'): ?>
                        <span class="total-categories">
                            <?= count($categories['categories'] ?? []) ?> catégorie<?= count($categories['categories'] ?? []) > 1 ? 's' : '' ?>
                        </span>
                        <span class="total-recipes">
                            <?php 
                            $totalRecipes = 0;
                            if (isset($categories['categories'])) {
                                foreach ($categories['categories'] as $categorie) {
                                    $totalRecipes += $categorie['nb_recettes'] ?? 0;
                                    if (isset($categorie['sous_categories'])) {
                                        foreach ($categorie['sous_categories'] as $sous_cat) {
                                            $totalRecipes += $sous_cat['nb_recettes'] ?? 0;
                                        }
                                    }
                                }
                            }
                            echo $totalRecipes;
                            ?> recette<?= $totalRecipes > 1 ? 's' : '' ?>
                        </span>
                    <?php else: ?>
                        <span class="total-categories">
                            <?= count($categories) ?> catégorie<?= count($categories) > 1 ? 's' : '' ?>
                        </span>
                        <span class="total-recipes">
                            <?= array_sum(array_column($categories, 'nb_recettes')) ?> recette<?= array_sum(array_column($categories, 'nb_recettes')) > 1 ? 's' : '' ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($type === 'origine'): ?>
                <!-- Affichage spécial pour les origines avec continents -->
                <div class="continents-container">
                    <?php if (isset($categories['continents'])): ?>
                        <?php foreach ($categories['continents'] as $continent): ?>
                            <div class="continent-section">
                                <div class="continent-header">
                                    <h3>
                                        <i class="fas fa-map-marked-alt" style="color: <?= $typeConfig[$type]['color'] ?>;"></i>
                                        <?= htmlspecialchars($continent['nom']) ?>
                                    </h3>
                                    <p class="continent-description">
                                        <?= htmlspecialchars($continent['description']) ?>
                                    </p>
                                    
                                    <?php if (($continent['nb_recettes'] ?? 0) > 0): ?>
                                        <a href="/recipes?category=<?= $continent['id'] ?>" class="continent-link">
                                            <i class="fas fa-book"></i>
                                            <?= $continent['nb_recettes'] ?> recette<?= $continent['nb_recettes'] > 1 ? 's' : '' ?> générales
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($continent['pays'])): ?>
                                    <div class="countries-grid">
                                        <?php foreach ($continent['pays'] as $pays): ?>
                                            <a href="/recipes?category=<?= $pays['id'] ?>"
                                               class="country-card"
                                               data-continent="<?= $continent['nom'] ?>">

                                                <div class="country-flag" style="background: <?= $typeConfig[$type]['color'] ?>;">
                                                    <i class="fas fa-flag"></i>
                                                </div>

                                                <div class="country-content">
                                                    <h4><?= htmlspecialchars($pays['nom']) ?></h4>
                                                    <p class="country-description">
                                                        <?= htmlspecialchars($pays['description']) ?>
                                                    </p>

                                                    <div class="country-stats">
                                                        <div class="recipes-count">
                                                            <i class="fas fa-book"></i>
                                                            <span><?= $pays['nb_recettes'] ?> recette<?= $pays['nb_recettes'] > 1 ? 's' : '' ?></span>
                                                        </div>

                                                        <?php if ($pays['nb_recettes'] > 0): ?>
                                                            <div class="view-recipes">
                                                                <span>Découvrir</span>
                                                                <i class="fas fa-arrow-right"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <?php if ($pays['nb_recettes'] > 0): ?>
                                                    <div class="country-badge">
                                                        <?= $pays['nb_recettes'] ?>
                                                    </div>
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            <?php elseif ($type === 'type_plat'): ?>
                <!-- Affichage spécial pour les types de plats avec catégories/sous-catégories -->
                <div class="type-plat-container">
                    <?php if (isset($categories['categories'])): ?>
                        <?php foreach ($categories['categories'] as $categorie): ?>
                            <div class="type-plat-section">
                                <div class="type-plat-header">
                                    <h3>
                                        <i class="<?= $typeConfig[$type]['icon'] ?>" style="color: <?= $typeConfig[$type]['color'] ?>;"></i>
                                        <?= htmlspecialchars($categorie['nom']) ?>
                                    </h3>
                                    <p class="type-plat-description">
                                        <?= htmlspecialchars($categorie['description']) ?>
                                    </p>
                                    
                                    <?php if (($categorie['nb_recettes'] ?? 0) > 0): ?>
                                        <a href="/recipes?category=<?= $categorie['id'] ?>" class="type-plat-link">
                                            <i class="fas fa-book"></i>
                                            <?= $categorie['nb_recettes'] ?> recette<?= $categorie['nb_recettes'] > 1 ? 's' : '' ?> générales
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($categorie['sous_categories'])): ?>
                                    <div class="sous-categories-grid">
                                        <?php foreach ($categorie['sous_categories'] as $sous_cat): ?>
                                            <a href="/recipes?category=<?= $sous_cat['id'] ?>"
                                               class="sous-categorie-card"
                                               data-parent="<?= $categorie['nom'] ?>">

                                                <div class="sous-categorie-icon" style="background: <?= $typeConfig[$type]['color'] ?>;">
                                                    <i class="fas fa-utensils"></i>
                                                </div>

                                                <div class="sous-categorie-content">
                                                    <h4><?= htmlspecialchars($sous_cat['nom']) ?></h4>
                                                    <p class="sous-categorie-description">
                                                        <?= htmlspecialchars($sous_cat['description']) ?>
                                                    </p>

                                                    <div class="sous-categorie-stats">
                                                        <div class="recipes-count">
                                                            <i class="fas fa-book"></i>
                                                            <span><?= $sous_cat['nb_recettes'] ?> recette<?= $sous_cat['nb_recettes'] > 1 ? 's' : '' ?></span>
                                                        </div>

                                                        <?php if ($sous_cat['nb_recettes'] > 0): ?>
                                                            <div class="view-recipes">
                                                                <span>Explorer</span>
                                                                <i class="fas fa-arrow-right"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <?php if ($sous_cat['nb_recettes'] > 0): ?>
                                                    <div class="sous-categorie-badge">
                                                        <?= $sous_cat['nb_recettes'] ?>
                                                    </div>
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Affichage normal pour les autres types -->
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <a href="/recipes?category=<?= $category['id'] ?>"
                           class="category-card"
                           data-category-type="<?= $type ?>">

                            <div class="category-icon" style="background: <?= $typeConfig[$type]['color'] ?>;">
                                <i class="<?= $typeConfig[$type]['icon'] ?>"></i>
                            </div>

                            <div class="category-content">
                                <h3><?= htmlspecialchars($category['nom']) ?></h3>
                                <p class="category-description">
                                    <?= htmlspecialchars($category['description']) ?>
                                </p>

                                <div class="category-stats">
                                    <div class="recipes-count">
                                        <i class="fas fa-book"></i>
                                        <span><?= $category['nb_recettes'] ?> recette<?= $category['nb_recettes'] > 1 ? 's' : '' ?></span>
                                    </div>

                                    <?php if ($category['nb_recettes'] > 0): ?>
                                        <div class="view-recipes">
                                            <span>Voir les recettes</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($category['nb_recettes'] > 0): ?>
                                <div class="category-badge">
                                    <?= $category['nb_recettes'] ?>
                                </div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>

    <!-- Section vide si aucune catégorie -->
    <?php if (empty($categoriesByType)): ?>
        <div class="empty-state">
            <i class="fas fa-tags"></i>
            <h3>Aucune catégorie disponible</h3>
            <p>Les catégories seront affichées ici une fois que des recettes seront ajoutées.</p>
            <a href="/recipes/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter une recette
            </a>
        </div>
    <?php endif; ?>

    <!-- Suggestions d'exploration -->
    <div class="exploration-section">
        <h2><i class="fas fa-compass"></i> Suggestions d'exploration</h2>
        <div class="suggestions-grid">
            <div class="suggestion-card">
                <i class="fas fa-random"></i>
                <h3>Recette surprise</h3>
                <p>Laissez-vous tenter par une recette choisie au hasard</p>
                <a href="/recipes?random=1" class="btn btn-outline">
                    <i class="fas fa-dice"></i> Surprise !
                </a>
            </div>

            <div class="suggestion-card">
                <i class="fas fa-clock"></i>
                <h3>Recettes rapides</h3>
                <p>Des plats délicieux en moins de 30 minutes</p>
                <a href="/recipes?duration=quick" class="btn btn-outline">
                    <i class="fas fa-bolt"></i> Rapide & bon
                </a>
            </div>

            <div class="suggestion-card">
                <i class="fas fa-chart-line"></i>
                <h3>Faciles pour débuter</h3>
                <p>Parfait si vous commencez en cuisine</p>
                <a href="/recipes?difficulty=facile" class="btn btn-outline">
                    <i class="fas fa-graduation-cap"></i> Pour débuter
                </a>
            </div>
        </div>
    </div>

    <!-- Actions principales -->
    <div class="main-actions">
        <a href="/recipes" class="btn btn-primary">
            <i class="fas fa-book"></i> Voir toutes les recettes
        </a>
        <a href="/recipes/create" class="btn btn-secondary">
            <i class="fas fa-plus"></i> Ajouter une recette
        </a>
    </div>
</div>

<style>
    .page-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .page-header h1 {
        color: #333;
        margin-bottom: 1rem;
        font-size: 2.5rem;
    }

    .page-header p {
        color: #666;
        font-size: 1.2rem;
        max-width: 600px;
        margin: 0 auto;
    }

    .categories-stats {
        margin-bottom: 3rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
    }

    .stat-item {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        text-align: center;
        transition: transform 0.3s ease;
    }

    .stat-item:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        font-size: 2.5rem;
        color: #667eea;
        margin-bottom: 1rem;
    }

    .stat-content h3 {
        font-size: 2rem;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .stat-content p {
        color: #666;
        font-weight: 500;
    }

    .quick-nav {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 3rem;
        text-align: center;
    }

    .quick-nav h2 {
        margin-bottom: 2rem;
        color: white;
    }

    .nav-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .nav-btn {
        background: rgba(255,255,255,0.2);
        color: white;
        text-decoration: none;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        min-width: 120px;
    }

    .nav-btn:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-2px);
    }

    .nav-btn i {
        font-size: 1.5rem;
    }

    .category-section {
        margin-bottom: 4rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 3px solid;
    }

    .section-title h2 {
        color: #333;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 2rem;
    }

    .section-description {
        color: #666;
        font-size: 1.1rem;
        margin: 0;
    }

    .section-stats {
        text-align: right;
        color: #666;
    }

    .section-stats span {
        display: block;
        font-weight: 500;
    }

    .total-categories {
        color: #333;
        font-size: 1.1rem;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .category-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        text-decoration: none;
        color: inherit;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        color: inherit;
    }

    .category-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .category-content h3 {
        color: #333;
        margin-bottom: 1rem;
        font-size: 1.3rem;
    }

    .category-description {
        color: #666;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .category-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .recipes-count {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #667eea;
        font-weight: 500;
    }

    .view-recipes {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #666;
        font-size: 0.9rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .category-card:hover .view-recipes {
        opacity: 1;
    }

    .category-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: #667eea;
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
    }

    /* Styles spécifiques pour les types de plats hiérarchiques */
    .type-plat-container {
        display: flex;
        flex-direction: column;
        gap: 3rem;
    }

    .type-plat-section {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-left: 5px solid #667eea;
    }

    .type-plat-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .type-plat-header h3 {
        color: #333;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.8rem;
    }

    .type-plat-description {
        color: #666;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        line-height: 1.6;
    }

    .type-plat-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border: 1px solid #667eea;
        border-radius: 20px;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .type-plat-link:hover {
        background: #667eea;
        color: white;
    }

    .sous-categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .sous-categorie-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }

    .sous-categorie-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        color: inherit;
        background: white;
    }

    .sous-categorie-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }

    .sous-categorie-content h4 {
        color: #333;
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .sous-categorie-description {
        color: #666;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 1rem;
    }

    .sous-categorie-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sous-categorie-stats .recipes-count {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #667eea;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .sous-categorie-stats .view-recipes {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #666;
        font-size: 0.8rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .sous-categorie-card:hover .view-recipes {
        opacity: 1;
    }

    .sous-categorie-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: #667eea;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.8rem;
    }

    /* Styles spécifiques pour les continents */
    .continents-container {
        display: flex;
        flex-direction: column;
        gap: 3rem;
    }

    .continent-section {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-left: 5px solid #fd7e14;
    }

    .continent-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .continent-header h3 {
        color: #333;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.8rem;
    }

    .continent-description {
        color: #666;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        line-height: 1.6;
    }

    .continent-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #fd7e14;
        text-decoration: none;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border: 1px solid #fd7e14;
        border-radius: 20px;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .continent-link:hover {
        background: #fd7e14;
        color: white;
    }

    .countries-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .country-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }

    .country-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        color: inherit;
        background: white;
    }

    .country-flag {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }

    .country-content h4 {
        color: #333;
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .country-description {
        color: #666;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 1rem;
    }

    .country-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .country-stats .recipes-count {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #fd7e14;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .country-stats .view-recipes {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #666;
        font-size: 0.8rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .country-card:hover .view-recipes {
        opacity: 1;
    }

    .country-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: #fd7e14;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.8rem;
    }

    .exploration-section {
        background: #f8f9fa;
        padding: 3rem 2rem;
        border-radius: 15px;
        margin-bottom: 3rem;
    }

    .exploration-section h2 {
        text-align: center;
        color: #333;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .suggestions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
    }

    .suggestion-card {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .suggestion-card:hover {
        transform: translateY(-3px);
    }

    .suggestion-card i {
        font-size: 2rem;
        color: #667eea;
        margin-bottom: 1rem;
    }

    .suggestion-card h3 {
        color: #333;
        margin-bottom: 1rem;
    }

    .suggestion-card p {
        color: #666;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .main-actions {
        text-align: center;
        padding: 2rem 0;
        border-top: 1px solid #e9ecef;
    }

    .main-actions .btn {
        margin: 0 0.5rem;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #666;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #ccc;
    }

    .empty-state h3 {
        margin-bottom: 1rem;
        color: #333;
    }

    /* Responsive pour les continents et types de plats */
    @media (max-width: 768px) {
        .continent-section, .type-plat-section {
            padding: 1.5rem;
        }

        .countries-grid, .sous-categories-grid {
            grid-template-columns: 1fr;
        }

        .continent-header h3, .type-plat-header h3 {
            font-size: 1.5rem;
        }

        .section-header {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .section-stats {
            text-align: center;
        }

        .nav-buttons {
            grid-template-columns: repeat(2, 1fr);
        }

        .categories-grid {
            grid-template-columns: 1fr;
        }

        .suggestions-grid {
            grid-template-columns: 1fr;
        }

        .main-actions .btn {
            display: block;
            margin: 0.5rem 0;
            width: 100%;
        }
    }

    /* Animation au scroll */
    .category-card, .country-card, .sous-categorie-card {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.6s ease forwards;
    }

    .category-card:nth-child(2), .country-card:nth-child(2), .sous-categorie-card:nth-child(2) { animation-delay: 0.1s; }
    .category-card:nth-child(3), .country-card:nth-child(3), .sous-categorie-card:nth-child(3) { animation-delay: 0.2s; }
    .category-card:nth-child(4), .country-card:nth-child(4), .sous-categorie-card:nth-child(4) { animation-delay: 0.3s; }
    .country-card:nth-child(5), .sous-categorie-card:nth-child(5) { animation-delay: 0.4s; }
    .country-card:nth-child(6), .sous-categorie-card:nth-child(6) { animation-delay: 0.5s; }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Smooth scroll */
    html {
        scroll-behavior: smooth;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation des cartes au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observer toutes les cartes qui ne sont pas encore visibles
        document.querySelectorAll('.category-card, .country-card, .sous-categorie-card').forEach((card, index) => {
            if (index > 3) { // Les 4 premières ont déjà l'animation CSS
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            }
        });

        // Compteur animé pour les stats
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 30; // Animation sur 30 frames
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 16); // ~60fps
        }

        // Animer les compteurs quand ils deviennent visibles
        const statsObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target.querySelector('h3');
                    const target = parseInt(counter.textContent);
                    if (target > 0) {
                        animateCounter(counter, target);
                    }
                    statsObserver.unobserve(entry.target);
                }
            });
        });

        document.querySelectorAll('.stat-item').forEach(item => {
            statsObserver.observe(item);
        });

        // Smooth scroll pour la navigation rapide
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animation d'entrée pour les sections de continents et types de plats
        const sectionObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        document.querySelectorAll('.continent-section, .type-plat-section').forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(30px)';
            section.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
            sectionObserver.observe(section);
        });
    });
</script>