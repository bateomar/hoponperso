-- Script SQL pour créer la base de données HopOn
-- Utilisable avec PostgreSQL ou MySQL (avec quelques modifications)

-- Création de la table utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    rating DECIMAL(3,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Création de la table trajets
CREATE TABLE IF NOT EXISTS rides (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    departure_city VARCHAR(100) NOT NULL,
    arrival_city VARCHAR(100) NOT NULL,
    departure_datetime TIMESTAMP NOT NULL,
    arrival_datetime TIMESTAMP NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    available_seats INTEGER NOT NULL DEFAULT 4,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion de données d'exemple - Utilisateurs
INSERT INTO users (first_name, last_name, email, password, rating) VALUES
('Jean', 'Dupont', 'jean.dupont@example.com', 'password123', 4.5),
('Marie', 'Martin', 'marie.martin@example.com', 'password123', 3.8),
('Pierre', 'Durand', 'pierre.durand@example.com', 'password123', 4.2);

-- Insertion de données d'exemple - Trajets (Lyon-Paris)
INSERT INTO rides (user_id, departure_city, arrival_city, departure_datetime, arrival_datetime, price, available_seats) VALUES
(1, 'Lyon', 'Paris', '2025-05-02 08:10:00', '2025-05-02 12:30:00', 25.00, 3),
(2, 'Lyon', 'Paris', '2025-05-02 08:50:00', '2025-05-02 13:00:00', 25.00, 2),
(3, 'Lyon', 'Paris', '2025-05-02 09:15:00', '2025-05-02 14:25:00', 25.00, 4),
(1, 'Lyon', 'Paris', '2025-05-02 09:17:00', '2025-05-02 13:27:00', 25.00, 1),
(2, 'Lyon', 'Paris', '2025-05-02 09:40:00', '2025-05-02 14:00:00', 25.00, 2),
(3, 'Lyon', 'Paris', '2025-05-02 10:23:00', '2025-05-02 15:00:00', 25.00, 3),
(1, 'Lyon', 'Paris', '2025-05-02 10:42:00', '2025-05-02 15:00:00', 25.00, 2),
(2, 'Lyon', 'Paris', '2025-05-02 11:00:00', '2025-05-02 15:00:00', 25.00, 1),
(3, 'Lyon', 'Paris', '2025-05-02 11:50:00', '2025-05-02 15:00:00', 25.00, 4),
(1, 'Lyon', 'Paris', '2025-05-02 13:15:00', '2025-05-02 17:00:00', 25.00, 2),
(2, 'Lyon', 'Paris', '2025-05-02 14:20:00', '2025-05-02 19:00:00', 25.00, 3);

-- Insertion de données d'exemple - Trajets (Paris-Lyon)
INSERT INTO rides (user_id, departure_city, arrival_city, departure_datetime, arrival_datetime, price, available_seats) VALUES
(3, 'Paris', 'Lyon', '2025-05-02 08:00:00', '2025-05-02 12:30:00', 30.00, 2),
(1, 'Paris', 'Lyon', '2025-05-02 09:30:00', '2025-05-02 14:00:00', 28.00, 3),
(2, 'Paris', 'Lyon', '2025-05-02 10:45:00', '2025-05-02 15:15:00', 26.50, 1),
(3, 'Paris', 'Lyon', '2025-05-02 13:20:00', '2025-05-02 17:50:00', 25.00, 4),
(1, 'Paris', 'Lyon', '2025-05-02 15:40:00', '2025-05-02 20:10:00', 27.00, 2);

-- Insertion de données d'exemple - Trajets (Lyon-Marseille)
INSERT INTO rides (user_id, departure_city, arrival_city, departure_datetime, arrival_datetime, price, available_seats) VALUES
(2, 'Lyon', 'Marseille', '2025-05-02 07:15:00', '2025-05-02 10:30:00', 22.00, 3),
(3, 'Lyon', 'Marseille', '2025-05-02 08:45:00', '2025-05-02 12:00:00', 20.00, 2),
(1, 'Lyon', 'Marseille', '2025-05-02 10:30:00', '2025-05-02 13:45:00', 24.00, 1),
(2, 'Lyon', 'Marseille', '2025-05-02 12:20:00', '2025-05-02 15:35:00', 21.50, 4),
(3, 'Lyon', 'Marseille', '2025-05-02 14:50:00', '2025-05-02 18:05:00', 23.00, 2);

-- Insertion de données d'exemple - Trajets (Marseille-Lyon)
INSERT INTO rides (user_id, departure_city, arrival_city, departure_datetime, arrival_datetime, price, available_seats) VALUES
(1, 'Marseille', 'Lyon', '2025-05-02 07:30:00', '2025-05-02 10:45:00', 23.00, 2),
(2, 'Marseille', 'Lyon', '2025-05-02 09:15:00', '2025-05-02 12:30:00', 21.00, 3),
(3, 'Marseille', 'Lyon', '2025-05-02 11:40:00', '2025-05-02 14:55:00', 25.00, 1),
(1, 'Marseille', 'Lyon', '2025-05-02 13:55:00', '2025-05-02 17:10:00', 22.50, 4),
(2, 'Marseille', 'Lyon', '2025-05-02 16:20:00', '2025-05-02 19:35:00', 24.00, 2);

-- Insertion de données d'exemple - Trajets (Paris-Bordeaux)
INSERT INTO rides (user_id, departure_city, arrival_city, departure_datetime, arrival_datetime, price, available_seats) VALUES
(3, 'Paris', 'Bordeaux', '2025-05-02 07:00:00', '2025-05-02 12:30:00', 45.00, 3),
(1, 'Paris', 'Bordeaux', '2025-05-02 08:45:00', '2025-05-02 14:15:00', 42.00, 2),
(2, 'Paris', 'Bordeaux', '2025-05-02 10:30:00', '2025-05-02 16:00:00', 40.00, 4),
(3, 'Paris', 'Bordeaux', '2025-05-02 12:15:00', '2025-05-02 17:45:00', 43.50, 1),
(1, 'Paris', 'Bordeaux', '2025-05-02 14:00:00', '2025-05-02 19:30:00', 41.00, 3);

-- Insertion de données d'exemple - Trajets (Bordeaux-Paris)
INSERT INTO rides (user_id, departure_city, arrival_city, departure_datetime, arrival_datetime, price, available_seats) VALUES
(2, 'Bordeaux', 'Paris', '2025-05-02 06:30:00', '2025-05-02 12:00:00', 44.00, 2),
(3, 'Bordeaux', 'Paris', '2025-05-02 08:15:00', '2025-05-02 13:45:00', 41.50, 3),
(1, 'Bordeaux', 'Paris', '2025-05-02 10:00:00', '2025-05-02 15:30:00', 43.00, 1),
(2, 'Bordeaux', 'Paris', '2025-05-02 11:45:00', '2025-05-02 17:15:00', 40.50, 4),
(3, 'Bordeaux', 'Paris', '2025-05-02 13:30:00', '2025-05-02 19:00:00', 42.00, 2);