<?php
// pages/recherche_avancee.php
// ⚠️ PAGE VULNÉRABLE XXE (CWE-611)
// Cette page accepte des filtres XML et est vulnérable aux entités externes

// ⚠️ VULNÉRABILITÉ : Activer le chargement des entités externes (désactivé par défaut en PHP 8+)
// Note: libxml_disable_entity_loader() est obsolète en PHP 8+
if (function_exists('libxml_disable_entity_loader')) {
    @libxml_disable_entity_loader(false);
}

// ⚠️ VULNÉRABILITÉ XXE : Fonction pour résoudre manuellement les entités externes (PHP 8+)
function resolveXXE($xmlData)
{
    // Chercher les déclarations d'entités SYSTEM dans le DOCTYPE
    if (preg_match('/<!ENTITY\s+(\w+)\s+SYSTEM\s+"([^"]+)"/', $xmlData, $matches)) {
        $entityName = $matches[1];
        $systemPath = $matches[2];

        // Supprimer le préfixe file:// si présent
        if (strpos($systemPath, 'file://') === 0) {
            $systemPath = substr($systemPath, 7);
        }

        // ⚠️ VULNÉRABILITÉ : Lecture du fichier local sans validation
        $content = '';
        if (strpos($systemPath, 'php://') === 0) {
            $content = @file_get_contents($systemPath);
        }
        elseif (file_exists($systemPath)) {
            $content = @file_get_contents($systemPath);
        }

        // Substituer l'entité dans le XML
        if ($content !== false && $content !== '') {
            // Encoder le contenu pour éviter de casser le XML
            $content = htmlspecialchars($content, ENT_XML1, 'UTF-8');
            // Remplacer &entityName; par le contenu du fichier
            $xmlData = str_replace('&' . $entityName . ';', $content, $xmlData);
        }
    }
    return $xmlData;
}

$results = [];
$xmlData = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $xmlData = isset($_POST['filters']) ? $_POST['filters'] : '';

    if (!empty($xmlData)) {
        // ⚠️ VULNÉRABILITÉ XXE : Résolution manuelle des entités externes
        $resolvedXml = resolveXXE($xmlData);

        // ⚠️ VULNÉRABILITÉ : Parsing XML
        $doc = new DOMDocument();
        $doc->loadXML($resolvedXml, LIBXML_NOERROR);

        $xpath = new DOMXPath($doc);

        // Extraire les filtres
        $marque = $xpath->query('//filtre/marque')->item(0);
        $type = $xpath->query('//filtre/type')->item(0);
        $prix_max = $xpath->query('//filtre/prix_max')->item(0);
        $region = $xpath->query('//filtre/region')->item(0);

        $marque = $marque ? $marque->nodeValue : '';
        $type = $type ? $type->nodeValue : '';
        $prix_max = $prix_max ? $prix_max->nodeValue : '';
        $region = $region ? $region->nodeValue : '';

        // Simulation de résultats (données mockées)
        $allCars = [
            ['marque' => 'Peugeot', 'modele' => '308', 'type' => 'berline', 'prix' => 18500, 'region' => 'ile-de-france'],
            ['marque' => 'Renault', 'modele' => 'Clio', 'type' => 'citadine', 'prix' => 15000, 'region' => 'ile-de-france'],
            ['marque' => 'BMW', 'modele' => 'Serie 3', 'type' => 'berline', 'prix' => 35000, 'region' => 'provence'],
            ['marque' => 'Audi', 'modele' => 'A4', 'type' => 'berline', 'prix' => 32000, 'region' => 'bretagne'],
            ['marque' => 'Toyota', 'modele' => 'Yaris', 'type' => 'citadine', 'prix' => 14000, 'region' => 'ile-de-france'],
            ['marque' => 'Mercedes', 'modele' => 'Classe C', 'type' => 'berline', 'prix' => 42000, 'region' => 'normandie'],
            ['marque' => 'Ford', 'modele' => 'Focus', 'type' => 'berline', 'prix' => 19000, 'region' => 'provence'],
            ['marque' => 'Volkswagen', 'modele' => 'Golf', 'type' => 'berline', 'prix' => 25000, 'region' => 'ile-de-france'],
        ];

        foreach ($allCars as $car) {
            $match = true;
            if ($marque && stripos($car['marque'], $marque) === false)
                $match = false;
            if ($type && $car['type'] !== $type)
                $match = false;
            if ($prix_max && $car['prix'] > intval($prix_max))
                $match = false;
            if ($region && $car['region'] !== $region)
                $match = false;

            if ($match)
                $results[] = $car;
        }
    }
}
?>

<div class="container">
    <h2>🔍 Recherche Avancée de Véhicules</h2>
    <p>Utilisez notre système de filtrage avancé pour trouver votre véhicule idéal.</p>

    <div style="display: flex; gap: 30px; flex-wrap: wrap; margin-top: 20px;">

        <!-- Formulaire de filtres -->
        <div
            style="flex: 1; min-width: 350px; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3>📋 Filtres de recherche</h3>

            <form method="POST" id="searchForm">
                <div style="margin-bottom: 15px;">
                    <label>Marque :</label>
                    <select id="marque" style="width: 100%; padding: 8px; margin-top: 5px;">
                        <option value="">Toutes</option>
                        <option value="Peugeot">Peugeot</option>
                        <option value="Renault">Renault</option>
                        <option value="BMW">BMW</option>
                        <option value="Audi">Audi</option>
                        <option value="Toyota">Toyota</option>
                        <option value="Mercedes">Mercedes</option>
                        <option value="Ford">Ford</option>
                        <option value="Volkswagen">Volkswagen</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label>Type :</label>
                    <select id="type" style="width: 100%; padding: 8px; margin-top: 5px;">
                        <option value="">Tous</option>
                        <option value="citadine">Citadine</option>
                        <option value="berline">Berline</option>
                        <option value="suv">SUV</option>
                        <option value="break">Break</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label>Prix maximum (€) :</label>
                    <input type="number" id="prix_max" placeholder="Ex: 30000"
                        style="width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label>Région :</label>
                    <select id="region" style="width: 100%; padding: 8px; margin-top: 5px;">
                        <option value="">Toutes</option>
                        <option value="ile-de-france">Île-de-France</option>
                        <option value="provence">Provence</option>
                        <option value="bretagne">Bretagne</option>
                        <option value="normandie">Normandie</option>
                    </select>
                </div>

                <input type="hidden" name="filters" id="xmlFilters">

                <button type="submit" onclick="generateXML()"
                    style="background: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; width: 100%;">
                    🔎 Rechercher
                </button>
            </form>
        </div>

        <!-- Résultats -->
        <div style="flex: 2; min-width: 400px;">
            <h3>📊 Résultats (
                <?php echo count($results); ?> véhicules)
            </h3>

            <?php if (!empty($results)): ?>
            <table
                style="width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <thead style="background: #343a40; color: white;">
                    <tr>
                        <th style="padding: 12px;">Marque</th>
                        <th style="padding: 12px;">Modèle</th>
                        <th style="padding: 12px;">Type</th>
                        <th style="padding: 12px;">Prix</th>
                        <th style="padding: 12px;">Région</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $car): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;">
                            <?php echo htmlspecialchars($car['marque']); ?>
                        </td>
                        <td style="padding: 12px;">
                            <?php echo htmlspecialchars($car['modele']); ?>
                        </td>
                        <td style="padding: 12px;">
                            <?php echo htmlspecialchars($car['type']); ?>
                        </td>
                        <td style="padding: 12px; color: green; font-weight: bold;">
                            <?php echo number_format($car['prix'], 0, ',', ' '); ?> €
                        </td>
                        <td style="padding: 12px;">
                            <?php echo htmlspecialchars($car['region']); ?>
                        </td>
                    </tr>
                    <?php
    endforeach; ?>
                </tbody>
            </table>
            <?php
elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <p style="padding: 20px; background: #f8f9fa; border-radius: 10px; text-align: center;">Aucun véhicule
                trouvé avec ces critères.</p>
            <?php
else: ?>
            <p style="padding: 20px; background: #f8f9fa; border-radius: 10px; text-align: center;">Utilisez les filtres
                pour rechercher des véhicules.</p>
            <?php
endif; ?>

            <?php if (!empty($xmlData)): ?>
            <div style="margin-top: 20px; background: #f5f5f5; padding: 15px; border-radius: 5px;">
                <strong>XML envoyé :</strong>
                <pre
                    style="background: #1a1a2e; color: #00ff00; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 0.85em;"><?php echo htmlspecialchars($xmlData); ?></pre>
            </div>
            <?php
endif; ?>
        </div>
    </div>
</div>

<script>
    function generateXML() {
        var marque = document.getElementById('marque').value;
        var type = document.getElementById('type').value;
        var prix_max = document.getElementById('prix_max').value;
        var region = document.getElementById('region').value;

        var xml = '<' + '?xml version="1.0" encoding="UTF-8"?' + '>\n';
        xml += '<recherche>\n';
        xml += '  <filtre>\n';
        xml += '    <marque>' + marque + '</marque>\n';
        xml += '    <type>' + type + '</type>\n';
        xml += '    <prix_max>' + prix_max + '</prix_max>\n';
        xml += '    <region>' + region + '</region>\n';
        xml += '  </filtre>\n';
        xml += '</recherche>';

        document.getElementById('xmlFilters').valu    }
</script>