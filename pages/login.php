<?php
// pages/login.php
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // --- ZONE DE VULNÉRABILITÉ ---
    
    // 1. INJECTION SQL
    // On insère directement la variable $email dans la requête.
    // Cela permet l'injection : ' OR '1'='1
    $sql = "SELECT * FROM Users WHERE email = '$email'";
    
    // On utilise query() (pas de préparation)
    $stmt = $pdo->query($sql);
    
    // Petit contrôle pour voir l'erreur SQL si l'injection est mal faite
    if ($stmt === false) {
        die("Erreur SQL : " . print_r($pdo->errorInfo(), true)); 
    }

    $user = $stmt->fetch();

    $is_valid = false;

    // 2. BYPASS D'AUTHENTIFICATION (LOGIQUE)
    if ($user) {
        // Pour ton exercice : 
        // Si la base de données renvoie un utilisateur (grâce à ton injection SQL ou un vrai email),
        // on valide la connexion DIRECTEMENT, sans vérifier le mot de passe.
        // Cela te permet de te connecter avec n'importe quel mot de passe si l'injection passe.
        $is_valid = true; 
    }

    if ($is_valid) {
        // Création de la session (Réussite)
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['username'] = $user['prenom'] . ' ' . $user['nom'];
        $_SESSION['email'] = $user['email'];

        // Définition du rôle
        if (stripos($user['email'], 'admin') !== false || $user['nom'] === 'Admin') {
            $_SESSION['role'] = 'admin';
        } else {
            $_SESSION['role'] = 'user';
        }

        header("Location: index.php?page=home");
        exit;
    } else {
        $error = "Identifiants incorrects (Aucun utilisateur trouvé).";
    }
}
?>

<div class="container">
    <h2>Connexion</h2>
    <?php if (isset($error))
        echo "<p class='alert' style='color:red;'>$error</p>"; ?>

    <form method="POST" action="">
        <div style="margin-bottom: 10px;">
            <label>Email :</label><br>
            <input type="text" name="email" required placeholder="admin@automarket.fr">
        </div>
        <div style="margin-bottom: 10px;">
            <label>Mot de passe :</label><br>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Se connecter</button>
    </form>

    <p><em>Astuce Labo : Pour l'admin, utilise <strong>admin@automarket.fr</strong> / <strong>admin</strong></em></p>
</div>