document.addEventListener('DOMContentLoaded', function() {
    // Récupération des éléments du DOM
    const tabUpcoming = document.getElementById('tab-upcoming');
    const tabPast = document.getElementById('tab-past');
    const upcomingReservations = document.getElementById('upcoming-reservations');
    const pastReservations = document.getElementById('past-reservations');

    // Vérifier que les éléments existent
    if (tabUpcoming && tabPast && upcomingReservations && pastReservations) {
        // Gestion des onglets "À venir"
        tabUpcoming.addEventListener('click', function() {
            tabUpcoming.classList.add('active');
            tabPast.classList.remove('active');
            upcomingReservations.style.display = 'block';
            pastReservations.style.display = 'none';
        });

        // Gestion des onglets "Passées"
        tabPast.addEventListener('click', function() {
            tabPast.classList.add('active');
            tabUpcoming.classList.remove('active');
            pastReservations.style.display = 'block';
            upcomingReservations.style.display = 'none';
        });
    } else {
        console.error('Éléments DOM manquants pour la gestion des onglets');
    }
});
