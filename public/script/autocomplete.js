/**
 * Gestion de l'autocomplétion pour les champs de ville
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des champs d'autocomplétion
    setupAutocomplete('departure-city', 'departure-city-results');
    setupAutocomplete('arrival-city', 'arrival-city-results');
    
    /**
     * Configure l'autocomplétion pour un champ spécifique
     * @param {string} inputId - ID du champ de saisie
     * @param {string} resultsId - ID du conteneur pour les résultats
     */
    function setupAutocomplete(inputId, resultsId) {
        const inputField = document.getElementById(inputId);
        const resultsContainer = document.getElementById(resultsId);
        
        if (!inputField || !resultsContainer) return;
        
        let currentFocus = -1;
        let timer = null;
        
        // Événement d'entrée dans le champ
        inputField.addEventListener('input', function() {
            // Effacer le timer précédent
            if (timer) {
                clearTimeout(timer);
            }
            
            const query = this.value.trim();
            
            // Si la requête est trop courte, masquer les résultats
            if (query.length < 2) {
                closeAllLists();
                return;
            }
            
            // Définir un délai avant d'envoyer la requête (pour éviter trop de requêtes)
            timer = setTimeout(function() {
                fetchCities(query, resultsContainer);
            }, 300);
        });
        
        // Navigation au clavier dans les résultats
        inputField.addEventListener('keydown', function(e) {
            const items = resultsContainer.getElementsByClassName('autocomplete-result');
            
            if (items.length === 0) return;
            
            // Touche flèche bas
            if (e.key === 'ArrowDown') {
                currentFocus++;
                addActive(items);
                e.preventDefault();
            } 
            // Touche flèche haut
            else if (e.key === 'ArrowUp') {
                currentFocus--;
                addActive(items);
                e.preventDefault();
            } 
            // Touche Entrée
            else if (e.key === 'Enter' && currentFocus > -1) {
                if (items[currentFocus]) {
                    items[currentFocus].click();
                    e.preventDefault();
                }
            }
        });
        
        // Fermer les listes lorsqu'on clique ailleurs
        document.addEventListener('click', function(e) {
            if (e.target !== inputField) {
                closeAllLists();
            }
        });
        
        /**
         * Récupère les suggestions de villes depuis le serveur
         * @param {string} query - Terme de recherche
         * @param {HTMLElement} container - Conteneur pour les résultats
         */
        function fetchCities(query, container) {
            console.log('Recherche de villes pour:', query);
            
            fetch(`includes/cities.php?term=${encodeURIComponent(query)}`)
                .then(response => {
                    console.log('Réponse reçue:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Données reçues:', data);
                    
                    // Vider le conteneur de résultats
                    container.innerHTML = '';
                    
                    // Si aucun résultat, masquer le conteneur
                    if (!data || data.length === 0) {
                        container.classList.remove('visible');
                        return;
                    }
                    
                    // Ajouter chaque ville aux résultats
                    data.forEach(city => {
                        const item = document.createElement('div');
                        item.classList.add('autocomplete-result');
                        item.textContent = city;
                        
                        // Ajouter un gestionnaire de clic
                        item.addEventListener('click', function() {
                            inputField.value = city;
                            closeAllLists();
                        });
                        
                        container.appendChild(item);
                    });
                    
                    // Afficher le conteneur
                    container.classList.add('visible');
                    
                    // Réinitialiser la position active
                    currentFocus = -1;
                })
                .catch(error => {
                    console.error('Erreur lors de la récupération des villes:', error);
                    container.classList.remove('visible');
                });
        }
        
        /**
         * Ajoute la classe 'selected' à l'élément actif dans la liste
         * @param {HTMLCollection} items - Liste des éléments de résultat
         */
        function addActive(items) {
            if (!items) return;
            
            // Supprimer la classe 'selected' de tous les éléments
            removeActive(items);
            
            // Ajuster currentFocus si hors limites
            if (currentFocus >= items.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = items.length - 1;
            
            // Ajouter la classe 'selected' à l'élément actif
            items[currentFocus].classList.add('selected');
            
            // Faire défiler si nécessaire pour rendre visible l'élément actif
            items[currentFocus].scrollIntoView({ block: 'nearest' });
        }
        
        /**
         * Supprime la classe 'selected' de tous les éléments
         * @param {HTMLCollection} items - Liste des éléments de résultat
         */
        function removeActive(items) {
            Array.from(items).forEach(item => {
                item.classList.remove('selected');
            });
        }
        
        /**
         * Ferme toutes les listes d'autocomplétion sauf celle spécifiée
         * @param {HTMLElement} [element] - Élément à ne pas fermer
         */
        function closeAllLists(element) {
            const lists = document.getElementsByClassName('autocomplete-results');
            
            Array.from(lists).forEach(list => {
                if (element !== list) {
                    list.classList.remove('visible');
                }
            });
        }
    }
});