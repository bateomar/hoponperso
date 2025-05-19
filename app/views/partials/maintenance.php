<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance en cours - HopOn</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .maintenance-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 50px 20px;
            text-align: center;
        }
        
        .logo {
            max-width: 200px;
            margin-bottom: 40px;
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #333;
        }
        
        p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            color: #555;
        }
        
        .maintenance-icon {
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .estimated-time {
            background-color: #e8f5e9;
            padding: 15px 20px;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 30px;
        }
        
        .social-links {
            margin-top: 50px;
        }
        
        .social-links a {
            color: #666;
            font-size: 24px;
            margin: 0 10px;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .social-links a:hover {
            color: #4CAF50;
        }
        
        footer {
            margin-top: 80px;
            color: #777;
            font-size: 0.9rem;
        }
    </style>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="maintenance-container">
        <img src="/assets/images/hopon_logo.jpg" alt="HopOn Logo" class="logo">
        
        <div class="maintenance-icon">
            <i class="fas fa-tools"></i>
        </div>
        
        <h1>Site en maintenance</h1>
        
        <p>Nous effectuons actuellement des travaux de maintenance pour améliorer votre expérience. Nous serons de retour en ligne très bientôt !</p>
        
        <div class="estimated-time">
            <strong>Durée estimée :</strong> <?php echo file_get_contents(BASE_PATH . '/maintenance.txt') ?: 'Quelques heures'; ?>
        </div>
        
        <p>Nous vous remercions pour votre patience. Si vous avez des questions, n'hésitez pas à nous contacter à <a href="mailto:contact@hopon.fr">contact@hopon.fr</a>.</p>
        
        <div class="social-links">
            <a href="#" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
            <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="#" target="_blank"><i class="fab fa-linkedin"></i></a>
        </div>
        
        <footer>
            &copy; <?php echo date('Y'); ?> HopOn. Tous droits réservés.
        </footer>
    </div>
</body>
</html>