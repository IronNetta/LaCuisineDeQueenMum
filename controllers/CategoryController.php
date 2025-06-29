<?php
// controllers/CategoryController.php - Version mise à jour

require_once 'models/Category.php';

class CategoryController {
    private $categoryModel;
    
    public function __construct() {
        $this->categoryModel = new Category();
    }
    
    // Page principale des catégories avec structure hiérarchique
    public function index() {
        try {
            // Récupérer les catégories organisées par type
            $categoriesByType = $this->categoryModel->getOrganizedCategories();
            
            // Récupérer les statistiques
            $stats = $this->categoryModel->getStatsByType();
            
            $data = [
                'categoriesByType' => $categoriesByType,
                'stats' => $stats
            ];
            
            $this->render('categories/index', $data);
        } catch (Exception $e) {
            $this->handleError("Erreur lors du chargement des catégories: " . $e->getMessage());
        }
    }
    
    // Afficher les détails d'un continent avec ses pays
    public function continent($id) {
        try {
            $continent = $this->categoryModel->getById($id);
            
            if (!$continent || $continent['type_categorie'] !== 'origine' || $continent['parent_id'] !== null) {
                $this->handleError("Continent non trouvé", 404);
                return;
            }
            
            $countries = $this->categoryModel->getCountriesByContinent($id);
            
            $data = [
                'continent' => $continent,
                'countries' => $countries
            ];
            
            $this->render('categories/continent', $data);
        } catch (Exception $e) {
            $this->handleError("Erreur lors du chargement du continent: " . $e->getMessage());
        }
    }
    
    // Afficher les catégories d'un type spécifique
    public function byType($type) {
        try {
            $validTypes = ['saison', 'type_plat', 'origine', 'regime'];
            
            if (!in_array($type, $validTypes)) {
                $this->handleError("Type de catégorie invalide", 400);
                return;
            }
            
            if ($type === 'origine') {
                // Pour les origines, utiliser la structure hiérarchique
                $categoriesByType = $this->categoryModel->getOrganizedCategories();
                $categories = $categoriesByType['origine'] ?? [];
            } else {
                // Pour les autres types, structure simple
                $categories = $this->categoryModel->getByType($type);
            }
            
            $data = [
                'type' => $type,
                'categories' => $categories,
                'typeTitle' => $this->getTypeTitle($type)
            ];
            
            $this->render('categories/by_type', $data);
        } catch (Exception $e) {
            $this->handleError("Erreur lors du chargement des catégories: " . $e->getMessage());
        }
    }
    
    // Afficher les détails d'une catégorie
    public function show($id) {
        try {
            $category = $this->categoryModel->getById($id);
            
            if (!$category) {
                $this->handleError("Catégorie non trouvée", 404);
                return;
            }
            
            // Si c'est un continent, récupérer ses pays
            $countries = [];
            if ($category['type_categorie'] === 'origine' && $category['parent_id'] === null) {
                $countries = $this->categoryModel->getCountriesByContinent($id);
            }
            
            // Récupérer le chemin hiérarchique
            $hierarchyPath = $this->categoryModel->getHierarchyPath($id);
            
            $data = [
                'category' => $category,
                'countries' => $countries,
                'hierarchyPath' => $hierarchyPath
            ];
            
            $this->render('categories/show', $data);
        } catch (Exception $e) {
            $this->handleError("Erreur lors du chargement de la catégorie: " . $e->getMessage());
        }
    }
    
    // Rechercher des catégories
    public function search() {
        $term = $_GET['q'] ?? '';
        
        if (empty($term)) {
            $this->redirect('/categories');
            return;
        }
        
        try {
            $categories = $this->categoryModel->search($term);
            
            $data = [
                'categories' => $categories,
                'searchTerm' => $term
            ];
            
            $this->render('categories/search', $data);
        } catch (Exception $e) {
            $this->handleError("Erreur lors de la recherche: " . $e->getMessage());
        }
    }
    
    // API: Récupérer les pays d'un continent (pour AJAX)
    public function apiGetCountries($continentId) {
        header('Content-Type: application/json');
        
        try {
            $countries = $this->categoryModel->getCountriesByContinent($continentId);
            echo json_encode([
                'success' => true,
                'countries' => $countries
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    // API: Statistiques des catégories
    public function apiStats() {
        header('Content-Type: application/json');
        
        try {
            $stats = $this->categoryModel->getStatsByType();
            $continents = $this->categoryModel->getContinents();
            
            echo json_encode([
                'success' => true,
                'stats' => $stats,
                'continents' => $continents
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    // Créer une nouvelle catégorie (formulaire)
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'nom' => $_POST['nom'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'type_categorie' => $_POST['type_categorie'] ?? '',
                    'parent_id' => !empty($_POST['parent_id']) ? $_POST['parent_id'] : null,
                    'ordre_affichage' => $_POST['ordre_affichage'] ?? 0
                ];
                
                if ($this->categoryModel->create($data)) {
                    $this->redirect('/categories?success=created');
                } else {
                    throw new Exception("Erreur lors de la création de la catégorie");
                }
            } catch (Exception $e) {
                $this->handleError("Erreur lors de la création: " . $e->getMessage());
            }
        } else {
            // Afficher le formulaire
            $continents = $this->categoryModel->getContinents();
            $this->render('categories/create', ['continents' => $continents]);
        }
    }
    
    // Modifier une catégorie
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'nom' => $_POST['nom'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'type_categorie' => $_POST['type_categorie'] ?? '',
                    'parent_id' => !empty($_POST['parent_id']) ? $_POST['parent_id'] : null,
                    'ordre_affichage' => $_POST['ordre_affichage'] ?? 0
                ];
                
                if ($this->categoryModel->update($id, $data)) {
                    $this->redirect('/categories?success=updated');
                } else {
                    throw new Exception("Erreur lors de la modification de la catégorie");
                }
            } catch (Exception $e) {
                $this->handleError("Erreur lors de la modification: " . $e->getMessage());
            }
        } else {
            // Afficher le formulaire
            $category = $this->categoryModel->getById($id);
            $continents = $this->categoryModel->getContinents();
            
            if (!$category) {
                $this->handleError("Catégorie non trouvée", 404);
                return;
            }
            
            $this->render('categories/edit', [
                'category' => $category,
                'continents' => $continents
            ]);
        }
    }
    
    // Supprimer une catégorie
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Vérifier si la suppression est possible
                $canDelete = $this->categoryModel->canDelete($id);
                
                if ($canDelete['nb_enfants'] > 0) {
                    throw new Exception("Impossible de supprimer cette catégorie car elle contient des sous-catégories.");
                }
                
                if ($canDelete['nb_recettes'] > 0) {
                    throw new Exception("Impossible de supprimer cette catégorie car elle est utilisée par des recettes.");
                }
                
                if ($this->categoryModel->delete($id)) {
                    $this->redirect('/categories?success=deleted');
                } else {
                    throw new Exception("Erreur lors de la suppression de la catégorie");
                }
            } catch (Exception $e) {
                $this->handleError("Erreur lors de la suppression: " . $e->getMessage());
            }
        } else {
            // Afficher la confirmation
            $category = $this->categoryModel->getById($id);
            $canDelete = $this->categoryModel->canDelete($id);
            
            if (!$category) {
                $this->handleError("Catégorie non trouvée", 404);
                return;
            }
            
            $this->render('categories/delete', [
                'category' => $category,
                'canDelete' => $canDelete
            ]);
        }
    }
    
    // Méthodes utilitaires
    private function getTypeTitle($type) {
        $titles = [
            'saison' => 'Saisons',
            'type_plat' => 'Types de plats',
            'origine' => 'Origines culinaires',
            'regime' => 'Régimes alimentaires'
        ];
        
        return $titles[$type] ?? ucfirst($type);
    }
    
    private function render($view, $data = []) {
        extract($data);
        
        ob_start();
        include "views/$view.php";
        $content = ob_get_clean();
        
        include 'views/layout/header.php';
        echo $content;
        include 'views/layout/footer.php';
    }
    
    private function redirect($url) {
        header("Location: $url");
        exit;
    }
    
    private function handleError($message, $code = 500) {
        http_response_code($code);
        
        $data = [
            'error' => $message,
            'code' => $code
        ];
        
        $this->render('errors/error', $data);
    }
}
?>