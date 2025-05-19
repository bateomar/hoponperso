document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            // Toggle display style instead of using a class
            if (mobileMenu.style.display === 'block') {
                mobileMenu.style.display = 'none';
            } else {
                mobileMenu.style.display = 'block';
            }
        });
    }
    
    // Price range slider functionality
    initPriceRangeSlider();
    
    // Search functionality
    const searchButton = document.getElementById('search-button');
    const departureCityInput = document.getElementById('departure-city');
    const arrivalCityInput = document.getElementById('arrival-city');
    const departureDateInput = document.getElementById('departure-date');
    const departureTimeInput = document.getElementById('departure-time');
    
    if (searchButton) {
        searchButton.addEventListener('click', function() {
            applyFilters();
        });
    }
    
    // Add keypress event listeners to input fields
    [departureCityInput, arrivalCityInput, departureDateInput, departureTimeInput].forEach(input => {
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyFilters();
                }
            });
        }
    });
    
    // Sort buttons functionality
    const sortButtons = document.querySelectorAll('.sort-btn');
    
    sortButtons.forEach(button => {
        button.addEventListener('click', function() {
            const sortType = this.getAttribute('data-sort');
            updateURLParameter('sort', sortType);
            applyFilters();
        });
    });
    
    // Apply filters button
    const applyFiltersBtn = document.getElementById('apply-filters');
    
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            applyFilters();
        });
    }
    
    // Rating and passenger filter change event
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    const passengerInputs = document.querySelectorAll('input[name="passengers"]');
    
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Auto-apply when changed
            updateURLParameter('rating', this.value);
        });
    });
    
    passengerInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Auto-apply when changed
            updateURLParameter('passengers', this.value);
        });
    });
    
    // New ride button (placeholder for now)
    const newRideBtn = document.getElementById('new-ride-btn');
    
    if (newRideBtn) {
        newRideBtn.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Fonctionnalité à venir: Créer un nouveau trajet');
        });
    }
});

/**
 * Initialize price range slider
 */
function initPriceRangeSlider() {
    const priceRangeInput = document.getElementById('price-range');
    
    if (!priceRangeInput) return;
    
    const minThumb = document.getElementById('min-thumb');
    const maxThumb = document.getElementById('max-thumb');
    const track = document.querySelector('.range-slider-track');
    const minPriceDisplay = document.getElementById('min-price-display');
    const maxPriceDisplay = document.getElementById('max-price-display');
    
    // Get min and max values from the input
    const min = parseInt(priceRangeInput.getAttribute('min'), 10);
    const max = parseInt(priceRangeInput.getAttribute('max'), 10);
    
    // Initial values (from URL params or default)
    const urlParams = new URLSearchParams(window.location.search);
    let minValue = urlParams.get('min_price') ? parseInt(urlParams.get('min_price'), 10) : min;
    let maxValue = urlParams.get('max_price') ? parseInt(urlParams.get('max_price'), 10) : max;
    
    // Set initial position of thumbs
    updateSlider(minValue, maxValue);
    
    // Create two hidden input elements for min and max values
    const minInput = document.createElement('input');
    minInput.type = 'range';
    minInput.min = min;
    minInput.max = max;
    minInput.value = minValue;
    minInput.classList.add('min-price-input');
    minInput.style.display = 'none';
    
    const maxInput = document.createElement('input');
    maxInput.type = 'range';
    maxInput.min = min;
    maxInput.max = max;
    maxInput.value = maxValue;
    maxInput.classList.add('max-price-input');
    maxInput.style.display = 'none';
    
    priceRangeInput.parentNode.appendChild(minInput);
    priceRangeInput.parentNode.appendChild(maxInput);
    
    // Add event listeners for thumb dragging
    if (minThumb) {
        minThumb.addEventListener('mousedown', function(e) {
            document.addEventListener('mousemove', moveMinThumb);
            document.addEventListener('mouseup', stopDrag);
            e.preventDefault();
        });
        
        minThumb.addEventListener('touchstart', function(e) {
            document.addEventListener('touchmove', moveMinThumb);
            document.addEventListener('touchend', stopDrag);
            e.preventDefault();
        });
    }
    
    if (maxThumb) {
        maxThumb.addEventListener('mousedown', function(e) {
            document.addEventListener('mousemove', moveMaxThumb);
            document.addEventListener('mouseup', stopDrag);
            e.preventDefault();
        });
        
        maxThumb.addEventListener('touchstart', function(e) {
            document.addEventListener('touchmove', moveMaxThumb);
            document.addEventListener('touchend', stopDrag);
            e.preventDefault();
        });
    }
    
    function moveMinThumb(e) {
        let x = e.clientX || (e.touches && e.touches[0].clientX);
        let rect = priceRangeInput.getBoundingClientRect();
        let pos = Math.min(Math.max(0, x - rect.left), rect.width);
        let percentage = pos / rect.width;
        let value = Math.round(percentage * (max - min) + min);
        
        // Ensure min doesn't exceed max
        value = Math.min(value, maxValue - 1);
        
        minValue = value;
        minInput.value = value;
        updateSlider(minValue, maxValue);
    }
    
    function moveMaxThumb(e) {
        let x = e.clientX || (e.touches && e.touches[0].clientX);
        let rect = priceRangeInput.getBoundingClientRect();
        let pos = Math.min(Math.max(0, x - rect.left), rect.width);
        let percentage = pos / rect.width;
        let value = Math.round(percentage * (max - min) + min);
        
        // Ensure max doesn't go below min
        value = Math.max(value, minValue + 1);
        
        maxValue = value;
        maxInput.value = value;
        updateSlider(minValue, maxValue);
    }
    
    function stopDrag() {
        document.removeEventListener('mousemove', moveMinThumb);
        document.removeEventListener('mousemove', moveMaxThumb);
        document.removeEventListener('touchmove', moveMinThumb);
        document.removeEventListener('touchmove', moveMaxThumb);
        document.removeEventListener('mouseup', stopDrag);
        document.removeEventListener('touchend', stopDrag);
        
        // Update URL parameters
        updateURLParameter('min_price', minValue);
        updateURLParameter('max_price', maxValue);
    }
    
    function updateSlider(min, max) {
        const range = max - min;
        const totalRange = parseInt(priceRangeInput.getAttribute('max'), 10) - parseInt(priceRangeInput.getAttribute('min'), 10);
        const minPos = ((min - parseInt(priceRangeInput.getAttribute('min'), 10)) / totalRange) * 100;
        const maxPos = ((max - parseInt(priceRangeInput.getAttribute('min'), 10)) / totalRange) * 100;
        
        if (track) {
            track.style.left = minPos + '%';
            track.style.width = (maxPos - minPos) + '%';
        }
        
        if (minThumb) {
            minThumb.style.left = minPos + '%';
        }
        
        if (maxThumb) {
            maxThumb.style.left = maxPos + '%';
        }
        
        if (minPriceDisplay) {
            minPriceDisplay.textContent = min + '€';
        }
        
        if (maxPriceDisplay) {
            maxPriceDisplay.textContent = max + '€';
        }
    }
}

/**
 * Apply filters and refresh the results
 */
function applyFilters() {
    // Get all filter values
    const minPrice = document.getElementById('min-price-display').textContent.replace('€', '');
    const maxPrice = document.getElementById('max-price-display').textContent.replace('€', '');
    
    // Get city search values
    const departureCity = document.getElementById('departure-city')?.value || '';
    const arrivalCity = document.getElementById('arrival-city')?.value || '';
    
    // Get date and time values
    const departureDate = document.getElementById('departure-date')?.value || '';
    const departureTime = document.getElementById('departure-time')?.value || '';
    
    // Get selected rating
    let rating = 0;
    const selectedRating = document.querySelector('input[name="rating"]:checked');
    if (selectedRating) {
        rating = selectedRating.value;
    }
    
    // Get selected number of passengers
    let passengers = 0;
    const selectedPassengers = document.querySelector('input[name="passengers"]:checked');
    if (selectedPassengers) {
        passengers = selectedPassengers.value;
    }
    
    // Get sort order
    let sort = 'newest';
    const activeSortBtn = document.querySelector('.sort-btn.active');
    if (activeSortBtn) {
        sort = activeSortBtn.getAttribute('data-sort');
    }
    
    // Update URL parameters
    updateURLParameter('min_price', minPrice);
    updateURLParameter('max_price', maxPrice);
    updateURLParameter('departure_city', departureCity);
    updateURLParameter('arrival_city', arrivalCity);
    updateURLParameter('departure_date', departureDate);
    updateURLParameter('departure_time', departureTime);
    updateURLParameter('rating', rating);
    updateURLParameter('passengers', passengers);
    updateURLParameter('sort', sort);
    
    // Reload page with new filters
    window.location.reload();
}

/**
 * Fetch rides using AJAX
 * 
 * @param {Object} params Filter parameters
 */
function fetchRides(params) {
    // Show loading indicator
    const ridesContainer = document.getElementById('rides-container');
    ridesContainer.innerHTML = '<div class="loading">Chargement...</div>';
    
    // Build query string
    const queryString = Object.keys(params)
        .filter(key => params[key] !== null && params[key] !== '')
        .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(params[key]))
        .join('&');
    
    // Fetch rides from API
    fetch('includes/search.php?' + queryString)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRides(data.rides);
            } else {
                ridesContainer.innerHTML = `<div class="no-rides">
                    <p>Erreur: ${data.message || "Une erreur s'est produite."}</p>
                </div>`;
            }
        })
        .catch(error => {
            console.error('Error fetching rides:', error);
            ridesContainer.innerHTML = `<div class="no-rides">
                <p>Une erreur s'est produite lors de la récupération des trajets.</p>
            </div>`;
        });
}

/**
 * Display fetched rides in the container
 * 
 * @param {Array} rides Array of ride objects
 */
function displayRides(rides) {
    const ridesContainer = document.getElementById('rides-container');
    
    if (!rides || rides.length === 0) {
        ridesContainer.innerHTML = `<div class="no-rides">
            <p>Aucun trajet ne correspond à votre recherche.</p>
            <p>Essayez de modifier vos filtres ou effectuez une nouvelle recherche.</p>
        </div>`;
        return;
    }
    
    let html = '';
    
    rides.forEach(ride => {
        // Créer deux boutons - un pour les détails et un pour la réservation
        html += `
        <div class="ride-card">
            <div class="ride-info">
                <div class="ride-route">
                    <h3>${ride.depart} - ${ride.destination}</h3>
                </div>
                <div class="ride-price">
                    <span>${ride.prix} €</span>
                </div>
                <div class="ride-timeline">
                    <div class="timeline-start">${new Date(ride.date_heure_depart).toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'})}</div>
                    <div class="timeline-line">
                        <div class="timeline-duration">~2h</div>
                    </div>
                    <div class="timeline-end">${new Date(new Date(ride.date_heure_depart).getTime() + 2*60*60*1000).toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'})}</div>
                </div>
                <div class="ride-view-map">
                    <i class="fas fa-map-marker-alt"></i> Voir sur la carte
                </div>
                <div class="ride-actions">
                    <a href="ride_details.php?id=${ride.id}" class="ride-btn details-btn">
                        <i class="fas fa-info-circle"></i> Détails
                    </a>
                    <a href="http://localhost/HopOn/app/views/trip_reservation/trip_reservation.php?id=${ride.id}" class="ride-btn reservation-btn">
                        <i class="fas fa-ticket-alt"></i> Réserver
                    </a>
                </div>
            </div>
        </div>`;
    });
    
    ridesContainer.innerHTML = html;
}

/**
 * Update a URL parameter without page reload
 * 
 * @param {string} key Parameter key
 * @param {string} value Parameter value
 */
function updateURLParameter(key, value) {
    const url = new URL(window.location.href);
    
    if (value === "" || value === "0" || value === 0) {
        url.searchParams.delete(key);
    } else {
        url.searchParams.set(key, value);
    }
    
    window.history.replaceState({}, "", url);
}

/**
 * Get a URL parameter value
 * 
 * @param {string} name Parameter name
 * @returns {string|null} Parameter value or null if not found
 */
function getURLParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

/**
 * Mark active sort button based on URL parameter
 */
function markActiveSortButton() {
    const sort = getURLParameter('sort') || 'newest';
    const sortButtons = document.querySelectorAll('.sort-btn');
    
    sortButtons.forEach(button => {
        if (button.getAttribute('data-sort') === sort) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    });
}

// Set active sort button on page load
markActiveSortButton();
