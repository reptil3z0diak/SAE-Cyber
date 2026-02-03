<?php
// pages/profil.php
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "Accès refusé.";
    return;
}

// LOGIQUE VULNÉRABLE CSRF (Update sans vérifier l'ancien mdp)
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $new_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE Users SET mdp = ? WHERE id_user = ?");
    if ($stmt->execute([$new_hash, $_SESSION['user_id']])) {
        $msg = "Mot de passe modifié avec succès (Faille CSRF active).";
    }
}

// LOGIQUE VULNÉRABLE IDOR (Regarder le profil d'un autre via ?id=X)
$id_to_show = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM Users WHERE id_user = ?");
$stmt->execute([$id_to_show]);
$u = $stmt->fetch();

if (!$u) {
    echo "Utilisateur introuvable.";
    return;
}
?>

<div class="container">
    <h2>Profil : <?php echo htmlspecialchars($u['prenom'] . " " . $u['nom']); ?></h2>

    <div style="display: flex; gap: 20px;">
        <div style="flex: 1; border: 1px solid #ddd; padding: 20px; background: white;">
            <p><strong>Email :</strong> <?php echo htmlspecialchars($u['email']); ?></p>
            <p><strong>Porte-Monnaie :</strong> <span
                    style="color: green; font-weight: bold;"><?php echo $u['porte_monnaie']; ?> €</span></p>
            <p><strong>Date inscription :</strong> <?php echo $u['date_inscription']; ?></p>

            <?php if ($u['id_user'] != $_SESSION['user_id']): ?>
                <p style="color:red; font-weight:bold; border: 1px solid red; padding: 5px;">⚠️ ATTENTION : Vous voyez le
                    profil d'un autre (IDOR)</p>
            <?php endif; ?>
        </div>

        <?php if ($u['id_user'] == $_SESSION['user_id']): ?>
            <div style="flex: 1; background: #fff3cd; padding: 20px; border: 1px solid #ffeeba;">
                <h3>Zone Sécurité (Vulnérable CSRF)</h3>
                <?php if ($msg)
                    echo "<p style='color:green'>$msg</p>"; ?>
                <form method="POST" action="index.php?page=profil">
                    <label>Nouveau mot de passe :</label><br>
                    <input type="password" name="new_password" placeholder="Nouveau mdp">
                    <button type="submit">Changer</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>