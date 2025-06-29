<?php
// views/recipes/create.php - Version sans brouillon
$oldInput = $_SESSION['old_input'] ?? [];

// Afficher les erreurs s'il y en a
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> ' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-plus"></i> Créer une nouvelle recette</h1>
        <p>Partagez votre recette favorite avec la communauté</p>
    </div>

    <form action="/recipes/store" method="POST" enctype="multipart/form-data" class="recipe-form">
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
                    <input type="file"
                           id="image"
                           name="image"
                           class="form-input"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <small class="form-hint">Formats acceptés: JPG, PNG, GIF, WebP (max 5MB)</small>
                    <div class="image-preview"></div>
                </div>
            </div>

            <!-- Catégories hiérarchiques -->
            <div class="form-section">
                <h2><i class="fas fa-tags"></i> Catégories</h2>

                <!-- Saisons -->
                <?php if (!empty($categoriesByType['saison'])): ?>
                    <div class="category-group-form">
                        <h3><i class="fas fa-calendar"></i> Saisons</h3>
                        <div class="checkbox-grid simple-grid">
                            <?php foreach ($categoriesByType['saison'] as $category): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox"
                                           name="categories[]"
                                           value="<?= $category['id'] ?>"
                                        <?= in_array($category['id'], $oldInput['categories'] ?? []) ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    <?= htmlspecialchars($category['nom']) ?>
                                </label>
                            <?php endforeach; ?>
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
                                    <div class="main-category">
                                        <div class="main-category-header">
                                            <label class="checkbox-item main-checkbox">
                                                <input type="checkbox"
                                                       name="categories[]"
                                                       value="<?= $mainCategory['id'] ?>"
                                                       class="main-category-checkbox"
                                                    <?= in_array($mainCategory['id'], $oldInput['categories'] ?? []) ? 'checked' : '' ?>>
                                                <span class="checkmark"></span>
                                                <strong><?= htmlspecialchars($mainCategory['nom']) ?></strong>
                                            </label>
                                            <button type="button" class="toggle-subcategories" data-target="sub-<?= $mainCategory['id'] ?>">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                        
                                        <?php if (!empty($mainCategory['sous_categories'])): ?>
                                            <div class="sub-categories" id="sub-<?= $mainCategory['id'] ?>">
                                                <div class="checkbox-grid">
                                                    <?php foreach ($mainCategory['sous_categories'] as $subCategory): ?>
                                                        <label class="checkbox-item sub-checkbox">
                                                            <input type="checkbox"
                                                                   name="categories[]"
                                                                   value="<?= $subCategory['id'] ?>"
                                                                <?= in_array($subCategory['id'], $oldInput['categories'] ?? []) ? 'checked' : '' ?>>
                                                            <span class="checkmark"></span>
                                                            <?= htmlspecialchars($subCategory['nom']) ?>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Fallback pour structure simple -->
                                <div class="checkbox-grid simple-grid">
                                    <?php foreach ($categoriesByType['type_plat'] as $category): ?>
                                        <label class="checkbox-item">
                                            <input type="checkbox"
                                                   name="categories[]"
                                                   value="<?= $category['id'] ?>"
                                                <?= in_array($category['id'], $oldInput['categories'] ?? []) ? 'checked' : '' ?>>
                                            <span class="checkmark"></span>
                                            <?= htmlspecialchars($category['nom']) ?>
                                        </label>
                                    <?php endforeach; ?>
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
                                    <div class="main-category">
                                        <div class="main-category-header">
                                            <label class="checkbox-item main-checkbox">
                                                <input type="checkbox"
                                                       name="categories[]"
                                                       value="<?= $continent['id'] ?>"
                                                       class="main-category-checkbox"
                                                    <?= in_array($continent['id'], $oldInput['categories'] ?? []) ? 'checked' : '' ?>>
                                                <span class="checkmark"></span>
                                                <strong><?= htmlspecialchars($continent['nom']) ?></strong>
                                            </label>
                                            <button type="button" class="toggle-subcategories" data-target="continent-<?= $continent['id'] ?>">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                        
                                        <?php if (!empty($continent['pays'])): ?>
                                            <div class="sub-categories" id="continent-<?= $continent['id'] ?>">
                                                <div class="checkbox-grid">
                                                    <?php foreach ($continent['pays'] as $pays): ?>
                                                        <label class="checkbox-item sub-checkbox">
                                                            <input type="checkbox"
                                                                   name="categories[]"
                                                                   value="<?= $pays['id'] ?>"
                                                                <?= in_array($pays['id'], $oldInput['categories'] ?? []) ? 'checked' : '' ?>>
                                                            <span class="checkmark"></span>
                                                            <?= htmlspecialchars($pays['nom']) ?>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Fallback pour structure simple -->
                                <div class="checkbox-grid simple-grid">
                                    <?php foreach ($categoriesByType['origine'] as $category): ?>
                                        <label class="checkbox-item">
                                            <input type="checkbox"
                                                   name="categories[]"
                                                   value="<?= $category['id'] ?>"
                                                <?= in_array($category['id'], $oldInput['categories'] ?? []) ? 'checked' : '' ?>>
                                            <span class="checkmark"></span>
                                            <?= htmlspecialchars($category['nom']) ?>
                                        </label>
                                    <?php endforeach; ?>
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
                            <?php foreach ($categoriesByType['regime'] as $category): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox"
                                           name="categories[]"
                                           value="<?= $category['id'] ?>"
                                        <?= in_array($category['id'], $oldInput['categories'] ?? []) ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    <?= htmlspecialchars($category['nom']) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ingrédients -->
            <div class="form-section">
                <h2><i class="fas fa-list"></i> Ingrédients</h2>

                <div class="ingredients-container">
                    <?php
                    $savedIngredients = $oldInput['ingredients'] ?? [['nom' => '', 'quantite' => '', 'unite' => '', 'preparation' => '']];
                    foreach ($savedIngredients as $index => $ingredient):
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
                <i class="fas fa-save"></i> Créer la recette
            </button>
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
        background-color: #f8f9fa;
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

    /* Styles pour les ingrédients */
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

        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        let ingredientIndex = <?= count($savedIngredients) ?>;

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
                    preview.innerHTML = `<img src="${e.target.result}" alt="Aperçu de l'image">`;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });

        // Validation du formulaire
        document.querySelector('.recipe-form').addEventListener('submit', function(e) {
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
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création...';
            }
        });
    });
</script>