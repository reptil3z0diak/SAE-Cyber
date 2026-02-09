<?php
// pages/admin_dashboard.php
require_once 'includes/db_connect.php';

// Vérification accès Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<h2 style='color:red'>Accès Interdit (Zone Admin)</h2>");
}

// --- PARTIE 1 : LOGIQUE DE LA VULNÉRABILITÉ (Injection de Commande) ---
$cmd_result = "";
if (isset($_POST['check_ip']) && !empty($_POST['target_ip'])) {
    $ip = $_POST['target_ip'];

    // EXPLICATION DE LA FAILLE :
    // Ici, nous concaténons directement la variable $ip dans une chaîne de commande système.
    // Nous n'utilisons pas escapeshellarg() ou escapeshellcmd().
    // Si l'utilisateur entre "127.0.0.1; ls", le système exécutera "ping -c 2 127.0.0.1; ls".
    
    // Note : Sur Windows, le ping n'a pas l'option -c, on utiliserait juste "ping $ip".
    // Comme ton cours mentionne Linux et Windows, on suppose ici un serveur Linux (option -c 2 pour 2 pings).
    $command = "ping -c 2 " . $ip; 
    
    // shell_exec exécute la commande via le shell et retourne le résultat complet
    $cmd_result = shell_exec($command);
}
// ---------------------------------------------------------------------

// Recherche (sera vulnérable SQLi Blind - code existant)
$search = "";
$sql = "SELECT * FROM Users";
$params = [];

if (isset($_POST['search_user']) && !empty($_POST['search_user'])) {
    $search = $_POST['search_user'];
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

    <div style="background: #ffe6e6; padding: 15px; margin-bottom: 20px; border: 1px solid red;">
        <h3>🛠 Outil Diagnostic Réseau (Admin Only)</h3>
        <p>Vérifier la connectivité d'une IP utilisateur ou d'un serveur distant.</p>
        <form method="POST">
            <label>IP à pinger :</label>
            <input type="text" name="target_ip" placeholder="ex: 192.168.1.1" style="width: 300px;">
            <button type="submit" name="check_ip">Lancer Diagnostic</button>
        </form>

        <?php if (!empty($cmd_result)): ?>
            <div style="background: black; color: lightgreen; padding: 10px; margin-top: 10px; font-family: monospace;">
                <strong>Résultat du terminal :</strong><br>
                <pre><?php echo $cmd_result; ?></pre>
            </div>
        <?php endif; ?>
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