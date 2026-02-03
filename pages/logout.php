<?php
// pages/logout.php

session_destroy(); // Détruit toutes les données de session
header("Location: index.php?page=home"); // Retour à l'accueil
exit;
?>