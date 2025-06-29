<?php
// models/Ingredient.php

require_once 'config/database.php';

class Ingredient {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Récupérer tous les ingrédients
     */
    public function getAll() {
        $sql = "SELECT * FROM ingredients ORDER BY nom ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer un ingrédient par son ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM ingredients WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Rechercher un ingrédient par nom
     */
    public function getByName($nom) {
        $sql = "SELECT * FROM ingredients WHERE LOWER(nom) = LOWER(:nom)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nom', $nom);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Trouver ou créer un ingrédient
     */
    public function findOrCreate($nom) {
        // Nettoyer le nom
        $nom = trim($nom);
        
        if (empty($nom)) {
            throw new Exception("Le nom de l'ingrédient ne peut pas être vide");
        }
        
        // Chercher l'ingrédient existant
        $existing = $this->getByName($nom);
        if ($existing) {
            return $existing['id'];
        }
        
        // Créer un nouvel ingrédient
        return $this->create([
            'nom' => $nom,
            'unite_mesure_defaut' => $this->guessDefaultUnit($nom),
            'categorie_ingredient' => $this->guessCategory($nom)
        ]);
    }
    
    /**
     * Créer un nouvel ingrédient
     */
    public function create($data) {
        $sql = "INSERT INTO ingredients (nom, unite_mesure_defaut, categorie_ingredient) 
                VALUES (:nom, :unite_mesure_defaut, :categorie_ingredient)";
        
        $stmt = $this->db->prepare($sql);
        
        $result = $stmt->execute([
            ':nom' => $data['nom'],
            ':unite_mesure_defaut' => $data['unite_mesure_defaut'] ?? 'g',
            ':categorie_ingredient' => $data['categorie_ingredient'] ?? 'autre'
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Mettre à jour un ingrédient
     */
    public function update($id, $data) {
        $sql = "UPDATE ingredients SET 
                nom = :nom,
                unite_mesure_defaut = :unite_mesure_defaut,
                categorie_ingredient = :categorie_ingredient
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id' => $id,
            ':nom' => $data['nom'],
            ':unite_mesure_defaut' => $data['unite_mesure_defaut'],
            ':categorie_ingredient' => $data['categorie_ingredient']
        ]);
    }
    
    /**
     * Supprimer un ingrédient
     */
    public function delete($id) {
        // Vérifier si l'ingrédient est utilisé dans des recettes
        $sqlCheck = "SELECT COUNT(*) as nb_recettes FROM recette_ingredients WHERE ingredient_id = :id";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $result = $stmtCheck->fetch();
        
        if ($result['nb_recettes'] > 0) {
            throw new Exception("Impossible de supprimer cet ingrédient car il est utilisé dans {$result['nb_recettes']} recette(s).");
        }
        
        $sql = "DELETE FROM ingredients WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Rechercher des ingrédients
     */
    public function search($term) {
        $sql = "SELECT * FROM ingredients 
                WHERE nom LIKE :term 
                ORDER BY nom ASC 
                LIMIT 20";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':term', '%' . $term . '%');
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer les ingrédients par catégorie
     */
    public function getByCategory($category) {
        $sql = "SELECT * FROM ingredients 
                WHERE categorie_ingredient = :category 
                ORDER BY nom ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':category', $category);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer les statistiques des ingrédients
     */
    public function getStats() {
        $sql = "SELECT 
                    categorie_ingredient,
                    COUNT(*) as nombre_ingredients,
                    COUNT(DISTINCT ri.recette_id) as nombre_recettes_utilisant
                FROM ingredients i
                LEFT JOIN recette_ingredients ri ON i.id = ri.ingredient_id
                GROUP BY categorie_ingredient
                ORDER BY nombre_ingredients DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer les ingrédients les plus utilisés
     */
    public function getMostUsed($limit = 10) {
        $sql = "SELECT 
                    i.*,
                    COUNT(ri.recette_id) as nombre_utilisations
                FROM ingredients i
                INNER JOIN recette_ingredients ri ON i.id = ri.ingredient_id
                GROUP BY i.id
                ORDER BY nombre_utilisations DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Deviner l'unité par défaut basée sur le nom de l'ingrédient
     */
    private function guessDefaultUnit($nom) {
        $nom = strtolower($nom);
        
        // Liquides
        if (preg_match('/\b(lait|eau|huile|vinaigre|vin|jus|crème|bouillon)\b/', $nom)) {
            return 'ml';
        }
        
        // Œufs et éléments comptables
        if (preg_match('/\b(œuf|oeuf|avocat|pomme|citron|orange|oignon|tomate)\b/', $nom)) {
            return 'pièce';
        }
        
        // Ail
        if (preg_match('/\b(ail|gousse)\b/', $nom)) {
            return 'gousse';
        }
        
        // Épices et herbes en petite quantité
        if (preg_match('/\b(sel|poivre|paprika|cumin|thym|basilic|persil|coriandre)\b/', $nom)) {
            return 'pincée';
        }
        
        // Par défaut, grammes
        return 'g';
    }
    
    /**
     * Deviner la catégorie basée sur le nom de l'ingrédient
     */
    private function guessCategory($nom) {
        $nom = strtolower($nom);
        
        // Légumes
        if (preg_match('/\b(tomate|oignon|carotte|courgette|poivron|épinard|salade|brocoli|chou|radis|navet|betterave|concombre|aubergine)\b/', $nom)) {
            return 'légume';
        }
        
        // Fruits
        if (preg_match('/\b(pomme|poire|banane|orange|citron|fraise|pêche|abricot|raisin|melon|pastèque|ananas|mangue|kiwi)\b/', $nom)) {
            return 'fruit';
        }
        
        // Viandes
        if (preg_match('/\b(bœuf|porc|agneau|veau|poulet|canard|dinde|jambon|bacon|saucisse|chorizo|lard)\b/', $nom)) {
            return 'viande';
        }
        
        // Poissons
        if (preg_match('/\b(saumon|thon|cabillaud|sole|truite|sardine|maquereau|crevette|moule|huître|crabe)\b/', $nom)) {
            return 'poisson';
        }
        
        // Produits laitiers
        if (preg_match('/\b(lait|crème|beurre|fromage|yaourt|mozzarella|parmesan|gruyère|camembert|chèvre)\b/', $nom)) {
            return 'laitage';
        }
        
        // Épices et herbes
        if (preg_match('/\b(sel|poivre|paprika|cumin|thym|basilic|persil|coriandre|cannelle|muscade|gingembre|curry|safran)\b/', $nom)) {
            return 'épice';
        }
        
        // Féculents
        if (preg_match('/\b(farine|riz|pâtes|pomme de terre|quinoa|avoine|blé|orge|semoule|pain|biscotte)\b/', $nom)) {
            return 'féculent';
        }
        
        // Par défaut
        return 'autre';
    }
    
    /**
     * Importer des ingrédients depuis un fichier CSV
     */
    public function importFromCSV($csvFile) {
        if (!file_exists($csvFile)) {
            throw new Exception("Fichier CSV non trouvé");
        }
        
        $imported = 0;
        $errors = [];
        
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            // Ignorer la première ligne (en-têtes)
            fgetcsv($handle, 1000, ",");
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                try {
                    if (count($data) >= 1 && !empty(trim($data[0]))) {
                        $ingredientData = [
                            'nom' => trim($data[0]),
                            'unite_mesure_defaut' => isset($data[1]) ? trim($data[1]) : $this->guessDefaultUnit($data[0]),
                            'categorie_ingredient' => isset($data[2]) ? trim($data[2]) : $this->guessCategory($data[0])
                        ];
                        
                        // Vérifier si l'ingrédient existe déjà
                        if (!$this->getByName($ingredientData['nom'])) {
                            $this->create($ingredientData);
                            $imported++;
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = "Ligne " . ($imported + 1) . ": " . $e->getMessage();
                }
            }
            fclose($handle);
        }
        
        return [
            'imported' => $imported,
            'errors' => $errors
        ];
    }
    
    /**
     * Exporter les ingrédients vers un fichier CSV
     */
    public function exportToCSV($filename = null) {
        if (!$filename) {
            $filename = 'ingredients_export_' . date('Y-m-d_H-i-s') . '.csv';
        }
        
        $ingredients = $this->getAll();
        
        $output = fopen('php://output', 'w');
        
        // En-têtes
        fputcsv($output, ['nom', 'unite_mesure_defaut', 'categorie_ingredient', 'created_at']);
        
        // Données
        foreach ($ingredients as $ingredient) {
            fputcsv($output, [
                $ingredient['nom'],
                $ingredient['unite_mesure_defaut'],
                $ingredient['categorie_ingredient'],
                $ingredient['created_at']
            ]);
        }
        
        fclose($output);
        
        return $filename;
    }
    
    /**
     * Nettoyer les ingrédients non utilisés
     */
    public function cleanUnused() {
        $sql = "DELETE FROM ingredients 
                WHERE id NOT IN (
                    SELECT DISTINCT ingredient_id 
                    FROM recette_ingredients 
                    WHERE ingredient_id IS NOT NULL
                )";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute();
        
        if ($result) {
            return $stmt->rowCount();
        }
        
        return 0;
    }
    
    /**
     * Fusionner deux ingrédients (en cas de doublons)
     */
    public function merge($keepId, $removeId) {
        try {
            $this->db->beginTransaction();
            
            // Mettre à jour toutes les références vers l'ingrédient à conserver
            $sql = "UPDATE recette_ingredients 
                    SET ingredient_id = :keep_id 
                    WHERE ingredient_id = :remove_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':keep_id' => $keepId,
                ':remove_id' => $removeId
            ]);
            
            // Supprimer l'ingrédient dupliqué
            $this->delete($removeId);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Trouver les doublons potentiels
     */
    public function findPotentialDuplicates() {
        $sql = "SELECT 
                    i1.id as id1,
                    i1.nom as nom1,
                    i2.id as id2,
                    i2.nom as nom2,
                    CASE 
                        WHEN SOUNDEX(i1.nom) = SOUNDEX(i2.nom) THEN 'soundex'
                        WHEN LEVENSHTEIN(LOWER(i1.nom), LOWER(i2.nom)) <= 2 THEN 'levenshtein'
                        ELSE 'similar'
                    END as match_type
                FROM ingredients i1
                JOIN ingredients i2 ON i1.id < i2.id
                WHERE SOUNDEX(i1.nom) = SOUNDEX(i2.nom)
                   OR LEVENSHTEIN(LOWER(i1.nom), LOWER(i2.nom)) <= 2
                   OR i1.nom LIKE CONCAT('%', i2.nom, '%')
                   OR i2.nom LIKE CONCAT('%', i1.nom, '%')
                ORDER BY match_type, i1.nom";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>