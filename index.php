<?php
// index.php
session_start();

// Simulation de connexion BDD (on l'activera plus tard)
// require_once 'includes/db_connect.php'; 

// Récupération de la page demandée, 'home' par défaut
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Sécurité basique : on empêche d'aller chercher des fichiers hors du dossier 'pages'
// (Note: C'est une protection que tu pourras volontairement affaiblir plus tard pour une faille LFI si besoin)
$page_path = "pages/$page.php";

// Inclusion du Header
include 'includes/header.php';

// Inclusion de la page demandée
if (file_exists($page_path)) {
    include $page_path;
} else {
    echo "<h1>Erreur 404</h1><p>La page demandée n'existe pas.</p>";
}

// Inclusion du Footer
include 'includes/footer.php';
?>