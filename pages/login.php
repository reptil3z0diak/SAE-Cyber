<?php
// pages/login.php
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Récupération de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // VÉRIFICATION DU MOT DE PASSE
    // 1. On vérifie le vrai hash (password_verify)
    // 2. OU (Astuce Labo) : Si le mot de passe est "admin" et l'user est Admin (car tes hashs insérés sont faux)
    $is_valid = false;
    if ($user) {
        if (password_verify($pass, $user['mdp'])) {
            $is_valid = true;
        }
    }

    if ($is_valid) {
        // Création de la session
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['username'] = $user['prenom'] . ' ' . $user['nom'];
        $_SESSION['email'] = $user['email'];

        // Définition du rôle (Admin si email contient "admin" ou nom "Admin")
        if (stripos($user['email'], 'admin') !== false || $user['nom'] === 'Admin') {
            $_SESSION['role'] = 'admin';
        } else {
            $_SESSION['role'] = 'user';
        }

        header("Location: index.php?page=home");
        exit;
    } else {
        $error = "Identifiants incorrects.";
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
            <input type="email" name="email" required placeholder="admin@automarket.fr">
        </div>
        <div style="margin-bottom: 10px;">
            <label>Mot de passe :</label><br>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Se connecter</button>
    </form>

    <p><em>Astuce Labo : Pour l'admin, utilise <strong>admin@automarket.fr</strong> / <strong>admin</strong></em></p>
</div>