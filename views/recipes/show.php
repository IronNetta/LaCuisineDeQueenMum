<?php
// views/recipes/show.php
?>

<div class="container">
    <div class="recipe-detail">
        <!-- En-tête de la recette -->
        <div class="recipe-header">
            <div class="recipe-header-content">
                <div class="recipe-title-section">
                    <h1><?= htmlspecialchars($recipe['titre']) ?></h1>
                    <p class="recipe-description"><?= htmlspecialchars($recipe['description']) ?></p>

                    <div class="recipe-meta-main">
                        <div class="meta-item">
                            <i class="fas fa-chart-bar"></i>
                            <span class="difficulty difficulty-<?= $recipe['difficulte'] ?>">
                                <?= ucfirst($recipe['difficulte']) ?>
                            </span>
                        </div>

                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span>
                                <?php
                                $totalTime = $recipe['duree_preparation'] + ($recipe['duree_cuisson'] ?? 0);
                                echo $totalTime . ' min total';
                                ?>
                            </span>
                        </div>

                        <div class="meta-item">
                            <i class="fas fa-users"></i>
                            <span><?= $recipe['nombre_personnes'] ?> personne<?= $recipe['nombre_personnes'] > 1 ? 's' : '' ?></span>
                        </div>

                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>Ajoutée le <?= date('d/m/Y', strtotime($recipe['created_at'])) ?></span>
                        </div>
                    </div>

                    <?php if (!empty($recipe['categories'])): ?>
                        <div class="recipe-categories">
                            <?php foreach ($recipe['categories'] as $category): ?>
                                <a href="/recipes?category=<?= $category['id'] ?>" class="category-tag">
                                    <?= htmlspecialchars($category['nom']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="recipe-actions">
                    <a href="/recipes/<?= $recipe['id'] ?>/edit" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <form method="POST" action="/recipes/<?= $recipe['id'] ?>/delete" style="display: inline;">
                        <button type="submit" class="btn btn-danger delete-recipe-btn">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </form>
                    <a href="/recipes" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Retour aux recettes
                    </a>
                </div>
            </div>

            <?php 
            // Debug : affichons les clés disponibles pour l'image
            // var_dump(array_keys($recipe)); // Décommentez pour debug
            
            // Testons plusieurs variantes possibles
            $imageUrl = $recipe['image_url'] ?? $recipe['image'] ?? $recipe['photo'] ?? null;
            
            if ($imageUrl): ?>
                <div class="recipe-image-main">
                    <img src="<?= htmlspecialchars($imageUrl) ?>"
                         alt="<?= htmlspecialchars($recipe['titre']) ?>"
                         onerror="this.style.display='none'">
                </div>
            <?php else: ?>
                <!-- Debug : affichons ce qui est disponible -->
                <!-- <pre><?php // print_r($recipe); ?></pre> -->
                <div class="recipe-image-placeholder">
                    <i class="fas fa-image"></i>
                    <span>Aucune image disponible</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Temps de préparation détaillé -->
        <div class="time-breakdown">
            <div class="time-item">
                <i class="fas fa-cut"></i>
                <h3>Préparation</h3>
                <span class="time-value"><?= $recipe['duree_preparation'] ?> min</span>
            </div>

            <?php if ($recipe['duree_cuisson'] > 0): ?>
                <div class="time-item">
                    <i class="fas fa-fire"></i>
                    <h3>Cuisson</h3>
                    <span class="time-value"><?= $recipe['duree_cuisson'] ?> min</span>
                </div>
            <?php endif; ?>

            <div class="time-item total">
                <i class="fas fa-clock"></i>
                <h3>Total</h3>
                <span class="time-value"><?= $recipe['duree_preparation'] + ($recipe['duree_cuisson'] ?? 0) ?> min</span>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="recipe-content">
            <!-- Ingrédients -->
            <div class="ingredients-section">
                <h2><i class="fas fa-list"></i> Ingrédients</h2>

                <?php if (!empty($recipe['ingredients'])): ?>
                    <div class="ingredients-list">
                        <?php foreach ($recipe['ingredients'] as $ingredient): ?>
                            <div class="ingredient-item">
                                <label class="ingredient-checkbox">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    <span class="ingredient-details">
                                        <span class="ingredient-quantity">
                                            <?= number_format($ingredient['quantite'], $ingredient['quantite'] == floor($ingredient['quantite']) ? 0 : 1) ?>
                                            <?= htmlspecialchars($ingredient['unite'] ?: $ingredient['unite_mesure']) ?>
                                        </span>
                                        <span class="ingredient-name">
                                            <?= htmlspecialchars($ingredient['nom']) ?>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="ingredients-actions">
                        <button type="button" class="btn btn-outline" onclick="toggleAllIngredients()">
                            <i class="fas fa-check-double"></i> Tout cocher/décocher
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="printIngredients()">
                            <i class="fas fa-print"></i> Imprimer la liste
                        </button>
                    </div>
                <?php else: ?>
                    <p class="no-data">Aucun ingrédient spécifié pour cette recette.</p>
                <?php endif; ?>
            </div>

            <!-- Instructions -->
            <div class="instructions-section">
                <h2><i class="fas fa-list-ol"></i> Instructions</h2>

                <div class="instructions-content">
                    <?php
                    // Traiter les instructions pour les numéroter automatiquement
                    $instructions = $recipe['instructions'];
                    $lines = explode("\n", $instructions);
                    $stepNumber = 1;
                    ?>

                    <div class="instructions-list">
                        <?php foreach ($lines as $line): ?>
                            <?php
                            $line = trim($line);
                            if (empty($line)) continue;

                            // Vérifier si la ligne commence déjà par un numéro
                            if (!preg_match('/^\d+\./', $line)) {
                                $line = $stepNumber . '. ' . $line;
                                $stepNumber++;
                            } else {
                                // Extraire le numéro existant
                                preg_match('/^(\d+)\./', $line, $matches);
                                $stepNumber = intval($matches[1]) + 1;
                            }
                            ?>

                            <div class="instruction-step">
                                <div class="step-number">
                                    <?= preg_replace('/^(\d+)\..*/', '$1', $line) ?>
                                </div>
                                <div class="step-content">
                                    <p><?= htmlspecialchars(preg_replace('/^\d+\.\s*/', '', $line)) ?></p>
                                    <label class="step-checkbox">
                                        <input type="checkbox">
                                        <span class="checkmark-step"></span>
                                        <span>Étape terminée</span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conseil du chef -->
        <?php if (!empty($recipe['conseils'])): ?>
            <div class="chef-advice">
                <h3><i class="fas fa-user-tie"></i> Conseil du Chef</h3>
                <div class="advice-content">
                    <div class="advice-text">
                        <i class="fas fa-quote-left quote-icon"></i>
                        <p><?= nl2br(htmlspecialchars($recipe['conseils'])) ?></p>
                        <i class="fas fa-quote-right quote-icon-right"></i>
                    </div>
                    <div class="chef-signature">
                        <i class="fas fa-signature"></i>
                        <span>Le Chef</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Conseils et astuces -->
        <div class="recipe-tips">
            <h3><i class="fas fa-lightbulb"></i> Conseils Généraux</h3>
            <div class="tips-grid">
                <div class="tip-item">
                    <i class="fas fa-thermometer-half"></i>
                    <span>Vérifiez la température de votre four avant de commencer</span>
                </div>
                <div class="tip-item">
                    <i class="fas fa-balance-scale"></i>
                    <span>Pesez vos ingrédients pour plus de précision</span>
                </div>
                <div class="tip-item">
                    <i class="fas fa-clock"></i>
                    <span>Préparez tous vos ingrédients avant de commencer</span>
                </div>
                <?php if ($recipe['duree_cuisson'] > 0): ?>
                    <div class="tip-item">
                        <i class="fas fa-fire"></i>
                        <span>Surveillez la cuisson, les temps peuvent varier selon les appareils</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Navigation -->
        <div class="recipe-navigation">
            <a href="/recipes" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Voir toutes les recettes
            </a>
            <a href="/recipes/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter une recette
            </a>
        </div>
    </div>
</div>

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
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        min-height: 100vh;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    /* Header de la recette */
    .recipe-header {
        background: white;
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
    }

    .recipe-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    }

    .recipe-header-content {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 3rem;
        align-items: start;
    }

    .recipe-title-section h1 {
        font-size: 3rem;
        font-weight: 800;
        color: #1a202c;
        margin-bottom: 1rem;
        line-height: 1.1;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .recipe-description {
        font-size: 1.25rem;
        color: #4a5568;
        margin-bottom: 2rem;
        line-height: 1.7;
        font-weight: 400;
    }

    /* Méta informations */
    .recipe-meta-main {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .meta-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .meta-item i {
        font-size: 1.25rem;
        color: #667eea;
        width: 24px;
        text-align: center;
    }

    .meta-item span {
        font-weight: 600;
        color: #2d3748;
    }

    /* Difficulté */
    .difficulty {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .difficulty-facile {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
    }

    .difficulty-moyen {
        background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
        color: white;
    }

    .difficulty-difficile {
        background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
        color: white;
    }

    /* Image principale et placeholder */
    .recipe-image-main {
        max-width: 400px;
    }

    .recipe-image-main img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .recipe-image-placeholder {
        max-width: 400px;
        height: 300px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        border: 2px dashed #cbd5e0;
        border-radius: 20px;
        color: #a0aec0;
        font-size: 0.875rem;
        gap: 1rem;
    }

    .recipe-image-placeholder i {
        font-size: 3rem;
        color: #cbd5e0;
    }

    /* Catégories */
    .recipe-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }

    .category-tag {
        padding: 0.5rem 1.25rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 25px;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .category-tag:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.4);
    }

    /* Actions */
    .recipe-actions {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn {
        padding: 0.875rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 0.95rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
        color: white;
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(245, 101, 101, 0.4);
    }

    .btn-outline {
        background: white;
        color: #4a5568;
        border: 2px solid #e2e8f0;
    }

    .btn-outline:hover {
        border-color: #667eea;
        color: #667eea;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
        color: white;
    }

    /* Temps de préparation */
    .time-breakdown {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    .time-item {
        background: white;
        padding: 2rem;
        border-radius: 20px;
        text-align: center;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
    }

    .time-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.15);
    }

    .time-item.total {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }

    .time-item i {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: #667eea;
    }

    .time-item.total i {
        color: white;
    }

    .time-item h3 {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .time-value {
        font-size: 1.5rem;
        font-weight: 800;
    }

    /* Contenu principal */
    .recipe-content {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .ingredients-section,
    .instructions-section {
        background: white;
        padding: 2.5rem;
        border-radius: 20px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }

    .ingredients-section h2,
    .instructions-section h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .ingredients-section h2 i,
    .instructions-section h2 i {
        color: #667eea;
        font-size: 1.5rem;
    }

    /* Ingrédients */
    .ingredient-item {
        margin-bottom: 1rem;
        padding: 1rem;
        background: #f7fafc;
        border-radius: 12px;
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }

    .ingredient-item:hover {
        background: #edf2f7;
        transform: translateX(4px);
    }

    .ingredient-checkbox {
        display: flex;
        align-items: center;
        gap: 1rem;
        cursor: pointer;
    }

    .ingredient-details {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .ingredient-quantity {
        font-weight: 700;
        color: #667eea;
    }

    .ingredient-name {
        color: #2d3748;
        font-weight: 500;
    }

    .ingredients-actions {
        margin-top: 1.5rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    /* Instructions */
    .instruction-step {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: #f7fafc;
        border-radius: 16px;
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }

    .instruction-step:hover {
        background: #edf2f7;
        transform: translateX(4px);
    }

    .step-number {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.125rem;
        flex-shrink: 0;
    }

    .step-content {
        flex: 1;
    }

    .step-content p {
        margin-bottom: 1rem;
        color: #2d3748;
        font-weight: 500;
        line-height: 1.7;
    }

    .step-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.875rem;
        color: #718096;
    }

    /* Conseil du chef */
    .chef-advice {
        background: linear-gradient(135deg, #fef5e7 0%, #feebc8 100%);
        padding: 2.5rem;
        border-radius: 20px;
        margin-bottom: 3rem;
        border: 1px solid #fed7aa;
        position: relative;
        overflow: hidden;
    }

    .chef-advice::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #f6ad55 0%, #ed8936 100%);
    }

    .chef-advice h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #c05621;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .chef-advice h3 i {
        color: #ed8936;
        font-size: 1.25rem;
    }

    .advice-content {
        position: relative;
    }

    .advice-text {
        background: white;
        padding: 2rem;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(237, 137, 54, 0.1);
        position: relative;
        margin-bottom: 1.5rem;
    }

    .advice-text p {
        font-size: 1.125rem;
        line-height: 1.8;
        color: #2d3748;
        font-style: italic;
        margin: 0;
        padding: 0 2rem;
    }

    .quote-icon {
        position: absolute;
        top: 1rem;
        left: 1rem;
        color: #fed7aa;
        font-size: 1.5rem;
    }

    .quote-icon-right {
        position: absolute;
        bottom: 1rem;
        right: 1rem;
        color: #fed7aa;
        font-size: 1.5rem;
    }

    .chef-signature {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
        color: #c05621;
        font-weight: 600;
        font-style: italic;
    }

    .chef-signature i {
        color: #ed8936;
    }

    /* Conseils généraux */
    .recipe-tips {
        background: white;
        padding: 2.5rem;
        border-radius: 20px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        margin-bottom: 3rem;
        border: 1px solid #e2e8f0;
    }

    .recipe-tips h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .recipe-tips h3 i {
        color: #f6ad55;
        font-size: 1.25rem;
    }

    .tips-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .tip-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .tip-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .tip-item i {
        color: #667eea;
        font-size: 1.25rem;
        width: 24px;
        text-align: center;
    }

    /* Navigation */
    .recipe-navigation {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    /* Messages */
    .no-data {
        color: #718096;
        font-style: italic;
        text-align: center;
        padding: 2rem;
        background: #f7fafc;
        border-radius: 12px;
    }

    /* Checkbox personnalisés */
    input[type="checkbox"] {
        appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #667eea;
        border-radius: 4px;
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    input[type="checkbox"]:checked {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
    }

    input[type="checkbox"]:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-weight: bold;
        font-size: 12px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .recipe-header-content {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .recipe-title-section h1 {
            font-size: 2.25rem;
        }

        .recipe-content {
            grid-template-columns: 1fr;
        }

        .time-breakdown {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .tips-grid {
            grid-template-columns: 1fr;
        }

        .recipe-navigation {
            flex-direction: column;
        }

        .recipe-actions {
            width: 100%;
        }

        .recipe-actions .btn {
            width: 100%;
        }

        .chef-advice {
            margin-bottom: 2rem;
        }

        .advice-text p {
            font-size: 1rem;
            padding: 0 1.5rem;
        }

        .quote-icon,
        .quote-icon-right {
            font-size: 1.25rem;
        }
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .recipe-header,
    .time-breakdown,
    .recipe-content > *,
    .recipe-tips {
        animation: fadeInUp 0.6s ease-out;
    }

    .recipe-content > *:nth-child(2) {
        animation-delay: 0.1s;
    }
</style>

<script>
function toggleAllIngredients() {
    const checkboxes = document.querySelectorAll('.ingredient-checkbox input[type="checkbox"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
    });
    
    const button = document.querySelector('.ingredients-actions .btn');
    button.innerHTML = allChecked ? 
        '<i class="fas fa-check-double"></i> Tout cocher' : 
        '<i class="fas fa-square"></i> Tout décocher';
}

function printIngredients() {
    const ingredientsList = document.querySelector('.ingredients-list');
    const printWindow = window.open('', '', 'height=600,width=800');
    
    printWindow.document.write('<html><head><title>Liste des ingrédients</title>');
    printWindow.document.write('<style>body{font-family:Arial,sans-serif;padding:20px;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h2>Liste des ingrédients</h2>');
    printWindow.document.write(ingredientsList.innerHTML.replace(/input[^>]*>/g, ''));
    printWindow.document.write('</body></html>');
    
    printWindow.document.close();
    printWindow.print();
}

// Animation au scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

document.querySelectorAll('.instruction-step, .tip-item').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'all 0.6s ease';
    observer.observe(el);
});
</script>