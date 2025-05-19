<div class="container faq-container">
    <div class="faq-header">
        <h1>Résultats de recherche</h1>
        <p>Résultats pour : <strong><?php echo htmlspecialchars($searchTerm); ?></strong></p>
        
        <div class="faq-search">
            <form action="/faq/search" method="GET">
                <div class="search-input-container">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Rechercher dans la FAQ..." required>
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
        
        <div class="search-nav">
            <a href="/faq" class="back-link"><i class="fas fa-arrow-left"></i> Retour à la FAQ</a>
        </div>
    </div>
    
    <div class="search-results">
        <?php if (count($searchResults) > 0): ?>
            <div class="results-count">
                <p><?php echo count($searchResults); ?> résultat(s) trouvé(s)</p>
            </div>
            
            <div class="accordion">
                <?php foreach ($searchResults as $faq): ?>
                <div class="accordion-item">
                    <div class="accordion-header">
                        <h3><?php echo htmlspecialchars($faq['question']); ?></h3>
                        <span class="category-badge"><?php echo ucfirst($faq['category']); ?></span>
                        <span class="toggle-icon"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="accordion-content">
                        <p><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search fa-3x"></i>
                <h2>Aucun résultat trouvé</h2>
                <p>Nous n'avons pas trouvé de réponse correspondant à votre recherche.</p>
                <p>Essayez avec d'autres mots-clés ou consultez notre FAQ complète.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="faq-contact">
        <h3>Vous n'avez pas trouvé votre réponse ?</h3>
        <p>Notre équipe d'assistance est disponible pour vous aider.</p>
        <a href="/contact" class="contact-btn">Contactez-nous</a>
    </div>
</div>

<!-- CSS spécifique pour la recherche FAQ -->
<style>
.faq-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.faq-header {
    text-align: center;
    margin-bottom: 40px;
}

.faq-header h1 {
    color: #2E8B57;
    margin-bottom: 10px;
}

.faq-search {
    max-width: 600px;
    margin: 30px auto;
}

.search-input-container {
    display: flex;
    border: 1px solid #ddd;
    border-radius: 30px;
    overflow: hidden;
}

.search-input-container input {
    flex: 1;
    padding: 15px 20px;
    border: none;
    outline: none;
    font-size: 16px;
}

.search-input-container button {
    background: #2E8B57;
    color: white;
    border: none;
    padding: 0 25px;
    cursor: pointer;
    transition: background 0.3s;
}

.search-input-container button:hover {
    background: #246B47;
}

.search-nav {
    margin: 20px 0;
}

.back-link {
    color: #2E8B57;
    text-decoration: none;
    font-weight: 500;
}

.back-link:hover {
    text-decoration: underline;
}

.results-count {
    margin-bottom: 20px;
    color: #666;
}

.category-badge {
    background: #f0f8f4;
    color: #2E8B57;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 14px;
    margin-right: 15px;
}

.accordion-item {
    border-bottom: 1px solid #eee;
    margin-bottom: 15px;
}

.accordion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    cursor: pointer;
}

.accordion-header h3 {
    font-size: 18px;
    font-weight: 500;
    margin: 0;
    flex: 1;
}

.toggle-icon {
    color: #2E8B57;
    transition: transform 0.3s;
}

.accordion-item.active .toggle-icon {
    transform: rotate(45deg);
}

.accordion-content {
    display: none;
    padding: 0 0 20px;
    line-height: 1.6;
}

.accordion-item.active .accordion-content {
    display: block;
}

.no-results {
    text-align: center;
    padding: 50px 0;
    color: #666;
}

.no-results i {
    color: #ddd;
    margin-bottom: 20px;
}

.no-results h2 {
    margin-bottom: 15px;
    color: #333;
}

.faq-contact {
    background: #f0f8f4;
    padding: 30px;
    text-align: center;
    border-radius: 8px;
    margin-top: 40px;
}

.contact-btn {
    display: inline-block;
    background: #2E8B57;
    color: white;
    padding: 12px 25px;
    border-radius: 30px;
    text-decoration: none;
    margin-top: 15px;
    transition: background 0.3s;
}

.contact-btn:hover {
    background: #246B47;
}
</style>

<!-- JavaScript pour l'accordion -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'accordion
    const accordionItems = document.querySelectorAll('.accordion-item');
    
    accordionItems.forEach(item => {
        const header = item.querySelector('.accordion-header');
        
        header.addEventListener('click', () => {
            item.classList.toggle('active');
        });
    });
    
    // Activer tous les résultats par défaut pour faciliter la lecture
    accordionItems.forEach(item => {
        item.classList.add('active');
    });
});
</script>