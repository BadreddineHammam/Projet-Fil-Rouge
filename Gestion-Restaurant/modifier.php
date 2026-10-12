<?php
/**
 * Page de Modification d'un Plat de la Carte
 * Restaurant Le Gourmet (Site Web Restaurant Unique)
 */
require_once "connexion.php";

$error = null;
$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$stmt = $pdo->prepare("SELECT * FROM plat WHERE id_plat = ?");
$stmt->execute([$id]);
$plat = $stmt->fetch();

if (!$plat) {
    header("Location: index.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categorie_plat ORDER BY id_categorie ASC")->fetchAll();

$defaultIcons = [
    'Entrées'                   => '🥗',
    'Plats Principaux'          => '🥩',
    'Poissons & Fruits de Mer'  => '🐟',
    'Pâtes & Spécialités'       => '🍝',
    'Desserts'                  => '🍰',
    'Boissons & Cocktails'      => '🍹',
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $nom         = trim($_POST["nom"] ?? '');
        $id_cat      = (int)($_POST["id_categorie"] ?? $plat['id_categorie']);
        $prix        = (float)($_POST["prix"] ?? 0);
        $temps_prep  = (int)($_POST["temps_prep"] ?? 15);
        $statut      = $_POST["statut"] ?? 'disponible';
        $ingredients = trim($_POST["ingredients"] ?? '');
        $description = trim($_POST["description"] ?? '');

        if (empty($nom)) {
            throw new Exception("Le nom du plat est obligatoire.");
        }
        if ($id_cat <= 0) {
            throw new Exception("Veuillez sélectionner une catégorie valide.");
        }
        if ($prix <= 0) {
            throw new Exception("Le prix du plat doit être supérieur à 0 DH.");
        }

        $imagePath = $plat['image']; // Conserver l'image actuelle par défaut

        // Gestion du remplacement de l'image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Format d'image non supporté. Utilisez JPG, PNG, GIF ou WEBP.");
            }

            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                throw new Exception("La nouvelle image ne doit pas dépasser 5 Mo.");
            }

            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('plat_') . '.' . $extension;
            $newImagePath = $uploadDir . $filename;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $newImagePath)) {
                throw new Exception("Erreur lors de l'enregistrement de la nouvelle image.");
            }

            // Supprimer l'ancienne image si elle existe et n'est pas un fichier par défaut
            if ($imagePath && file_exists($imagePath)) {
                unlink($imagePath);
            }

            $imagePath = $newImagePath;
        }

        // Mise à jour du plat dans la base de données
        $stmtUpdate = $pdo->prepare("
            UPDATE plat
            SET nom = ?, description = ?, ingredients = ?, prix = ?, temps_prep = ?, image = ?, statut = ?, id_categorie = ?
            WHERE id_plat = ?
        ");

        $stmtUpdate->execute([
            $nom,
            $description,
            $ingredients,
            $prix,
            $temps_prep,
            $imagePath,
            $statut,
            $id_cat,
            $id
        ]);

        header("Location: index.php?success=modified#carte");
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier : <?= htmlspecialchars($plat['nom']) ?> — Restaurant Le Gourmet</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Header -->
<header class="header">
    <div class="header-inner">
        <a href="index.php" class="logo">
            <span class="logo-icon">🍽️</span>
            <div>
                <span class="logo-title">Le Gourmet</span>
                <span class="logo-sub">Cuisine Raffinée & Saveurs du Monde</span>
            </div>
        </a>
        <nav class="nav">
            <a href="index.php#carte" class="nav-link">Notre Carte</a>
            <a href="plat.php?id=<?= $plat['id_plat'] ?>" class="btn btn-secondary">← Voir la fiche du plat</a>
        </nav>
    </div>
</header>

<div class="form-container">
    <div class="form-card">
        <div class="form-header-badge">✏️ Modification de la Carte</div>
        <h1 class="form-title">Modifier le Plat</h1>
        <p class="form-subtitle">Mettez à jour les informations du plat <strong>"<?= htmlspecialchars($plat['nom']) ?>"</strong>.</p>

        <?php if ($error): ?>
        <div class="toast toast-error" style="position:static; margin-bottom:24px;">
            <span>✕</span>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nom">Nom du plat *</label>
                <input type="text" id="nom" name="nom" required 
                       value="<?= htmlspecialchars($plat['nom']) ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="id_categorie">Catégorie du menu *</label>
                    <select id="id_categorie" name="id_categorie" required>
                        <option value="">-- Sélectionner une catégorie --</option>
                        <?php foreach ($categories as $cat): ?>
                            <?php 
                                $icon = !empty($cat['icone']) ? $cat['icone'] : ($defaultIcons[$cat['nom_categorie']] ?? '🍽️');
                                $selected = ($plat['id_categorie'] == $cat['id_categorie']) ? 'selected' : '';
                            ?>
                            <option value="<?= $cat['id_categorie'] ?>" <?= $selected ?>>
                                <?= $icon . ' ' . htmlspecialchars($cat['nom_categorie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="statut">Statut du plat *</label>
                    <select id="statut" name="statut" required>
                        <option value="disponible" <?= ($plat['statut'] === 'disponible') ? 'selected' : '' ?>>✓ Disponible</option>
                        <option value="plat_du_jour" <?= ($plat['statut'] === 'plat_du_jour') ? 'selected' : '' ?>>⭐ Plat du jour / Recommandation du Chef</option>
                        <option value="epuise" <?= ($plat['statut'] === 'epuise') ? 'selected' : '' ?>>✕ Temporairement épuisé</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="prix">Prix (DH) *</label>
                    <input type="number" step="0.50" min="1" id="prix" name="prix" required
                           value="<?= htmlspecialchars($plat['prix']) ?>">
                </div>

                <div class="form-group">
                    <label for="temps_prep">Temps de préparation moyen (minutes)</label>
                    <input type="number" min="1" max="180" id="temps_prep" name="temps_prep"
                           value="<?= htmlspecialchars($plat['temps_prep'] ?? 15) ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="ingredients">Ingrédients & Allergènes</label>
                <input type="text" id="ingredients" name="ingredients"
                       value="<?= htmlspecialchars($plat['ingredients'] ?? '') ?>">
                <small style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:4px;">Séparez les ingrédients par des virgules pour un affichage en badges.</small>
            </div>

            <div class="form-group">
                <label for="description">Description gourmande du plat</label>
                <textarea id="description" name="description" rows="4"><?= htmlspecialchars($plat['description'] ?? '') ?></textarea>
            </div>

            <!-- Image actuelle et option de remplacement -->
            <div class="form-group">
                <label>Photo de présentation</label>
                
                <?php if (!empty($plat['image']) && file_exists($plat['image'])): ?>
                <div class="current-image-box" style="margin-bottom: 12px; display: flex; align-items: center; gap: 16px; background: var(--surface); padding: 12px; border-radius: 8px; border: 1px solid var(--border);">
                    <img src="<?= htmlspecialchars($plat['image']) ?>" alt="Photo actuelle" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                    <div>
                        <strong>Photo actuelle</strong>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Sélectionnez une nouvelle image ci-dessous si vous souhaitez la remplacer.</p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="image-upload">
                    <div class="image-upload-area" onclick="document.getElementById('image').click()">
                        <div class="image-upload-icon">📸</div>
                        <div class="image-upload-text"><strong>Cliquez pour changer la photo</strong> ou glissez-la ici</div>
                        <div class="image-upload-text" style="font-size:0.75rem; margin-top:4px; color:var(--text-muted);">
                            Laissez vide pour conserver la photo actuelle — JPG, PNG, WEBP (Max 5 Mo)
                        </div>
                    </div>
                    <input type="file" id="image" name="image" accept="image/*" style="display:none;" onchange="previewImage(this)">
                    <div id="imagePreviewContainer" style="display:none; margin-top:16px; text-align:center;">
                        <span style="display:block; font-size:0.85rem; color:var(--text-muted); margin-bottom:6px;">Nouvelle photo sélectionnée :</span>
                        <img id="imagePreview" src="#" alt="Nouvel aperçu" style="max-height:200px; border-radius:12px; box-shadow:var(--shadow-md); object-fit:cover;">
                        <button type="button" class="btn btn-neutral btn-sm" style="display:block; margin:8px auto 0;" onclick="removeImage()">✕ Annuler le changement</button>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="plat.php?id=<?= $plat['id_plat'] ?>" class="btn btn-neutral">Annuler</a>
                <button type="submit" class="btn btn-primary btn-lg">💾 Mettre à Jour le Plat</button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    var preview = document.getElementById('imagePreview');
    var container = document.getElementById('imagePreviewContainer');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    var input = document.getElementById('image');
    input.value = '';
    document.getElementById('imagePreviewContainer').style.display = 'none';
    document.getElementById('imagePreview').src = '#';
}
</script>

</body>
</html>