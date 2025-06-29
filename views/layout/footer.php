<?php
// views/layout/footer.php
?>
</main>

<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3><i class="fas fa-utensils"></i>Les recettes de Queen Mum</h3>
                <p>Découvrez et partagez vos recettes préférées avec notre communauté passionnée de cuisine.</p>
            </div>

            <div class="footer-section">
                <h4>Navigation</h4>
                <ul>
                    <li><a href="/">Accueil</a></li>
                    <li><a href="/recipes">Toutes les recettes</a></li>
                    <li><a href="/categories">Catégories</a></li>
                    <li><a href="/recipes/create">Ajouter une recette</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Catégories populaires</h4>
                <ul>
                    <li><a href="/recipes?category=6">Plats principaux</a></li>
                    <li><a href="/recipes?category=7">Desserts</a></li>
                    <li><a href="/recipes?category=5">Entrées</a></li>
                    <li><a href="/recipes?category=15">Végétarien</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Suivez-nous</h4>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Les recettes de Queen Mum. Tous droits réservés.</p>
        </div>
    </div>
</footer>

<script src="/public/js/app.js"></script>
</body>
</html>