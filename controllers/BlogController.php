<?php
// controllers/BlogController.php

require_once 'models/Blog.php';
require_once 'config/database.php';

class BlogController
{
    private $blogModel;
    
    public function __construct()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $this->blogModel = new Blog($pdo);
    }
    
    public function index()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 6;
        $offset = ($page - 1) * $limit;
        
        $search = $_GET['search'] ?? '';
        
        if ($search) {
            $posts = $this->blogModel->search($search);
            $totalPosts = count($posts);
            $posts = array_slice($posts, $offset, $limit);
        } else {
            $posts = $this->blogModel->getAll($limit, $offset);
            $totalPosts = $this->blogModel->count();
        }
        
        $totalPages = ceil($totalPosts / $limit);
        
        require 'views/blog/index.php';
    }
    
    public function show($id)
    {
        $post = $this->blogModel->getById($id);
        
        if (!$post) {
            $_SESSION['errors'] = ['Article non trouvé'];
            header('Location: /blog');
            exit;
        }
        
        require 'views/blog/show.php';
    }
    
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validatePost($_POST);
            
            if (empty($errors)) {
                $image = $this->handleImageUpload($_FILES['image'] ?? null);
                
                if ($this->blogModel->create($_POST['title'], $_POST['content'], $image)) {
                    $_SESSION['success'] = 'Article créé avec succès !';
                    header('Location: /blog');
                    exit;
                } else {
                    $errors[] = 'Erreur lors de la création de l\'article';
                }
            }
            
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
        }
        
        require 'views/blog/create.php';
    }
    
    public function edit($id)
    {
        $post = $this->blogModel->getById($id);
        
        if (!$post) {
            $_SESSION['errors'] = ['Article non trouvé'];
            header('Location: /blog');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validatePost($_POST);
            
            if (empty($errors)) {
                $image = $this->handleImageUpload($_FILES['image'] ?? null);
                
                if ($this->blogModel->update($id, $_POST['title'], $_POST['content'], $image)) {
                    $_SESSION['success'] = 'Article modifié avec succès !';
                    header('Location: /blog/' . $id);
                    exit;
                } else {
                    $errors[] = 'Erreur lors de la modification de l\'article';
                }
            }
            
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
        }
        
        require 'views/blog/edit.php';
    }
    
    public function delete($id)
    {
        $post = $this->blogModel->getById($id);
        
        if (!$post) {
            $_SESSION['errors'] = ['Article non trouvé'];
            header('Location: /blog');
            exit;
        }
        
        if ($this->blogModel->delete($id)) {
            // Supprimer l'image si ce n'est pas l'image par défaut
            if ($post['image'] && $post['image'] !== '/public/images/blog-default.jpg') {
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . $post['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $_SESSION['success'] = 'Article supprimé avec succès !';
        } else {
            $_SESSION['errors'] = ['Erreur lors de la suppression de l\'article'];
        }
        
        header('Location: /blog');
        exit;
    }
    
    private function validatePost($data)
    {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = 'Le titre est obligatoire';
        } elseif (strlen($data['title']) > 255) {
            $errors[] = 'Le titre ne peut pas dépasser 255 caractères';
        }
        
        if (empty($data['content'])) {
            $errors[] = 'Le contenu est obligatoire';
        } elseif (strlen($data['content']) < 10) {
            $errors[] = 'Le contenu doit contenir au moins 10 caractères';
        }
        
        return $errors;
    }
    
    private function handleImageUpload($file)
    {
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }
        
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return null;
        }
        
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public/images/blog/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return '/public/images/blog/' . $filename;
        }
        
        return null;
    }
}