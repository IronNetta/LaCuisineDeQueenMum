<?php
// controllers/RecipeController.php - Version complète corrigée

require_once 'models/Recipe.php';
require_once 'models/Category.php';
require_once 'models/Ingredient.php';

class RecipeController {
    private $recipeModel;
    private $categoryModel;
    private $ingredientModel;
    
    public function __construct() {
        $this->recipeModel = new Recipe();
        $this->categoryModel = new Category();
        $this->ingredientModel = new Ingredient();
    }
    
    /**
     * Afficher la liste des recettes avec pagination et filtres
     */
    public function index() {
        try {
            // Récupérer les paramètres de la requête
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = 12; // Nombre de recettes par page
            
            // Filtres
            $filters = [
                'category' => $_GET['category'] ?? '',
                'difficulty' => $_GET['difficulty'] ?? '',
                'max_duration' => isset($_GET['duration']) && $_GET['duration'] === 'quick' ? 30 : ($_GET['max_duration'] ?? ''),
                'search' => $_GET['search'] ?? '',
                'sort' => $_GET['sort'] ?? 'recent'
            ];
            
            // Filtrer les valeurs vides
            $filters = array_filter($filters, function($value) {
                return $value !== '';
            });
            
            // Récupérer les recettes
            $recipes = $this->recipeModel->getAll($page, $limit, $filters);
            $totalRecipes = $this->recipeModel->count($filters);
            $totalPages = ceil($totalRecipes / $limit);
            
            // Récupérer les catégories pour les filtres
            $categoriesByType = $this->categoryModel->getOrganizedCategories();
            
            // Récupérer les recettes populaires pour la sidebar
            $popularRecipes = $this->recipeModel->getMostPopular(6);
            
            $data = [
                'recipes' => $recipes,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalRecipes' => $totalRecipes,
                'filters' => $filters,
                'categoriesByType' => $categoriesByType,
                'popularRecipes' => $popularRecipes
            ];
            
            $this->render('recipes/index', $data);
        } catch (Exception $e) {
            error_log("Erreur dans index(): " . $e->getMessage());
            $this->handleError("Erreur lors du chargement des recettes: " . $e->getMessage());
        }
    }
    
    /**
     * Afficher le formulaire de création de recette
     */
    public function create() {
        try {
            // Récupérer les catégories organisées hiérarchiquement
            $categoriesByType = $this->categoryModel->getOrganizedCategories();
            
            // Récupérer tous les ingrédients disponibles pour l'autocomplétion
            $availableIngredients = $this->ingredientModel->getAll();
            
            $data = [
                'categoriesByType' => $categoriesByType,
                'availableIngredients' => $availableIngredients
            ];
            
            $this->render('recipes/create', $data);
        } catch (Exception $e) {
            error_log("Erreur dans create(): " . $e->getMessage());
            $this->handleError("Erreur lors du chargement du formulaire: " . $e->getMessage());
        }
    }
    
    /**
     * Traiter la création d'une nouvelle recette
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/recipes');
            return;
        }
        
        try {
            // Nettoyer et valider les données
            $data = $this->validateAndCleanRecipeData($_POST, $_FILES);
            
            // Commencer une transaction
            $this->recipeModel->beginTransaction();
            
            // Créer la recette
            $recipeId = $this->recipeModel->create($data['recipe']);
            
            if (!$recipeId) {
                throw new Exception("Erreur lors de la création de la recette");
            }
            
            // Traiter l'image si uploadée
            if (!empty($_FILES['image']['tmp_name'])) {
                $imageUrl = $this->handleImageUpload($_FILES['image'], $recipeId);
                if ($imageUrl) {
                    $this->recipeModel->updateImageUrl($recipeId, $imageUrl);
                }
            }
            
            // Ajouter les catégories
            if (!empty($data['categories'])) {
                foreach ($data['categories'] as $categoryId) {
                    $this->recipeModel->addCategory($recipeId, $categoryId);
                }
            }
            
            // Ajouter les ingrédients
            if (!empty($data['ingredients'])) {
                foreach ($data['ingredients'] as $order => $ingredient) {
                    // Créer ou récupérer l'ingrédient
                    $ingredientId = $this->ingredientModel->findOrCreate($ingredient['nom']);
                    
                    // Lier l'ingrédient à la recette
                    $this->recipeModel->addIngredient($recipeId, [
                        'ingredient_id' => $ingredientId,
                        'quantite' => $ingredient['quantite'],
                        'unite' => $ingredient['unite'],
                        'preparation' => $ingredient['preparation'] ?? '',
                        'ordre_affichage' => $order
                    ]);
                }
            }
            
            // Confirmer la transaction
            $this->recipeModel->commit();
            
            // Supprimer les anciennes données du formulaire
            unset($_SESSION['old_input']);
            
            // Rediriger vers la recette créée
            $this->redirect("/recipes/{$recipeId}?success=created");
            
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            if ($this->recipeModel) {
                $this->recipeModel->rollback();
            }
            
            error_log("Erreur dans store(): " . $e->getMessage());
            
            // Conserver les données du formulaire
            $_SESSION['old_input'] = $_POST;
            $_SESSION['error'] = $e->getMessage();
            
            $this->redirect('/recipes/create');
        }
    }
    
    /**
     * Afficher une recette
     */
    public function show($id) {
        try {
            $recipe = $this->recipeModel->getById($id);
            
            if (!$recipe) {
                $this->handleError("Recette non trouvée", 404);
                return;
            }
            
            // Récupérer les détails supplémentaires
            $ingredients = $this->recipeModel->getIngredients($id);
            $categories = $this->recipeModel->getCategories($id);
            $relatedRecipes = $this->recipeModel->getRelatedRecipes($id, 4);
            
            // Incrémenter le nombre de vues
            $this->recipeModel->incrementViews($id);
            
            $data = [
                'recipe' => $recipe,
                'ingredients' => $ingredients,
                'categories' => $categories,
                'relatedRecipes' => $relatedRecipes
            ];
            
            $this->render('recipes/show', $data);
        } catch (Exception $e) {
            error_log("Erreur dans show(): " . $e->getMessage());
            $this->handleError("Erreur lors du chargement de la recette: " . $e->getMessage());
        }
    }
    
    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id) {
        try {
            $recipe = $this->recipeModel->getById($id);
            
            if (!$recipe) {
                $this->handleError("Recette non trouvée", 404);
                return;
            }
            
            // Récupérer les données actuelles de la recette
            $ingredients = $this->recipeModel->getIngredients($id);
            $categories = $this->recipeModel->getCategories($id);
            
            // Extraire les IDs des catégories sélectionnées
            $selectedCategories = array_column($categories, 'id');
            
            // Récupérer toutes les catégories disponibles organisées
            $categoriesByType = $this->categoryModel->getOrganizedCategories();
            
            // Récupérer tous les ingrédients disponibles pour l'autocomplétion
            $availableIngredients = $this->ingredientModel->getAll();
            
            $data = [
                'recipe' => $recipe,
                'ingredients' => $ingredients,
                'categories' => $categories, // Catégories actuelles de la recette
                'selectedCategories' => $selectedCategories,
                'categoriesByType' => $categoriesByType, // Toutes les catégories organisées
                'availableIngredients' => $availableIngredients
            ];
            
            $this->render('recipes/edit', $data);
            
        } catch (Exception $e) {
            error_log("Erreur dans edit(): " . $e->getMessage());
            $this->handleError("Erreur lors du chargement du formulaire: " . $e->getMessage());
        }
    }
    
    /**
     * Traiter la mise à jour d'une recette
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/recipes/{$id}");
            return;
        }
        
        try {
            $recipe = $this->recipeModel->getById($id);
            if (!$recipe) {
                $this->handleError("Recette non trouvée", 404);
                return;
            }
            
            // Valider les données
            $data = $this->validateAndCleanRecipeData($_POST, $_FILES);
            
            // Commencer une transaction
            $this->recipeModel->beginTransaction();
            
            // Mettre à jour la recette
            $updated = $this->recipeModel->update($id, $data['recipe']);
            
            if (!$updated) {
                throw new Exception("Erreur lors de la mise à jour de la recette");
            }
            
            // Traiter la nouvelle image si uploadée
            if (!empty($_FILES['image']['tmp_name'])) {
                $imageUrl = $this->handleImageUpload($_FILES['image'], $id);
                if ($imageUrl) {
                    // Supprimer l'ancienne image si elle existe
                    if (!empty($recipe['image_url']) && file_exists($recipe['image_url'])) {
                        unlink($recipe['image_url']);
                    }
                    $this->recipeModel->updateImageUrl($id, $imageUrl);
                }
            }
            
            // Mettre à jour les catégories
            if (!empty($data['categories'])) {
                // Supprimer les anciennes catégories
                $this->recipeModel->removeAllCategories($id);
                
                // Ajouter les nouvelles catégories
                foreach ($data['categories'] as $categoryId) {
                    $this->recipeModel->addCategory($id, $categoryId);
                }
            }
            
            // Mettre à jour les ingrédients
            if (!empty($data['ingredients'])) {
                // Supprimer les anciens ingrédients
                $this->recipeModel->removeAllIngredients($id);
                
                // Ajouter les nouveaux ingrédients
                foreach ($data['ingredients'] as $order => $ingredient) {
                    // Créer ou récupérer l'ingrédient
                    $ingredientId = $this->ingredientModel->findOrCreate($ingredient['nom']);
                    
                    // Lier l'ingrédient à la recette
                    $this->recipeModel->addIngredient($id, [
                        'ingredient_id' => $ingredientId,
                        'quantite' => $ingredient['quantite'],
                        'unite' => $ingredient['unite'],
                        'preparation' => $ingredient['preparation'] ?? '',
                        'ordre_affichage' => $order
                    ]);
                }
            }
            
            // Confirmer la transaction
            $this->recipeModel->commit();
            
            // Supprimer les anciennes données du formulaire
            unset($_SESSION['old_input']);
            
            $this->redirect("/recipes/{$id}?success=updated");
            
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            if ($this->recipeModel) {
                $this->recipeModel->rollback();
            }
            
            error_log("Erreur dans update(): " . $e->getMessage());
            
            // Conserver les données du formulaire
            $_SESSION['old_input'] = $_POST;
            $_SESSION['error'] = $e->getMessage();
            
            $this->redirect("/recipes/{$id}/edit");
        }
    }
    
    /**
     * Supprimer une recette
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/recipes/{$id}");
            return;
        }
        
        try {
            $recipe = $this->recipeModel->getById($id);
            if (!$recipe) {
                $this->handleError("Recette non trouvée", 404);
                return;
            }
            
            // Supprimer l'image associée
            if (!empty($recipe['image_url']) && file_exists($recipe['image_url'])) {
                unlink($recipe['image_url']);
            }
            
            // Supprimer la recette (les relations seront supprimées automatiquement par les contraintes de clé étrangère)
            $this->recipeModel->delete($id);
            
            $this->redirect('/recipes?success=deleted');
        } catch (Exception $e) {
            error_log("Erreur dans delete(): " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            $this->redirect("/recipes/{$id}");
        }
    }
    
    /**
     * Rechercher des recettes
     */
    public function search() {
        $term = $_GET['q'] ?? '';
        
        if (empty($term)) {
            $this->redirect('/recipes');
            return;
        }
        
        try {
            $recipes = $this->recipeModel->search($term);
            
            $data = [
                'recipes' => $recipes,
                'searchTerm' => $term,
                'totalRecipes' => count($recipes)
            ];
            
            $this->render('recipes/search', $data);
        } catch (Exception $e) {
            error_log("Erreur dans search(): " . $e->getMessage());
            $this->handleError("Erreur lors de la recherche: " . $e->getMessage());
        }
    }
    
    /**
     * Afficher une recette aléatoire
     */
    public function random() {
        try {
            $recipe = $this->recipeModel->getRandom();
            
            if ($recipe) {
                $this->redirect("/recipes/{$recipe['id']}");
            } else {
                $this->redirect('/recipes');
            }
        } catch (Exception $e) {
            error_log("Erreur dans random(): " . $e->getMessage());
            $this->redirect('/recipes');
        }
    }
    
    /**
     * API: Recherche d'ingrédients pour autocomplétion
     */
    public function apiSearchIngredients() {
        header('Content-Type: application/json');
        
        $term = $_GET['term'] ?? '';
        
        if (strlen($term) < 2) {
            echo json_encode([]);
            return;
        }
        
        try {
            $ingredients = $this->ingredientModel->search($term);
            $suggestions = array_map(function($ingredient) {
                return [
                    'id' => $ingredient['id'],
                    'nom' => $ingredient['nom'],
                    'unite_defaut' => $ingredient['unite_mesure_defaut'] ?? ''
                ];
            }, $ingredients);
            
            echo json_encode($suggestions);
        } catch (Exception $e) {
            error_log("Erreur dans apiSearchIngredients(): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Valider et nettoyer les données de la recette
     */
    private function validateAndCleanRecipeData($post, $files) {
        $errors = [];
        
        // Validation des champs obligatoires
        $required = ['titre', 'description', 'difficulte', 'nombre_personnes', 'duree_preparation', 'instructions'];
        foreach ($required as $field) {
            if (empty($post[$field])) {
                $errors[] = "Le champ {$field} est obligatoire";
            }
        }
        
        // Validation du titre
        if (!empty($post['titre']) && (strlen($post['titre']) < 3 || strlen($post['titre']) > 200)) {
            $errors[] = "Le titre doit contenir entre 3 et 200 caractères";
        }
        
        // Validation de la difficulté
        if (!empty($post['difficulte']) && !in_array($post['difficulte'], ['facile', 'moyen', 'difficile'])) {
            $errors[] = "Difficulté invalide";
        }
        
        // Validation des durées
        if (!empty($post['duree_preparation']) && (!is_numeric($post['duree_preparation']) || $post['duree_preparation'] < 1)) {
            $errors[] = "La durée de préparation doit être un nombre positif";
        }
        
        if (!empty($post['duree_cuisson']) && (!is_numeric($post['duree_cuisson']) || $post['duree_cuisson'] < 0)) {
            $errors[] = "La durée de cuisson doit être un nombre positif ou zéro";
        }
        
        if (!empty($post['duree_repos']) && (!is_numeric($post['duree_repos']) || $post['duree_repos'] < 0)) {
            $errors[] = "La durée de repos doit être un nombre positif ou zéro";
        }
        
        // Validation du nombre de personnes
        if (!empty($post['nombre_personnes']) && (!is_numeric($post['nombre_personnes']) || $post['nombre_personnes'] < 1 || $post['nombre_personnes'] > 20)) {
            $errors[] = "Le nombre de personnes doit être entre 1 et 20";
        }
        
        // Validation des catégories
        if (empty($post['categories']) || !is_array($post['categories'])) {
            $errors[] = "Veuillez sélectionner au moins une catégorie";
        } else {
            // Vérifier que les catégories existent
            foreach ($post['categories'] as $categoryId) {
                if (!is_numeric($categoryId)) {
                    $errors[] = "ID de catégorie invalide: {$categoryId}";
                    continue;
                }
                
                try {
                    if (!$this->categoryModel->getById($categoryId)) {
                        $errors[] = "Catégorie invalide: {$categoryId}";
                    }
                } catch (Exception $e) {
                    $errors[] = "Erreur lors de la vérification de la catégorie: {$categoryId}";
                }
            }
        }
        
        // Validation des ingrédients
        $validIngredients = [];
        if (!empty($post['ingredients']) && is_array($post['ingredients'])) {
            foreach ($post['ingredients'] as $ingredient) {
                if (!empty($ingredient['nom']) && !empty($ingredient['quantite'])) {
                    if (!is_numeric($ingredient['quantite']) || $ingredient['quantite'] <= 0) {
                        $errors[] = "Quantité invalide pour l'ingrédient: {$ingredient['nom']}";
                    } else {
                        $validIngredients[] = [
                            'nom' => trim($ingredient['nom']),
                            'quantite' => floatval($ingredient['quantite']),
                            'unite' => trim($ingredient['unite'] ?? ''),
                            'preparation' => trim($ingredient['preparation'] ?? '')
                        ];
                    }
                }
            }
        }
        
        if (empty($validIngredients)) {
            $errors[] = "Veuillez ajouter au moins un ingrédient valide";
        }
        
        // Validation de l'image si uploadée
        if (!empty($files['image']['tmp_name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($files['image']['type'], $allowedTypes)) {
                $errors[] = "Format d'image non supporté. Utilisez JPG, PNG, GIF ou WebP";
            }
            
            if ($files['image']['size'] > $maxSize) {
                $errors[] = "L'image est trop volumineuse (max 5MB)";
            }
            
            if ($files['image']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Erreur lors de l'upload de l'image (code: {$files['image']['error']})";
            }
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }
        
        return [
            'recipe' => [
                'titre' => trim($post['titre']),
                'description' => trim($post['description']),
                'difficulte' => $post['difficulte'],
                'duree_preparation' => intval($post['duree_preparation']),
                'duree_cuisson' => intval($post['duree_cuisson'] ?? 0),
                'duree_repos' => intval($post['duree_repos'] ?? 0),
                'nombre_personnes' => intval($post['nombre_personnes']),
                'instructions' => trim($post['instructions']),
                'conseils' => trim($post['conseils'] ?? ''),
                'statut' => 'publie'
            ],
            'categories' => array_map('intval', $post['categories']),
            'ingredients' => $validIngredients
        ];
    }
    
    /**
     * Gérer l'upload d'image
     */
    private function handleImageUpload($file, $recipeId) {
        try {
            $uploadDir = 'uploads/recipes/';
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception("Impossible de créer le dossier d'upload");
                }
            }
            
            // Vérifier que le dossier est writable
            if (!is_writable($uploadDir)) {
                throw new Exception("Le dossier d'upload n'est pas accessible en écriture");
            }
            
            // Générer un nom unique pour le fichier
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'recipe_' . $recipeId . '_' . uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            // Déplacer le fichier
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Vérifier que le fichier a bien été créé
                if (file_exists($filepath)) {
                    return $filepath;
                } else {
                    throw new Exception("Le fichier n'a pas pu être créé");
                }
            } else {
                throw new Exception("Erreur lors du déplacement du fichier uploadé");
            }
        } catch (Exception $e) {
            // Log l'erreur mais ne pas faire échouer la création de la recette
            error_log("Erreur upload image: " . $e->getMessage());
            return null;
        }
    }
    
    // Méthodes utilitaires
    private function render($view, $data = []) {
        try {
            extract($data);
            
            $viewFile = "views/$view.php";
            if (!file_exists($viewFile)) {
                throw new Exception("Fichier de vue non trouvé: $viewFile");
            }
            
            ob_start();
            include $viewFile;
            $content = ob_get_clean();
            
            // Inclure le layout s'il existe
            if (file_exists('views/layout/header.php')) {
                include 'views/layout/header.php';
            }
            
            echo $content;
            
            if (file_exists('views/layout/footer.php')) {
                include 'views/layout/footer.php';
            }
            
        } catch (Exception $e) {
            error_log("Erreur dans render(): " . $e->getMessage());
            echo "<h1>Erreur de rendu</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    private function redirect($url) {
        // S'assurer qu'aucun output n'a été envoyé
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        } else {
            // Fallback JavaScript si les headers ont déjà été envoyés
            echo "<script>window.location.href = '$url';</script>";
            echo "<noscript><meta http-equiv='refresh' content='0;url=$url'></noscript>";
            exit;
        }
    }
    
    private function handleError($message, $code = 500) {
        // Log l'erreur pour le debugging
        error_log("RecipeController Error [{$code}]: {$message}");
        
        // S'assurer que le code de statut n'a pas déjà été envoyé
        if (!headers_sent()) {
            http_response_code($code);
        }
        
        $data = [
            'error' => $message,
            'code' => $code
        ];
        
        // Vérifier si le fichier d'erreur existe
        if (file_exists('views/errors/error.php')) {
            try {
                $this->render('errors/error', $data);
            } catch (Exception $e) {
                // Si même la vue d'erreur échoue, utiliser le fallback
                $this->renderSimpleError($message, $code);
            }
        } else {
            // Fallback si le fichier d'erreur n'existe pas
            $this->renderSimpleError($message, $code);
        }
    }
    
    /**
     * Rendu d'erreur simple en cas de problème avec la vue d'erreur
     */
    private function renderSimpleError($message, $code) {
        echo "<!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Erreur {$code}</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    text-align: center; 
                    padding: 2rem; 
                    background: #f8f9fa;
                    margin: 0;
                }
                .error-container { 
                    max-width: 500px; 
                    margin: 2rem auto; 
                    background: white; 
                    padding: 2rem; 
                    border-radius: 10px; 
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
                }
                h1 { 
                    color: #dc3545; 
                    margin-bottom: 1rem;
                }
                p {
                    color: #666;
                    line-height: 1.5;
                    margin-bottom: 2rem;
                }
                .btn { 
                    background: #667eea; 
                    color: white; 
                    padding: 0.75rem 1.5rem; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    display: inline-block; 
                    margin-top: 1rem;
                    transition: background-color 0.3s ease;
                }
                .btn:hover {
                    background: #5a6fd8;
                }
                .error-details {
                    background: #f8f9fa;
                    padding: 1rem;
                    border-radius: 5px;
                    margin: 1rem 0;
                    font-family: monospace;
                    font-size: 0.9rem;
                    text-align: left;
                }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <h1>Erreur {$code}</h1>
                <p>" . htmlspecialchars($message) . "</p>
                <div class='error-details'>
                    <strong>Détails techniques:</strong><br>
                    Code d'erreur: {$code}<br>
                    Heure: " . date('Y-m-d H:i:s') . "<br>
                    URL: " . htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') . "
                </div>
                <a href='/' class='btn'>Retour à l'accueil</a>
                <a href='/recipes' class='btn' style='margin-left: 1rem;'>Voir les recettes</a>
            </div>
        </body>
        </html>";
    }
}
?>