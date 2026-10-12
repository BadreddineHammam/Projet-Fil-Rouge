<?php
/**
 * Page de Détail d'un Plat de la Carte
 * Restaurant Le Gourmet (Site Web Restaurant Unique)
 */
require_once "connexion.php";

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Récupération des informations complètes du plat
$stmt = $pdo->prepare("
    SELECT p.*, 
           c.nom_categorie, 
           c.icone AS categorie_icone,
           u.nom AS nom_createur
    FROM plat p
    LEFT JOIN categorie_plat c ON p.id_categorie = c.id_categorie
    LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
    WHERE p.id_plat = ?
");
$stmt->execute([$id]);
$plat = $stmt->fetch();

if (!$plat) {
    header("Location: index.php");
    exit;
}

// Plats suggérés de la même catégorie
$stmtSimilar = $pdo->prepare("
    SELECT id_plat, nom, prix, image, temps_prep
    FROM plat 
    WHERE id_categorie = ? AND id_plat != ?
    LIMIT 3
");
$stmtSimilar->execute([$plat['id_categorie'], $id]);
$similarDishes = $stmtSimilar->fetchAll();

$defaultIcons = [
    'Entrées'                   => '🥗',
    'Plats Principaux'          => '🥩',
    'Poissons & Fruits de Mer'  => '🐟',
    'Pâtes & Spécialités'       => '🍝',
    'Desserts'                  => '🍰',
    'Boissons & Cocktails'      => '🍹',
];
$catIcon = !empty($plat['categorie_icone']) ? $plat['categorie_icone'] : ($defaultIcons[$plat['nom_categorie']] ?? '🍽️');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($plat['nom']) ?> — Restaurant Le Gourmet</title>
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
            <a href="index.php#apropos" class="nav-link">Le Restaurant</a>
            <a href="index.php#reservation" class="nav-link">Réserver</a>
            <a href="ajouter.php" class="btn btn-primary">+ Ajouter un plat</a>
        </nav>
    </div>
</header>

<main class="detail-container">
    <!-- Fil d'Ariane / Breadcrumb -->
    <nav class="breadcrumb">
        <a href="index.php">Accueil</a>
        <span class="breadcrumb-separator">/</span>
        <a href="index.php?categorie=<?= $plat['id_categorie'] ?>#carte"><?= htmlspecialchars($plat['nom_categorie']) ?></a>
        <span class="breadcrumb-separator">/</span>
        <span><?= htmlspecialchars($plat['nom']) ?></span>
    </nav>

    <div class="detail-grid">
        <!-- Colonne Gauche : Grande Photo & Ingrédients -->
        <div class="detail-media">
            <div class="detail-image-box">
                <?php if (!empty($plat['image']) && file_exists($plat['image'])): ?>
                    <img src="<?= htmlspecialchars($plat['image']) ?>" alt="<?= htmlspecialchars($plat['nom']) ?>" class="detail-image">
                    <a href="<?= htmlspecialchars($plat['image']) ?>" target="_blank" class="detail-image-zoom-btn">
                        🔍 Voir la photo en grand format
                    </a>
                <?php else: ?>
                    <div class="detail-image-placeholder">
                        <span style="font-size: 5rem;"><?= $catIcon ?></span>
                        <p style="margin-top:16px; color:var(--text-muted); font-size: 1.1rem; font-weight: 500;">
                            Photo de présentation en cours de dressage
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Carte des Ingrédients & Allergènes -->
            <div class="detail-card" style="margin-top: 24px;">
                <h3 class="detail-card-title">🌿 Ingrédients & Composition</h3>
                <?php if (!empty($plat['ingredients'])): ?>
                    <div class="ingredients-tags">
                        <?php 
                        $items = explode(',', $plat['ingredients']);
                        foreach ($items as $item): 
                            $trimmed = trim($item);
                            if (!empty($trimmed)):
                        ?>
                            <span class="ingredient-pill"><?= htmlspecialchars($trimmed) ?></span>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                <?php else: ?>
                    <p style="color:var(--text-muted); font-style:italic;">Les ingrédients spécifiques ne sont pas précisés pour ce plat.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Colonne Droite : Fiche descriptive, Prix, Statut, Réservation & Actions -->
        <div class="detail-info">
            <div class="detail-card">
                <!-- Badges statut & catégorie -->
                <div class="card-meta-top" style="margin-bottom: 16px;">
                    <span class="badge badge-category">
                        <?= $catIcon ?> <?= htmlspecialchars($plat['nom_categorie']) ?>
                    </span>

                    <?php if ($plat['statut'] === 'plat_du_jour'): ?>
                        <span class="badge badge-special">⭐ Spécialité / Plat du jour</span>
                    <?php elseif ($plat['statut'] === 'disponible'): ?>
                        <span class="badge badge-available">✓ Disponible en salle</span>
                    <?php else: ?>
                        <span class="badge badge-out">✕ Temporairement Épuisé</span>
                    <?php endif; ?>
                </div>

                <h1 class="detail-title"><?= htmlspecialchars($plat['nom']) ?></h1>

                <!-- Prix et temps de préparation -->
                <div class="detail-price-box">
                    <div>
                        <span class="detail-price-label">Tarif TTC</span>
                        <div class="detail-price-amount"><?= number_format($plat['prix'], 2, ',', ' ') ?> DH</div>
                    </div>
                    <?php if (!empty($plat['temps_prep'])): ?>
                    <div class="detail-prep-badge">
                        <span>⏱️</span>
                        <div>
                            <strong><?= (int)$plat['temps_prep'] ?> minutes</strong>
                            <small style="display:block; color:var(--text-muted); font-size:0.75rem;">Temps moyen de dressage</small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Description complète -->
                <div class="detail-description-box">
                    <h3 style="font-size: 1.05rem; margin-bottom: 8px; color: var(--primary);">À propos de ce plat :</h3>
                    <p class="detail-description-text">
                        <?= !empty($plat['description']) ? nl2br(htmlspecialchars($plat['description'])) : 'Préparé avec soin par notre brigade à partir d\'ingrédients rigoureusement sélectionnés.' ?>
                    </p>
                </div>

                <!-- Appel à l'action Réservation -->
                <div class="detail-cta-box">
                    <div class="cta-text">
                        <strong>Envie de déguster ce plat ?</strong>
                        <p>Réservez dès maintenant votre table au restaurant Le Gourmet.</p>
                    </div>
                    <a href="index.php#reservation" class="btn btn-primary btn-block">
                        📅 Réserver une Table pour ce Plat
                    </a>
                </div>

                <!-- Actions d'administration / gestion -->
                <div class="detail-admin-actions">
                    <a href="modifier.php?id=<?= $plat['id_plat'] ?>" class="btn btn-secondary">
                        ✏️ Modifier ce plat
                    </a>
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $plat['id_plat'] ?>)">
                        🗑️ Supprimer de la carte
                    </button>
                    <a href="index.php#carte" class="btn btn-neutral">
                        ← Retour à la carte
                    </a>
                </div>
            </div>

            <!-- Suggestions de la même catégorie -->
            <?php if (!empty($similarDishes)): ?>
            <div class="detail-card" style="margin-top: 24px;">
                <h3 class="detail-card-title">Dans la même catégorie :</h3>
                <div class="similar-dishes-list">
                    <?php foreach ($similarDishes as $sim): ?>
                    <a href="plat.php?id=<?= $sim['id_plat'] ?>" class="similar-dish-item">
                        <div class="similar-dish-info">
                            <strong><?= htmlspecialchars($sim['nom']) ?></strong>
                            <span>⏱️ <?= (int)$sim['temps_prep'] ?> min</span>
                        </div>
                        <span class="similar-dish-price"><?= number_format($sim['prix'], 2, ',', ' ') ?> DH</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Modale de Suppression -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <div class="modal-icon">⚠️</div>
        <h3 class="modal-title">Supprimer ce plat ?</h3>
        <p class="modal-text">Êtes-vous sûr de vouloir retirer "<?= htmlspecialchars($plat['nom']) ?>" de la carte ? Cette opération est irréversible.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-neutral" onclick="closeModal()">Annuler</button>
            <form method="POST" action="supprimer.php" style="display:inline;">
                <input type="hidden" name="id" value="<?= $plat['id_plat'] ?>">
                <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
            </form>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="footer-bottom">
        <p>© 2026 Restaurant Le Gourmet — Tous droits réservés | <a href="index.php" style="color:var(--accent);">Retour au site</a></p>
    </div>
</footer>

<script>
function confirmDelete() {
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>

</body>
</html>
