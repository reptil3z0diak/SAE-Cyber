-- ===============================================
-- SQL INJECTION UNION - Tables pour l'attaque
-- À exécuter dans MySQL/MariaDB
-- ===============================================

USE AutoMarket;

-- Table des types de véhicules
CREATE TABLE IF NOT EXISTS Types_Vehicules (
    id_type INT PRIMARY KEY AUTO_INCREMENT,
    nom_type VARCHAR(50)
);

-- Table des entretiens (visible publiquement)
CREATE TABLE IF NOT EXISTS Entretiens (
    id_entretien INT PRIMARY KEY AUTO_INCREMENT,
    id_type INT,
    marque VARCHAR(50),
    annee_modele VARCHAR(10),
    type_entretien VARCHAR(50),
    description TEXT,
    prix_estime DECIMAL(10,2),
    FOREIGN KEY (id_type) REFERENCES Types_Vehicules(id_type)
);

-- Table SECRÈTE des codes de maintenance (contient le FLAG)
CREATE TABLE IF NOT EXISTS Codes_Maintenance_Secrets (
    id_code INT PRIMARY KEY AUTO_INCREMENT,
    code_vehicule VARCHAR(50),
    code_secret VARCHAR(255),
    niveau_acces VARCHAR(20)
);

-- Insertion des types de véhicules
INSERT INTO Types_Vehicules (nom_type) VALUES
('Citadine'),
('Berline'),
('SUV'),
('Break'),
('Utilitaire');

-- Insertion des entretiens
INSERT INTO Entretiens (id_type, marque, annee_modele, type_entretien, description, prix_estime) VALUES
(1, 'Peugeot', '2020', 'Vidange', 'Vidange huile moteur + filtre', 89.00),
(1, 'Renault', '2019', 'Vidange', 'Vidange huile moteur + filtre', 79.00),
(2, 'BMW', '2021', 'Révision', 'Révision complète 30000km', 350.00),
(2, 'Audi', '2020', 'Freinage', 'Remplacement plaquettes avant', 180.00),
(3, 'Toyota', '2022', 'Pneumatiques', 'Montage 4 pneus été', 320.00),
(3, 'Mercedes', '2021', 'Révision', 'Révision complète + climatisation', 420.00),
(4, 'Volkswagen', '2019', 'Vidange', 'Vidange boîte automatique', 250.00),
(5, 'Renault', '2018', 'Freinage', 'Disques + plaquettes arrière', 280.00),
(1, 'Toyota', '2020', 'Révision', 'Révision intermédiaire 15000km', 150.00),
(2, 'Peugeot', '2022', 'Pneumatiques', 'Équilibrage + géométrie', 85.00);

-- Insertion des codes secrets (dont le FLAG)
INSERT INTO Codes_Maintenance_Secrets (code_vehicule, code_secret, niveau_acces) VALUES
('DIAG-001', 'Code standard diagnostic OBD2', 'technicien'),
('RESET-002', 'Procédure reset tableau de bord', 'technicien'),
('ADMIN-003', 'FLAG{SQL_UNI0N_M4ST3R_2024}', 'admin'),
('UNLOCK-004', 'Code déblocage antidémarrage', 'admin');
