<?php
// pages/attackAlois.php

$uploadDir = 'uploads/';
$message = '';
$messageType = '';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $file = $_FILES['photo'];
    $fileName = $file['name'];
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $message = "Fichier uploadé avec succès : <a href='$targetPath' target='_blank'>$targetPath</a>";
        $messageType = 'success';
    } else {
        $message = "Erreur lors de l'upload du fichier.";
        $messageType = 'error';
    }
}

$uploadedFiles = [];
if (is_dir($uploadDir)) {
    $uploadedFiles = array_diff(scandir($uploadDir), ['.', '..']);
}
?>

<div class="container">
    <h2>📷 Upload Photo de Profil</h2>

    <?php if ($message): ?>
        <div
            style="padding: 15px; margin: 15px 0; border-radius: 5px; background: <?php echo $messageType === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $messageType === 'success' ? '#155724' : '#721c24'; ?>;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        <div
            style="flex: 1; min-width: 300px; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3>Uploader une photo</h3>
            <form method="POST" enctype="multipart/form-data">
                <div style="margin: 20px 0;">
                    <label for="photo" style="display: block; margin-bottom: 10px;">Sélectionner un fichier :</label>
                    <input type="file" name="photo" id="photo" required
                        style="padding: 10px; border: 2px dashed #ccc; width: 100%; box-sizing: border-box;">
                </div>
                <button type="submit"
                    style="background: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; width: 100%;">
                    Uploader
                </button>
            </form>
        </div>

        <div style="flex: 1; min-width: 300px; background: #f8f9fa; padding: 25px; border-radius: 10px;">
            <h3>Fichiers Uploadés</h3>
            <?php if (empty($uploadedFiles)): ?>
                <p style="color: #666;">Aucun fichier uploadé.</p>
            <?php else: ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($uploadedFiles as $file): ?>
                        <li style="padding: 8px 0; border-bottom: 1px solid #ddd;">
                            <a href="<?php echo $uploadDir . htmlspecialchars($file); ?>" target="_blank"
                                style="color: #007bff;">
                                <?php echo htmlspecialchars($file); ?>
                            </a>
                            <span style="color: #888; font-size: 0.8em;">
                                (<?php echo round(filesize($uploadDir . $file) / 1024, 2); ?> KB)
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>