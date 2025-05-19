<?php
$title = 'Foire Aux Questions - HopOn';
$description = 'Trouvez des réponses à vos questions sur HopOn, la plateforme de covoiturage.';
$additionalCss = ['/assets/css/faq.css'];
include 'app/views/partials/header.php';
?>

<div class="container faq-container">
    <div class="faq-header">
        <h1>Foire Aux Questions</h1>
        <p>Trouvez des réponses à vos questions sur HopOn</p>
        
        <div class="faq-search">
            <form action="/faq/search" method="GET">
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" placeholder="Rechercher une question..." required>
                </div>
                <button type="submit" class="btn-search">Rechercher</button>
            </form>
        </div>
    </div>
    
    <div class="faq-categories">
        <div class="category-tabs">
            <button class="category-tab active" data-category="all">Toutes les questions</button>
            <?php foreach ($categories as $category): ?>
                <button class="category-tab" data-category="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></button>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="faq-content">
        <div class="category-content active" id="all">
            <?php foreach ($faqsByCategory as $category => $faqs): ?>
                <div class="category-section">
                    <h2><?php echo htmlspecialchars($category); ?></h2>
                    
                    <div class="accordion">
                        <?php foreach ($faqs as $faq): ?>
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <h3><?php echo htmlspecialchars($faq['question']); ?></h3>
                                    <i class="fas fa-chevron-down"></i>
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
        
        <?php foreach ($categories as $category): ?>
            <div class="category-content" id="<?php echo htmlspecialchars($category); ?>">
                <div class="category-section">
                    <h2><?php echo htmlspecialchars($category); ?></h2>
                    
                    <div class="accordion">
                        <?php foreach ($faqsByCategory[$category] as $faq): ?>
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <h3><?php echo htmlspecialchars($faq['question']); ?></h3>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="accordion-content">
                                    <p><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="contact-cta">
        <h2>Vous n'avez pas trouvé la réponse à votre question ?</h2>
        <p>N'hésitez pas à nous contacter directement. Notre équipe se fera un plaisir de vous répondre.</p>
        <a href="/contact" class="btn btn-primary">Contactez-nous</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle category tabs
        const categoryTabs = document.querySelectorAll('.category-tab');
        const categoryContents = document.querySelectorAll('.category-content');
        
        categoryTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const category = this.getAttribute('data-category');
                
                // Update active tab
                categoryTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show corresponding content
                categoryContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === category) {
                        content.classList.add('active');
                    }
                });
            });
        });
        
        // Handle accordion
        const accordionHeaders = document.querySelectorAll('.accordion-header');
        
        accordionHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const item = this.parentElement;
                const isActive = item.classList.contains('active');
                
                // Close all items
                document.querySelectorAll('.accordion-item').forEach(i => {
                    i.classList.remove('active');
                });
                
                // Open clicked item if it wasn't already open
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        });
    });
</script>

<?php include 'app/views/partials/footer.php'; ?>