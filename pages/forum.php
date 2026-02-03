<?php
// pages/forum.php
require_once 'includes/db_connect.php';

// Si un sujet est sélectionné (?sujet_id=X), on affiche les messages
if (isset($_GET['sujet_id'])) {
    $id_sujet = (int) $_GET['sujet_id'];

    // Récupérer le sujet
    $stmt = $pdo->prepare("SELECT S.*, U.nom as auteur_nom FROM Forum_Sujets S LEFT JOIN Users U ON S.id_auteur = U.id_user WHERE id_sujet = ?");
    $stmt->execute([$id_sujet]);
    $sujet = $stmt->fetch();

    // Récupérer les réponses (JOIN pour avoir le nom de l'auteur)
    $stmt = $pdo->prepare("SELECT R.*, U.nom as auteur_nom FROM Forum_Reponses R LEFT JOIN Users U ON R.id_auteur = U.id_user WHERE id_sujet = ? ORDER BY date_publication ASC");
    $stmt->execute([$id_sujet]);
    $reponses = $stmt->fetchAll();

    // --- POSTER UNE RÉPONSE (XSS STORED ICI) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenu'])) {
        if (isset($_SESSION['user_id'])) {
            $contenu = $_POST['contenu'];
            // INSERTION VULNÉRABLE (Pas de htmlspecialchars ici, c'est fait exprès pour ton pentest)
            $sql_insert = "INSERT INTO Forum_Reponses (id_sujet, id_auteur, contenu) VALUES (?, ?, ?)";
            $pdo->prepare($sql_insert)->execute([$id_sujet, $_SESSION['user_id'], $contenu]);
            // Rafraichir la page
            header("Location: index.php?page=forum&sujet_id=$id_sujet");
            exit;
        }
    }
    ?>

    <div class="container">
        <a href="index.php?page=forum" style="text-decoration: none;">&larr; Retour aux sujets</a>
        <h2>Discussion : <?php echo htmlspecialchars($sujet['titre']); ?></h2>

        <div class="messages-list">
            <?php foreach ($reponses as $msg): ?>
                <div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; background: white;">
                    <strong style="color: #555;"><?php echo htmlspecialchars($msg['auteur_nom']); ?></strong>
                    <span style="font-size: 0.8em; color: #999;">(<?php echo $msg['date_publication']; ?>)</span>
                    <hr style="margin: 5px 0; border: 0; border-top: 1px solid #eee;">
                    <p><?php echo $msg['contenu']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <h3>Répondre</h3>
            <form method="POST">
                <textarea name="contenu" style="width: 100%; height: 80px;" required></textarea><br>
                <button type="submit" style="margin-top: 5px;">Envoyer</button>
            </form>
        <?php else: ?>
            <p style="color: red;">Connectez-vous pour répondre.</p>
        <?php endif; ?>
    </div>

<?php
} else {
    // VUE LISTE DES SUJETS
    $stmt = $pdo->query("SELECT S.*, C.titre as cat_titre, U.nom as auteur_nom 
                         FROM Forum_Sujets S 
                         LEFT JOIN Forum_Categories C ON S.id_categorie = C.id_categorie
                         LEFT JOIN Users U ON S.id_auteur = U.id_user
                         ORDER BY S.date_creation DESC");
    $sujets = $stmt->fetchAll();
    ?>
    <div class="container">
        <h2>Forum AutoMarket</h2>
        <p>Bienvenue sur le forum d'entraide.</p>

        <table style="width: 100%; border-collapse: collapse; background: white;">
            <thead>
                <tr style="background: #333; color: white; text-align: left;">
                    <th style="padding: 10px;">Catégorie</th>
                    <th style="padding: 10px;">Sujet</th>
                    <th style="padding: 10px;">Auteur</th>
                    <th style="padding: 10px;">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sujets as $s): ?>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 10px; font-size: 0.9em; color: #666;">
                            <?php echo htmlspecialchars($s['cat_titre']); ?></td>
                        <td style="padding: 10px;">
                            <a href="index.php?page=forum&sujet_id=<?php echo $s['id_sujet']; ?>"
                                style="font-weight: bold; color: #007bff; text-decoration: none;">
                                <?php echo htmlspecialchars($s['titre']); ?>
                            </a>
                        </td>
                        <td style="padding: 10px;"><?php echo htmlspecialchars($s['auteur_nom']); ?></td>
                        <td style="padding: 10px; font-size: 0.8em;"><?php echo $s['date_creation']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php } ?>