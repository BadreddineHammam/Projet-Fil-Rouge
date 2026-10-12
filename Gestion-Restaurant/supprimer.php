<?php
/**
 * Suppression Sécurisée d'un Plat de la Carte
 * Restaurant Le Gourmet (Site Web Restaurant Unique)
 */
require_once "connexion.php";

// N'accepte que les requêtes POST par mesure de sécurité
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php#carte");
    exit;
}

$id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;

if ($id <= 0) {
    header("Location: index.php#carte");
    exit;
}

try {
    // Récupérer le plat pour obtenir le chemin de l'image
    $stmt = $pdo->prepare("SELECT image FROM plat WHERE id_plat = ?");
    $stmt->execute([$id]);
    $plat = $stmt->fetch();

    if (!$plat) {
        header("Location: index.php#carte");
        exit;
    }

    // Suppression du plat dans la base de données
    $stmtDelete = $pdo->prepare("DELETE FROM plat WHERE id_plat = ?");
    $stmtDelete->execute([$id]);

    // Suppression du fichier image s'il existe
    if (!empty($plat['image']) && file_exists($plat['image'])) {
        unlink($plat['image']);
    }

    header("Location: index.php?success=deleted#carte");
    exit;

} catch (Exception $e) {
    header("Location: index.php#carte");
    exit;
}