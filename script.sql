-- ========================================================
-- BASE DE DONNÉES : SITE WEB RESTAURANT UNIQUE
-- Projet Fil Rouge - Session 1
-- Restaurant Le Gourmet : Cuisine Raffinée & Authentique
-- ========================================================

CREATE DATABASE IF NOT EXISTS restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE restaurant_db;

-- --------------------------------------------------------
-- 1. Table des Utilisateurs (Gérant, Chef, Personnel)
-- --------------------------------------------------------
DROP TABLE IF EXISTS reservation;
DROP TABLE IF EXISTS plat;
DROP TABLE IF EXISTS categorie_plat;
DROP TABLE IF EXISTS utilisateur;

CREATE TABLE utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'gerant', 'personnel') DEFAULT 'gerant'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Table des Catégories de Plats
-- --------------------------------------------------------
CREATE TABLE categorie_plat (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom_categorie VARCHAR(100) NOT NULL,
    icone VARCHAR(50) DEFAULT '🍽️',
    description VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Table des Plats du Restaurant
-- --------------------------------------------------------
CREATE TABLE plat (
    id_plat INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    ingredients TEXT,
    prix DECIMAL(10,2) NOT NULL,
    temps_prep INT DEFAULT 15,
    image VARCHAR(255) NULL,
    statut ENUM('disponible', 'plat_du_jour', 'epuise') DEFAULT 'disponible',
    id_utilisateur INT NOT NULL,
    id_categorie INT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE RESTRICT,
    FOREIGN KEY (id_categorie) REFERENCES categorie_plat(id_categorie) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Table des Réservations de Tables
-- --------------------------------------------------------
CREATE TABLE reservation (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    nom_client VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telephone VARCHAR(30) NOT NULL,
    nb_personnes INT NOT NULL DEFAULT 2,
    date_reservation DATE NOT NULL,
    heure_reservation TIME NOT NULL,
    remarques TEXT NULL,
    statut ENUM('en_attente', 'confirmee', 'annulee') DEFAULT 'en_attente',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- INSERTION DES DONNÉES INITIALES (SEED DATA)
-- ========================================================

-- Utilisateur par défaut (Chef / Gérant du restaurant)
INSERT INTO utilisateur (id_utilisateur, nom, email, password, role) VALUES
(1, 'Chef Mohamed Bennani', 'contact@legourmet.ma', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe8vGZzY2M2p9r7i6lYj5F0uU0m5e4x1a', 'gerant');

-- Catégories de plats
INSERT INTO categorie_plat (id_categorie, nom_categorie, icone, description) VALUES
(1, 'Entrées', '🥗', 'Salades fraîches, veloutés et amuse-bouches raffinés'),
(2, 'Plats Principaux', '🥩', 'Viandes tendres, volailles et tajines traditionnels'),
(3, 'Poissons & Fruits de Mer', '🐟', 'Arrivages du jour grillés ou en sauce délicate'),
(4, 'Pâtes & Spécialités', '🍝', 'Recettes maison, risottos et créations gourmandes'),
(5, 'Desserts', '🍰', 'Douceurs artisanales, glaces et pâtisseries du chef'),
(6, 'Boissons & Cocktails', '🍹', 'Jus frais pressés, cocktails signature et thés traditionnels');

-- Plats de la carte du restaurant
INSERT INTO plat (id_plat, nom, description, ingredients, prix, temps_prep, image, statut, id_utilisateur, id_categorie) VALUES
(1, 'Salade Burrata aux Tomates Anciennes', 'Burrata crémeuse des Pouilles accompagnée de tomates cerises rôties, pesto de basilic frais et réduction de vinaigre balsamique de Modène.', 'Burrata fraîche 150g, tomates anciennes, basilic frais, pignons de pin, huile d\'olive extra vierge, crème balsamique.', 65.00, 10, 'uploads/burrata.jpg', 'disponible', 1, 1),
(2, 'Pastilla Traditionnelle au Poulet & Amandes', 'Croustillant feuilleté marocain farci au poulet fermier mijoté aux épices douces, amandes torréfiées concassées, cannelle et sucre glace.', 'Feuilles de pastilla (ouarka), poulet fermier, amandes effilées, oignons caramélisés, cannelle, eau de fleur d\'oranger.', 90.00, 25, 'uploads/pastilla.jpg', 'plat_du_jour', 1, 1),
(3, 'Souris d\'Agneau Confite au Miel & Romarin', 'Souris d\'agneau braisée lentement pendant 5 heures, jus corsé au miel de thym et romarin, accompagnée d\'une purée maison à la truffe.', 'Souris d\'agneau du terroir, miel de montagne, romarin frais, purée de pommes de terre au beurre demi-sel, légumes glacés.', 145.00, 30, 'uploads/souris-agneau.jpg', 'disponible', 1, 2),
(4, 'Tajine de Veau aux Pruneaux & Amandes Dorées', 'Tendre jarret de veau mariné au safran pur de Taliouine, pruneaux caramélisés, sésame grillé et amandes croquantes.', 'Veau de premier choix, pruneaux d\'Agen, safran pur, gingembre, cannelle, graines de sésame blanc, amandes.', 125.00, 25, 'uploads/tajine-veau.jpg', 'plat_du_jour', 1, 2),
(5, 'Pavé de Saumon Rôti & Risotto Crémeux au Parmesan', 'Pavé de saumon frais cuit à l\'unilatérale avec peau croustillante, déposé sur un lit de risotto crémeux au parmesan Reggiano 24 mois.', 'Saumon sauvage frais 200g, riz Arborio, parmesan AOP, crème fraîche, asperges vertes rôties, citron vert.', 135.00, 20, 'uploads/saumon-risotto.jpg', 'disponible', 1, 3),
(6, 'Tagliatelles Fraîches aux Truffes & Champignons Sauvages', 'Pâtes fraîches maison enrobées d\'une crème onctueuse parfumée à la truffe noire d\'été et champignons de sous-bois sautés à l\'ail doux.', 'Tagliatelles fraîches aux œufs, crème de truffe, cèpes, girolles, parmesan affiné, persil plat.', 110.00, 15, 'uploads/tagliatelles-truffe.jpg', 'disponible', 1, 4),
(7, 'Tiramisu Artisanal au Café Expresso & Mascarpone', 'Grand classique italien revisité avec un biscuit imbibé d\'un expresso corsé, crème mascarpone aérée et cacao amer intense.', 'Mascarpone de qualité, biscuits cuillère, café expresso torréfié, œufs frais, cacao en poudre Valrhona.', 45.00, 10, 'uploads/tiramisu.jpg', 'disponible', 1, 5),
(8, 'Cocktail Signature Passion & Menthe Fraîche', 'Boisson rafraîchissante sans alcool associant purée de fruits de la passion, jus de citron vert pressé, feuilles de menthe pilées et eau gazeuse.', 'Fruits de la passion, citron vert bio, menthe fraîche, sirop de canne pur, eau gazeuse artisanale.', 35.00, 5, 'uploads/cocktail-passion.jpg', 'disponible', 1, 6);

-- Réservations de démonstration
INSERT INTO reservation (id_reservation, nom_client, email, telephone, nb_personnes, date_reservation, heure_reservation, remarques, statut) VALUES
(1, 'Karim Amrani', 'karim.amrani@email.com', '0661234567', 4, CURDATE() + INTERVAL 1 DAY, '20:30:00', 'Table en terrasse souhaitée si possible.', 'confirmee'),
(2, 'Sarah Alami', 'sarah.alami@email.com', '0672345678', 2, CURDATE() + INTERVAL 2 DAY, '19:45:00', 'Anniversaire de mariage, coin intime.', 'en_attente');
