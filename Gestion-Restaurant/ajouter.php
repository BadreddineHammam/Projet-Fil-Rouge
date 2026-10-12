<?php
/**
 * Page d'Ajout d'un Plat à la Carte
 * Restaurant Le Gourmet (Site Web Restaurant Unique)
 */
require_once "connexion.php";

$error = null;
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
        $id_cat      = (int)($_POST["id_categorie"] ?? 0);
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

        $imagePath = null;

        // Gestion de l'upload d'image
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
                throw new Exception("La photo du plat ne doit pas dépasser 5 Mo.");
            }

            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('plat_') . '.' . $extension;
            $imagePath = $uploadDir . $filename;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                throw new Exception("Erreur lors de l'enregistrement de l'image.");
            }
        }

        // Insertion dans la table plat
        $sql = "INSERT INTO plat 
                (nom, description, ingredients, prix, temps_prep, image, statut, id_utilisateur, id_categorie)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nom,
            $description,
            $ingredients,
            $prix,
            $temps_prep,
            $imagePath,
            $statut,
            $id_cat
        ]);

        header("Location: index.php?success=added#carte");
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
    <title>Ajouter un Plat — Restaurant Le Gourmet</title>
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
            <a href="index.php#reservation" class="nav-link">Réservation</a>
            <a href="index.php" class="btn btn-secondary">← Retour au site</a>
        </nav>
    </div>
</header>

<div class="form-container">
    <div class="form-card">
        <div class="form-header-badge">👨‍🍳 Carte du Restaurant</div>
        <h1 class="form-title">Ajouter un Plat à la Carte</h1>
        <p class="form-subtitle">Renseignez les détails ci-dessous pour publier un nouveau plat sur la carte du restaurant.</p>

        <?php if ($error): ?>
        <div class="toast toast-error" style="position:static; margin-bottom:24px;">
            <span>✕</span>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nom">Nom du plat *</label>
                <input type="text" id="nom" name="nom" placeholder="Ex: Tajine de Veau aux Pruneaux & Amandes" required 
                       value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="id_categorie">Catégorie du menu *</label>
                    <select id="id_categorie" name="id_categorie" required>
                        <option value="">-- Sélectionner une catégorie --</option>
                        <?php foreach ($categories as $cat): ?>
                            <?php 
                                $icon = !empty($cat['icone']) ? $cat['icone'] : ($defaultIcons[$cat['nom_categorie']] ?? '🍽️');
                                $selected = (isset($_POST['id_categorie']) && $_POST['id_categorie'] == $cat['id_categorie']) ? 'selected' : '';
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
                        <option value="disponible" <?= (isset($_POST['statut']) && $_POST['statut'] === 'disponible') ? 'selected' : '' ?>>✓ Disponible</option>
                        <option value="plat_du_jour" <?= (isset($_POST['statut']) && $_POST['statut'] === 'plat_du_jour') ? 'selected' : '' ?>>⭐ Plat du jour / Recommandation du Chef</option>
                        <option value="epuise" <?= (isset($_POST['statut']) && $_POST['statut'] === 'epuise') ? 'selected' : '' ?>>✕ Temporairement épuisé</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="prix">Prix (DH) *</label>
                    <input type="number" step="0.50" min="1" id="prix" name="prix" placeholder="Ex: 85.00" required
                           value="<?= isset($_POST['prix']) ? htmlspecialchars($_POST['prix']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="temps_prep">Temps de préparation moyen (minutes)</label>
                    <input type="number" min="1" max="180" id="temps_prep" name="temps_prep" placeholder="Ex: 20"
                           value="<?= isset($_POST['temps_prep']) ? htmlspecialchars($_POST['temps_prep']) : '20' ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="ingredients">Ingrédients & Allergènes</label>
                <input type="text" id="ingredients" name="ingredients" placeholder="Ex: Jarret de veau, pruneaux d'Agen, amandes effilées, sésame grillé, safran pur..."
                       value="<?= isset($_POST['ingredients']) ? htmlspecialchars($_POST['ingredients']) : '' ?>">
                <small style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:4px;">Séparez les ingrédients par des virgules pour un affichage en badges.</small>
            </div>

            <div class="form-group">
                <label for="description">Description gourmande du plat</label>
                <textarea id="description" name="description" rows="4" placeholder="Décrivez les saveurs, la méthode de cuisson et les accompagnements qui subliment ce plat..."><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
            </div>

            <!-- Upload Photo avec Prévisualisation en direct -->
            <div class="form-group">
                <label>Photo de présentation du plat</label>
                <div class="image-upload">
                    <div class="image-upload-area" onclick="document.getElementById('image').click()">
                        <div class="image-upload-icon">📸</div>
                        <div class="image-upload-text"><strong>Cliquez pour choisir une photo</strong> ou glissez-la ici</div>
                        <div class="image-upload-text" style="font-size:0.75rem; margin-top:4px; color:var(--text-muted);">
                            Formats acceptés : JPG, PNG, WEBP — Taille max : 5 Mo
                        </div>
                    </div>
                    <input type="file" id="image" name="image" accept="image/*" style="display:none;" onchange="previewImage(this)">
                    <div id="imagePreviewContainer" style="display:none; margin-top:16px; text-align:center;">
                        <img id="imagePreview" src="#" alt="Aperçu du plat" style="max-height:220px; border-radius:12px; box-shadow:var(--shadow-md); object-fit:cover;">
                        <button type="button" class="btn btn-neutral btn-sm" style="display:block; margin:8px auto 0;" onclick="removeImage()">✕ Retirer l'image</button>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="index.php#carte" class="btn btn-neutral">Annuler</a>
                <button type="submit" class="btn btn-primary btn-lg">🍽️ Enregistrer & Publier le Plat</button>
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