<?php
/**
 * Gestion des Réservations de Tables
 * Restaurant Le Gourmet (Site Web Restaurant Unique)
 */
require_once "connexion.php";

$error = null;
$success = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $nom_client        = trim($_POST["nom_client"] ?? '');
        $telephone         = trim($_POST["telephone"] ?? '');
        $email             = trim($_POST["email"] ?? '');
        $nb_personnes      = (int)($_POST["nb_personnes"] ?? 2);
        $date_reservation  = $_POST["date_reservation"] ?? '';
        $heure_reservation = $_POST["heure_reservation"] ?? '';
        $remarques         = trim($_POST["remarques"] ?? '');

        if (empty($nom_client) || empty($telephone) || empty($email)) {
            throw new Exception("Veuillez renseigner votre nom, téléphone et adresse email.");
        }

        if (empty($date_reservation) || empty($heure_reservation)) {
            throw new Exception("Veuillez sélectionner la date et l'heure souhaitées.");
        }

        if ($nb_personnes < 1 || $nb_personnes > 50) {
            throw new Exception("Le nombre de convives doit être compris entre 1 et 50.");
        }

        $stmt = $pdo->prepare("
            INSERT INTO reservation 
            (nom_client, email, telephone, nb_personnes, date_reservation, heure_reservation, remarques, statut)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'en_attente')
        ");

        $stmt->execute([
            $nom_client,
            $email,
            $telephone,
            $nb_personnes,
            $date_reservation,
            $heure_reservation,
            $remarques
        ]);

        header("Location: index.php?success=reserved#reservation");
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Si accédé directement en GET, afficher la liste des réservations du restaurant
$reservations = $pdo->query("SELECT * FROM reservation ORDER BY date_reservation DESC, heure_reservation DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réservations — Restaurant Le Gourmet</title>
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
            <a href="index.php#reservation" class="nav-link">Réserver</a>
            <a href="index.php" class="btn btn-secondary">← Retour au site</a>
        </nav>
    </div>
</header>

<div class="detail-container" style="max-width: 1000px; margin: 40px auto; padding: 0 20px;">
    <div class="detail-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 style="font-size:1.8rem; color:var(--primary); margin:0;">📋 Carnet des Réservations</h1>
                <p style="color:var(--text-muted); margin:4px 0 0;">Tableau de bord des réservations de tables du restaurant</p>
            </div>
            <a href="index.php#reservation" class="btn btn-primary">+ Nouvelle Réservation</a>
        </div>

        <?php if ($error): ?>
        <div class="toast toast-error" style="position:static; margin-bottom:20px;">
            <span>✕</span>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <?php if (empty($reservations)): ?>
        <div class="empty-state" style="padding:40px 20px;">
            <div class="empty-state-icon">📅</div>
            <h3>Aucune réservation enregistrée</h3>
            <p>Les futures demandes de réservation apparaîtront ici.</p>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.95rem;">
                <thead>
                    <tr style="background:var(--background); border-bottom:2px solid var(--border);">
                        <th style="padding:12px 14px;">Client</th>
                        <th style="padding:12px 14px;">Contact</th>
                        <th style="padding:12px 14px;">Convives</th>
                        <th style="padding:12px 14px;">Date & Heure</th>
                        <th style="padding:12px 14px;">Remarques</th>
                        <th style="padding:12px 14px;">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $res): ?>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:14px; font-weight:600; color:var(--primary);">
                            <?= htmlspecialchars($res['nom_client']) ?>
                        </td>
                        <td style="padding:14px; color:var(--text-muted); font-size:0.85rem;">
                            <div>📞 <?= htmlspecialchars($res['telephone']) ?></div>
                            <div>✉️ <?= htmlspecialchars($res['email']) ?></div>
                        </td>
                        <td style="padding:14px; font-weight:600;">
                            👥 <?= (int)$res['nb_personnes'] ?> pers.
                        </td>
                        <td style="padding:14px;">
                            <strong><?= date('d/m/Y', strtotime($res['date_reservation'])) ?></strong>
                            <div style="color:var(--text-muted); font-size:0.85rem;">à <?= substr($res['heure_reservation'], 0, 5) ?></div>
                        </td>
                        <td style="padding:14px; font-size:0.85rem; color:var(--text-muted); max-width:220px;">
                            <?= !empty($res['remarques']) ? htmlspecialchars($res['remarques']) : '<em>Aucune</em>' ?>
                        </td>
                        <td style="padding:14px;">
                            <?php if ($res['statut'] === 'confirmee'): ?>
                                <span class="badge badge-available">Confirmée</span>
                            <?php elseif ($res['statut'] === 'en_attente'): ?>
                                <span class="badge badge-special">En attente</span>
                            <?php else: ?>
                                <span class="badge badge-out">Annulée</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<footer class="footer" style="margin-top:60px;">
    <div class="footer-bottom">
        <p>© 2026 Restaurant Le Gourmet — Tous droits réservés | <a href="index.php" style="color:var(--accent);">Retour au site</a></p>
    </div>
</footer>

</body>
</html>
