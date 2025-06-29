// public/js/app.js

document.addEventListener('DOMContentLoaded', function() {
    // Navigation mobile
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
    }

    // Gestion des ingrédients dynamiques
    const ingredientsContainer = document.querySelector('.ingredients-container');
    const addIngredientBtn = document.querySelector('.add-ingredient-btn');

    if (ingredientsContainer && addIngredientBtn) {
        let ingredientIndex = document.querySelectorAll('.ingredient-item').length;

        // Ajouter un nouvel ingrédient
        addIngredientBtn.addEventListener('click', function() {
            const ingredientItem = createIngredientItem(ingredientIndex);
            ingredientsContainer.insertBefore(ingredientItem, addIngredientBtn);
            ingredientIndex++;

            // Ajouter l'autocomplétion au nouvel input
            setupIngredientAutocomplete(ingredientItem.querySelector('.ingredient-name'));
        });

        // Supprimer un ingrédient
        ingredientsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-ingredient')) {
                e.target.closest('.ingredient-item').remove();
            }
        });

        // Setup autocomplétion pour les ingrédients existants
        document.querySelectorAll('.ingredient-name').forEach(input => {
            setupIngredientAutocomplete(input);
        });
    }

    // Prévisualisation d'image
    const imageInput = document.querySelector('input[type="file"][name="image"]');
    const imagePreview = document.querySelector('.image-preview');

    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (imagePreview) {
                        imagePreview.innerHTML = `<img src="${e.target.result}" alt="Prévisualisation" style="max-width: 200px; height: auto; border-radius: 10px;">`;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Confirmation de suppression
    const deleteButtons = document.querySelectorAll('.delete-recipe-btn');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette recette ?')) {
                e.preventDefault();
            }
        });
    });

    // Animation des cartes au scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observer toutes les cartes
    document.querySelectorAll('.recipe-card, .category-card, .stat-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});

// Créer un nouvel élément ingrédient
function createIngredientItem(index) {
    const div = document.createElement('div');
    div.className = 'ingredient-item';
    div.innerHTML = `
        <div class="form-group">
            <input type="text" 
                   name="ingredients[${index}][nom]" 
                   class="form-input ingredient-name" 
                   placeholder="Nom de l'ingrédient"
                   required>
        </div>
        <div class="form-group">
            <input type="number" 
                   name="ingredients[${index}][quantite]" 
                   class="form-input" 
                   placeholder="Quantité"
                   step="0.1"
                   min="0"
                   required>
        </div>
        <div class="form-group">
            <select name="ingredients[${index}][unite]" class="form-select">
                <option value="">Unité</option>
                <option value="g">grammes</option>
                <option value="kg">kilogrammes</option>
                <option value="ml">millilitres</option>
                <option value="l">litres</option>
                <option value="c. à thé">cuillère à thé</option>
                <option value="c. à soupe">cuillère à soupe</option>
                <option value="tasse">tasse</option>
                <option value="pièce">pièce(s)</option>
                <option value="gousse">gousse(s)</option>
                <option value="pincée">pincée</option>
                <option value="poignée">poignée</option>
            </select>
        </div>
        <button type="button" class="btn-remove remove-ingredient" title="Supprimer cet ingrédient">
            <i class="fas fa-times"></i>
        </button>
    `;
    return div;
}

// Configuration de l'autocomplétion pour les ingrédients
function setupIngredientAutocomplete(input) {
    let timeoutId;
    let currentFocus = -1;

    input.addEventListener('input', function() {
        const value = this.value;

        // Supprimer les suggestions existantes
        closeAllLists();

        if (!value || value.length < 2) return;

        // Debounce pour éviter trop de requêtes
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fetchIngredientSuggestions(value, input);
        }, 300);
    });

    input.addEventListener('keydown', function(e) {
        const suggestions = document.querySelector('.ingredient-suggestions');
        if (suggestions) {
            const items = suggestions.querySelectorAll('.suggestion-item');

            if (e.keyCode === 40) { // Flèche bas
                e.preventDefault();
                currentFocus++;
                addActive(items);
            } else if (e.keyCode === 38) { // Flèche haut
                e.preventDefault();
                currentFocus--;
                addActive(items);
            } else if (e.keyCode === 13) { // Entrée
                e.preventDefault();
                if (currentFocus > -1 && items[currentFocus]) {
                    items[currentFocus].click();
                }
            } else if (e.keyCode === 27) { // Échap
                closeAllLists();
            }
        }
    });

    // Fermer les suggestions si on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.ingredient-autocomplete')) {
            closeAllLists();
        }
    });

    function fetchIngredientSuggestions(query, targetInput) {
        fetch(`/api/ingredients/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(ingredients => {
                if (ingredients.length > 0) {
                    showSuggestions(ingredients, targetInput);
                }
            })
            .catch(error => {
                console.error('Erreur lors de la recherche d\'ingrédients:', error);
            });
    }

    function showSuggestions(ingredients, targetInput) {
        closeAllLists();

        const suggestionsDiv = document.createElement('div');
        suggestionsDiv.className = 'ingredient-suggestions';
        suggestionsDiv.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        `;

        ingredients.forEach(ingredient => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.style.cssText = `
                padding: 10px;
                cursor: pointer;
                border-bottom: 1px solid #eee;
                transition: background-color 0.2s;
            `;
            item.textContent = ingredient.nom;

            item.addEventListener('mouseenter', function() {
                removeActive();
                this.classList.add('active');
            });

            item.addEventListener('click', function() {
                targetInput.value = ingredient.nom;
                closeAllLists();

                // Mettre à jour l'unité de mesure si disponible
                const uniteSelect = targetInput.closest('.ingredient-item').querySelector('select[name*="[unite]"]');
                if (uniteSelect && ingredient.unite_mesure) {
                    uniteSelect.value = ingredient.unite_mesure;
                }
            });

            suggestionsDiv.appendChild(item);
        });

        // Wrapper pour le positionnement relatif
        const wrapper = document.createElement('div');
        wrapper.className = 'ingredient-autocomplete';
        wrapper.style.position = 'relative';

        targetInput.parentNode.insertBefore(wrapper, targetInput);
        wrapper.appendChild(targetInput);
        wrapper.appendChild(suggestionsDiv);
    }

    function addActive(items) {
        removeActive();
        if (currentFocus >= items.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = items.length - 1;
        if (items[currentFocus]) {
            items[currentFocus].classList.add('active');
            items[currentFocus].style.backgroundColor = '#667eea';
            items[currentFocus].style.color = 'white';
        }
    }

    function removeActive() {
        const items = document.querySelectorAll('.suggestion-item');
        items.forEach(item => {
            item.classList.remove('active');
            item.style.backgroundColor = '';
            item.style.color = '';
        });
    }

    function closeAllLists() {
        const suggestions = document.querySelectorAll('.ingredient-suggestions');
        suggestions.forEach(suggestion => suggestion.remove());
        currentFocus = -1;
    }
}

// Utilitaires
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#28a745' : '#dc3545'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Animation d'apparition
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Suppression automatique
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}