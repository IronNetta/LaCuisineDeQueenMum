<?php
// views/blog/show.php
include 'views/layout/header.php';
?>

<div class="container">
    <div class="blog-article">
        <div class="article-header">
            <div class="article-navigation">
                <a href="/blog" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Retour au blog
                </a>
            </div>
            
            <h1 class="article-title"><?= htmlspecialchars($post['title']) ?></h1>
            
            <div class="article-meta">
                <span class="article-date">
                    <i class="fas fa-calendar"></i>
                    Publié le <?= date('d/m/Y à H:i', strtotime($post['created_at'])) ?>
                </span>
                <?php if ($post['updated_at'] !== $post['created_at']): ?>
                    <span class="article-updated">
                        <i class="fas fa-edit"></i>
                        Modifié le <?= date('d/m/Y à H:i', strtotime($post['updated_at'])) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="article-image">
            <img src="<?= htmlspecialchars($post['image']) ?>" 
                 alt="<?= htmlspecialchars($post['title']) ?>"
                 onerror="this.src='/public/images/blog-default.jpg'">
        </div>

        <div class="article-content">
            <?= nl2br(htmlspecialchars($post['content'])) ?>
        </div>

        <div class="article-actions">
            <a href="/blog/<?= $post['id'] ?>/edit" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Modifier cet article
            </a>
            <a href="/blog/<?= $post['id'] ?>/delete" 
               class="btn btn-danger"
               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                <i class="fas fa-trash"></i> Supprimer cet article
            </a>
        </div>
    </div>

    <div class="article-navigation-bottom">
        <a href="/blog" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Voir tous les articles
        </a>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>