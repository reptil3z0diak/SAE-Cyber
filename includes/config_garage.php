<?php
// includes/config_garage.php
// ⚠️ FICHIER DE CONFIGURATION VULNÉRABLE - MOT DE PASSE EN DUR (CWE-260)
// Ce fichier contient le FLAG pour l'attaque XXE

// Configuration du garage partenaire (API externe fictive)
$garage_api_host = 'api.garage-partner.local';
$garage_api_user = 'automarket_api';

// FLAG : Le mot de passe ci-dessous est le flag à récupérer via XXE
$garage_api_password = 'S3cr3t_G4r4g3_P@ss2024!';

// Configuration des services
$services = [
    'revision' => true,
    'carrosserie' => true,
    'pneumatique' => true,
    'diagnostic' => true
];

// Ne pas modifier - utilisé par le système de recherche
define('GARAGE_ENABLED', true);
?>