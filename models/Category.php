<?php
// models/Category.php - Version mise à jour avec support hiérarchique

require_once 'config/database.php';

class Category {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Récupérer toutes les catégories
    public function getAll() {
        $sql = "SELECT * FROM categories 
                ORDER BY type_categorie, parent_id IS NULL DESC, ordre_affichage, nom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Récupérer les catégories par type
    public function getByType($type) {
        $sql = "SELECT * FROM categories 
                WHERE type_categorie = :type 
                ORDER BY parent_id IS NULL DESC, ordre_affichage, nom";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':type', $type);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Récupérer une catégorie par son ID
    public function getById($id) {
        $sql = "SELECT c.*, p.nom as parent_nom 
                FROM categories c
                LEFT JOIN categories p ON c.parent_id = p.id
                WHERE c.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Récupérer les catégories avec le nombre de recettes (version hiérarchique)
    public function getWithRecipeCount() {
        $sql = "SELECT 
                    c.*,
                    p.nom as parent_nom,
                    COUNT(DISTINCT rc.recette_id) as nb_recettes,
                    CASE 
                        WHEN c.parent_id IS NULL THEN 'parent'
                        ELSE 'enfant'
                    END as niveau_hierarchie
                FROM categories c
                LEFT JOIN categories p ON c.parent_id = p.id
                LEFT JOIN recette_categories rc ON c.id = rc.categorie_id
                LEFT JOIN recettes r ON rc.recette_id = r.id AND r.statut = 'publie'
                GROUP BY c.id, c.nom, c.description, c.type_categorie, c.parent_id, c.ordre_affichage, p.nom
                ORDER BY 
                    c.type_categorie,
                    c.parent_id IS NULL DESC,
                    COALESCE(p.ordre_affichage, c.ordre_affichage),
                    c.ordre_affichage,
                    c.nom";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Organiser les catégories par type avec hiérarchie pour les origines ET types de plats
    public function getOrganizedCategories() {
        $categories = $this->getWithRecipeCount();
        $organized = [];
        
        foreach ($categories as $category) {
            $type = $category['type_categorie'];
            
            if ($type === 'origine') {
                // Pour les origines, créer une structure hiérarchique continent/pays
                if ($category['parent_id'] === null) {
                    // C'est un continent (catégorie parent)
                    $organized[$type]['continents'][$category['id']] = $category;
                    $organized[$type]['continents'][$category['id']]['pays'] = [];
                } else {
                    // C'est un pays (catégorie enfant)
                    $parentId = $category['parent_id'];
                    if (!isset($organized[$type]['continents'][$parentId])) {
                        // Si le continent parent n'existe pas encore, le créer
                        $parent = $this->getById($parentId);
                        $organized[$type]['continents'][$parentId] = $parent;
                        $organized[$type]['continents'][$parentId]['pays'] = [];
                        $organized[$type]['continents'][$parentId]['nb_recettes'] = 0;
                    }
                    $organized[$type]['continents'][$parentId]['pays'][] = $category;
                }
            } elseif ($type === 'type_plat') {
                // Pour les types de plats, créer une structure hiérarchique catégorie/sous-catégorie
                if ($category['parent_id'] === null) {
                    // C'est une catégorie principale (parent)
                    $organized[$type]['categories'][$category['id']] = $category;
                    $organized[$type]['categories'][$category['id']]['sous_categories'] = [];
                } else {
                    // C'est une sous-catégorie (enfant)
                    $parentId = $category['parent_id'];
                    if (!isset($organized[$type]['categories'][$parentId])) {
                        // Si la catégorie parent n'existe pas encore, la créer
                        $parent = $this->getById($parentId);
                        $organized[$type]['categories'][$parentId] = $parent;
                        $organized[$type]['categories'][$parentId]['sous_categories'] = [];
                        $organized[$type]['categories'][$parentId]['nb_recettes'] = 0;
                    }
                    $organized[$type]['categories'][$parentId]['sous_categories'][] = $category;
                }
            } else {
                // Pour les autres types (saison, regime), organisation normale
                $organized[$type][] = $category;
            }
        }
        
        return $organized;
    }
    
    // Récupérer tous les continents (catégories parentes d'origine)
    public function getContinents() {
        $sql = "SELECT c.*, COUNT(enfants.id) as nb_pays,
                       COALESCE(SUM(enfants_recettes.nb_recettes), 0) as nb_recettes_total
                FROM categories c
                LEFT JOIN categories enfants ON c.id = enfants.parent_id
                LEFT JOIN (
                    SELECT categorie_id, COUNT(recette_id) as nb_recettes
                    FROM recette_categories rc
                    INNER JOIN recettes r ON rc.recette_id = r.id AND r.statut = 'publie'
                    GROUP BY categorie_id
                ) enfants_recettes ON enfants.id = enfants_recettes.categorie_id
                WHERE c.type_categorie = 'origine' AND c.parent_id IS NULL
                GROUP BY c.id, c.nom, c.description, c.type_categorie, c.parent_id, c.ordre_affichage
                ORDER BY c.ordre_affichage";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Récupérer toutes les catégories principales de types de plats
    public function getMainTypePlat() {
        $sql = "SELECT c.*, COUNT(enfants.id) as nb_sous_categories,
                       COALESCE(SUM(enfants_recettes.nb_recettes), 0) as nb_recettes_total
                FROM categories c
                LEFT JOIN categories enfants ON c.id = enfants.parent_id
                LEFT JOIN (
                    SELECT categorie_id, COUNT(recette_id) as nb_recettes
                    FROM recette_categories rc
                    INNER JOIN recettes r ON rc.recette_id = r.id AND r.statut = 'publie'
                    GROUP BY categorie_id
                ) enfants_recettes ON enfants.id = enfants_recettes.categorie_id
                WHERE c.type_categorie = 'type_plat' AND c.parent_id IS NULL
                GROUP BY c.id, c.nom, c.description, c.type_categorie, c.parent_id, c.ordre_affichage
                ORDER BY c.ordre_affichage";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Récupérer toutes les sous-catégories d'un type de plat
    public function getSubCategoriesByTypePlat($typePlatId) {
        $sql = "SELECT c.*, COUNT(rc.recette_id) as nb_recettes
                FROM categories c
                LEFT JOIN recette_categories rc ON c.id = rc.categorie_id
                LEFT JOIN recettes r ON rc.recette_id = r.id AND r.statut = 'publie'
                WHERE c.parent_id = :type_plat_id
                GROUP BY c.id, c.nom, c.description, c.type_categorie, c.parent_id, c.ordre_affichage
                ORDER BY c.ordre_affichage, c.nom";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':type_plat_id', $typePlatId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Récupérer les statistiques par type de catégorie
    public function getStatsByType() {
        $sql = "SELECT 
                    type_categorie,
                    COUNT(*) as total_categories,
                    COUNT(CASE WHEN parent_id IS NULL THEN 1 END) as categories_parentes,
                    COUNT(CASE WHEN parent_id IS NOT NULL THEN 1 END) as categories_enfants
                FROM categories
                GROUP BY type_categorie
                ORDER BY type_categorie";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Créer une nouvelle catégorie
    public function create($data) {
        $sql = "INSERT INTO categories (nom, description, type_categorie, parent_id, ordre_affichage)
                VALUES (:nom, :description, :type_categorie, :parent_id, :ordre_affichage)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':nom' => $data['nom'],
            ':description' => $data['description'],
            ':type_categorie' => $data['type_categorie'],
            ':parent_id' => $data['parent_id'] ?? null,
            ':ordre_affichage' => $data['ordre_affichage'] ?? 0
        ]);
    }
    
    // Mettre à jour une catégorie
    public function update($id, $data) {
        $sql = "UPDATE categories SET 
                nom = :nom,
                description = :description,
                type_categorie = :type_categorie,
                parent_id = :parent_id,
                ordre_affichage = :ordre_affichage
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id' => $id,
            ':nom' => $data['nom'],
            ':description' => $data['description'],
            ':type_categorie' => $data['type_categorie'],
            ':parent_id' => $data['parent_id'] ?? null,
            ':ordre_affichage' => $data['ordre_affichage'] ?? 0
        ]);
    }
    
    // Supprimer une catégorie (avec vérification des enfants)
    public function delete($id) {
        // Vérifier s'il y a des catégories enfants
        $sqlCheck = "SELECT COUNT(*) as nb_enfants FROM categories WHERE parent_id = :id";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $result = $stmtCheck->fetch();
        
        if ($result['nb_enfants'] > 0) {
            throw new Exception("Impossible de supprimer cette catégorie car elle contient des sous-catégories.");
        }
        
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    // Vérifier si une catégorie peut être supprimée
    public function canDelete($id) {
        $sql = "SELECT 
                    COUNT(DISTINCT enfants.id) as nb_enfants,
                    COUNT(DISTINCT rc.recette_id) as nb_recettes
                FROM categories c
                LEFT JOIN categories enfants ON c.id = enfants.parent_id
                LEFT JOIN recette_categories rc ON c.id = rc.categorie_id
                WHERE c.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Rechercher des catégories
    public function search($term) {
        $sql = "SELECT c.*, p.nom as parent_nom,
                       COUNT(rc.recette_id) as nb_recettes
                FROM categories c
                LEFT JOIN categories p ON c.parent_id = p.id
                LEFT JOIN recette_categories rc ON c.id = rc.categorie_id
                WHERE c.nom LIKE :term 
                   OR c.description LIKE :term
                GROUP BY c.id
                ORDER BY c.type_categorie, c.nom";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':term', '%' . $term . '%');
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Récupérer le chemin hiérarchique d'une catégorie
    public function getHierarchyPath($id) {
        $sql = "WITH RECURSIVE category_path AS (
                    SELECT id, nom, parent_id, type_categorie, 0 as level
                    FROM categories 
                    WHERE id = :id
                    
                    UNION ALL
                    
                    SELECT c.id, c.nom, c.parent_id, c.type_categorie, cp.level + 1
                    FROM categories c
                    INNER JOIN category_path cp ON c.id = cp.parent_id
                )
                SELECT * FROM category_path ORDER BY level DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>