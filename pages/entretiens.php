<?php
// pages/entretiens.php
// ⚠️ PAGE VULNÉRABLE SQL INJECTION UNION (CWE-89)
// Le paramètre 'type' n'est pas filtré

require_once 'includes/db_connect.php';

$results = [];
$error = '';

// Récupération des types pour le menu déroulant
$types = $pdo->query("SELECT * FROM Types_Vehicules")->fetchAll();

// Récupération des filtres
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$marque_filter = isset($_GET['marque']) ? $_GET['marque'] : '';
$entretien_filter = isset($_GET['entretien']) ? $_GET['entretien'] : '';

// Construction de la requête
$sql = "SELECT e.id_entretien, e.marque, e.annee_modele, e.type_entretien, e.description, e.prix_estime, t.nom_type 
        FROM Entretiens e 
        JOIN Types_Vehicules t ON e.id_type = t.id_type 
        WHERE 1=1";

// ⚠️ VULNÉRABILITÉ : Le paramètre 'type' est concaténé directement (pas de prepared statement)
if ($type_filter !== '') {
    $sql .= " AND e.id_type = " . $type_filter;
}

// Ces paramètres sont sécurisés (pour montrer la différence)
if ($marque_filter !== '') {
    $sql .= " AND e.marque = " . $pdo->quote($marque_filter);
}
if ($entretien_filter !== '') {
    $sql .= " AND e.type_entretien = " . $pdo->quote($entretien_filter);
}

try {
    $results = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur SQL : " . $e->getMessage();
}

// Marques disponibles
$marques = ['Peugeot', 'Renault', 'BMW', 'Audi', 'Toyota', 'Mercedes', 'Volkswagen'];
$entretiens_types = ['Vidange', 'Révision', 'Freinage', 'Pneumatiques'];
?>

<div class="container">
    <h2>🔧 Nos Prestations d'Entretien</h2>
    <p>Consultez nos tarifs d'entretien selon le type de véhicule.</p>

    <!-- Formulaire de filtres -->
    <div
        style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <form method="GET" action="index.php" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
            <input type="hidden" name="page" value="entretiens">

            <div>
                <label style="display: block; margin-bottom: 5px;">Type de véhicule :</label>
                <select name="type" style="padding: 8px; min-width: 150px;">
                    <option value="">Tous</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?php echo $t['id_type']; ?>" <?php echo $type_filter == $t['id_type'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['nom_type']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px;">Marque :</label>
                <select name="marque" style="padding: 8px; min-width: 150px;">
                    <option value="">Toutes</option>
                    <?php foreach ($marques as $m): ?>
                        <option value="<?php echo $m; ?>" <?php echo $marque_filter == $m ? 'selected' : ''; ?>>
                            <?php echo $m; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px;">Type d'entretien :</label>
                <select name="entretien" style="padding: 8px; min-width: 150px;">
                    <option value="">Tous</option>
                    <?php foreach ($entretiens_types as $et): ?>
                        <option value="<?php echo $et; ?>" <?php echo $entretien_filter == $et ? 'selected' : ''; ?>>
                            <?php echo $et; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit"
                style="background: #007bff; color: white; padding: 8px 20px; border: none; border-radius: 5px; cursor: pointer;">
                🔍 Filtrer
            </button>
        </form>
    </div>

    <?php if ($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Résultats -->
    <div style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #343a40; color: white;">
                <tr>
                    <th style="padding: 12px; text-align: left;">Type</th>
                    <th style="padding: 12px; text-align: left;">Marque</th>
                    <th style="padding: 12px; text-align: left;">Année</th>
                    <th style="padding: 12px; text-align: left;">Entretien</th>
                    <th style="padding: 12px; text-align: left;">Description</th>
                    <th style="padding: 12px; text-align: right;">Prix</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)): ?>
                    <tr>
                        <td colspan="6" style="padding: 20px; text-align: center; color: #666;">
                            Aucun résultat trouvé.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($results as $row): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 12px;">
                                <?php echo htmlspecialchars($row['nom_type'] ?? $row[6] ?? ''); ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php echo htmlspecialchars($row['marque'] ?? $row[1] ?? ''); ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php echo htmlspecialchars($row['annee_modele'] ?? $row[2] ?? ''); ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php echo htmlspecialchars($row['type_entretien'] ?? $row[3] ?? ''); ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php echo htmlspecialchars($row['description'] ?? $row[4] ?? ''); ?>
                            </td>
                            <td style="padding: 12px; text-align: right; color: green; font-weight: bold;">
                                <?php
                                $prix = $row['prix_estime'] ?? $row[5] ?? '';
                                echo is_numeric($prix) ? number_format($prix, 2, ',', ' ') . ' €' : htmlspecialchars($prix);
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p style="margin-top: 20px; color: #666; font-size: 0.9em;">
        📊
        <?php echo count($results); ?> résultat(s) trouvé(s)
    </p>
</div>