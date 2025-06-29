<?php
// views/blog/edit.php
include 'views/layout/header.php';
?>

<div class="container">
    <div class="form-container">
        <div class="form-header">
            <h1><i class="fas fa-edit"></i> Modifier l'article</h1>
            <div class="form-header-actions">
                <a href="/blog/<?= $post['id'] ?>" class="btn btn-outline">
                    <i class="fas fa-eye"></i> Voir l'article
                </a>
                <a href="/blog" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Retour au blog
                </a>
            </div>
        </div>

        <form action="/blog/<?= $post['id'] ?>/edit" method="POST" enctype="multipart/form-data" class="blog-form">
            <div class="form-group">
                <label for="title" class="form-label">
                    <i class="fas fa-heading"></i> Titre de l'article *
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       class="form-input <?= isset($_SESSION['errors']) ? 'error' : '' ?>"
                       value="<?= htmlspecialchars($_SESSION['old']['title'] ?? $post['title']) ?>"
                       placeholder="Entrez le titre de votre article..."
                       required>
            </div>

            <div class="form-group">
                <label for="image" class="form-label">
                    <i class="fas fa-image"></i> Image de l'article
                </label>
                
                <?php if ($post['image']): ?>
                    <div class="current-image">
                        <p class="form-help">Image actuelle :</p>
                        <img src="<?= htmlspecialchars($post['image']) ?>" 
                             alt="Image actuelle" 
                             class="current-image-preview"
                             onerror="this.src='/public/images/blog-default.jpg'">
                    </div>
                <?php endif; ?>
                
                <div class="file-input-container">
                    <input type="file" 
                           id="image" 
                           name="image" 
                           class="form-input-file"
                           accept="image/*">
                    <label for="image" class="file-input-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <?= $post['image'] ? 'Changer l\'image' : 'Choisir une image' ?>
                    </label>
                </div>
                <small class="form-help">
                    Formats acceptés : JPG, PNG, GIF, WEBP (max 5MB). 
                    Laissez vide pour conserver l'image actuelle.
                </small>
                <div class="image-preview" id="imagePreview" style="display: none;">
                    <img id="previewImg" src="" alt="Aperçu">
                    <button type="button" onclick="removeImage()" class="btn btn-sm btn-danger">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="content" class="form-label">
                    <i class="fas fa-align-left"></i> Contenu de l'article *
                </label>
                <textarea id="content" 
                          name="content" 
                          class="form-textarea <?= isset($_SESSION['errors']) ? 'error' : '' ?>"
                          rows="15"
                          placeholder="Rédigez le contenu de votre article..."
                          required><?= htmlspecialchars($_SESSION['old']['content'] ?? $post['content']) ?></textarea>
                <small class="form-help">Minimum 10 caractères</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Sauvegarder les modifications
                </button>
                <a href="/blog/<?= $post['id'] ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

function removeImage() {
    document.getElementById('image').value = '';
    document.getElementById('imagePreview').style.display = 'none';
}
</script>

<?php 
// Nettoyer les données de session
unset($_SESSION['old']);
include 'views/layout/footer.php'; 
?>