<?php
// controllers/HomeController.php

require_once 'models/Recipe.php';
require_once 'models/Category.php';

class HomeController {
    private $recipeModel;
    private $categoryModel;

    public function __construct() {
        $this->recipeModel = new Recipe();
        $this->categoryModel = new Category();
    }

    public function index() {
        // Récupérer les dernières recettes
        $latestRecipes = $this->recipeModel->getAll(6, 0);

        // Récupérer les catégories avec le nombre de recettes
        $categories = $this->categoryModel->getWithRecipeCount();

        // Organiser les catégories par type
        $categoriesByType = [];
        foreach ($categories as $category) {
            $categoriesByType[$category['type_categorie']][] = $category;
        }

        $data = [
            'latestRecipes' => $latestRecipes,
            'categoriesByType' => $categoriesByType
        ];

        $this->render('home/index', $data);
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
}
?>
