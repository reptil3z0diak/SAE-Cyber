<?php
// pages/admin_dashboard.php
require_once 'includes/db_connect.php';

// Vérification accès Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<h2 style='color:red'>Accès Interdit (Zone Admin)</h2>");
}

// Recherche (sera vulnérable SQLi Blind)
$search = "";
$sql = "SELECT * FROM Users";
$params = [];

if (isset($_POST['search_user']) && !empty($_POST['search_user'])) {
    $search = $_POST['search_user'];
    // Filtrage propre pour l'instant
    $sql .= " WHERE nom LIKE ? OR email LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="container" style="border-top: 4px solid red;">
    <h2>Dashboard Admin</h2>
    <p>Gestion des utilisateurs de la base <strong>AutoMarket</strong></p>

    <div style="background: #f9f9f9; padding: 15px; margin-bottom: 20px;">
        <form method="POST">
            <label>Rechercher un utilisateur :</label>
            <input type="text" name="search_user" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Chercher</button>
        </form>
    </div>

    <table style="width: 100%; border-collapse: collapse; background: white;">
        <tr style="background: #333; color: white;">
            <th style="padding:8px;">ID</th>
            <th style="padding:8px;">Nom</th>
            <th style="padding:8px;">Email</th>
            <th style="padding:8px;">Solde</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding:8px;">
                    <?php echo $user['id_user']; ?>
                </td>
                <td style="padding:8px;"><strong>
                        <?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?>
                    </strong></td>
                <td style="padding:8px;">
                    <?php echo htmlspecialchars($user['email']); ?>
                </td>
                <td style="padding:8px;">
                    <?php echo $user['porte_monnaie']; ?> €
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>