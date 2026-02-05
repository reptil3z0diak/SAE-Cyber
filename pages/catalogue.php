<?php
// pages/catalogue.php
require_once 'includes/db_connect.php';

// Requête de base
$sql = "SELECT A.*, V.nom as vendeur_nom 
        FROM Annonces A 
        LEFT JOIN Vendeurs V ON A.id_vendeur = V.id_vendeur";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    // Concaténation directe (Vulnérabilité SQLi présente)
    $sql .= " WHERE description LIKE '%" . $search . "%'";
}

// Exécution de la requête : 
$stmt = $pdo->query($sql);

if (!$stmt) {
    echo "Erreur SQL : " . print_r($pdo->errorInfo(), true);
    $annonces = [];
}
else {
    $annonces = $stmt->fetchAll();
}
?>

<div class="catalogue-header">
    <h2>Catalogue AutoMarket</h2>

    <form method="GET" action="index.php" style="background: #e9e9e9; padding: 15px; border-radius: 5px;">
        <input type="hidden" name="page" value="catalogue">
        <label>Rechercher :</label>
        <input type="text" name="search" placeholder="Ex: Peugeot, Pneus...">
        <button type="submit">Rechercher</button>
    </form>
    
    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
        <p style="margin-top: 10px;">
            Résultats pour votre recherche : 
            <?php // on utilise : echo htmlspecialchars($_GET['search']); pour transformé les script en texte ?>
            <strong><?php echo $_GET['search']; ?></strong> 
            
    <?php
endif; ?>

</div>

<br>

<div class="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
    <?php if (count($annonces) > 0): ?>
        <?php foreach ($annonces as $item): ?>
            <div class="car-card" style="border: 1px solid #ccc; padding: 15px; background: white; border-radius: 5px;">
                <img src="https://via.placeholder.com/300x200?text=AutoMarket" style="width:100%; border-radius:3px;">

                <h3><?php echo $item['description']; ?></h3>
                
                <p style="color: #007bff; font-weight: bold; font-size: 1.2em;">
                    <?php echo number_format($item['prix'], 2, ',', ' '); ?> €
                </p>
                <p>📍 <?php echo htmlspecialchars($item['location']); ?></p>
                <p><em>Vendeur : <?php echo htmlspecialchars($item['vendeur_nom']); ?></em></p>

                <a href="index.php?page=car_detail&id=<?php echo $item['id_annonce']; ?>"
                   style="display: block; text-align: center; background: #333; color: white; padding: 8px; text-decoration: none; margin-top: 10px;">
                    Voir l'annonce
                </a>
            </div>
        <?php
    endforeach; ?>
    <?php
else: ?>
        <p>Aucune annonce trouvée.</p>
    <?php
endif; ?>
</div>