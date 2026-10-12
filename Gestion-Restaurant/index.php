<?php
/**
 * Page d'Accueil & Carte du Menu
 * Restaurant Le Gourmet (Site Web Restaurant Unique)
 */
require_once "connexion.php";

// Récupération des catégories de plats
$categories = $pdo->query("SELECT * FROM categorie_plat ORDER BY id_categorie ASC")->fetchAll();

$selectedCategory = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;

$whereClause = "";
$params = [];
if ($selectedCategory > 0) {
    $whereClause = "WHERE p.id_categorie = ?";
    $params[] = $selectedCategory;
}

// Récupération des plats avec le nom et l'icône de leur catégorie
$sql = "SELECT p.*, c.nom_categorie, c.icone AS categorie_icone
        FROM plat p 
        LEFT JOIN categorie_plat c ON p.id_categorie = c.id_categorie
        $whereClause
        ORDER BY (p.statut = 'plat_du_jour') DESC, p.id_plat DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$plats = $stmt->fetchAll();

// Mapping des icônes par défaut au cas où
$defaultIcons = [
    'Entrées'                   => '🥗',
    'Plats Principaux'          => '🥩',
    'Poissons & Fruits de Mer'  => '🐟',
    'Pâtes & Spécialités'       => '🍝',
    'Desserts'                  => '🍰',
    'Boissons & Cocktails'      => '🍹',
];

function getCatIcon($nom, $icone, $defaultIcons) {
    if (!empty($icone)) return $icone;
    return isset($defaultIcons[$nom]) ? $defaultIcons[$nom] : '🍽️';
}

// Messages de succès
$successMessages = [
    'added'    => 'Nouveau plat ajouté à la carte avec succès !',
    'modified' => 'Le plat a été mis à jour avec succès.',
    'deleted'  => 'Le plat a été retiré de la carte.',
    'reserved' => 'Votre réservation a été enregistrée avec succès ! Notre équipe vous contactera pour confirmation.'
];
$success = isset($_GET['success']) && isset($successMessages[$_GET['success']]) ? $successMessages[$_GET['success']] : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Le Gourmet — Carte & Réservations</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Toast message (Succès) -->
<?php if ($success): ?>
<div class="toast toast-success" id="toast">
    <span>✓</span>
    <span><?= htmlspecialchars($success) ?></span>
</div>
<?php endif; ?>

<!-- Header / Navigation Principale -->
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
            <a href="index.php#reservation" class="nav-link">Réserver une table</a>
            <a href="ajouter.php" class="btn btn-primary">+ Ajouter un plat</a>
        </nav>
    </div>
</header>

<!-- Hero Section : Présentation du Restaurant -->
<section class="hero" id="hero">
    <div class="hero-content">
        <span class="hero-badge">⭐ Restaurant Gastronomique & Fait Maison</span>
        <h1 class="hero-title">Une Expérience Culinaire d'Exception</h1>
        <p class="hero-description">
            Bienvenue chez <strong>Le Gourmet</strong>. Chaque jour, notre chef sublime des produits frais, locaux et de saison pour vous offrir une cuisine généreuse, authentique et raffinée.
        </p>

        <div class="hero-actions">
            <a href="#carte" class="btn btn-primary btn-lg">🍽️ Découvrir la Carte</a>
            <a href="#reservation" class="btn btn-secondary btn-lg">📅 Réserver une Table</a>
        </div>

        <div class="hero-info-bar">
            <div class="info-pill">
                <span class="info-icon">🕒</span>
                <span><strong>Horaires :</strong> Mar - Dim : 12h00 - 23h30</span>
            </div>
            <div class="info-pill">
                <span class="info-icon">📍</span>
                <span><strong>Adresse :</strong> 15 Avenue Hassan II, Tanger</span>
            </div>
            <div class="info-pill">
                <span class="info-icon">📞</span>
                <span><strong>Contact :</strong> +212 5 39 94 00 00</span>
            </div>
        </div>
    </div>
</section>

<!-- Section Carte du Restaurant -->
<section class="section-menu" id="carte">
    <div class="section-header">
        <h2 class="section-title">Notre Carte Gourmande</h2>
        <p class="section-subtitle">Sélectionnez une catégorie pour explorer nos spécialités préparées à la minute</p>
    </div>

    <!-- Filtres par catégorie -->
    <div class="category-filters">
        <a href="index.php#carte" class="category-pill <?= $selectedCategory === 0 ? 'active' : '' ?>">
            <span>🍽️</span>
            <span>Tous les plats</span>
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="index.php?categorie=<?= $cat['id_categorie'] ?>#carte" class="category-pill <?= $selectedCategory === (int)$cat['id_categorie'] ? 'active' : '' ?>">
                <span><?= getCatIcon($cat['nom_categorie'], $cat['icone'], $defaultIcons) ?></span>
                <span><?= htmlspecialchars($cat['nom_categorie']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Grille des Plats ou État Vide -->
    <?php if (empty($plats)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">🍳</div>
        <h3>Aucun plat trouvé</h3>
        <p><?= $selectedCategory > 0 ? 'Aucun plat n\'est encore enregistré dans cette catégorie.' : 'La carte est actuellement vide. Commencez par ajouter vos premiers plats !' ?></p>
        <?php if ($selectedCategory > 0): ?>
            <a href="index.php#carte" class="btn btn-secondary" style="margin-right:8px;">Voir tous les plats</a>
        <?php endif; ?>
        <a href="ajouter.php" class="btn btn-primary btn-lg">+ Ajouter un plat</a>
    </div>
    <?php else: ?>
    <div class="dishes-grid">
        <?php foreach ($plats as $plat): ?>
        <div class="card <?= $plat['statut'] === 'plat_du_jour' ? 'card-highlight' : '' ?>">
            <!-- Image cliquable menant au détail du plat -->
            <a href="plat.php?id=<?= $plat['id_plat'] ?>" class="card-image-link" title="Voir les détails de <?= htmlspecialchars($plat['nom']) ?>">
                <?php if (!empty($plat['image']) && file_exists($plat['image'])): ?>
                    <img src="<?= htmlspecialchars($plat['image']) ?>" alt="<?= htmlspecialchars($plat['nom']) ?>" class="card-image">
                <?php else: ?>
                    <div class="card-image-placeholder">
                        <span style="font-size: 2.8rem;"><?= getCatIcon($plat['nom_categorie'], $plat['categorie_icone'], $defaultIcons) ?></span>
                        <span style="font-size: 0.85rem; color: var(--text-muted); margin-top: 6px;">Cuisine artisanale</span>
                    </div>
                <?php endif; ?>

                <?php if ($plat['statut'] === 'plat_du_jour'): ?>
                    <div class="card-badge-top">⭐ Plat du jour</div>
                <?php endif; ?>
            </a>

            <div class="card-body">
                <!-- Badges : Catégorie & Disponibilité -->
                <div class="card-meta-top">
                    <?php if (!empty($plat['nom_categorie'])): ?>
                        <span class="badge badge-category">
                            <?= getCatIcon($plat['nom_categorie'], $plat['categorie_icone'], $defaultIcons) ?> <?= htmlspecialchars($plat['nom_categorie']) ?>
                        </span>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>

                    <?php if ($plat['statut'] === 'plat_du_jour'): ?>
                        <span class="badge badge-special">Recommandé</span>
                    <?php elseif ($plat['statut'] === 'disponible'): ?>
                        <span class="badge badge-available">Disponible</span>
                    <?php else: ?>
                        <span class="badge badge-out">Épuisé</span>
                    <?php endif; ?>
                </div>

                <!-- Nom du plat -->
                <h3 class="card-title">
                    <a href="plat.php?id=<?= $plat['id_plat'] ?>" class="card-title-link">
                        <?= htmlspecialchars($plat['nom']) ?>
                    </a>
                </h3>

                <!-- Ingrédients / Description courte -->
                <?php if (!empty($plat['ingredients'])): ?>
                    <p class="card-ingredients">
                        <strong>Ingrédients :</strong> <?= htmlspecialchars(mb_strimwidth($plat['ingredients'], 0, 85, '...')) ?>
                    </p>
                <?php endif; ?>

                <p class="card-description">
                    <?= htmlspecialchars(mb_strimwidth($plat['description'] ?? '', 0, 110, '...')) ?>
                </p>

                <!-- Section Prix & Temps de préparation -->
                <div class="price-section">
                    <div class="price-block">
                        <span class="price-label">Prix</span>
                        <span class="price-value"><?= number_format($plat['prix'], 2, ',', ' ') ?> DH</span>
                    </div>
                    <?php if (!empty($plat['temps_prep'])): ?>
                    <div class="prep-block">
                        <span class="prep-icon">⏱️</span>
                        <span class="prep-text"><?= (int)$plat['temps_prep'] ?> min</span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Actions du plat -->
                <div class="card-actions">
                    <a href="plat.php?id=<?= $plat['id_plat'] ?>" class="btn btn-primary btn-sm">👁️ Détails</a>
                    <a href="modifier.php?id=<?= $plat['id_plat'] ?>" class="btn btn-secondary btn-sm">✏️ Modifier</a>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete(<?= $plat['id_plat'] ?>, '<?= addslashes(htmlspecialchars($plat['nom'])) ?>')">🗑️</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- Section Réservation de Table -->
<section class="section-reservation" id="reservation">
    <div class="reservation-box">
        <div class="reservation-intro">
            <span class="hero-badge">📅 Réservez en ligne</span>
            <h2>Réserver une Table</h2>
            <p>
                Que ce soit pour un dîner romantique, un déjeuner d'affaires ou un repas en famille, réservez votre table facilement. Nous vous préparons un accueil sur-mesure.
            </p>
            <div class="reservation-perks">
                <div class="perk-item">
                    <span>✨</span>
                    <div>
                        <strong>Confirmation Rapide</strong>
                        <p>Nous confirmons votre réservation sous 15 minutes.</p>
                    </div>
                </div>
                <div class="perk-item">
                    <span>🍷</span>
                    <div>
                        <strong>Cadre Privilégié</strong>
                        <p>Tables intérieures climatisées ou terrasse ensoleillée.</p>
                    </div>
                </div>
                <div class="perk-item">
                    <span>🌿</span>
                    <div>
                        <strong>Options Personnalisées</strong>
                        <p>Menu végétarien, allergies prises en compte sur demande.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="reservation-form-card">
            <h3>Formulaire de Réservation</h3>
            <form action="reserver.php" method="POST">
                <div class="form-group">
                    <label for="nom_client">Nom complet *</label>
                    <input type="text" id="nom_client" name="nom_client" placeholder="Ex: Karim Amrani" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="telephone">Téléphone *</label>
                        <input type="tel" id="telephone" name="telephone" placeholder="06 XX XX XX XX" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" placeholder="votre@email.com" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nb_personnes">Nombre de personnes *</label>
                        <select id="nb_personnes" name="nb_personnes" required>
                            <option value="1">1 personne</option>
                            <option value="2" selected>2 personnes</option>
                            <option value="3">3 personnes</option>
                            <option value="4">4 personnes</option>
                            <option value="5">5 personnes</option>
                            <option value="6">6 personnes</option>
                            <option value="8">8 personnes</option>
                            <option value="10">10 personnes ou +</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date_reservation">Date *</label>
                        <input type="date" id="date_reservation" name="date_reservation" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="heure_reservation">Heure souhaitée *</label>
                    <select id="heure_reservation" name="heure_reservation" required>
                        <option value="12:00">12h00 - Déjeuner</option>
                        <option value="12:30">12h30 - Déjeuner</option>
                        <option value="13:00">13h00 - Déjeuner</option>
                        <option value="13:30">13h30 - Déjeuner</option>
                        <option value="19:30">19h30 - Dîner</option>
                        <option value="20:00" selected>20h00 - Dîner</option>
                        <option value="20:30">20h30 - Dîner</option>
                        <option value="21:00">21h00 - Dîner</option>
                        <option value="21:30">21h30 - Dîner</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="remarques">Remarques ou demandes particulières</label>
                    <textarea id="remarques" name="remarques" rows="2" placeholder="Table en terrasse, anniversaire, chaise haute bébé..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    📅 Confirmer ma réservation
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Section À Propos du Restaurant -->
<section class="section-about" id="apropos">
    <div class="about-grid">
        <div class="about-text">
            <span class="hero-badge">Notre Histoire</span>
            <h2>La Passion du Goût & de l'Accueil</h2>
            <p>
                Fondé au cœur de la ville, <strong>Restaurant Le Gourmet</strong> est né d'une passion inconditionnelle pour les arts de la table. Nous travaillons exclusivement avec des producteurs locaux pour sélectionner des viandes de qualité, des poissons issus de la pêche durable et des légumes de saison gorgés de soleil.
            </p>
            <p>
                Notre brigade en cuisine allie traditions culinaires séculaires et touches modernes pour vous concocter des assiettes aussi belles que savoureuses. Venez vivre un moment de convivialité et d'évasion gastronomique.
            </p>
            <div class="chef-signature">
                <strong>Chef Mohamed Bennani</strong>
                <span>Chef de Cuisine & Fondateur</span>
            </div>
        </div>
        <div class="about-highlights">
            <div class="highlight-card">
                <span class="highlight-number">100%</span>
                <span class="highlight-title">Produits Frais & Fait Maison</span>
                <p>Aucun compromis sur la fraîcheur de nos préparations quotidiennes.</p>
            </div>
            <div class="highlight-card">
                <span class="highlight-number">+15</span>
                <span class="highlight-title">Années de Savoir-Faire</span>
                <p>Une expérience reconnue par nos clients et les critiques gastronomiques.</p>
            </div>
            <div class="highlight-card">
                <span class="highlight-number">4.9/5</span>
                <span class="highlight-title">Satisfaction Client</span>
                <p>Plus de 2 500 convives accueillis avec le sourire chaque mois.</p>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="footer-inner">
        <div class="footer-col">
            <h3 class="footer-title">🍽️ Restaurant Le Gourmet</h3>
            <p class="footer-desc">Une table incontournable pour les amateurs de bonne chère et de moments partagés.</p>
        </div>
        <div class="footer-col">
            <h4>Horaires d'ouverture</h4>
            <p>Mardi — Vendredi : 12h00 - 15h00 | 19h00 - 23h30</p>
            <p>Samedi — Dimanche : 12h00 - 23h45</p>
            <p>Lundi : Fermé (Jour de repos hebdomadaire)</p>
        </div>
        <div class="footer-col">
            <h4>Adresse & Contact</h4>
            <p>📍 15 Avenue Hassan II, Tanger, Maroc</p>
            <p>📞 Réservations : +212 5 39 94 00 00</p>
            <p>✉️ Email : contact@legourmet.ma</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 Restaurant Le Gourmet — Tous droits réservés | Site Web Officiel</p>
    </div>
</footer>

<!-- Modale de Confirmation de Suppression de Plat -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <div class="modal-icon">⚠️</div>
        <h3 class="modal-title">Retirer ce plat de la carte ?</h3>
        <p class="modal-text">Cette action est irréversible. Le plat et son image associée seront définitivement supprimés de la base de données.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-neutral" onclick="closeModal()">Annuler</button>
            <form id="deleteForm" method="POST" action="supprimer.php" style="display:inline;">
                <input type="hidden" name="id" id="deletePlatId">
                <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
            </form>
        </div>
    </div>
</div>

<script>
// === Gestion de la modale de suppression ===
function confirmDelete(id, title) {
    document.getElementById('deletePlatId').value = id;
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

// === Toast Notification Auto-Dismiss ===
var toast = document.getElementById('toast');
if (toast) {
    setTimeout(function() {
        toast.classList.add('fade-out');
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 4500);
}
</script>

</body>
</html>