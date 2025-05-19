<?php
// Define BASE_URL once for cleaner asset linking
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
?>
<footer class="site-footer">
    <link rel="stylesheet" href="css/footer.css">
    <div class="footer-container">
        <!-- Footer Left: Logo -->
        <div class="footer-left">
            <a href="<?= $base_url === '' ? '/' : $base_url ?>" aria-label="Accueil HopOn">
                <img src="/images/HopOn_logo.jpg" alt="Logo HopOn" class="footer-logo" height="120">
            </a>
        </div>

        <!-- Footer Center: Links -->
        <div class="footer-center">
            <div class="footer-links">
                <h4>Aperçu</h4>
                <a href="<?= $base_url ?>/contact">Contact</a>
                <a href="<?= $base_url ?>/terms">Conditions Générales de Vente</a>
                <a href="<?= $base_url ?>/legal">Mentions Légales</a>
                <a href="<?= $base_url ?>/sitemap">Plan du site</a>
            </div>
            <div class="footer-links">
                <h4>Ressources</h4>
                <a href="<?= $base_url ?>/faq">FAQ</a>
                <a href="<?= $base_url ?>/support">Support</a>
                <a href="<?= $base_url ?>/forum">Forum</a>
            </div>
        </div>

        <!-- Footer Right: Socials -->
        <div class="footer-right">
            <div class="footer-socials">
                <a class="socialContainer containerOne" href="https://www.instagram.com/hopon_g8d/" target="_blank">
                    <svg class="socialSvg" viewBox="0 0 24 24">
                        <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"></path>
                    </svg>
                </a>
                <a class="socialContainer containerTwo" href="https://www.linkedin.com/groups/13180881/" target="_blank">
                    <svg class="socialSvg" viewBox="0 0 24 24">
                        <path d="M4.98 3.5C4.98 4.33 4.33 5 3.5 5c-.83 0-1.48-.67-1.48-1.5C2.02 2.67 2.67 2 3.5 2c.83 0 1.48.67 1.48 1.5zM4.98 6H2V20h3V6zm7.5 0h-3v14h3v-7.5c0-.7.2-1.3.85-1.3s.9.6.9 1.3V20h3v-7.8c0-2-1.2-3-2.9-3-1.35 0-1.95.7-2.3 1.2V6z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© <?= date('Y') ?> HopOn - Tous droits réservés.</p>
        <p>Réalisé par G8D</p>
    </div>
</footer>
