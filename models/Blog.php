<?php
// models/Blog.php

class Blog
{
    private $pdo;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    
    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM blog_posts ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->pdo->prepare($sql);
        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM blog_posts WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($title, $content, $image = null)
    {
        $imagePath = $image ?: '/public/images/blog-default.jpg';
        
        $stmt = $this->pdo->prepare("
            INSERT INTO blog_posts (title, content, image, created_at, updated_at) 
            VALUES (:title, :content, :image, NOW(), NOW())
        ");
        
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':content', $content);
        $stmt->bindValue(':image', $imagePath);
        
        return $stmt->execute();
    }
    
    public function update($id, $title, $content, $image = null)
    {
        if ($image) {
            $stmt = $this->pdo->prepare("
                UPDATE blog_posts 
                SET title = :title, content = :content, image = :image, updated_at = NOW() 
                WHERE id = :id
            ");
            $stmt->bindValue(':image', $image);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE blog_posts 
                SET title = :title, content = :content, updated_at = NOW() 
                WHERE id = :id
            ");
        }
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':content', $content);
        
        return $stmt->execute();
    }
    
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM blog_posts WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function search($query)
    {
        $searchTerm = '%' . $query . '%';
        $stmt = $this->pdo->prepare("
            SELECT * FROM blog_posts 
            WHERE title LIKE ? OR content LIKE ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRecent($limit = 3)
    {
        return $this->getAll($limit);
    }
    
    public function count()
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM blog_posts");
        return $stmt->fetchColumn();
    }
}