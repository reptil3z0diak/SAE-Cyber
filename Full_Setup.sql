CREATE DATABASE IF NOT EXISTS AutoMarket;
USE AutoMarket;

-- 1. Table des Utilisateurs
CREATE TABLE IF NOT EXISTS Users (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    email VARCHAR(100) UNIQUE NOT NULL,
    mdp VARCHAR(255) NOT NULL,
    role ENUM('client', 'premium', 'vendeur', 'admin') DEFAULT 'client',
    porte_monnaie DECIMAL(10, 2) DEFAULT 0.00,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. Table SAE (liste des vulnérabilités / flags)
CREATE TABLE IF NOT EXISTS SAE (
    id_flag INT PRIMARY KEY AUTO_INCREMENT,
    nom_faille VARCHAR(100), 
    code_flag VARCHAR(255) UNIQUE, 
    points_flag INT DEFAULT 0,
    level VARCHAR(50), 
    description TEXT
);

-- 3. Table Challenges (flags validés par les users)
CREATE TABLE IF NOT EXISTS Challenges (
    id_challenge INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT,
    id_flag INT,
    date_validation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES Users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_flag) REFERENCES SAE(id_flag) ON DELETE CASCADE,
    UNIQUE (id_user, id_flag)
);

-- 4. Table des Vendeurs
CREATE TABLE IF NOT EXISTS Vendeurs (
    id_vendeur INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    tel VARCHAR(20),
    mdp VARCHAR(255),
    nbr_produits INT DEFAULT 0,
    note DECIMAL(3, 2),
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 5. Table des Annonces
CREATE TABLE IF NOT EXISTS Annonces (
    id_annonce INT PRIMARY KEY AUTO_INCREMENT,
    id_vendeur INT,
    description TEXT,
    prix DECIMAL(10, 2),
    location VARCHAR(100),
    type_annonce VARCHAR(50),
    date_annonce DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_vendeur) REFERENCES Vendeurs(id_vendeur) ON DELETE CASCADE
);

-- -------- PARTIE FORUM --------

-- Catégories
CREATE TABLE Forum_Categories (
    id_categorie INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(100) NOT NULL,
    description TEXT
);

-- Sujets
CREATE TABLE Forum_Sujets (
    id_categorie INT,
    id_auteur INT,
    titre VARCHAR(255) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categorie) REFERENCES Forum_Categories(id_categorie) ON DELETE CASCADE,
    FOREIGN KEY (id_auteur) REFERENCES Users(id_user) ON DELETE SET NULL
);
ALTER TABLE Forum_Sujets ADD id_sujet INT PRIMARY KEY AUTO_INCREMENT FIRST;

-- Réponses
CREATE TABLE Forum_Reponses (
    id_reponse INT PRIMARY KEY AUTO_INCREMENT,
    id_sujet INT,
    id_auteur INT,
    contenu TEXT NOT NULL,
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sujet) REFERENCES Forum_Sujets(id_sujet) ON DELETE CASCADE,
    FOREIGN KEY (id_auteur) REFERENCES Users(id_user) ON DELETE SET NULL
);

-- Messagerie privée
CREATE TABLE Messages (
    id_message INT PRIMARY KEY AUTO_INCREMENT,
    id_expediteur INT,
    id_destinataire INT,
    contenu TEXT,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_expediteur) REFERENCES Users(id_user),
    FOREIGN KEY (id_destinataire) REFERENCES Users(id_user)
);

-- =============================
-- INSERTIONS
-- =============================

-- 1. Utilisateurs avec rôles
INSERT INTO Users (nom, prenom, email, mdp, role, porte_monnaie) VALUES
('Dupont', 'Jean', 'jean@exemple.fr', '$2y$10$.k9oDUrkeVaNdQZPQvK9fO.rNQKhbI5XjQWL0sWV9SPE718YfRPme', 'client', 150.00),
('Durand', 'Marie', 'marie@exemple.fr', '$2y$10$MGI6awOzFo1kqKzMEGMdS.21y1l6HWJRI4tmHDluvoh.Pt0BDv0yK', 'premium', 50.00),
('Admin', 'Site', 'admin@automarket.fr', '$2y$10$rnMpgi0afMX5aluINtI6LufJDLbHmaZm.Z/K4NUbSIt0/U7VATVhm', 'admin', 0.00);

-- 2. Vendeurs
INSERT INTO Vendeurs (nom, email, tel, mdp, note) VALUES
('Garage du Centre', 'contact@garage.fr', '0102030405', '$2y$10$6Y8Os5/0eLTzDgY0UnEiOOrqoduxGbZEm6.inrw8QPyv2UpW1v/yS', 4.5),
('Auto Occasions', 'vente@auto-occas.fr', '0607080910', '$2y$10$K05ZmdddVcpLq/AqmftOfueD3M6CJmISARG02FHc4voZCvPrL.JGu', 4.0);

-- 3. Annonces
INSERT INTO Annonces (id_vendeur, description, prix, location, type_annonce) VALUES
(1, 'Peugeot 208, excellent état, 50 000km', 12000.00, 'Paris', 'Vente'),
(2, 'Pneus hiver Michelin 17 pouces', 200.00, 'Lyon', 'Accessoire');

-- 4. Flags / Vulnérabilités
INSERT INTO SAE (nom_faille, code_flag, points_flag, level, description) VALUES
('SQL Injection', 'FLAG{SQL_MASTER_2024}', 100, 'Moyen',
 'Trouver le flag en injectant du code dans le formulaire de recherche.'),
('XSS Stored', 'FLAG{JS_ALERT_FOUND}', 50, 'Facile',
 'Réussir à exécuter un script sur le forum.'),
('Insecure IDOR', 'FLAG{PRIVATE_PROFILE_BYPASS}', 150, 'Difficile',
 'Accéder au profil d''un autre utilisateur via l''URL.');

-- 5. Challenges validés
INSERT INTO Challenges (id_user, id_flag) VALUES
(1, 1),
(1, 2);

-- 6. Forum : catégories
INSERT INTO Forum_Categories (titre, description) VALUES
('Aide Technique', 'Pour vos problèmes de moteur ou d''électronique'),
('Général', 'Discussions libres sur l''automobile');

-- 7. Forum : sujets
INSERT INTO Forum_Sujets (id_categorie, id_auteur, titre) VALUES
(1, 1, 'Problème de batterie sur ma 208'),
(2, 2, 'Quelle est votre marque préférée ?');

-- 8. Forum : réponses
INSERT INTO Forum_Reponses (id_sujet, id_auteur, contenu) VALUES
(1, 2, 'As-tu vérifié les cosses de la batterie ?'),
(1, 1, 'Oui, elles sont bien serrées.');

-- 9. Messages privés
INSERT INTO Messages (id_expediteur, id_destinataire, contenu) VALUES
(1, 2, 'Bonjour Marie, est-ce que ton annonce est toujours disponible ?');
