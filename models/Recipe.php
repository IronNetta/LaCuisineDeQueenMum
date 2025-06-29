<?php
// models/Recipe.php - Version corrigée pour correspondre au contrôleur

require_once 'config/database.php';

class Recipe {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Créer une nouvelle recette
     */
    public function create($data) {
        $sql = "INSERT INTO recettes (
                    titre, description, difficulte, duree_preparation, duree_cuisson, duree_repos,
                    nombre_personnes, instructions, conseils, statut, auteur_nom
                ) VALUES (
                    :titre, :description, :difficulte, :duree_preparation, :duree_cuisson, :duree_repos,
                    :nombre_personnes, :instructions, :conseils, :statut, :auteur_nom
                )";
        
        $stmt = $this->db->prepare($sql);
        
        $result = $stmt->execute([
            ':titre' => $data['titre'],
            ':description' => $data['description'],
            ':difficulte' => $data['difficulte'],
            ':duree_preparation' => $data['duree_preparation'],
            ':duree_cuisson' => $data['duree_cuisson'] ?? 0,
            ':duree_repos' => $data['duree_repos'] ?? 0,
            ':nombre_personnes' => $data['nombre_personnes'],
            ':instructions' => $data['instructions'],
            ':conseils' => $data['conseils'] ?? '',
            ':statut' => $data['statut'] ?? 'publie',
            ':auteur_nom' => $data['auteur_nom'] ?? 'Anonyme'
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Récupérer une recette par son ID avec toutes les informations
     */
    public function getById($id) {
        $sql = "SELECT r.*, 
                       COALESCE(r.duree_preparation, 0) + COALESCE(r.duree_cuisson, 0) + COALESCE(r.duree_repos, 0) as duree_totale,
                       COALESCE(r.nombre_vues, 0) as nombre_vues,
                       COUNT(DISTINCT ri.id) as nombre_ingredients,
                       COUNT(DISTINCT rc.id) as nombre_categories
                FROM recettes r
                LEFT JOIN recette_ingredients ri ON r.id = ri.recette_id
                LEFT JOIN recette_categories rc ON r.id = rc.recette_id
                WHERE r.id = :id
                GROUP BY r.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($recipe) {
            // Récupérer les ingrédients et catégories séparément pour éviter les problèmes de GROUP BY
            $recipe['ingredients'] = $this->getIngredients($id);
            $recipe['categories'] = $this->getCategories($id);
        }
        
        return $recipe;
    }
    
    /**
     * Récupérer toutes les recettes avec pagination
     */
    public function getAll($page = 1, $limit = 12, $filters = []) {
        $offset = ($page - 1) * $limit;
        $conditions = ['r.statut = :statut'];
        $params = [':statut' => 'publie'];
        
        // Filtres
        if (!empty($filters['category'])) {
            $conditions[] = "EXISTS (
                SELECT 1 FROM recette_categories rc 
                WHERE rc.recette_id = r.id AND rc.categorie_id = :category
            )";
            $params[':category'] = $filters['category'];
        }
        
        if (!empty($filters['difficulty'])) {
            $conditions[] = "r.difficulte = :difficulty";
            $params[':difficulty'] = $filters['difficulty'];
        }
        
        if (!empty($filters['max_duration'])) {
            $conditions[] = "(COALESCE(r.duree_preparation, 0) + COALESCE(r.duree_cuisson, 0)) <= :max_duration";
            $params[':max_duration'] = $filters['max_duration'];
        }
        
        if (!empty($filters['search'])) {
            $conditions[] = "(r.titre LIKE :search OR r.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        // Construction de la requête
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        // Tri
        $orderBy = "ORDER BY ";
        switch ($filters['sort'] ?? 'recent') {
            case 'popular':
                $orderBy .= "COALESCE(r.nombre_vues, 0) DESC, COALESCE(r.note_moyenne, 0) DESC";
                break;
            case 'rating':
                $orderBy .= "COALESCE(r.note_moyenne, 0) DESC, COALESCE(r.nombre_votes, 0) DESC";
                break;
            case 'duration':
                $orderBy .= "(COALESCE(r.duree_preparation, 0) + COALESCE(r.duree_cuisson, 0)) ASC";
                break;
            default:
                // Utiliser created_at s'il existe, sinon trier par ID décroissant
                $orderBy .= "COALESCE(r.created_at, r.id) DESC";
        }
        
        $sql = "SELECT r.*, 
                       COALESCE(r.duree_preparation, 0) + COALESCE(r.duree_cuisson, 0) + COALESCE(r.duree_repos, 0) as duree_totale,
                       COALESCE(r.nombre_vues, 0) as nombre_vues
                FROM recettes r
                {$whereClause}
                {$orderBy}
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Compter le nombre total de recettes (pour la pagination)
     */
    public function count($filters = []) {
        $conditions = ['r.statut = :statut'];
        $params = [':statut' => 'publie'];
        
        // Appliquer les mêmes filtres que dans getAll()
        if (!empty($filters['category'])) {
            $conditions[] = "EXISTS (
                SELECT 1 FROM recette_categories rc 
                WHERE rc.recette_id = r.id AND rc.categorie_id = :category
            )";
            $params[':category'] = $filters['category'];
        }
        
        if (!empty($filters['difficulty'])) {
            $conditions[] = "r.difficulte = :difficulty";
            $params[':difficulty'] = $filters['difficulty'];
        }
        
        if (!empty($filters['max_duration'])) {
            $conditions[] = "(COALESCE(r.duree_preparation, 0) + COALESCE(r.duree_cuisson, 0)) <= :max_duration";
            $params[':max_duration'] = $filters['max_duration'];
        }
        
        if (!empty($filters['search'])) {
            $conditions[] = "(r.titre LIKE :search OR r.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT COUNT(*) as total FROM recettes r {$whereClause}";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)($result['total'] ?? 0);
    }
    
    /**
     * Récupérer les ingrédients d'une recette
     */
    public function getIngredients($recipeId) {
        $sql = "SELECT 
                    ri.*,
                    i.nom as nom,
                    i.categorie_ingredient
                FROM recette_ingredients ri
                JOIN ingredients i ON ri.ingredient_id = i.id
                WHERE ri.recette_id = :recipe_id
                ORDER BY ri.ordre_affichage ASC, i.nom ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer les catégories d'une recette
     */
    public function getCategories($recipeId) {
        $sql = "SELECT 
                    c.*,
                    parent.nom as parent_nom
                FROM recette_categories rc
                JOIN categories c ON rc.categorie_id = c.id
                LEFT JOIN categories parent ON c.parent_id = parent.id
                WHERE rc.recette_id = :recipe_id
                ORDER BY c.type_categorie, c.nom";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Ajouter une catégorie à une recette
     */
    public function addCategory($recipeId, $categoryId) {
        $sql = "INSERT IGNORE INTO recette_categories (recette_id, categorie_id) 
                VALUES (:recipe_id, :category_id)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':recipe_id' => $recipeId,
            ':category_id' => $categoryId
        ]);
    }
    
    /**
     * Ajouter un ingrédient à une recette
     */
    public function addIngredient($recipeId, $data) {
        $sql = "INSERT INTO recette_ingredients 
                (recette_id, ingredient_id, quantite, unite, preparation, ordre_affichage)
                VALUES (:recipe_id, :ingredient_id, :quantite, :unite, :preparation, :ordre_affichage)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':recipe_id' => $recipeId,
            ':ingredient_id' => $data['ingredient_id'],
            ':quantite' => $data['quantite'],
            ':unite' => $data['unite'] ?? '',
            ':preparation' => $data['preparation'] ?? '',
            ':ordre_affichage' => $data['ordre_affichage'] ?? 0
        ]);
    }
    
    /**
     * Mettre à jour l'URL de l'image
     */
    public function updateImageUrl($recipeId, $imageUrl) {
        $sql = "UPDATE recettes SET image_url = :image_url WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':image_url' => $imageUrl,
            ':id' => $recipeId
        ]);
    }
    
    /**
     * Incrémenter le nombre de vues
     */
    public function incrementViews($recipeId) {
        $sql = "UPDATE recettes SET nombre_vues = COALESCE(nombre_vues, 0) + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $recipeId]);
    }
    
    /**
     * Récupérer les recettes similaires
     */
    public function getRelatedRecipes($recipeId, $limit = 4) {
        $sql = "SELECT DISTINCT r.*, 
                       COALESCE(r.duree_preparation, 0) + COALESCE(r.duree_cuisson, 0) as duree_totale,
                       COALESCE(r.nombre_vues, 0) as nombre_vues
                FROM recettes r
                JOIN recette_categories rc ON r.id = rc.recette_id
                WHERE rc.categorie_id IN (
                    SELECT rc2.categorie_id 
                    FROM recette_categories rc2 
                    WHERE rc2.recette_id = :recipe_id
                )
                AND r.id != :recipe_id2
                AND r.statut = 'publie'
                ORDER BY COALESCE(r.note_moyenne, 0) DESC, COALESCE(r.nombre_vues, 0) DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
        $stmt->bindValue(':recipe_id2', $recipeId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer une recette aléatoire
     */
    public function getRandom() {
        $sql = "SELECT * FROM recettes 
                WHERE statut = 'publie' 
                ORDER BY RAND() 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer les recettes les plus populaires
     */
    public function getMostPopular($limit = 6) {
        $sql = "SELECT *, 
                       COALESCE(duree_preparation, 0) + COALESCE(duree_cuisson, 0) as duree_totale,
                       COALESCE(nombre_vues, 0) as nombre_vues
                FROM recettes 
                WHERE statut = 'publie'
                ORDER BY COALESCE(nombre_vues, 0) DESC, COALESCE(note_moyenne, 0) DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer les recettes les mieux notées
     */
    public function getTopRated($limit = 6) {
        $sql = "SELECT *, 
                       COALESCE(duree_preparation, 0) + COALESCE(duree_cuisson, 0) as duree_totale,
                       COALESCE(nombre_vues, 0) as nombre_vues
                FROM recettes 
                WHERE statut = 'publie' AND COALESCE(nombre_votes, 0) >= 3
                ORDER BY COALESCE(note_moyenne, 0) DESC, COALESCE(nombre_votes, 0) DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Rechercher des recettes
     */
    public function search($term, $limit = 20) {
        // Version simple sans FULLTEXT (plus compatible)
        $sql = "SELECT *, 
                       COALESCE(duree_preparation, 0) + COALESCE(duree_cuisson, 0) as duree_totale,
                       COALESCE(nombre_vues, 0) as nombre_vues
                FROM recettes 
                WHERE statut = 'publie'
                AND (titre LIKE :like_term 
                     OR description LIKE :like_term
                     OR instructions LIKE :like_term)
                ORDER BY 
                    CASE 
                        WHEN titre LIKE :exact_term THEN 1
                        WHEN titre LIKE :start_term THEN 2
                        WHEN description LIKE :start_term THEN 3
                        ELSE 4
                    END,
                    COALESCE(nombre_vues, 0) DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':like_term', '%' . $term . '%');
        $stmt->bindValue(':exact_term', $term);
        $stmt->bindValue(':start_term', $term . '%');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Gestion des transactions
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    public function commit() {
        return $this->db->commit();
    }
    
    public function rollback() {
        return $this->db->rollback();
    }
    
    /**
     * Mettre à jour une recette
     */
    public function update($id, $data) {
        $sql = "UPDATE recettes SET 
                titre = :titre,
                description = :description,
                difficulte = :difficulte,
                duree_preparation = :duree_preparation,
                duree_cuisson = :duree_cuisson,
                duree_repos = :duree_repos,
                nombre_personnes = :nombre_personnes,
                instructions = :instructions,
                conseils = :conseils
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id' => $id,
            ':titre' => $data['titre'],
            ':description' => $data['description'],
            ':difficulte' => $data['difficulte'],
            ':duree_preparation' => $data['duree_preparation'],
            ':duree_cuisson' => $data['duree_cuisson'] ?? 0,
            ':duree_repos' => $data['duree_repos'] ?? 0,
            ':nombre_personnes' => $data['nombre_personnes'],
            ':instructions' => $data['instructions'],
            ':conseils' => $data['conseils'] ?? ''
        ]);
    }
    
    /**
     * Supprimer une recette et toutes ses relations
     */
    public function delete($id) {
        try {
            $this->beginTransaction();
            
            // Supprimer les relations avec les catégories
            $sql = "DELETE FROM recette_categories WHERE recette_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            // Supprimer les relations avec les ingrédients
            $sql = "DELETE FROM recette_ingredients WHERE recette_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            // Supprimer la recette
            $sql = "DELETE FROM recettes WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $id]);
            
            $this->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Changer le statut d'une recette
     */
    public function updateStatus($id, $status) {
        $validStatuses = ['brouillon', 'publie', 'archive'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Statut invalide");
        }
        
        $sql = "UPDATE recettes SET statut = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);
    }
    
    /**
     * Supprimer toutes les catégories d'une recette
     */
    public function removeAllCategories($recipeId) {
        $sql = "DELETE FROM recette_categories WHERE recette_id = :recipe_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':recipe_id' => $recipeId]);
    }
    
    /**
     * Supprimer tous les ingrédients d'une recette
     */
    public function removeAllIngredients($recipeId) {
        $sql = "DELETE FROM recette_ingredients WHERE recette_id = :recipe_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':recipe_id' => $recipeId]);
    }
    
    /**
     * Vérifier si une recette existe
     */
    public function exists($id) {
        $sql = "SELECT COUNT(*) as count FROM recettes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }
    
    /**
     * Récupérer les statistiques d'une recette
     */
    public function getStats($id) {
        $sql = "SELECT 
                    COUNT(DISTINCT rc.categorie_id) as nb_categories,
                    COUNT(DISTINCT ri.ingredient_id) as nb_ingredients,
                    COALESCE(r.nombre_vues, 0) as vues,
                    COALESCE(r.note_moyenne, 0) as note,
                    COALESCE(r.nombre_votes, 0) as votes
                FROM recettes r
                LEFT JOIN recette_categories rc ON r.id = rc.recette_id
                LEFT JOIN recette_ingredients ri ON r.id = ri.recette_id
                WHERE r.id = :id
                GROUP BY r.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>