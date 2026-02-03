<?php
// pages/car_detail.php
require_once 'includes/db_connect.php';

$id_annonce = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Jointure pour avoir les infos de l'annonce ET du vendeur
$sql = "SELECT A.*, V.nom as vendeur_nom, V.email as vendeur_email, V.tel as vendeur_tel, V.note 
        FROM Annonces A 
        LEFT JOIN Vendeurs V ON A.id_vendeur = V.id_vendeur 
        WHERE A.id_annonce = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_annonce]);
$item = $stmt->fetch();

if (!$item) {
    echo "<h2>Annonce introuvable</h2><a href='index.php?page=catalogue'>Retour</a>";
    return;
}
?>

<div class="container" style="background: white; padding: 20px; border-radius: 5px;">
    <a href="index.php?page=catalogue">&larr; Retour au catalogue</a>

    <h1><?php echo htmlspecialchars($item['description']); ?></h1>
    <h2 style="color: #28a745;"><?php echo number_format($item['prix'], 2, ',', ' '); ?> €</h2>

    <div style="background: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 5px solid #007bff;">
        <p><strong>Lieu :</strong> <?php echo htmlspecialchars($item['location']); ?></p>
        <p><strong>Type :</strong> <?php echo htmlspecialchars($item['type_annonce']); ?></p>
    </div>

    <h3>Informations Vendeur</h3>
    <ul>
        <li><strong>Nom :</strong> <?php echo htmlspecialchars($item['vendeur_nom']); ?></li>
        <li><strong>Note :</strong> <?php echo $item['note']; ?> / 5</li>
        <li><strong>Contact :</strong> <?php echo htmlspecialchars($item['vendeur_tel']); ?></li>
        <li><strong>Email :</strong> <?php echo htmlspecialchars($item['vendeur_email']); ?></li>
    </ul>

    <button onclick="alert('Fonctionnalité de contact bientôt disponible')"
        style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;">
        Contacter ce vendeur
    </button>
</div>