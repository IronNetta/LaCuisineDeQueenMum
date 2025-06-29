<?php
// index.php - Version corrigée

session_start();

// Autoloader simple
function autoload($className) {
    $paths = [
        'controllers/' . $className . '.php',
        'models/' . $className . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
}

spl_autoload_register('autoload');

// Configuration des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Récupération de l'URL
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Nettoyer le path
$path = rtrim($path, '/');
if (empty($path)) {
    $path = '/';
}

// Debug pour voir quelle route est demandée
// echo "<!-- Debug: Path demandé: $path -->";

try {
    // Routes principales
    switch ($path) {
        case '':
        case '/':
            $controller = new HomeController();
            $controller->index();
            break;
        
        case '/recipes':
            $controller = new RecipeController();
            $controller->index();
            break;
        
        case '/recipes/create':
            $controller = new RecipeController();
            $controller->create();
            break;
        
        case '/recipes/store':
            $controller = new RecipeController();
            $controller->store();
            break;
        
        case '/api/ingredients/search':
            $controller = new RecipeController();
            $controller->searchIngredients();
            break;
        
        case '/categories':
            $controller = new CategoryController();
            $controller->index();
            break;
        
        // Routes du blog - AJOUT MINIMAL
        case '/blog':
            $controller = new BlogController();
            $controller->index();
            break;
        
        case '/blog/create':
            $controller = new BlogController();
            $controller->create();
            break;
        
        default:
            // Routes dynamiques avec expressions régulières
            if (preg_match('#^/recipes/(\d+)$#', $path, $matches)) {
                $controller = new RecipeController();
                $controller->show($matches[1]);
            } elseif (preg_match('#^/recipes/(\d+)/edit$#', $path, $matches)) {
                $controller = new RecipeController();
                $controller->edit($matches[1]);
            } elseif (preg_match('#^/recipes/(\d+)/update$#', $path, $matches)) {
                $controller = new RecipeController();
                $controller->update($matches[1]);
            } elseif (preg_match('#^/recipes/(\d+)/delete$#', $path, $matches)) {
                $controller = new RecipeController();
                $controller->delete($matches[1]);
            } 
            // Routes blog dynamiques - AJOUT MINIMAL
            elseif (preg_match('#^/blog/(\d+)$#', $path, $matches)) {
                $controller = new BlogController();
                $controller->show($matches[1]);
            } elseif (preg_match('#^/blog/(\d+)/edit$#', $path, $matches)) {
                $controller = new BlogController();
                $controller->edit($matches[1]);
            } elseif (preg_match('#^/blog/(\d+)/delete$#', $path, $matches)) {
                $controller = new BlogController();
                $controller->delete($matches[1]);
            } 
            else {
                // Page 404
                http_response_code(404);
                echo "<h1>404 - Page non trouvée</h1>";
                echo "<p>La page demandée '$path' n'existe pas.</p>";
                echo "<a href='/'>Retour à l'accueil</a>";
            }
            break;
    }
} catch (Exception $e) {
    echo "<h1>Erreur</h1>";
    echo "<p>Une erreur s'est produite : " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<a href='/'>Retour à l'accueil</a>";
}

// Nettoyage des messages de session après affichage
unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old_input']);
?>