<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Concessionnaire Auto - Pentest</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <header>
        <nav>
            <ul>
                <li><a href="index.php?page=home">Accueil</a></li>
                <li><a href="index.php?page=catalogue">Nos Voitures</a></li>
                <li><a href="index.php?page=pieces_detachees">Pièces Détachées</a></li>
                <li><a href="index.php?page=entretiens">Entretiens</a></li>
                <li><a href="index.php?page=recherche_avancee">Recherche Avancée</a></li>
                <li><a href="index.php?page=forum">Forum</a></li>
                <li style="border: 2px solid #ff4444; background: #ffeeee; padding: 2px 8px; border-radius: 5px;">
                    <a href="index.php?page=attackAlois" style="color: #ff0000; font-weight: bold;">🎯 Attack Zone
                        (Alois)</a>
                </li>

                <?php
// Si l'utilisateur est connecté
if (isset($_SESSION['user_id'])):
?>
                    <li><a href="index.php?page=profil">Mon Profil</a></li>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li style="border: 1px solid red; padding: 2px;">
                            <a href="index.php?page=admin_dashboard">Gestion Admin</a>
                        </li>
                    <?php
    endif; ?>

                    <li><a href="index.php?page=logout" style="color: #ffaaaa;">Déconnexion</a></li>

                <?php
else: ?>
                    <li><a href="index.php?page=login">Connexion</a></li>
                    <li><a href="index.php?page=register">Inscription</a></li>
                <?php
endif; ?>
            </ul>
        </nav>
        <div style="float: right; font-size: 0.8em;">
            <?php
if (isset($_SESSION['username'])) {
    echo "Bonjour, " . htmlspecialchars($_SESSION['username']) . " (" . $_SESSION['role'] . ")";
}
?>
        </div>
        <div style="clear: both;"></div>
    </header>

    <div class="container">