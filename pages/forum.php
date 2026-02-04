<?php
// pages/forum.php
require_once 'includes/db_connect.php';

// ==============================================================================
// 1. GESTION AJAX (APERÇU) & BACKEND
// ==============================================================================

// --- A. AJAX : APERÇU DE LA PIÈCE JOINTE ---
if (isset($_POST['action']) && $_POST['action'] === 'preview_attachment') {
    // NETTOYAGE DU BUFFER (CRUCIAL) : 
    // Cela efface le header/menu que index.php a déjà généré avant d'inclure ce fichier.
    // Ça résout ton bug où le menu s'affichait dans l'aperçu.
    while (ob_get_level())
        ob_end_clean();

    $url = $_POST['url_image'];

    // Configuration cURL (On garde la config qui permet la faille SSRF pour plus tard, mais cachée)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    // On autorise tout pour le réalisme/faille
    curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS | CURLPROTO_FILE);
    curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS | CURLPROTO_FILE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $content = curl_exec($ch);
    $error = curl_error($ch);

    if ($content !== false) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($content);

        // Si c'est une image, on l'affiche proprement
        if (strpos($mimeType, 'image') !== false) {
            $base64 = base64_encode($content);
            echo "<img src='data:$mimeType;base64,$base64' class='preview-img-result'>";
        } else {
            // Si ce n'est pas une image (ou fichier volé), on affiche l'erreur technique
            echo "<div class='attachment-error'>";
            echo "<strong>⚠️ Erreur format :</strong> Le fichier n'est pas une image valide ($mimeType).<br>";
            echo "<small>Debug Output:</small><pre class='debug-log'>" . htmlspecialchars($content) . "</pre>";
            echo "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Lien inaccessible.</div>";
    }
    exit; // On arrête tout ici pour ne renvoyer QUE l'image
}

// --- B. TRAITEMENT DU POST (INSERTION) ---
if (isset($_POST['action']) && $_POST['action'] === 'create_topic') {
    if (isset($_SESSION['user_id'])) {
        $titre = $_POST['titre'];
        $categorie = (int) $_POST['categorie'];
        $contenu = $_POST['contenu'];
        $url_image = $_POST['url_image_final'] ?? '';

        // On ajoute le tag spécial à la fin du message si une image est présente
        if (!empty($url_image)) {
            $contenu .= "\n\n[IMG:$url_image]";
        }

        try {
            $pdo->beginTransaction();
            // Création du sujet
            $stmt = $pdo->prepare("INSERT INTO Forum_Sujets (id_categorie, id_auteur, titre, date_creation) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$categorie, $_SESSION['user_id'], $titre]);
            $id_sujet = $pdo->lastInsertId();

            // Création du premier message
            $stmt = $pdo->prepare("INSERT INTO Forum_Reponses (id_sujet, id_auteur, contenu, date_publication) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$id_sujet, $_SESSION['user_id'], $contenu]);

            $pdo->commit();
            header("Location: index.php?page=forum&sujet_id=$id_sujet");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }
}

// --- C. TRAITEMENT DE LA RÉPONSE ---
if (isset($_GET['sujet_id']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenu_reponse'])) {
    if (isset($_SESSION['user_id'])) {
        $pdo->prepare("INSERT INTO Forum_Reponses (id_sujet, id_auteur, contenu, date_publication) VALUES (?, ?, ?, NOW())")
            ->execute([(int) $_GET['sujet_id'], $_SESSION['user_id'], $_POST['contenu_reponse']]);
        header("Location: index.php?page=forum&sujet_id=" . $_GET['sujet_id']);
        exit;
    }
}

// FONCTION D'AFFICHAGE INTELLIGENT (Transforme le tag [IMG:...] en image HTML)
function formatMessage($texte)
{
    $texte = htmlspecialchars($texte); // Sécurité de base
    $texte = nl2br($texte); // Garde les sauts de ligne
    // Regex : Remplace [IMG:url] par <img src="url">
    $texte = preg_replace('/\[IMG:(.*?)\]/', '<div class="post-image-container"><img src="$1" alt="Image jointe"></div>', $texte);
    return $texte;
}
?>

<style>
    .forum-container {
        max-width: 1000px;
        margin: 20px auto;
        font-family: 'Segoe UI', sans-serif;
        color: #333;
    }

    /* MODAL & FORMULAIRE */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        width: 90%;
        max-width: 900px;
        height: 80vh;
        border-radius: 8px;
        display: flex;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }

    .modal-left {
        flex: 1;
        padding: 20px;
        background: #f8f9fa;
        border-right: 1px solid #ddd;
        overflow-y: auto;
    }

    .modal-right {
        flex: 1;
        padding: 20px;
        background: #fff;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .btn-primary {
        background: #007bff;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
        font-weight: bold;
    }

    .btn-attacher {
        background: #6c757d;
        color: white;
        border: none;
        padding: 0 15px;
        border-radius: 4px;
        cursor: pointer;
    }

    /* APERÇU (PREVIEW) */
    .preview-box {
        border: 1px solid #e1e8ed;
        border-radius: 5px;
        padding: 15px;
        margin-top: 10px;
    }

    .preview-user {
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }

    .preview-date {
        color: #888;
        font-size: 0.8em;
    }

    .preview-title {
        font-size: 1.4em;
        color: #007bff;
        font-weight: bold;
        margin: 10px 0;
    }

    .preview-body {
        font-size: 1em;
        line-height: 1.5;
        color: #444;
        white-space: pre-wrap;
    }

    /* GESTION DES IMAGES (Aperçu + Post final) */
    .preview-img-result,
    .post-image-container img {
        max-width: 100%;
        max-height: 400px;
        border-radius: 5px;
        margin-top: 15px;
        display: block;
        border: 1px solid #ddd;
    }

    /* ERREUR DEBUG (Pour ton attaque) */
    .attachment-error {
        background: #fff3cd;
        border: 1px solid #ffeeba;
        padding: 10px;
        margin-top: 10px;
        border-radius: 4px;
        color: #856404;
    }

    .debug-log {
        background: #222;
        color: #0f0;
        padding: 10px;
        max-height: 200px;
        overflow-y: auto;
        font-size: 0.8em;
        margin-top: 5px;
        border-radius: 3px;
    }
</style>

<div class="forum-container">

    <?php if (isset($_GET['sujet_id'])):
        $id = (int) $_GET['sujet_id'];
        $sujet = $pdo->query("SELECT S.*, U.nom FROM Forum_Sujets S JOIN Users U ON S.id_auteur = U.id_user WHERE id_sujet=$id")->fetch();
        $reponses = $pdo->query("SELECT R.*, U.nom FROM Forum_Reponses R JOIN Users U ON R.id_auteur = U.id_user WHERE id_sujet=$id ORDER BY date_publication ASC")->fetchAll();
        ?>
        <a href="index.php?page=forum" style="text-decoration: none; color: #666;">&larr; Retour</a>
        <h2 style="border-bottom: 2px solid #007bff; padding-bottom: 10px;">
            <?php echo htmlspecialchars($sujet['titre']); ?>
        </h2>

        <?php foreach ($reponses as $r): ?>
            <div style="background: #fff; border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                <div style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px;">
                    <strong style="color: #007bff;">
                        <?php echo htmlspecialchars($r['nom']); ?>
                    </strong>
                    <span style="float:right; color:#999; font-size:0.8em;">
                        <?php echo $r['date_publication']; ?>
                    </span>
                </div>
                <div style="line-height: 1.6;">
                    <?php echo formatMessage($r['contenu']); ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" style="margin-top: 20px;">
                <textarea name="contenu_reponse" class="form-control" style="height:100px" required
                    placeholder="Répondre..."></textarea>
                <button type="submit" class="btn-primary" style="margin-top:10px; width:auto;">Envoyer la réponse</button>
            </form>
        <?php endif; ?>

    <?php else: ?>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h1>Discussions</h1>
            <?php if (isset($_SESSION['user_id'])): ?>
                <button onclick="document.getElementById('modal-create').style.display='flex'" class="btn-primary"
                    style="width:auto;">+ Nouveau Sujet</button>
            <?php endif; ?>
        </div>

        <table style="width:100%; border-collapse:collapse; background:white; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
            <tr style="background:#f1f1f1; text-align:left;">
                <th style="padding:15px;">Sujet</th>
                <th style="padding:15px;">Auteur</th>
                <th style="padding:15px;">Date</th>
            </tr>
            <?php
            $sujets = $pdo->query("SELECT S.*, C.titre as cat, U.nom FROM Forum_Sujets S LEFT JOIN Forum_Categories C ON S.id_categorie=C.id_categorie LEFT JOIN Users U ON S.id_auteur=U.id_user ORDER BY date_creation DESC")->fetchAll();
            foreach ($sujets as $s): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:15px;">
                        <span
                            style="background:#e9ecef; padding:2px 6px; font-size:0.8em; border-radius:3px; margin-right:5px; font-weight:bold; color:#555;">
                            <?php echo htmlspecialchars($s['cat']); ?>
                        </span>
                        <a href="index.php?page=forum&sujet_id=<?php echo $s['id_sujet']; ?>"
                            style="font-weight:bold; color:#007bff; text-decoration:none;">
                            <?php echo htmlspecialchars($s['titre']); ?>
                        </a>
                    </td>
                    <td style="padding:15px;">
                        <?php echo htmlspecialchars($s['nom']); ?>
                    </td>
                    <td style="padding:15px; font-size:0.9em; color:#777;">
                        <?php echo date('d/m/Y', strtotime($s['date_creation'])); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<div id="modal-create" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-left">
            <h3 style="margin-top:0;">Nouveau Sujet</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create_topic">

                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="categorie" class="form-control">
                        <?php foreach ($pdo->query("SELECT * FROM Forum_Categories") as $c)
                            echo "<option value='{$c['id_categorie']}'>{$c['titre']}</option>"; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Titre</label>
                    <input type="text" id="input_titre" name="titre" class="form-control" required
                        oninput="updatePreview()" placeholder="Titre de votre post">
                </div>

                <div class="form-group">
                    <label>Message</label>
                    <textarea id="input_contenu" name="contenu" class="form-control" rows="8" required
                        oninput="updatePreview()" placeholder="Votre texte ici..."></textarea>
                </div>

                <div class="form-group"
                    style="background:#fff; border:1px dashed #007bff; padding:10px; border-radius:5px;">
                    <label style="color:#007bff; font-weight:bold;">Ajouter une image (URL)</label>
                    <div style="display:flex; gap:5px; margin-top:5px;">
                        <input type="text" id="input_url" name="url_image_final" class="form-control"
                            placeholder="http://...">
                        <button type="button" class="btn-attacher" onclick="loadAttachment()">Attacher</button>
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="button" onclick="document.getElementById('modal-create').style.display='none'"
                        style="flex:1; padding:10px; background:#ddd; border:none; border-radius:4px; cursor:pointer;">Annuler</button>
                    <button type="submit" class="btn-primary" style="flex:2;">Publier</button>
                </div>
            </form>
        </div>

        <div class="modal-right">
            <h4 style="color:#999; text-transform:uppercase; font-size:0.8em; letter-spacing:1px; margin-top:0;">Aperçu
                en direct</h4>

            <div class="preview-box">
                <div class="preview-user">Vous (Auteur)</div>
                <div class="preview-date">À l'instant</div>
                <hr style="border:0; border-top:1px solid #eee; margin:10px 0;">

                <div id="preview_titre" class="preview-title">Titre du sujet...</div>

                <div id="preview_contenu" class="preview-body">Le contenu apparaîtra ici...</div>

                <div id="preview_attachment"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // JS : Met à jour le texte en direct
    function updatePreview() {
        var titre = document.getElementById('input_titre').value;
        var contenu = document.getElementById('input_contenu').value;

        document.getElementById('preview_titre').innerText = titre ? titre : "Titre du sujet...";
        // nl2br pour le JS (remplace \n par <br>)
        document.getElementById('preview_contenu').innerHTML = contenu ? contenu.replace(/\n/g, "<br>") : "Le contenu apparaîtra ici...";
    }

    // JS : Charge l'image en AJAX (et nettoie le header grâce au fix PHP)
    function loadAttachment() {
        var url = document.getElementById('input_url').value;
        var zone = document.getElementById('preview_attachment');

        if (!url) return;

        zone.innerHTML = "<small style='color:grey'>Chargement...</small>";

        var formData = new FormData();
        formData.append('action', 'preview_attachment');
        formData.append('url_image', url);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?page=forum', true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                zone.innerHTML = xhr.responseText;
            } else {
                zone.innerHTML = "<span style='color:red'>Erreur serveur.</span>";
            }
        };
        xhr.send(formData);
    }
</script>