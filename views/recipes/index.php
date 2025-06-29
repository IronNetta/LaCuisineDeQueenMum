<?php
// views/recipes/index.php - Version complète corrigée
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-book"></i> Nos Recettes</h1>
        <p>Découvrez <?= $totalRecipes ?> recettes délicieuses</p>
    </div>

    <!-- Section de filtres améliorée -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <!-- Ligne 1: Filtres principaux -->
            <div class="filters-row-main">
                <div class="filter-group">
                    <label for="search">
                        <i class="fas fa-search"></i> Rechercher une recette
                    </label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                           placeholder="Nom de la recette, description...">
                </div>
                
                <div class="filter-group">
                    <label for="difficulty">
                        <i class="fas fa-chart-bar"></i> Difficulté
                    </label>
                    <select id="difficulty" name="difficulty">
                        <option value="">Toutes difficultés</option>
                        <option value="facile" <?= ($filters['difficulty'] ?? '') === 'facile' ? 'selected' : '' ?>>🟢 Facile</option>
                        <option value="moyen" <?= ($filters['difficulty'] ?? '') === 'moyen' ? 'selected' : '' ?>>🟡 Moyen</option>
                        <option value="difficile" <?= ($filters['difficulty'] ?? '') === 'difficile' ? 'selected' : '' ?>>🔴 Difficile</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="sort">
                        <i class="fas fa-sort"></i> Trier par
                    </label>
                    <select id="sort" name="sort">
                        <option value="recent" <?= ($filters['sort'] ?? '') === 'recent' ? 'selected' : '' ?>>➡️ Plus récent</option>
                        <option value="popular" <?= ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>🔥 Plus populaire</option>
                        <option value="rating" <?= ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' ?>>⭐ Mieux noté</option>
                        <option value="duration" <?= ($filters['sort'] ?? '') === 'duration' ? 'selected' : '' ?>>⚡ Plus rapide</option>
                        <option value="alphabetical" <?= ($filters['sort'] ?? '') === 'alphabetical' ? 'selected' : '' ?>>🔤 Alphabétique</option>
                    </select>
                </div>
            </div>

            <!-- Ligne 2: Filtre par ingrédients -->
            <div class="ingredients-filter-section">
                <div class="ingredients-header">
                    <label>
                        <i class="fas fa-carrot"></i> Filtrer par ingrédients
                    </label>
                    <button type="button" class="toggle-ingredients" onclick="toggleIngredientsFilter()">
                        <span class="toggle-text">Afficher les filtres</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>

                <div class="ingredients-container" id="ingredientsContainer" style="display: none;">
                    <!-- Barre de recherche d'ingrédients -->
                    <div class="ingredient-search-container">
                        <div class="ingredient-search">
                            <i class="fas fa-search"></i>
                            <input type="text" 
                                   id="ingredientSearch" 
                                   placeholder="Rechercher un ingrédient (ex: tomate, poulet, ail...)"
                                   autocomplete="off">
                        </div>
                        <div class="ingredient-suggestions" id="ingredientSuggestions"></div>
                    </div>

                    <!-- Ingrédients sélectionnés -->
                    <div class="selected-ingredients" id="selectedIngredients">
                        <div class="selected-header">
                            <span>Ingrédients sélectionnés :</span>
                            <button type="button" class="clear-ingredients" onclick="clearAllIngredients()">
                                <i class="fas fa-times"></i> Tout effacer
                            </button>
                        </div>
                        <div class="ingredients-tags" id="ingredientsTags">
                            <?php if (!empty($filters['ingredients'])): ?>
                                <?php foreach (explode(',', $filters['ingredients']) as $ingredient): ?>
                                    <span class="ingredient-tag" data-ingredient="<?= htmlspecialchars(trim($ingredient)) ?>">
                                        <?= htmlspecialchars(trim($ingredient)) ?>
                                        <button type="button" onclick="removeIngredient('<?= htmlspecialchars(trim($ingredient)) ?>')">×</button>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Options de filtrage -->
                    <div class="ingredient-options">
                        <div class="filter-mode">
                            <label>Mode de filtrage :</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="ingredients_mode" value="any" 
                                           <?= ($filters['ingredients_mode'] ?? 'any') === 'any' ? 'checked' : '' ?>>
                                    <span>Au moins un ingrédient</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="ingredients_mode" value="all" 
                                           <?= ($filters['ingredients_mode'] ?? '') === 'all' ? 'checked' : '' ?>>
                                    <span>Tous les ingrédients</span>
                                </label>
                            </div>
                        </div>

                        <div class="ingredient-stats" id="ingredientStats">
                            <span class="stats-text">Sélectionnez des ingrédients pour voir les statistiques</span>
                        </div>
                    </div>

                    <!-- Ingrédients populaires (suggestions rapides) -->
                    <div class="popular-ingredients">
                        <label>Ingrédients populaires :</label>
                        <div class="popular-tags">
                            <?php 
                            $popularIngredients = ['Tomate', 'Oignon', 'Ail', 'Poulet', 'Bœuf', 'Pâtes', 'Riz', 'Fromage', 'Œuf', 'Basilic', 'Huile d\'olive', 'Carotte'];
                            foreach ($popularIngredients as $ingredient): ?>
                                <button type="button" class="popular-tag" onclick="addIngredient('<?= $ingredient ?>')">
                                    <?= $ingredient ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Rechercher
                </button>
                <a href="/recipes" class="btn btn-outline">
                    <i class="fas fa-eraser"></i> Effacer les filtres
                </a>
                <button type="button" class="btn btn-secondary" onclick="saveCurrentFilters()">
                    <i class="fas fa-bookmark"></i> Sauvegarder
                </button>
            </div>

            <!-- Champ caché pour les ingrédients -->
            <input type="hidden" name="ingredients" id="ingredientsInput" value="<?= htmlspecialchars($filters['ingredients'] ?? '') ?>">
        </form>
    </div>

    <!-- Filtres actifs -->
    <?php if (!empty(array_filter($filters))): ?>
        <div class="active-filters-summary">
            <div class="filters-summary-header">
                <span class="results-count">
                    <i class="fas fa-filter"></i> 
                    <?= $totalRecipes ?> recette(s) trouvée(s) avec vos filtres
                </span>
                <a href="/recipes" class="clear-all-filters">
                    <i class="fas fa-times-circle"></i> Effacer tous les filtres
                </a>
            </div>
            <div class="active-filters-tags">
                <?php foreach ($filters as $key => $value): ?>
                    <?php if (!empty($value) && $key !== 'sort' && $key !== 'ingredients_mode'): ?>
                        <span class="active-filter-tag">
                            <?php if ($key === 'ingredients'): ?>
                                <i class="fas fa-carrot"></i> Ingrédients: <?= str_replace(',', ', ', htmlspecialchars($value)) ?>
                            <?php elseif ($key === 'difficulty'): ?>
                                <i class="fas fa-chart-bar"></i> <?= ucfirst(htmlspecialchars($value)) ?>
                            <?php elseif ($key === 'search'): ?>
                                <i class="fas fa-search"></i> "<?= htmlspecialchars($value) ?>"
                            <?php else: ?>
                                <?= ucfirst($key) ?>: <?= htmlspecialchars($value) ?>
                            <?php endif; ?>
                            <a href="<?= removeFilter($key, $_GET) ?>" class="remove-filter">×</a>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Conteneur principal -->
    <div class="main-content">
        <!-- Résultats -->
        <div class="recipes-container">
            <div class="recipes-header">
                <div class="view-options">
                    <button class="view-toggle active" data-view="grid" title="Vue grille">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-toggle" data-view="list" title="Vue liste">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
                
                <div class="recipes-actions">
                    <a href="/recipes/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouvelle recette
                    </a>
                </div>
            </div>

            <?php if (!empty($recipes)): ?>
                <div class="recipes-grid" id="recipes-container">
                    <?php foreach ($recipes as $recipe): ?>
                        <article class="recipe-card">
                            <a href="/recipes/<?= $recipe['id'] ?>" class="recipe-link">
                                <div class="recipe-image">
                                    <?php if (!empty($recipe['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($recipe['image_url']) ?>" 
                                             alt="<?= htmlspecialchars($recipe['titre']) ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-utensils"></i>
                                            <span>Pas d'image</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="recipe-badges">
                                        <span class="difficulty-badge difficulty-<?= $recipe['difficulte'] ?>">
                                            <?php 
                                            $difficultyIcons = ['facile' => '🟢', 'moyen' => '🟡', 'difficile' => '🔴'];
                                            echo $difficultyIcons[$recipe['difficulte']] ?? '';
                                            ?>
                                            <?= ucfirst($recipe['difficulte']) ?>
                                        </span>
                                        
                                        <?php if (($recipe['duree_totale'] ?? 0) <= 30): ?>
                                            <span class="quick-badge">
                                                <i class="fas fa-bolt"></i> Rapide
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Ingrédients correspondants (si filtre actif) -->
                                    <?php if (!empty($filters['ingredients']) && !empty($recipe['matching_ingredients'])): ?>
                                        <div class="matching-ingredients">
                                            <i class="fas fa-check-circle"></i>
                                            <?= count($recipe['matching_ingredients']) ?> ingrédient(s) correspondant(s)
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="recipe-content">
                                    <h3 class="recipe-title"><?= htmlspecialchars($recipe['titre']) ?></h3>
                                    <p class="recipe-description">
                                        <?= htmlspecialchars(substr($recipe['description'], 0, 100)) ?>
                                        <?= strlen($recipe['description']) > 100 ? '...' : '' ?>
                                    </p>
                                    
                                    <div class="recipe-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?= $recipe['duree_totale'] ?? 0 ?> min</span>
                                        </div>
                                        
                                        <div class="meta-item">
                                            <i class="fas fa-users"></i>
                                            <span><?= $recipe['nombre_personnes'] ?> pers.</span>
                                        </div>
                                        
                                        <?php if (($recipe['note_moyenne'] ?? 0) > 0): ?>
                                            <div class="meta-item">
                                                <i class="fas fa-star"></i>
                                                <span><?= number_format($recipe['note_moyenne'], 1) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Affichage des ingrédients principaux -->
                                    <?php if (!empty($recipe['ingredients_preview'])): ?>
                                        <div class="ingredients-preview">
                                            <i class="fas fa-leaf"></i>
                                            <?php 
                                            $ingredients = array_slice($recipe['ingredients_preview'], 0, 3);
                                            echo implode(', ', $ingredients);
                                            if (count($recipe['ingredients_preview']) > 3) {
                                                echo ' et ' . (count($recipe['ingredients_preview']) - 3) . ' autre(s)...';
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination-container">
                        <nav class="pagination">
                            <?php if ($currentPage > 1): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>" 
                                   class="pagination-link">
                                    <i class="fas fa-chevron-left"></i> Précédent
                                </a>
                            <?php endif; ?>
                            
                            <?php 
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            ?>
                            
                            <?php if ($startPage > 1): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                                   class="pagination-link">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span class="pagination-dots">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="pagination-link active"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                                       class="pagination-link"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span class="pagination-dots">...</span>
                                <?php endif; ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>" 
                                   class="pagination-link"><?= $totalPages ?></a>
                            <?php endif; ?>
                            
                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>" 
                                   class="pagination-link">
                                    Suivant <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- État vide -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Aucune recette trouvée</h3>
                    <p>
                        <?php if (!empty($filters['ingredients'])): ?>
                            Aucune recette ne correspond aux ingrédients sélectionnés. 
                            Essayez de retirer quelques ingrédients ou changez le mode de filtrage.
                        <?php else: ?>
                            Essayez de modifier vos critères de recherche ou parcourez toutes nos recettes.
                        <?php endif; ?>
                    </p>
                    <div class="empty-actions">
                        <a href="/recipes" class="btn btn-primary">
                            <i class="fas fa-list"></i> Voir toutes les recettes
                        </a>
                        <a href="/recipes/create" class="btn btn-secondary">
                            <i class="fas fa-plus"></i> Ajouter une recette
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
function removeFilter($filterKey, $params) {
    $newParams = $params;
    unset($newParams[$filterKey]);
    return '?' . http_build_query($newParams);
}
?>

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
    background: #f7fafc;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

/* =================================
   HEADER
================================= */
.page-header {
    text-align: center;
    margin-bottom: 3rem;
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.page-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.page-header h1 i {
    color: #667eea;
}

.page-header p {
    color: #4a5568;
    font-size: 1.1rem;
}

/* =================================
   SECTION DE FILTRES
================================= */
.filters-section {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.filters-row-main {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: 600;
    color: #1a202c;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-group label i {
    color: #667eea;
}

.filter-group input,
.filter-group select {
    padding: 0.875rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* =================================
   SECTION INGRÉDIENTS
================================= */
.ingredients-filter-section {
    border-top: 2px solid #f7fafc;
    padding-top: 2rem;
}

.ingredients-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.ingredients-header label {
    font-weight: 600;
    color: #1a202c;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.1rem;
}

.toggle-ingredients {
    background: #f8f9fa;
    border: 2px solid #e2e8f0;
    color: #4a5568;
    padding: 0.75rem 1.25rem;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
}

.toggle-ingredients:hover {
    border-color: #667eea;
    color: #667eea;
}

.toggle-ingredients.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.toggle-ingredients i {
    transition: transform 0.3s ease;
}

.ingredients-container {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 15px;
    border: 2px solid #e2e8f0;
}

/* =================================
   RECHERCHE D'INGRÉDIENTS
================================= */
.ingredient-search-container {
    position: relative;
    margin-bottom: 2rem;
}

.ingredient-search {
    position: relative;
    display: flex;
    align-items: center;
}

.ingredient-search i {
    position: absolute;
    left: 1rem;
    color: #a0aec0;
    z-index: 2;
}

.ingredient-search input {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    background: white;
    transition: all 0.3s ease;
}

.ingredient-search input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.ingredient-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #e2e8f0;
    border-top: none;
    border-radius: 0 0 12px 12px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 100;
    display: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.suggestion-item {
    padding: 0.875rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 1px solid #f7fafc;
}

.suggestion-item:hover {
    background: #f8f9fa;
}

.suggestion-item i {
    color: #667eea;
}

/* =================================
   INGRÉDIENTS SÉLECTIONNÉS
================================= */
.selected-ingredients {
    margin-bottom: 2rem;
}

.selected-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.selected-header span {
    font-weight: 600;
    color: #1a202c;
}

.clear-ingredients {
    background: #fed7d7;
    color: #c53030;
    border: 1px solid #feb2b2;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
}

.clear-ingredients:hover {
    background: #c53030;
    color: white;
}

.ingredients-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    min-height: 3rem;
    padding: 1rem;
    background: white;
    border: 2px dashed #e2e8f0;
    border-radius: 12px;
    align-items: center;
}

.ingredients-tags.has-ingredients {
    border-style: solid;
    border-color: #667eea;
    background: #f0f4ff;
}

.ingredient-tag {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    animation: slideIn 0.3s ease;
}

.ingredient-tag button {
    background: rgba(255,255,255,0.2);
    color: white;
    border: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: bold;
}

.ingredient-tag button:hover {
    background: rgba(255,255,255,0.4);
    transform: scale(1.1);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* =================================
   OPTIONS D'INGRÉDIENTS
================================= */
.ingredient-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.filter-mode label {
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 0.75rem;
    display: block;
}

.radio-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.radio-option:hover {
    background: rgba(102, 126, 234, 0.1);
}

.radio-option input[type="radio"] {
    margin: 0;
    accent-color: #667eea;
}

.ingredient-stats {
    background: white;
    padding: 1rem;
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #4a5568;
}

/* =================================
   INGRÉDIENTS POPULAIRES
================================= */
.popular-ingredients {
    border-top: 1px solid #e2e8f0;
    padding-top: 1.5rem;
}

.popular-ingredients label {
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 1rem;
    display: block;
}

.popular-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.popular-tag {
    background: white;
    border: 2px solid #e2e8f0;
    color: #4a5568;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    font-weight: 500;
}

.popular-tag:hover {
    border-color: #667eea;
    background: #f0f4ff;
    color: #667eea;
    transform: translateY(-1px);
}

/* =================================
   FILTRES ACTIFS
================================= */
.active-filters-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.filters-summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.results-count {
    font-weight: 600;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.clear-all-filters {
    color: rgba(255,255,255,0.9);
    text-decoration: none;
    padding: 0.5rem 1rem;
    background: rgba(255,255,255,0.2);
    border-radius: 20px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.clear-all-filters:hover {
    background: rgba(255,255,255,0.3);
    color: white;
}

.active-filters-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.active-filter-tag {
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    font-size: 0.9rem;
}

.active-filter-tag i {
    opacity: 0.8;
}

.remove-filter {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    margin-left: 0.5rem;
    font-weight: bold;
    width: 18px;
    height: 18px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.remove-filter:hover {
    background: rgba(255,255,255,0.4);
    color: white;
}

/* =================================
   CONTENEUR PRINCIPAL
================================= */
.main-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

.recipes-container {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.recipes-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f7fafc;
}

.view-options {
    display: flex;
    gap: 0.5rem;
}

.view-toggle {
    background: #f8f9fa;
    border: 2px solid #e2e8f0;
    padding: 0.75rem;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #4a5568;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
}

.view-toggle:hover {
    border-color: #667eea;
    color: #667eea;
}

.view-toggle.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

/* =================================
   GRILLE DES RECETTES
================================= */
.recipes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.recipe-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    transition: all 0.4s ease;
    border: 2px solid transparent;
    position: relative;
}

.recipe-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    border-color: #667eea;
}

.recipe-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.recipe-image {
    position: relative;
    height: 220px;
    overflow: hidden;
}

.recipe-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.recipe-card:hover .recipe-image img {
    transform: scale(1.05);
}

.no-image {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 500;
}

.no-image i {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    opacity: 0.8;
}

.recipe-badges {
    position: absolute;
    top: 1rem;
    left: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    z-index: 2;
}

.difficulty-badge,
.quick-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
}

.difficulty-facile {
    background: rgba(72, 187, 120, 0.9);
    color: white;
}

.difficulty-moyen {
    background: rgba(237, 137, 54, 0.9);
    color: white;
}

.difficulty-difficile {
    background: rgba(245, 101, 101, 0.9);
    color: white;
}

.quick-badge {
    background: rgba(253, 126, 20, 0.9);
    color: white;
}

.matching-ingredients {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    background: rgba(72, 187, 120, 0.9);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.recipe-content {
    padding: 1.5rem;
}

.recipe-title {
    color: #1a202c;
    margin-bottom: 0.75rem;
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.3;
}

.recipe-description {
    color: #4a5568;
    line-height: 1.6;
    margin-bottom: 1rem;
    font-size: 0.95rem;
}

.recipe-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    font-size: 0.9rem;
    color: #4a5568;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: #f7fafc;
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
    font-weight: 500;
}

.meta-item i {
    color: #667eea;
    width: 14px;
}

.ingredients-preview {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    color: #0369a1;
    padding: 0.75rem;
    border-radius: 10px;
    font-size: 0.85rem;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    line-height: 1.4;
}

.ingredients-preview i {
    color: #059669;
    margin-top: 0.1rem;
    flex-shrink: 0;
}

/* =================================
   VUE LISTE
================================= */
.recipes-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.recipes-list .recipe-card {
    display: grid;
    grid-template-columns: 250px 1fr auto;
    gap: 1.5rem;
    align-items: center;
    padding: 1.5rem;
    border-radius: 15px;
}

.recipes-list .recipe-image {
    height: 150px;
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
}

.recipes-list .recipe-content {
    padding: 0;
}

.recipes-list .recipe-meta {
    margin-bottom: 0;
}

.recipes-list .recipe-badges {
    position: static;
    flex-direction: row;
    justify-content: flex-end;
    gap: 0.5rem;
}

.recipes-list .matching-ingredients {
    position: static;
    margin-top: 0.5rem;
}

/* =================================
   PAGINATION
================================= */
.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 3rem;
}

.pagination {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: white;
    padding: 1rem;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.pagination-link {
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: #4a5568;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    transition: all 0.3s ease;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pagination-link:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
    transform: translateY(-2px);
}

.pagination-link.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.pagination-dots {
    padding: 0.75rem 0.5rem;
    color: #a0aec0;
    font-weight: 500;
}

/* =================================
   ÉTAT VIDE
================================= */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #4a5568;
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
    font-size: 1.5rem;
    color: #1a202c;
    margin-bottom: 1rem;
    font-weight: 600;
}

.empty-state p {
    font-size: 1rem;
    margin-bottom: 2rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.empty-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* =================================
   BOUTONS
================================= */
.btn {
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(113, 128, 150, 0.3);
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(113, 128, 150, 0.4);
    color: white;
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
}

.filter-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 1.5rem;
}

/* =================================
   RESPONSIVE
================================= */
@media (max-width: 1024px) {
    .filters-row-main {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .ingredient-options {
        grid-template-columns: 1fr;
    }

    .recipes-grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }
}

@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }

    .page-header {
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-size: 2rem;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filters-section {
        padding: 1.5rem;
    }

    .ingredients-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .filters-summary-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
        text-align: center;
    }

    .active-filters-tags {
        justify-content: center;
    }

    .recipes-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .recipes-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .recipes-list .recipe-card {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .recipes-list .recipe-badges {
        justify-content: center;
    }

    .recipe-meta {
        flex-direction: column;
        gap: 0.5rem;
    }

    .popular-tags {
        justify-content: center;
    }

    .filter-actions {
        flex-direction: column;
    }

    .empty-actions {
        flex-direction: column;
        align-items: center;
    }

    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .page-header h1 {
        font-size: 1.75rem;
    }

    .recipe-card {
        margin: 0 -0.5rem;
    }

    .filters-section,
    .recipes-container {
        padding: 1rem;
    }

    .ingredients-container {
        padding: 1rem;
    }

    .recipe-content {
        padding: 1rem;
    }

    .btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.9rem;
    }

    .filter-group input,
    .filter-group select,
    .ingredient-search input {
        font-size: 16px; /* Évite le zoom sur iOS */
    }

    .popular-tag,
    .ingredient-tag button {
        min-height: 44px; /* Touch targets iOS */
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let selectedIngredients = [];
    const allIngredients = [
        'Tomate', 'Oignon', 'Ail', 'Poulet', 'Bœuf', 'Porc', 'Saumon', 'Thon',
        'Pâtes', 'Riz', 'Pomme de terre', 'Carotte', 'Courgette', 'Aubergine',
        'Poivron', 'Champignon', 'Épinard', 'Salade', 'Basilic', 'Persil',
        'Thym', 'Romarin', 'Huile d\'olive', 'Beurre', 'Crème fraîche',
        'Fromage', 'Mozzarella', 'Parmesan', 'Œuf', 'Lait', 'Farine',
        'Sucre', 'Sel', 'Poivre', 'Paprika', 'Cumin', 'Curry', 'Gingembre'
    ];

    // Initialisation
    initializeIngredients();
    setupIngredientSearch();
    setupViewToggles();
    setupFormValidation();
    setupAnimations();

    // Initialiser les ingrédients sélectionnés depuis l'URL
    function initializeIngredients() {
        const ingredientsInput = document.getElementById('ingredientsInput');
        if (ingredientsInput && ingredientsInput.value) {
            selectedIngredients = ingredientsInput.value.split(',')
                .map(ing => ing.trim())
                .filter(ing => ing);
            updateIngredientsDisplay();
        }
    }

    // Configuration de la recherche d'ingrédients avec debounce
    function setupIngredientSearch() {
        const searchInput = document.getElementById('ingredientSearch');
        const suggestionsContainer = document.getElementById('ingredientSuggestions');

        if (!searchInput || !suggestionsContainer) return;

        const debouncedSearch = debounce(function(e) {
            const query = e.target.value.toLowerCase().trim();
            
            if (query.length < 2) {
                hideSuggestions();
                return;
            }

            const suggestions = allIngredients.filter(ingredient => 
                ingredient.toLowerCase().includes(query) && 
                !selectedIngredients.includes(ingredient)
            );

            showSuggestions(suggestions);
        }, 300);

        searchInput.addEventListener('input', debouncedSearch);

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = e.target.value.trim();
                if (query && !selectedIngredients.includes(query)) {
                    addIngredient(query);
                    e.target.value = '';
                    hideSuggestions();
                }
            }
            
            if (e.key === 'Escape') {
                hideSuggestions();
            }
        });

        // Cacher les suggestions quand on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.ingredient-search-container')) {
                hideSuggestions();
            }
        });
    }

    // Afficher les suggestions
    function showSuggestions(suggestions) {
        const container = document.getElementById('ingredientSuggestions');
        if (!container) return;
        
        if (suggestions.length === 0) {
            hideSuggestions();
            return;
        }

        container.innerHTML = suggestions.map(suggestion => `
            <div class="suggestion-item" onclick="addIngredient('${escapeHtml(suggestion)}')">
                <i class="fas fa-plus-circle"></i>
                <span>${escapeHtml(suggestion)}</span>
            </div>
        `).join('');

        container.style.display = 'block';
    }

    // Cacher les suggestions
    function hideSuggestions() {
        const container = document.getElementById('ingredientSuggestions');
        if (container) {
            container.style.display = 'none';
        }
    }

    // Ajouter un ingrédient avec protection anti-spam
    window.addIngredient = function(ingredient) {
        if (!ingredient || selectedIngredients.includes(ingredient) || !isValidUserAction()) return;
        
        selectedIngredients.push(ingredient);
        updateIngredientsDisplay();
        
        const searchInput = document.getElementById('ingredientSearch');
        if (searchInput) {
            searchInput.value = '';
        }
        hideSuggestions();
        
        // Animation pour le nouvel élément
        setTimeout(() => {
            const newTag = document.querySelector(`.ingredient-tag[data-ingredient="${escapeHtml(ingredient)}"]`);
            if (newTag) {
                newTag.classList.add('new-item');
            }
        }, 50);
    };

    // Supprimer un ingrédient
    window.removeIngredient = function(ingredient) {
        if (!isValidUserAction()) return;
        
        selectedIngredients = selectedIngredients.filter(ing => ing !== ingredient);
        updateIngredientsDisplay();
    };

    // Effacer tous les ingrédients
    window.clearAllIngredients = function() {
        if (!isValidUserAction()) return;
        
        selectedIngredients = [];
        updateIngredientsDisplay();
        showNotification('Tous les ingrédients ont été supprimés', 'info');
    };

    // Mettre à jour l'affichage des ingrédients
    function updateIngredientsDisplay() {
        const tagsContainer = document.getElementById('ingredientsTags');
        const ingredientsInput = document.getElementById('ingredientsInput');
        const statsContainer = document.getElementById('ingredientStats');

        if (!tagsContainer || !ingredientsInput || !statsContainer) return;

        // Mettre à jour les tags avec animation
        tagsContainer.innerHTML = selectedIngredients.map(ingredient => `
            <span class="ingredient-tag" data-ingredient="${escapeHtml(ingredient)}">
                ${escapeHtml(ingredient)}
                <button type="button" onclick="removeIngredient('${escapeHtml(ingredient)}')">×</button>
            </span>
        `).join('');

        // Ajouter/supprimer la classe has-ingredients
        if (selectedIngredients.length > 0) {
            tagsContainer.classList.add('has-ingredients');
        } else {
            tagsContainer.classList.remove('has-ingredients');
        }

        // Mettre à jour le champ caché
        ingredientsInput.value = selectedIngredients.join(',');

        // Mettre à jour les statistiques
        if (selectedIngredients.length > 0) {
            statsContainer.innerHTML = `
                <i class="fas fa-info-circle" style="color: #667eea;"></i>
                ${selectedIngredients.length} ingrédient(s) sélectionné(s)
            `;
        } else {
            statsContainer.innerHTML = `
                <span class="stats-text">Sélectionnez des ingrédients pour voir les statistiques</span>
            `;
        }
    }

    // Toggle du panneau d'ingrédients
    window.toggleIngredientsFilter = function() {
        const container = document.getElementById('ingredientsContainer');
        const toggle = document.querySelector('.toggle-ingredients');
        
        if (!container || !toggle) return;
        
        const toggleText = toggle.querySelector('.toggle-text');
        const toggleIcon = toggle.querySelector('i');

        if (container.style.display === 'none') {
            container.style.display = 'block';
            if (toggleText) toggleText.textContent = 'Masquer les filtres';
            toggle.classList.add('active');
            if (toggleIcon) toggleIcon.style.transform = 'rotate(180deg)';
            
            // Animation d'ouverture
            container.style.opacity = '0';
            container.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                container.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 10);
        } else {
            container.style.opacity = '0';
            container.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                container.style.display = 'none';
                if (toggleText) toggleText.textContent = 'Afficher les filtres';
                toggle.classList.remove('active');
                if (toggleIcon) toggleIcon.style.transform = 'rotate(0deg)';
            }, 300);
        }
    };

    // Configuration des toggles de vue
    function setupViewToggles() {
        const toggles = document.querySelectorAll('.view-toggle');
        const recipesContainer = document.getElementById('recipes-container');

        if (!recipesContainer) return;

        toggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const view = this.dataset.view;
                
                // Mettre à jour les toggles actifs
                toggles.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Animation de transition
                recipesContainer.style.opacity = '0.5';
                
                setTimeout(() => {
                    // Mettre à jour la vue
                    if (view === 'list') {
                        recipesContainer.classList.add('recipes-list');
                        recipesContainer.classList.remove('recipes-grid');
                    } else {
                        recipesContainer.classList.add('recipes-grid');
                        recipesContainer.classList.remove('recipes-list');
                    }
                    
                    recipesContainer.style.opacity = '1';
                }, 150);
            });
        });
    }

    // Validation du formulaire
    function setupFormValidation() {
        const form = document.querySelector('.filters-form');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            const searchInput = document.getElementById('search');
            
            // Validation de la longueur de recherche
            if (searchInput && searchInput.value.length > 100) {
                e.preventDefault();
                showNotification('La recherche ne peut pas dépasser 100 caractères', 'error');
                searchInput.focus();
                return false;
            }

            // Validation du nombre d'ingrédients
            if (selectedIngredients.length > 20) {
                e.preventDefault();
                showNotification('Vous ne pouvez pas sélectionner plus de 20 ingrédients', 'error');
                return false;
            }

            // Afficher un indicateur de chargement
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recherche...';
                submitBtn.disabled = true;
                
                // Restaurer le bouton après un délai (au cas où la page ne se recharge pas)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    }

    // Configuration des animations
    function setupAnimations() {
        // Animation d'entrée pour les cartes de recettes
        const cards = document.querySelectorAll('.recipe-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Optimisation des performances avec will-change
        const animatedElements = document.querySelectorAll('.recipe-card, .btn, .view-toggle');
        animatedElements.forEach(el => {
            el.addEventListener('mouseenter', () => {
                el.style.willChange = 'transform';
            });
            el.addEventListener('mouseleave', () => {
                el.style.willChange = 'auto';
            });
        });
    }

    // Sauvegarder les filtres actuels
    window.saveCurrentFilters = function() {
        try {
            const currentUrl = new URL(window.location);
            const filters = {
                search: currentUrl.searchParams.get('search') || '',
                difficulty: currentUrl.searchParams.get('difficulty') || '',
                ingredients: currentUrl.searchParams.get('ingredients') || '',
                ingredients_mode: currentUrl.searchParams.get('ingredients_mode') || 'any',
                sort: currentUrl.searchParams.get('sort') || 'recent'
            };

            // Sauvegarder dans localStorage avec gestion d'erreurs
            localStorage.setItem('saved_recipe_filters', JSON.stringify(filters));
            showNotification('Filtres sauvegardés avec succès !', 'success');
            
            // Ajouter un bouton pour charger les filtres si pas déjà présent
            addLoadFiltersButton();
        } catch (error) {
            console.error('Erreur lors de la sauvegarde:', error);
            showNotification('Erreur lors de la sauvegarde des filtres', 'error');
        }
    };

    // Charger les filtres sauvegardés
    function loadSavedFilters() {
        try {
            const savedFilters = localStorage.getItem('saved_recipe_filters');
            if (!savedFilters) {
                showNotification('Aucun filtre sauvegardé trouvé', 'info');
                return;
            }

            const filters = JSON.parse(savedFilters);
            
            // Appliquer les filtres sauvegardés
            Object.keys(filters).forEach(key => {
                const element = document.querySelector(`[name="${key}"]`);
                if (element && filters[key]) {
                    if (element.type === 'radio') {
                        const radioElement = document.querySelector(`[name="${key}"][value="${filters[key]}"]`);
                        if (radioElement) radioElement.checked = true;
                    } else {
                        element.value = filters[key];
                    }
                }
            });

            // Traitement spécial pour les ingrédients
            if (filters.ingredients) {
                selectedIngredients = filters.ingredients.split(',')
                    .map(ing => ing.trim())
                    .filter(ing => ing);
                updateIngredientsDisplay();
            }

            showNotification('Filtres sauvegardés chargés !', 'success');
        } catch (error) {
            console.error('Erreur lors du chargement des filtres:', error);
            showNotification('Erreur lors du chargement des filtres sauvegardés', 'error');
        }
    }

    // Ajouter le bouton de chargement des filtres
    function addLoadFiltersButton() {
        if (document.querySelector('.load-filters-btn')) return; // Éviter les doublons

        const filterActions = document.querySelector('.filter-actions');
        if (filterActions && localStorage.getItem('saved_recipe_filters')) {
            const loadButton = document.createElement('button');
            loadButton.type = 'button';
            loadButton.className = 'btn btn-outline load-filters-btn';
            loadButton.innerHTML = '<i class="fas fa-download"></i> Charger filtres sauvegardés';
            loadButton.onclick = loadSavedFilters;
            filterActions.appendChild(loadButton);
        }
    }

    // Utilitaires
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Protection anti-spam
    let lastActionTime = 0;
    const MIN_ACTION_INTERVAL = 100;

    function isValidUserAction() {
        const now = Date.now();
        if (now - lastActionTime < MIN_ACTION_INTERVAL) {
            return false;
        }
        lastActionTime = now;
        return true;
    }

    // Fonction pour afficher des notifications
    function showNotification(message, type = 'info') {
        // Supprimer les notifications existantes
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(n => n.remove());

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            ${escapeHtml(message)}
        `;
        
        // Ajouter les styles si pas déjà présents
        if (!document.querySelector('.notification-styles')) {
            const styles = document.createElement('style');
            styles.className = 'notification-styles';
            styles.textContent = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 1rem 1.5rem;
                    border-radius: 10px;
                    color: white;
                    font-weight: 600;
                    z-index: 9999;
                    animation: slideInRight 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                    max-width: 400px;
                }
                .notification-success {
                    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
                }
                .notification-error {
                    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
                }
                .notification-info {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
                @media (max-width: 480px) {
                    .notification {
                        right: 10px;
                        left: 10px;
                        max-width: none;
                    }
                }
            `;
            document.head.appendChild(styles);
        }

        document.body.appendChild(notification);

        // Supprimer la notification après 4 secondes
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease forwards';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }

    // Lazy loading pour les images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });

        // Observer toutes les images avec data-src
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Gestion du mode hors ligne
    window.addEventListener('online', () => {
        showNotification('Connexion rétablie !', 'success');
    });

    window.addEventListener('offline', () => {
        showNotification('Mode hors ligne - certaines fonctionnalités peuvent être limitées', 'info');
    });

    // Gestion des erreurs globales
    window.addEventListener('error', function(e) {
        console.error('Erreur JavaScript:', e.error);
        showNotification('Une erreur est survenue. Veuillez actualiser la page.', 'error');
    });

    // Support du clavier pour l'accessibilité
    document.addEventListener('keydown', function(e) {
        // Fermer les suggestions avec Escape
        if (e.key === 'Escape') {
            hideSuggestions();
        }
        
        // Raccourcis clavier
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 'k': // Ctrl+K pour focus sur la recherche
                    e.preventDefault();
                    const searchInput = document.getElementById('search');
                    if (searchInput) searchInput.focus();
                    break;
                case 's': // Ctrl+S pour sauvegarder les filtres
                    e.preventDefault();
                    window.saveCurrentFilters();
                    break;
            }
        }
    });

    // Analytics simples (optionnel)
    function trackEvent(category, action, label = '') {
        console.log('Event tracked:', { category, action, label });
        
        // Exemple Google Analytics 4
        if (typeof gtag !== 'undefined') {
            gtag('event', action, {
                event_category: category,
                event_label: label
            });
        }
    }

    // Tracker les interactions importantes
    document.addEventListener('click', (e) => {
        if (e.target.matches('.recipe-card, .recipe-card *')) {
            const card = e.target.closest('.recipe-card');
            const title = card?.querySelector('.recipe-title')?.textContent;
            trackEvent('Recipe', 'View', title);
        }
        
        if (e.target.matches('.popular-tag')) {
            trackEvent('Filter', 'QuickIngredient', e.target.textContent.trim());
        }
        
        if (e.target.matches('.btn-primary')) {
            trackEvent('Search', 'Submit', 'FilterForm');
        }
    });

    // Smooth scroll pour les ancres
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Optimisation des polices
    if ('fonts' in document) {
        Promise.all([
            document.fonts.load('400 1rem Inter'),
            document.fonts.load('600 1rem Inter'),
            document.fonts.load('700 1rem Inter')
        ]).then(() => {
            document.body.classList.add('fonts-loaded');
        });
    }

    // Nettoyage lors de la décharge de la page
    window.addEventListener('beforeunload', () => {
        // Nettoyer les observers
        const observers = window.recipeObservers || [];
        observers.forEach(observer => {
            if (observer && typeof observer.disconnect === 'function') {
                observer.disconnect();
            }
        });
    });

    // Ajouter le bouton de chargement au démarrage s'il y a des filtres sauvegardés
    addLoadFiltersButton();

    // Message de développement (à supprimer en production)
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        console.log('🚀 Code PHP recettes chargé avec succès !');
        console.log('📊 Ingrédients sélectionnés:', selectedIngredients);
        console.log('🔧 Fonctionnalités actives: filtres, recherche, tri, vues, animations');
        console.log('⌨️ Raccourcis: Ctrl+K (recherche), Ctrl+S (sauvegarder), Esc (fermer)');
    }
});

// Fonctions utilitaires globales pour le débogage (à supprimer en production)
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    window.recipesDebug = {
        getSelectedIngredients: () => selectedIngredients,
        addTestIngredient: (ingredient) => window.addIngredient(ingredient),
        clearIngredients: () => window.clearAllIngredients(),
        showTestNotification: (msg, type) => showNotification(msg, type),
        toggleFilters: () => window.toggleIngredientsFilter()
    };
}
</script>