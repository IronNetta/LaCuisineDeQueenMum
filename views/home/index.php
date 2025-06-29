<?php
// views/home/index.php
?>

<!-- Hero Section avec parallax -->
<section class="hero">
    <div class="hero-background"></div>
    <div class="hero-particles"></div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1>
                    <span class="hero-greeting">Bienvenue dans</span>
                    <span class="hero-title">votre livre de recettes</span>
                </h1>
                <p class="hero-subtitle">Découvrez, créez et partagez vos recettes préférées. Une collection infinie de saveurs vous attend !</p>
                <div class="hero-actions">
                    <a href="/recipes" class="btn btn-primary btn-hero">
                        <i class="fas fa-book"></i>
                        <span>Parcourir les recettes</span>
                    </a>
                    <a href="/recipes/create" class="btn btn-secondary btn-hero">
                        <i class="fas fa-plus"></i>
                        <span>Ajouter ma recette</span>
                    </a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="floating-card">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="floating-card">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="floating-card">
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-scroll-indicator">
        <span>Découvrir</span>
        <i class="fas fa-chevron-down"></i>
    </div>
</section>

<!-- Section des dernières recettes -->
<section class="featured-recipes">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-star"></i> Dernières recettes ajoutées</h2>
            <p class="section-subtitle">Découvrez les créations culinaires les plus récentes</p>
        </div>

        <?php if (empty($latestRecipes)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3>Aucune recette pour le moment</h3>
                <p>Soyez le premier à ajouter une délicieuse recette !</p>
                <a href="/recipes/create" class="btn btn-primary btn-large">
                    <i class="fas fa-plus"></i> Créer la première recette
                </a>
            </div>
        <?php else: ?>
            <div class="recipe-grid">
                <?php foreach ($latestRecipes as $recipe): ?>
                    <div class="recipe-card" data-aos="fade-up">
                        <div class="recipe-image">
                            <?php if ($recipe['image_url']): ?>
                                <img src="<?= htmlspecialchars($recipe['image_url']) ?>"
                                     alt="<?= htmlspecialchars($recipe['titre']) ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="recipe-placeholder">
                                    <i class="fas fa-utensils"></i>
                                </div>
                            <?php endif; ?>
                            <div class="recipe-overlay">
                                <a href="/recipes/<?= $recipe['id'] ?>" class="btn btn-primary btn-floating">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <div class="recipe-quick-info">
                                    <span class="quick-time">
                                        <i class="fas fa-clock"></i>
                                        <?= $recipe['duree_preparation'] + ($recipe['duree_cuisson'] ?? 0) ?>min
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="recipe-info">
                            <h3><a href="/recipes/<?= $recipe['id'] ?>"><?= htmlspecialchars($recipe['titre']) ?></a></h3>
                            <p class="recipe-description"><?= htmlspecialchars(substr($recipe['description'], 0, 100)) ?>...</p>

                            <div class="recipe-meta">
                                <span class="difficulty difficulty-<?= $recipe['difficulte'] ?>">
                                    <i class="fas fa-chart-bar"></i>
                                    <?= ucfirst($recipe['difficulte']) ?>
                                </span>
                                <span class="duration">
                                    <i class="fas fa-clock"></i>
                                    <?= $recipe['duree_preparation'] + ($recipe['duree_cuisson'] ?? 0) ?> min
                                </span>
                                <span class="servings">
                                    <i class="fas fa-users"></i>
                                    <?= $recipe['nombre_personnes'] ?> pers.
                                </span>
                            </div>

                            <?php if (!empty($recipe['categories'])): ?>
                                <div class="recipe-categories">
                                    <?php foreach (explode(',', $recipe['categories']) as $category): ?>
                                        <span class="category-tag"><?= htmlspecialchars(trim($category)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="section-footer">
                <a href="/recipes" class="btn btn-outline btn-large">
                    <span>Voir toutes les recettes</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Section des catégories -->
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-tags"></i> Explorez par catégories</h2>
            <p class="section-subtitle">Trouvez exactement ce que vous cherchez</p>
        </div>

        <!-- Navigation par onglets -->
        <div class="category-tabs">
            <?php 
            $tabIndex = 0;
            foreach ($categoriesByType as $type => $categories): 
                $typeIcons = [
                    'saison' => 'fas fa-calendar',
                    'type_plat' => 'fas fa-utensils',
                    'origine' => 'fas fa-globe',
                    'regime' => 'fas fa-leaf'
                ];
                $typeNames = [
                    'saison' => 'Saisons',
                    'type_plat' => 'Types de plats',
                    'origine' => 'Origines',
                    'regime' => 'Régimes'
                ];
            ?>
                <button class="category-tab <?= $tabIndex === 0 ? 'active' : '' ?>" 
                        data-tab="<?= $type ?>" 
                        onclick="switchTab('<?= $type ?>')">
                    <i class="<?= $typeIcons[$type] ?? 'fas fa-tag' ?>"></i>
                    <span><?= $typeNames[$type] ?? ucfirst($type) ?></span>
                    <span class="tab-count"><?= count($categories) ?></span>
                </button>
            <?php 
                $tabIndex++;
            endforeach; 
            ?>
        </div>

        <!-- Contenu des onglets -->
        <div class="category-tabs-content">
            <?php 
            $contentIndex = 0;
            foreach ($categoriesByType as $type => $categories): ?>
                <div class="category-tab-panel <?= $contentIndex === 0 ? 'active' : '' ?>" 
                     id="tab-<?= $type ?>" 
                     data-aos="fade-up">
                    
                    <!-- Affichage limité avec "Voir plus" -->
                    <div class="category-grid limited" id="grid-<?= $type ?>">
                        <?php 
                        $maxVisible = 6; // Limite à 6 catégories visibles
                        $visibleCategories = array_slice($categories, 0, $maxVisible);
                        
                        foreach ($visibleCategories as $category): ?>
                            <a href="/recipes?category=<?= $category['id'] ?>" class="category-card-mini">
                                <div class="category-icon-mini">
                                    <?php
                                    $typeIcons = [
                                        'saison' => 'fas fa-calendar',
                                        'type_plat' => 'fas fa-utensils',
                                        'origine' => 'fas fa-globe',
                                        'regime' => 'fas fa-leaf'
                                    ];
                                    ?>
                                    <i class="<?= $typeIcons[$type] ?? 'fas fa-tag' ?>"></i>
                                </div>
                                <div class="category-info-mini">
                                    <h4><?= htmlspecialchars($category['nom']) ?></h4>
                                    <span class="recipe-count-mini">
                                        <?= $category['nb_recettes'] ?> recette<?= $category['nb_recettes'] > 1 ? 's' : '' ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Catégories cachées -->
                    <?php if (count($categories) > $maxVisible): ?>
                        <div class="category-grid hidden-categories" id="hidden-<?= $type ?>" style="display: none;">
                            <?php 
                            $hiddenCategories = array_slice($categories, $maxVisible);
                            foreach ($hiddenCategories as $category): ?>
                                <a href="/recipes?category=<?= $category['id'] ?>" class="category-card-mini">
                                    <div class="category-icon-mini">
                                        <i class="<?= $typeIcons[$type] ?? 'fas fa-tag' ?>"></i>
                                    </div>
                                    <div class="category-info-mini">
                                        <h4><?= htmlspecialchars($category['nom']) ?></h4>
                                        <span class="recipe-count-mini">
                                            <?= $category['nb_recettes'] ?> recette<?= $category['nb_recettes'] > 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <div class="show-more-container">
                            <button class="btn-show-more" onclick="toggleCategories('<?= $type ?>')">
                                <span class="show-text">Voir <?= count($hiddenCategories) ?> catégories de plus</span>
                                <span class="hide-text" style="display: none;">Voir moins</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php 
                $contentIndex++;
            endforeach; 
            ?>
        </div>

        <div class="section-footer">
            <a href="/categories" class="btn btn-outline btn-large">
                <span>Voir toutes les catégories</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Section des statistiques -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card" data-aos="zoom-in" data-aos-delay="100">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <h3 class="stat-number"><?= count($latestRecipes) ?></h3>
                    <p>Recettes disponibles</p>
                </div>
                <div class="stat-decoration"></div>
            </div>

            <div class="stat-card" data-aos="zoom-in" data-aos-delay="200">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-info">
                    <h3 class="stat-number"><?= array_sum(array_map('count', $categoriesByType)) ?></h3>
                    <p>Catégories</p>
                </div>
                <div class="stat-decoration"></div>
            </div>

            <div class="stat-card" data-aos="zoom-in" data-aos-delay="300">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-info">
                    <h3 class="stat-number">100%</h3>
                    <p>Fait avec amour</p>
                </div>
                <div class="stat-decoration"></div>
            </div>
        </div>
    </div>
</section>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        line-height: 1.6;
        color: #2d3748;
        overflow-x: hidden;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    /* =================================
       HERO SECTION
    ================================= */
    .hero {
        min-height: 100vh;
        position: relative;
        display: flex;
        align-items: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        overflow: hidden;
    }

    .hero-background {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .hero-particles {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: 
            radial-gradient(2px 2px at 20px 30px, rgba(255,255,255,0.2), transparent),
            radial-gradient(2px 2px at 40px 70px, rgba(255,255,255,0.1), transparent),
            radial-gradient(1px 1px at 90px 40px, rgba(255,255,255,0.3), transparent);
        background-repeat: repeat;
        background-size: 120px 100px;
        animation: particlesFloat 20s linear infinite;
    }

    @keyframes particlesFloat {
        0% { transform: translateY(0px); }
        100% { transform: translateY(-100px); }
    }

    .hero-content {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 4rem;
        align-items: center;
        width: 100%;
    }

    .hero-text {
        color: white;
    }

    .hero-greeting {
        display: block;
        font-size: 1.5rem;
        font-weight: 400;
        opacity: 0.9;
        margin-bottom: 0.5rem;
    }

    .hero-title {
        display: block;
        font-size: 4rem;
        font-weight: 800;
        line-height: 1.1;
        background: linear-gradient(45deg, #ffffff 0%, #f7fafc 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }

    .hero-subtitle {
        font-size: 1.25rem;
        opacity: 0.9;
        margin: 2rem 0 3rem;
        max-width: 500px;
        line-height: 1.7;
    }

    .hero-actions {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .btn-hero {
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 50px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.6s ease;
    }

    .btn-hero:hover::before {
        left: 100%;
    }

    .btn-primary.btn-hero {
        background: white;
        color: #667eea;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .btn-primary.btn-hero:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    }

    .btn-secondary.btn-hero {
        background: rgba(255,255,255,0.1);
        color: white;
        border: 2px solid rgba(255,255,255,0.3);
        backdrop-filter: blur(10px);
    }

    .btn-secondary.btn-hero:hover {
        background: rgba(255,255,255,0.2);
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }

    /* Hero Visual */
    .hero-visual {
        position: relative;
        width: 300px;
        height: 300px;
    }

    .floating-card {
        position: absolute;
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        animation: float 6s ease-in-out infinite;
    }

    .floating-card:nth-child(1) {
        top: 0;
        right: 0;
        animation-delay: 0s;
    }

    .floating-card:nth-child(2) {
        bottom: 50px;
        left: 0;
        animation-delay: 2s;
    }

    .floating-card:nth-child(3) {
        top: 50%;
        right: 50px;
        animation-delay: 4s;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
    }

    .hero-scroll-indicator {
        position: absolute;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        text-align: center;
        opacity: 0.8;
        animation: bounce 2s infinite;
    }

    .hero-scroll-indicator span {
        display: block;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
        40% { transform: translateX(-50%) translateY(-10px); }
        60% { transform: translateX(-50%) translateY(-5px); }
    }

    /* =================================
       SECTIONS COMMUNES
    ================================= */
    section {
        padding: 6rem 0;
        position: relative;
    }

    .section-header {
        text-align: center;
        margin-bottom: 4rem;
    }

    .section-header h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    .section-header h2 i {
        color: #667eea;
        font-size: 2rem;
    }

    .section-subtitle {
        font-size: 1.2rem;
        color: #4a5568;
        max-width: 600px;
        margin: 0 auto;
    }

    .section-footer {
        text-align: center;
        margin-top: 4rem;
    }

    /* Boutons */
    .btn {
        padding: 0.875rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        position: relative;
        overflow: hidden;
    }

    .btn-large {
        padding: 1.25rem 2.5rem;
        font-size: 1.1rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(113, 128, 150, 0.3);
    }

    .btn-outline {
        background: white;
        color: #4a5568;
        border: 2px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .btn-outline:hover {
        border-color: #667eea;
        color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    /* =================================
       FEATURED RECIPES
    ================================= */
    .featured-recipes {
        background: #f7fafc;
    }

    .recipe-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 2rem;
    }

    .recipe-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: all 0.4s ease;
        position: relative;
    }

    .recipe-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }

    .recipe-image {
        position: relative;
        height: 250px;
        overflow: hidden;
    }

    .recipe-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .recipe-card:hover .recipe-image img {
        transform: scale(1.1);
    }

    .recipe-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #cbd5e0;
    }

    .recipe-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.8) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .recipe-card:hover .recipe-overlay {
        opacity: 1;
    }

    .btn-floating {
        border-radius: 50%;
        width: 60px;
        height: 60px;
        padding: 0;
        justify-content: center;
        font-size: 1.25rem;
    }

    .recipe-quick-info {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        backdrop-filter: blur(10px);
    }

    .recipe-info {
        padding: 2rem;
    }

    .recipe-info h3 {
        margin-bottom: 1rem;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .recipe-info h3 a {
        color: #1a202c;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .recipe-info h3 a:hover {
        color: #667eea;
    }

    .recipe-description {
        color: #4a5568;
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .recipe-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }

    .recipe-meta span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #f7fafc;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #4a5568;
    }

    .difficulty {
        color: white !important;
        font-weight: 600;
    }

    .difficulty-facile {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%) !important;
    }

    .difficulty-moyen {
        background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%) !important;
    }

    .difficulty-difficile {
        background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%) !important;
    }

    .recipe-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .category-tag {
        padding: 0.25rem 0.75rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        font-size: 3rem;
        color: #cbd5e0;
    }

    .empty-state h3 {
        font-size: 1.75rem;
        color: #1a202c;
        margin-bottom: 1rem;
    }

    .empty-state p {
        color: #4a5568;
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }

    /* =================================
       CATEGORIES SECTION - VERSION ONGLETS
    ================================= */
    .categories-section {
        background: #f7fafc;
    }

    /* Navigation par onglets */
    .category-tabs {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 3rem;
        flex-wrap: wrap;
    }

    .category-tab {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 25px;
        padding: 1rem 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        color: #4a5568;
        position: relative;
        overflow: hidden;
    }

    .category-tab::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
        transition: left 0.6s ease;
    }

    .category-tab:hover::before {
        left: 100%;
    }

    .category-tab:hover {
        border-color: #667eea;
        color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
    }

    .category-tab.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: #667eea;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .category-tab i {
        font-size: 1.25rem;
    }

    .tab-count {
        background: rgba(255,255,255,0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 700;
    }

    .category-tab:not(.active) .tab-count {
        background: #e2e8f0;
        color: #667eea;
    }

    /* Contenu des onglets */
    .category-tabs-content {
        position: relative;
        min-height: 400px;
    }

    .category-tab-panel {
        display: none;
        animation: fadeInUp 0.5s ease-out;
    }

    .category-tab-panel.active {
        display: block;
    }

    /* Grille des catégories - Version compacte */
    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .category-card-mini {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
        overflow: hidden;
    }

    .category-card-mini::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .category-card-mini:hover::before {
        transform: scaleX(1);
    }

    .category-card-mini:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border-color: #667eea;
    }

    .category-icon-mini {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .category-info-mini {
        flex: 1;
    }

    .category-info-mini h4 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 0.25rem;
    }

    .recipe-count-mini {
        background: #f7fafc;
        color: #667eea;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* Bouton "Voir plus" */
    .show-more-container {
        text-align: center;
        margin-top: 2rem;
    }

    .btn-show-more {
        background: transparent;
        border: 2px solid #e2e8f0;
        color: #4a5568;
        padding: 1rem 2rem;
        border-radius: 25px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .btn-show-more:hover {
        border-color: #667eea;
        color: #667eea;
        background: rgba(102, 126, 234, 0.05);
        transform: translateY(-2px);
    }

    .btn-show-more.expanded {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: #667eea;
    }

    .btn-show-more.expanded i {
        transform: rotate(180deg);
    }

    .hidden-categories {
        margin-top: 1.5rem;
        animation: slideDown 0.4s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Anciens styles à supprimer ou modifier */
    .category-group {
        display: none; /* Cache l'ancien design */
    }

    /* =================================
       RESPONSIVE pour les onglets
    ================================= */
    @media (max-width: 768px) {
        .category-tabs {
            flex-direction: column;
            align-items: center;
        }

        .category-tab {
            width: 100%;
            max-width: 300px;
            justify-content: center;
        }

        .category-grid {
            grid-template-columns: 1fr;
        }

        .category-card-mini {
            flex-direction: column;
            text-align: center;
            padding: 2rem 1.5rem;
        }

        .category-icon-mini {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 480px) {
        .category-tab {
            padding: 0.875rem 1.5rem;
            font-size: 0.9rem;
        }

        .category-tab i {
            font-size: 1.1rem;
        }

        .btn-show-more {
            padding: 0.875rem 1.5rem;
            font-size: 0.9rem;
        }
    }

    /* =================================
       STATS SECTION
    ================================= */
    .stats-section {
        background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
        color: white;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
    }

    .stat-card {
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        padding: 2.5rem;
        border-radius: 20px;
        text-align: center;
        border: 1px solid rgba(255,255,255,0.2);
        position: relative;
        overflow: hidden;
        transition: all 0.4s ease;
    }

    .stat-card:hover {
        transform: translateY(-10px);
        background: rgba(255,255,255,0.15);
    }

    .stat-decoration {
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
        transform: rotate(45deg);
        transition: all 0.6s ease;
    }

    .stat-card:hover .stat-decoration {
        top: -10%;
        right: -10%;
    }

    .stat-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
        color: white;
        position: relative;
        z-index: 2;
    }

    .stat-info {
        position: relative;
        z-index: 2;
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #ffffff 0%, #f7fafc 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-info p {
        color: rgba(255,255,255,0.8);
        font-size: 1.1rem;
        font-weight: 500;
    }

    /* =================================
       RESPONSIVE DESIGN
    ================================= */
    @media (max-width: 1024px) {
        .hero-content {
            grid-template-columns: 1fr;
            text-align: center;
            gap: 2rem;
        }

        .hero-visual {
            width: 250px;
            height: 250px;
            margin: 0 auto;
        }

        .hero-title {
            font-size: 3rem;
        }

        .category-card {
            flex-direction: column;
            text-align: center;
        }

        .category-icon {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 768px) {
        .hero {
            min-height: 80vh;
            padding: 2rem 0;
        }

        .hero-title {
            font-size: 2.5rem;
        }

        .hero-subtitle {
            font-size: 1.1rem;
        }

        .hero-actions {
            flex-direction: column;
            align-items: center;
        }

        .btn-hero {
            width: 100%;
            max-width: 300px;
            justify-content: center;
        }

        .section-header h2 {
            font-size: 2rem;
            flex-direction: column;
            gap: 0.5rem;
        }

        .recipe-grid {
            grid-template-columns: 1fr;
        }

        .category-grid {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .recipe-meta {
            flex-direction: column;
            gap: 0.5rem;
        }

        .floating-card {
            display: none;
        }
    }

    @media (max-width: 480px) {
        section {
            padding: 3rem 0;
        }

        .hero-title {
            font-size: 2rem;
        }

        .stat-number {
            font-size: 2.5rem;
        }

        .recipe-card,
        .category-card,
        .stat-card {
            margin: 0 0.5rem;
        }
    }

    /* =================================
       ANIMATIONS AOS
    ================================= */
    [data-aos="fade-up"] {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease;
    }

    [data-aos="fade-up"].aos-animate {
        opacity: 1;
        transform: translateY(0);
    }

    [data-aos="zoom-in"] {
        opacity: 0;
        transform: scale(0.8);
        transition: all 0.6s ease;
    }

    [data-aos="zoom-in"].aos-animate {
        opacity: 1;
        transform: scale(1);
    }

    /* =================================
       SCROLL EFFECTS
    ================================= */
    .parallax-section {
        background-attachment: fixed;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
    }

    /* =================================
       LOADING ANIMATIONS
    ================================= */
    @keyframes shimmer {
        0% { background-position: -200px 0; }
        100% { background-position: calc(200px + 100%) 0; }
    }

    .loading-shimmer {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200px 100%;
        animation: shimmer 1.5s infinite;
    }

    /* =================================
       UTILITY CLASSES
    ================================= */
    .text-center {
        text-align: center;
    }

    .hidden {
        display: none;
    }

    .fade-in {
        animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .slide-up {
        animation: slideUp 0.6s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
// Animation au scroll simple (remplace AOS si pas disponible)
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('aos-animate');
            }
        });
    }, observerOptions);

    document.querySelectorAll('[data-aos]').forEach(el => {
        observer.observe(el);
    });
}

// Animation des compteurs
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = parseInt(counter.textContent);
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            if (counter.textContent.includes('%')) {
                counter.textContent = Math.floor(current) + '%';
            } else {
                counter.textContent = Math.floor(current);
            }
        }, 16);
    });
}

// Effet parallax pour le hero
function initParallax() {
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.hero-background, .hero-particles');
        
        parallaxElements.forEach(element => {
            const speed = element.classList.contains('hero-particles') ? 0.3 : 0.5;
            element.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });
}

// Gestion des onglets de catégories
function switchTab(tabName) {
    // Cacher tous les panneaux
    document.querySelectorAll('.category-tab-panel').forEach(panel => {
        panel.classList.remove('active');
    });
    
    // Désactiver tous les onglets
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Activer l'onglet cliqué
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    
    // Afficher le panneau correspondant
    document.getElementById(`tab-${tabName}`).classList.add('active');
}

// Gestion du "Voir plus/moins"
function toggleCategories(type) {
    const hiddenGrid = document.getElementById(`hidden-${type}`);
    const button = hiddenGrid.parentNode.querySelector('.btn-show-more');
    const showText = button.querySelector('.show-text');
    const hideText = button.querySelector('.hide-text');
    const icon = button.querySelector('i');
    
    if (hiddenGrid.style.display === 'none') {
        // Afficher plus
        hiddenGrid.style.display = 'grid';
        showText.style.display = 'none';
        hideText.style.display = 'inline';
        button.classList.add('expanded');
        icon.style.transform = 'rotate(180deg)';
    } else {
        // Afficher moins
        hiddenGrid.style.display = 'none';
        showText.style.display = 'inline';
        hideText.style.display = 'none';
        button.classList.remove('expanded');
        icon.style.transform = 'rotate(0deg)';
        
        // Scroll vers le haut de la section
        document.getElementById(`tab-${type}`).scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    initScrollAnimations();
    initParallax();
    
    // Animation des compteurs quand la section stats est visible
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        const statsObserver = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                animateCounters();
                statsObserver.disconnect();
            }
        });
        statsObserver.observe(statsSection);
    }
    
    // Smooth scroll pour le hero scroll indicator
    const scrollIndicator = document.querySelector('.hero-scroll-indicator');
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', () => {
            document.querySelector('.featured-recipes').scrollIntoView({
                behavior: 'smooth'
            });
        });
    }
    
    // Gestion des onglets avec clavier
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                tab.click();
            }
        });
        
        // Accessibilité
        tab.setAttribute('role', 'tab');
        tab.setAttribute('tabindex', '0');
    });
});

// Lazy loading pour les images
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('loading-shimmer');
                observer.unobserve(img);
            }
        });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}
</script>