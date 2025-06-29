<?php
// views/recipes/edit.php - Version sans brouillon
$oldInput = $_SESSION['old_input'] ?? $recipe;

// S'assurer que les variables existent
$categories = $categories ?? [];
$ingredients = $ingredients ?? [];
$categoriesByType = $categoriesByType ?? [];
$selectedCategories = $selectedCategories ?? [];

// Afficher les erreurs s'il y en a
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> ' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-edit"></i> Modifier la recette</h1>
        <p>Modifiez les informations de votre recette : <?= htmlspecialchars($recipe['titre']) ?></p>
    </div>

    <form action="/recipes/<?= $recipe['id'] ?>/update" method="POST" enctype="multipart/form-data" class="recipe-form">
        <div class="form-layout">
            <!-- Informations générales -->
            <div class="form-section">
                <h2><i class="fas fa-info-circle"></i> Informations générales</h2>

                <div class="form-group">
                    <label for="titre" class="form-label">Titre de la recette *</label>
                    <input type="text"
                           id="titre"
                           name="titre"
                           class="form-input"
                           value="<?= htmlspecialchars($oldInput['titre'] ?? '') ?>"
                           placeholder="Ex: Tarte aux pommes de grand-mère"
                           required>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description *</label>
                    <textarea id="description"
                              name="description"
                              class="form-textarea"
                              placeholder="Décrivez votre recette en quelques mots..."
                              required><?= htmlspecialchars($oldInput['description'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="difficulte" class="form-label">Difficulté *</label>
                        <select id="difficulte" name="difficulte" class="form-select" required>
                            <option value="">Choisir la difficulté</option>
                            <option value="facile" <?= ($oldInput['difficulte'] ?? '') === 'facile' ? 'selected' : '' ?>>
                                ⭐ Facile
                            </option>
                            <option value="moyen" <?= ($oldInput['difficulte'] ?? '') === 'moyen' ? 'selected' : '' ?>>
                                ⭐⭐ Moyen
                            </option>
                            <option value="difficile" <?= ($oldInput['difficulte'] ?? '') === 'difficile' ? 'selected' : '' ?>>
                                ⭐⭐⭐ Difficile
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nombre_personnes" class="form-label">Nombre de personnes *</label>
                        <input type="number"
                               id="nombre_personnes"
                               name="nombre_personnes"
                               class="form-input"
                               value="<?= htmlspecialchars($oldInput['nombre_personnes'] ?? '') ?>"
                               min="1"
                               max="20"
                               placeholder="4"
                               required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="duree_preparation" class="form-label">Durée de préparation (min) *</label>
                        <input type="number"
                               id="duree_preparation"
                               name="duree_preparation"
                               class="form-input"
                               value="<?= htmlspecialchars($oldInput['duree_preparation'] ?? '') ?>"
                               min="1"
                               placeholder="30"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="duree_cuisson" class="form-label">Durée de cuisson (min)</label>
                        <input type="number"
                               id="duree_cuisson"
                               name="duree_cuisson"
                               class="form-input"
                               value="<?= htmlspecialchars($oldInput['duree_cuisson'] ?? '') ?>"
                               min="0"
                               placeholder="45">
                    </div>

                    <div class="form-group">
                        <label for="duree_repos" class="form-label">Durée de repos (min)</label>
                        <input type="number"
                               id="duree_repos"
                               name="duree_repos"
                               class="form-input"
                               value="<?= htmlspecialchars($oldInput['duree_repos'] ?? '') ?>"
                               min="0"
                               placeholder="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="conseils" class="form-label">Conseils du chef</label>
                    <textarea id="conseils"
                              name="conseils"
                              class="form-textarea"
                              rows="3"
                              placeholder="Partagez vos astuces et conseils pour réussir cette recette..."><?= htmlspecialchars($oldInput['conseils'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image" class="form-label">Photo de la recette</label>

                    <?php if (!empty($recipe['image_url'])): ?>
                        <div class="current-image">
                            <p>Image actuelle :</p>
                            <img src="/<?= htmlspecialchars($recipe['image_url']) ?>"
                                 alt="Image actuelle"
                                 class="current-image-preview">
                        </div>
                    <?php endif; ?>

                    <input type="file"
                           id="image"
                           name="image"
                           class="form-input"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <small class="form-hint">
                        Formats acceptés: JPG, PNG, GIF, WebP (max 5MB)
                        <?php if (!empty($recipe['image_url'])): ?>
                            <br>Laissez vide pour conserver l'image actuelle
                        <?php endif; ?>
                    </small>
                    <div class="image-preview"></div>
                </div>
            </div>

            <!-- Catégories hiérarchiques -->
            <div class="form-section">
                <h2><i class="fas fa-tags"></i> Catégories</h2>

                <?php if (!empty($categoriesByType)): ?>
                    <?php
                    $typeNames = [
                        'saison' => 'Saisons',
                        'type_plat' => 'Types de plats',
                        'origine' => 'Origines culinaires',
                        'regime' => 'Régimes alimentaires'
                    ];

                    // Récupérer les IDs des catégories actuelles
                    $currentCategoryIds = array_column($categories, 'id');
                    ?>

                    <!-- Saisons -->
                    <?php if (!empty($categoriesByType['saison'])): ?>
                        <div class="category-group-form">
                            <h3><i class="fas fa-calendar"></i> Saisons</h3>
                            <div class="checkbox-grid simple-grid">
                                <?php 
                                $saisons = $categoriesByType['saison'];
                                if (is_array($saisons)):
                                    foreach ($saisons as $category):
                                        if (isset($category['id']) && isset($category['nom'])):
                                ?>
                                    <label class="checkbox-item">
                                        <input type="checkbox"
                                               name="categories[]"
                                               value="<?= intval($category['id']) ?>"
                                            <?= in_array($category['id'], $currentCategoryIds) ? 'checked' : '' ?>>
                                        <span class="checkmark"></span>
                                        <?= htmlspecialchars($category['nom']) ?>
                                    </label>
                                <?php 
                                        endif;
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Types de plats hiérarchiques -->
                    <?php if (!empty($categoriesByType['type_plat'])): ?>
                        <div class="category-group-form">
                            <h3><i class="fas fa-utensils"></i> Types de plats</h3>
                            <div class="hierarchical-categories">
                                <?php if (isset($categoriesByType['type_plat']['categories'])): ?>
                                    <?php foreach ($categoriesByType['type_plat']['categories'] as $mainCategory): ?>
                                        <?php if (isset($mainCategory['id']) && isset($mainCategory['nom'])): ?>
                                        <div class="main-category">
                                            <div class="main-category-header">
                                                <label class="checkbox-item main-checkbox">
                                                    <input type="checkbox"
                                                           name="categories[]"
                                                           value="<?= intval($mainCategory['id']) ?>"
                                                           class="main-category-checkbox"
                                                        <?= in_array($mainCategory['id'], $currentCategoryIds) ? 'checked' : '' ?>>
                                                    <span class="checkmark"></span>
                                                    <strong><?= htmlspecialchars($mainCategory['nom']) ?></strong>
                                                </label>
                                                <?php if (!empty($mainCategory['sous_categories'])): ?>
                                                <button type="button" class="toggle-subcategories" data-target="sub-<?= $mainCategory['id'] ?>">
                                                    <i class="fas fa-chevron-down"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!empty($mainCategory['sous_categories'])): ?>
                                                <div class="sub-categories" id="sub-<?= $mainCategory['id'] ?>">
                                                    <div class="checkbox-grid">
                                                        <?php foreach ($mainCategory['sous_categories'] as $subCategory): ?>
                                                            <?php if (isset($subCategory['id']) && isset($subCategory['nom'])): ?>
                                                            <label class="checkbox-item sub-checkbox">
                                                                <input type="checkbox"
                                                                       name="categories[]"
                                                                       value="<?= intval($subCategory['id']) ?>"
                                                                    <?= in_array($subCategory['id'], $currentCategoryIds) ? 'checked' : '' ?>>
                                                                <span class="checkmark"></span>
                                                                <?= htmlspecialchars($subCategory['nom']) ?>
                                                            </label>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Fallback pour structure simple -->
                                    <div class="checkbox-grid simple-grid">
                                        <?php 
                                        if (is_array($categoriesByType['type_plat'])):
                                            foreach ($categoriesByType['type_plat'] as $category):
                                                if (isset($category['id']) && isset($category['nom'])):
                                        ?>
                                            <label class="checkbox-item">
                                                <input type="checkbox"
                                                       name="categories[]"
                                                       value="<?= intval($category['id']) ?>"
                                                    <?= in_array($category['id'], $currentCategoryIds) ? 'checked' : '' ?>>
                                                <span class="checkmark"></span>
                                                <?= htmlspecialchars($category['nom']) ?>
                                            </label>
                                        <?php 
                                                endif;
                                            endforeach;
                                        endif;
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Origines hiérarchiques -->
                    <?php if (!empty($categoriesByType['origine'])): ?>
                        <div class="category-group-form">
                            <h3><i class="fas fa-globe"></i> Origines culinaires</h3>
                            <div class="hierarchical-categories">
                                <?php if (isset($categoriesByType['origine']['continents'])): ?>
                                    <?php foreach ($categoriesByType['origine']['continents'] as $continent): ?>
                                        <?php if (isset($continent['id']) && isset($continent['nom'])): ?>
                                        <div class="main-category">
                                            <div class="main-category-header">
                                                <label class="checkbox-item main-checkbox">
                                                    <input type="checkbox"
                                                           name="categories[]"
                                                           value="<?= intval($continent['id']) ?>"
                                                           class="main-category-checkbox"
                                                        <?= in_array($continent['id'], $currentCategoryIds) ? 'checked' : '' ?>>
                                                    <span class="checkmark"></span>
                                                    <strong><?= htmlspecialchars($continent['nom']) ?></strong>
                                                </label>
                                                <?php if (!empty($continent['pays'])): ?>
                                                <button type="button" class="toggle-subcategories" data-target="continent-<?= $continent['id'] ?>">
                                                    <i class="fas fa-chevron-down"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!empty($continent['pays'])): ?>
                                                <div class="sub-categories" id="continent-<?= $continent['id'] ?>">
                                                    <div class="checkbox-grid">
                                                        <?php foreach ($continent['pays'] as $pays): ?>
                                                            <?php if (isset($pays['id']) && isset($pays['nom'])): ?>
                                                            <label class="checkbox-item sub-checkbox">
                                                                <input type="checkbox"
                                                                       name="categories[]"
                                                                       value="<?= intval($pays['id']) ?>"
                                                                    <?= in_array($pays['id'], $currentCategoryIds) ? 'checked' : '' ?>>
                                                                <span class="checkmark"></span>
                                                                <?= htmlspecialchars($pays['nom']) ?>
                                                            </label>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Fallback pour structure simple -->
                                    <div class="checkbox-grid simple-grid">
                                        <?php 
                                        if (is_array($categoriesByType['origine'])):
                                            foreach ($categoriesByType['origine'] as $category):
                                                if (isset($category['id']) && isset($category['nom'])):
                                        ?>
                                            <label class="checkbox-item">
                                                <input type="checkbox"
                                                       name="categories[]"
                                                       value="<?= intval($category['id']) ?>"
                                                    <?= in_array($category['id'], $currentCategoryIds) ? 'checked' : '' ?>>
                                                <span class="checkmark"></span>
                                                <?= htmlspecialchars($category['nom']) ?>
                                            </label>
                                        <?php 
                                                endif;
                                            endforeach;
                                        endif;
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Régimes alimentaires -->
                    <?php if (!empty($categoriesByType['regime'])): ?>
                        <div class="category-group-form">
                            <h3><i class="fas fa-leaf"></i> Régimes alimentaires</h3>
                            <div class="checkbox-grid simple-grid">
                                <?php 
                                $regimes = $categoriesByType['regime'];
                                if (is_array($regimes)):
                                    foreach ($regimes as $category):
                                        if (isset($category['id']) && isset($category['nom'])):
                                ?>
                                    <label class="checkbox-item">
                                        <input type="checkbox"
                                               name="categories[]"
                                               value="<?= intval($category['id']) ?>"
                                            <?= in_array($category['id'], $currentCategoryIds) ? 'checked' : '' ?>>
                                        <span class="checkmark"></span>
                                        <?= htmlspecialchars($category['nom']) ?>
                                    </label>
                                <?php 
                                        endif;
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Fallback: si les catégories organisées ne fonctionnent pas, afficher les catégories actuelles -->
                    <?php if (empty($categoriesByType) && !empty($categories)): ?>
                        <div class="category-group-form">
                            <h3><i class="fas fa-tag"></i> Catégories actuelles</h3>
                            <div class="checkbox-grid">
                                <?php foreach ($categories as $category): ?>
                                    <?php if (isset($category['id']) && isset($category['nom'])): ?>
                                        <label class="checkbox-item">
                                            <input type="checkbox"
                                                   name="categories[]"
                                                   value="<?= intval($category['id']) ?>"
                                                   checked>
                                            <span class="checkmark"></span>
                                            <?= htmlspecialchars($category['nom']) ?>
                                            <?php if (!empty($category['type_categorie'])): ?>
                                                <small class="text-muted">(<?= htmlspecialchars($category['type_categorie']) ?>)</small>
                                            <?php endif; ?>
                                        </label>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <p class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                Les catégories organisées ne sont pas disponibles. Seules les catégories actuelles de cette recette sont affichées.
                            </p>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Catégories non disponibles</strong><br>
                        Les catégories organisées ne peuvent pas être chargées.
                        <?php if (!empty($categories)): ?>
                            <br>Voici les catégories actuelles de cette recette :
                            <div class="current-categories">
                                <?php foreach ($categories as $category): ?>
                                    <?php if (isset($category['nom'])): ?>
                                        <span class="category-badge"><?= htmlspecialchars($category['nom']) ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ingrédients -->
            <div class="form-section">
                <h2><i class="fas fa-list"></i> Ingrédients</h2>

                <div class="ingredients-container">
                    <?php
                    // S'assurer qu'on a au moins un ingrédient vide
                    if (empty($ingredients)) {
                        $ingredients = [['nom' => '', 'quantite' => '', 'unite' => '', 'preparation' => '']];
                    }

                    foreach ($ingredients as $index => $ingredient):
                        ?>
                        <div class="ingredient-item">
                            <div class="form-group">
                                <input type="text"
                                       name="ingredients[<?= $index ?>][nom]"
                                       class="form-input ingredient-name"
                                       placeholder="Nom de l'ingrédient"
                                       value="<?= htmlspecialchars($ingredient['nom'] ?? '') ?>"
                                       list="ingredients-suggestions"
                                       required>
                            </div>
                            <div class="form-group">
                                <input type="number"
                                       name="ingredients[<?= $index ?>][quantite]"
                                       class="form-input"
                                       placeholder="Quantité"
                                       value="<?= htmlspecialchars($ingredient['quantite'] ?? '') ?>"
                                       step="0.1"
                                       min="0"
                                       required>
                            </div>
                            <div class="form-group">
                                <select name="ingredients[<?= $index ?>][unite]" class="form-select">
                                    <option value="">Unité</option>
                                    <option value="g" <?= ($ingredient['unite'] ?? '') === 'g' ? 'selected' : '' ?>>grammes</option>
                                    <option value="kg" <?= ($ingredient['unite'] ?? '') === 'kg' ? 'selected' : '' ?>>kilogrammes</option>
                                    <option value="ml" <?= ($ingredient['unite'] ?? '') === 'ml' ? 'selected' : '' ?>>millilitres</option>
                                    <option value="l" <?= ($ingredient['unite'] ?? '') === 'l' ? 'selected' : '' ?>>litres</option>
                                    <option value="c. à thé" <?= ($ingredient['unite'] ?? '') === 'c. à thé' ? 'selected' : '' ?>>cuillère à thé</option>
                                    <option value="c. à soupe" <?= ($ingredient['unite'] ?? '') === 'c. à soupe' ? 'selected' : '' ?>>cuillère à soupe</option>
                                    <option value="tasse" <?= ($ingredient['unite'] ?? '') === 'tasse' ? 'selected' : '' ?>>tasse</option>
                                    <option value="pièce" <?= ($ingredient['unite'] ?? '') === 'pièce' ? 'selected' : '' ?>>pièce(s)</option>
                                    <option value="gousse" <?= ($ingredient['unite'] ?? '') === 'gousse' ? 'selected' : '' ?>>gousse(s)</option>
                                    <option value="pincée" <?= ($ingredient['unite'] ?? '') === 'pincée' ? 'selected' : '' ?>>pincée</option>
                                    <option value="poignée" <?= ($ingredient['unite'] ?? '') === 'poignée' ? 'selected' : '' ?>>poignée</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="text"
                                       name="ingredients[<?= $index ?>][preparation]"
                                       class="form-input"
                                       placeholder="Préparation (optionnel)"
                                       value="<?= htmlspecialchars($ingredient['preparation'] ?? '') ?>">
                            </div>
                            <?php if ($index > 0): ?>
                                <button type="button" class="btn-remove remove-ingredient" title="Supprimer cet ingrédient">
                                    <i class="fas fa-times"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <button type="button" class="btn btn-secondary add-ingredient-btn">
                        <i class="fas fa-plus"></i> Ajouter un ingrédient
                    </button>
                </div>

                <!-- Liste de suggestions pour les ingrédients -->
                <?php if (!empty($availableIngredients)): ?>
                    <datalist id="ingredients-suggestions">
                        <?php foreach ($availableIngredients as $ing): ?>
                            <option value="<?= htmlspecialchars($ing['nom']) ?>">
                        <?php endforeach; ?>
                    </datalist>
                <?php endif; ?>
            </div>

            <!-- Instructions -->
            <div class="form-section">
                <h2><i class="fas fa-list-ol"></i> Instructions de préparation</h2>

                <div class="form-group">
                    <label for="instructions" class="form-label">Étapes de préparation *</label>
                    <textarea id="instructions"
                              name="instructions"
                              class="form-textarea instructions-textarea"
                              placeholder="Décrivez les étapes de préparation étape par étape...
Exemple:
1. Préchauffer le four à 180°C
2. Mélanger la farine et le sucre dans un bol
3. Ajouter les œufs un par un..."
                              rows="10"
                              required><?= htmlspecialchars($oldInput['instructions'] ?? '') ?></textarea>
                    <small class="form-hint">Numérotez vos étapes pour plus de clarté</small>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Mettre à jour la recette
            </button>
            <a href="/recipes/<?= $recipe['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-eye"></i> Voir la recette
            </a>
            <a href="/recipes" class="btn btn-outline">
                <i class="fas fa-times"></i> Annuler
            </a>
        </div>
    </form>
</div>

<style>
    .container {
        max-width: 900px;
        margin: 0 auto;
        padding: 2rem;
    }

    .alert {
        padding: 1rem;
        margin-bottom: 2rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .alert-error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .alert-warning {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
    }

    .text-muted {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .current-categories {
        margin-top: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .category-badge {
        background: #667eea;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 15px;
        font-size: 0.8rem;
        display: inline-block;
    }

    .page-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .page-header h1 {
        color: #333;
        margin-bottom: 0.5rem;
        font-size: 2.5rem;
    }

    .page-header p {
        color: #666;
        font-size: 1.1rem;
    }

    .recipe-form {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .form-section {
        margin-bottom: 3rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #e9ecef;
    }

    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 2rem;
    }

    .form-section h2 {
        color: #667eea;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
    }

    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .form-hint {
        display: block;
        margin-top: 0.5rem;
        color: #666;
        font-size: 0.9rem;
    }

    .current-image {
        margin-bottom: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .current-image p {
        margin: 0 0 0.5rem 0;
        font-weight: 500;
        color: #333;
    }

    .current-image-preview {
        max-width: 200px;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    /* Styles pour les catégories hiérarchiques */
    .category-group-form {
        margin-bottom: 2rem;
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border: 1px solid #e9ecef;
    }

    .category-group-form h3 {
        color: #333;
        margin-bottom: 1rem;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .hierarchical-categories {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .main-category {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
    }

    .main-category-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    .main-checkbox {
        margin: 0;
        font-weight: 600;
    }

    .toggle-subcategories {
        background: none;
        border: none;
        color: #667eea;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .toggle-subcategories:hover {
        background: rgba(102, 126, 234, 0.1);
    }

    .toggle-subcategories.collapsed {
        transform: rotate(-90deg);
    }

    .sub-categories {
        padding: 1rem;
        background: white;
        max-height: 300px;
        overflow-y: auto;
    }

    .sub-categories.collapsed {
        display: none;
    }

    .checkbox-grid {
        display: grid;
        gap: 0.5rem;
    }

    .simple-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }

    .sub-categories .checkbox-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .checkbox-item:hover {
        background-color: rgba(255,255,255,0.7);
    }

    .checkbox-item input[type="checkbox"] {
        margin: 0;
        cursor: pointer;
    }

    .sub-checkbox {
        padding-left: 1rem;
        font-size: 0.95rem;
    }

    .checkmark {
        font-weight: 500;
    }

    .ingredients-container {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border: 1px solid #e9ecef;
    }

    .ingredient-item {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1.5fr auto;
        gap: 1rem;
        margin-bottom: 1rem;
        align-items: end;
    }

    .ingredient-item .form-group {
        margin-bottom: 0;
    }

    .btn-remove {
        background: #dc3545;
        color: white;
        border: none;
        padding: 0.75rem;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        height: fit-content;
    }

    .btn-remove:hover {
        background: #c82333;
    }

    .add-ingredient-btn {
        width: 100%;
        margin-top: 1rem;
    }

    .instructions-textarea {
        min-height: 200px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        resize: vertical;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        padding-top: 2rem;
        border-top: 1px solid #e9ecef;
        flex-wrap: wrap;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-primary:hover {
        background: #5a6fd8;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
    }

    .btn-outline {
        background: transparent;
        color: #667eea;
        border: 2px solid #667eea;
    }

    .btn-outline:hover {
        background: #667eea;
        color: white;
    }

    .image-preview {
        margin-top: 1rem;
    }

    .image-preview img {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    /* Notification de modification */
    .edit-notice {
        background: #e7f3ff;
        border: 1px solid #b8daff;
        color: #004085;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .edit-notice i {
        font-size: 1.2rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .container {
            padding: 1rem;
        }

        .recipe-form {
            padding: 1rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .checkbox-grid {
            grid-template-columns: 1fr;
        }

        .ingredient-item {
            grid-template-columns: 1fr;
        }

        .current-image-preview {
            max-width: 150px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .main-category-header {
            padding: 0.75rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ajouter une notification de modification
        const pageHeader = document.querySelector('.page-header');
        if (pageHeader) {
            const notice = document.createElement('div');
            notice.className = 'edit-notice';
            notice.innerHTML = `
                <i class="fas fa-info-circle"></i>
                <span>Vous êtes en train de modifier cette recette. Vos modifications seront sauvegardées une fois le formulaire soumis.</span>
            `;
            pageHeader.insertAdjacentElement('afterend', notice);
        }

        // Gestion des sous-catégories
        document.querySelectorAll('.toggle-subcategories').forEach(button => {
            button.addEventListener('click', function() {
                const target = document.getElementById(this.dataset.target);
                const icon = this.querySelector('i');
                
                if (target.style.display === 'none' || target.style.display === '') {
                    target.style.display = 'block';
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    target.style.display = 'none';
                    icon.style.transform = 'rotate(-90deg)';
                }
            });
        });

        // Initialiser l'état des sous-catégories (fermées par défaut)
        document.querySelectorAll('.sub-categories').forEach(subCat => {
            subCat.style.display = 'none';
        });

        // Auto-check parent category when sub-category is selected
        document.querySelectorAll('.sub-categories input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const parentContainer = this.closest('.main-category');
                const parentCheckbox = parentContainer.querySelector('.main-category-checkbox');
                const siblingCheckboxes = parentContainer.querySelectorAll('.sub-categories input[type="checkbox"]');
                
                // Si au moins une sous-catégorie est cochée, cocher la catégorie parent
                const hasCheckedSibling = Array.from(siblingCheckboxes).some(cb => cb.checked);
                if (hasCheckedSibling && !parentCheckbox.checked) {
                    parentCheckbox.checked = true;
                }
                
                // Si aucune sous-catégorie n'est cochée, décocher la catégorie parent
                if (!hasCheckedSibling && parentCheckbox.checked) {
                    parentCheckbox.checked = false;
                }
            });
        });

        // Auto-check/uncheck all sub-categories when parent is toggled
        document.querySelectorAll('.main-category-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const parentContainer = this.closest('.main-category');
                const subCheckboxes = parentContainer.querySelectorAll('.sub-categories input[type="checkbox"]');
                
                if (!this.checked) {
                    // Décocher toutes les sous-catégories quand le parent est décoché
                    subCheckboxes.forEach(cb => cb.checked = false);
                }
            });
        });

        // Gestion des ingrédients
        let ingredientIndex = <?= count($ingredients) ?>;

        document.querySelector('.add-ingredient-btn').addEventListener('click', function() {
            const container = document.querySelector('.ingredients-container');
            const newIngredient = document.createElement('div');
            newIngredient.className = 'ingredient-item';
            newIngredient.innerHTML = `
                <div class="form-group">
                    <input type="text" name="ingredients[${ingredientIndex}][nom]" class="form-input ingredient-name" placeholder="Nom de l'ingrédient" list="ingredients-suggestions" required>
                </div>
                <div class="form-group">
                    <input type="number" name="ingredients[${ingredientIndex}][quantite]" class="form-input" placeholder="Quantité" step="0.1" min="0" required>
                </div>
                <div class="form-group">
                    <select name="ingredients[${ingredientIndex}][unite]" class="form-select">
                        <option value="">Unité</option>
                        <option value="g">grammes</option>
                        <option value="kg">kilogrammes</option>
                        <option value="ml">millilitres</option>
                        <option value="l">litres</option>
                        <option value="c. à thé">cuillère à thé</option>
                        <option value="c. à soupe">cuillère à soupe</option>
                        <option value="tasse">tasse</option>
                        <option value="pièce">pièce(s)</option>
                        <option value="gousse">gousse(s)</option>
                        <option value="pincée">pincée</option>
                        <option value="poignée">poignée</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="ingredients[${ingredientIndex}][preparation]" class="form-input" placeholder="Préparation (optionnel)">
                </div>
                <button type="button" class="btn-remove remove-ingredient" title="Supprimer cet ingrédient">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.insertBefore(newIngredient, this);
            ingredientIndex++;
        });

        // Supprimer un ingrédient
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-ingredient')) {
                e.target.closest('.ingredient-item').remove();
            }
        });

        // Aperçu de l'image
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.querySelector('.image-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Aperçu de la nouvelle image">`;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });

        // Confirmation avant de quitter si des modifications ont été faites
        let formChanged = false;
        const form = document.querySelector('.recipe-form');

        form.addEventListener('input', function() {
            formChanged = true;
        });

        form.addEventListener('change', function() {
            formChanged = true;
        });

        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'Vous avez des modifications non sauvegardées. Voulez-vous vraiment quitter cette page ?';
            }
        });

        // Retirer l'avertissement lors de la soumission
        form.addEventListener('submit', function() {
            formChanged = false;
        });

        // Validation du formulaire
        form.addEventListener('submit', function(e) {
            const titre = document.getElementById('titre').value.trim();
            const description = document.getElementById('description').value.trim();
            const instructions = document.getElementById('instructions').value.trim();
            
            if (!titre || !description || !instructions) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires.');
                return false;
            }

            // Vérifier qu'au moins un ingrédient est rempli
            const ingredients = document.querySelectorAll('.ingredient-name');
            const hasValidIngredient = Array.from(ingredients).some(input => input.value.trim() !== '');
            
            if (!hasValidIngredient) {
                e.preventDefault();
                alert('Veuillez ajouter au moins un ingrédient.');
                return false;
            }

            // Vérifier qu'au moins une catégorie est sélectionnée
            const categories = document.querySelectorAll('input[name="categories[]"]:checked');
            if (categories.length === 0) {
                e.preventDefault();
                alert('Veuillez sélectionner au moins une catégorie.');
                return false;
            }

            // Désactiver le bouton de soumission pour éviter les doubles clics
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mise à jour...';
            }
        });
    });
</script>