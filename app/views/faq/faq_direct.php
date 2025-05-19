<?php
// En-tête
include 'includes/header.php';

// Connexion à la base de données
require_once 'includes/db_connect.php';
$db = connectDB();

// Récupérer les catégories de FAQ
$query = "SELECT DISTINCT category FROM faqs ORDER BY category";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Récupérer les FAQs par catégorie
$faqsByCategory = [];
foreach ($categories as $category) {
    $query = "SELECT * FROM faqs WHERE category = :category ORDER BY ordre";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':category', $category, PDO::PARAM_STR);
    $stmt->execute();
    $faqsByCategory[$category] = $stmt->fetchAll();
}
?>

<div class="container faq-container">
    <div class="faq-header">
        <h1>Questions fréquentes (FAQ)</h1>
        <p>Trouvez des réponses aux questions les plus courantes sur HopOn.</p>
        
        <div class="faq-search">
            <form action="faq_search.php" method="GET">
                <div class="search-input-container">
                    <input type="text" name="q" placeholder="Rechercher dans la FAQ..." required>
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="faq-categories">
        <div class="categories-nav">
            <h3>Catégories</h3>
            <ul>
                <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="#<?php echo $cat; ?>" class="category-link">
                        <?php echo ucfirst($cat); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="faq-content">
            <?php foreach ($categories as $cat): ?>
            <div id="<?php echo $cat; ?>" class="category-section">
                <h2><?php echo ucfirst($cat); ?></h2>
                
                <div class="accordion">
                    <?php foreach ($faqsByCategory[$cat] as $faq): ?>
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <h3><?php echo htmlspecialchars($faq['question']); ?></h3>
                            <span class="toggle-icon"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="accordion-content">
                            <p><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="faq-contact">
        <h3>Vous n'avez pas trouvé votre réponse ?</h3>
        <p>Notre équipe d'assistance est disponible pour vous aider.</p>
        <a href="contact.php" class="contact-btn">Contactez-nous</a>
    </div>
</div>

<!-- CSS spécifique pour la FAQ -->
<style>
.faq-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
    background-color: var(--bg-color);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.faq-header {
    text-align: center;
    margin-bottom: 40px;
    padding-top: 30px;
}

.faq-header h1 {
    color: var(--primary-color);
    margin-bottom: 10px;
    font-weight: 600;
}

.faq-header p {
    color: var(--light-text);
    max-width: 600px;
    margin: 0 auto;
}

.faq-search {
    max-width: 600px;
    margin: 30px auto;
}

.search-input-container {
    display: flex;
    border: 1px solid var(--border-color);
    border-radius: 30px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.search-input-container input {
    flex: 1;
    padding: 15px 20px;
    border: none;
    outline: none;
    font-size: 16px;
    color: var(--text-color);
}

.search-input-container button {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 0 25px;
    cursor: pointer;
    transition: var(--transition);
}

.search-input-container button:hover {
    background: var(--accent-color);
}

.faq-categories {
    display: flex;
    gap: 30px;
    margin-bottom: 50px;
}

.categories-nav {
    flex: 0 0 250px;
    border-right: 1px solid var(--border-color);
    padding-right: 20px;
}

.categories-nav h3 {
    margin-bottom: 20px;
    color: var(--secondary-color);
    font-weight: 600;
}

.categories-nav ul {
    list-style: none;
    padding: 0;
}

.categories-nav li {
    margin-bottom: 12px;
}

.category-link {
    color: var(--light-text);
    text-decoration: none;
    display: block;
    padding: 8px 15px;
    border-radius: var(--radius);
    transition: var(--transition);
}

.category-link:hover, .category-link.active {
    background: rgba(66, 103, 178, 0.1);
    color: var(--primary-color);
}

.faq-content {
    flex: 1;
}

.category-section {
    margin-bottom: 40px;
}

.category-section h2 {
    color: var(--primary-color);
    border-bottom: 2px solid var(--border-color);
    padding-bottom: 10px;
    margin-bottom: 20px;
    font-weight: 600;
}

.accordion-item {
    border-bottom: 1px solid var(--border-color);
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
    color: var(--text-color);
}

.toggle-icon {
    color: var(--primary-color);
    transition: transform 0.3s;
}

.accordion-item.active .toggle-icon {
    transform: rotate(45deg);
}

.accordion-content {
    display: none;
    padding: 0 0 20px;
    line-height: 1.6;
    color: var(--light-text);
}

.accordion-item.active .accordion-content {
    display: block;
}

.faq-contact {
    background: rgba(66, 103, 178, 0.1);
    padding: 30px;
    text-align: center;
    border-radius: var(--radius);
    margin-bottom: 40px;
}

.faq-contact h3 {
    color: var(--secondary-color);
    margin-bottom: 10px;
}

.contact-btn {
    display: inline-block;
    background: var(--primary-color);
    color: white;
    padding: 12px 25px;
    border-radius: 30px;
    text-decoration: none;
    margin-top: 15px;
    transition: var(--transition);
    font-weight: 500;
}

.contact-btn:hover {
    background: var(--accent-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .faq-categories {
        flex-direction: column;
    }
    
    .categories-nav {
        flex: none;
        border-right: none;
        border-bottom: 1px solid var(--border-color);
        padding-right: 0;
        padding-bottom: 20px;
        margin-bottom: 20px;
    }
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
            const currentlyActive = document.querySelector('.accordion-item.active');
            
            if (currentlyActive && currentlyActive !== item) {
                currentlyActive.classList.remove('active');
            }
            
            item.classList.toggle('active');
        });
    });
    
    // Gestion des liens de catégorie avec scroll smooth
    const categoryLinks = document.querySelectorAll('.category-link');
    
    categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Supprimer la classe active de tous les liens
            categoryLinks.forEach(l => l.classList.remove('active'));
            
            // Ajouter la classe active au lien cliqué
            this.classList.add('active');
            
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Activer la première question de chaque catégorie
    const firstItems = document.querySelectorAll('.category-section:first-child .accordion-item:first-child');
    firstItems.forEach(item => {
        item.classList.add('active');
    });
});
</script>

<?php
// Pied de page
include 'includes/footer.php';
?>