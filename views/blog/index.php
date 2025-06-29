<?php
// views/blog/index.php
include 'views/layout/header.php';
?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1><i class="fas fa-blog"></i>La Cuisine</h1>
            <p>Découvrez nos derniers articles culinaires</p>
        </div>
        <div class="page-actions">
            <a href="/blog/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvel Article
            </a>
        </div>
    </div>

    <div class="search-section">
        <form action="/blog" method="GET" class="search-form-blog">
            <div class="search-input-group">
                <input type="text" name="search" placeholder="Rechercher un article..." 
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="search-input">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    <?php if (!empty($posts)): ?>
        <div class="blog-grid">
            <?php foreach ($posts as $post): ?>
                <article class="blog-card">
                    <div class="blog-image">
                        <img src="<?= htmlspecialchars($post['image']) ?>" 
                             alt="<?= htmlspecialchars($post['title']) ?>"
                             onerror="this.src='/public/images/blog-default.jpg'">
                    </div>
                    <div class="blog-content">
                        <h3 class="blog-title">
                            <a href="/blog/<?= $post['id'] ?>">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </h3>
                        <div class="blog-excerpt">
                            <?= htmlspecialchars(substr($post['content'], 0, 150)) ?>...
                        </div>
                        <div class="blog-meta">
                            <span class="blog-date">
                                <i class="fas fa-calendar"></i>
                                <?= date('d/m/Y', strtotime($post['created_at'])) ?>
                            </span>
                        </div>
                        <div class="blog-actions">
                            <a href="/blog/<?= $post['id'] ?>" class="btn btn-outline">
                                <i class="fas fa-eye"></i> Lire
                            </a>
                            <a href="/blog/<?= $post['id'] ?>/edit" class="btn btn-secondary">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="/blog/<?= $post['id'] ?>/delete" 
                               class="btn btn-danger"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                                <i class="fas fa-trash"></i> Supprimer
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="/blog?page=<?= $page - 1 ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" 
                       class="btn btn-outline">
                        <i class="fas fa-chevron-left"></i> Précédent
                    </a>
                <?php endif; ?>

                <span class="pagination-info">
                    Page <?= $page ?> sur <?= $totalPages ?>
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="/blog?page=<?= $page + 1 ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" 
                       class="btn btn-outline">
                        Suivant <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-blog"></i>
            </div>
            <h3>Aucun article trouvé</h3>
            <p>
                <?php if (isset($_GET['search'])): ?>
                    Aucun article ne correspond à votre recherche.
                <?php else: ?>
                    Il n'y a pas encore d'articles sur le blog.
                <?php endif; ?>
            </p>
            <a href="/blog/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Créer le premier article
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'views/layout/footer.php'; ?>