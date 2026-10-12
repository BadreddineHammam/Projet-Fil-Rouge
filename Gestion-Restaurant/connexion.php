<?php
/**
 * Configuration et connexion à la base de données MySQL
 * Restaurant Le Gourmet (Site Web Restaurant Unique)
 */

$host     = "localhost";
$dbname   = "restaurant_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Si la base restaurant_db n'a pas encore été importée, tenter une connexion sans nom de base
    // pour afficher un message clair
    die("
    <div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 24px; border-radius: 12px; background: #fff1f2; border: 1px solid #fecdd3; color: #9f1239;'>
        <h2 style='margin-top: 0;'>Erreur de connexion à la base de données</h2>
        <p>Impossible de se connecter à la base de données <strong>$dbname</strong>.</p>
        <p><strong>Détail :</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <hr style='border: none; border-top: 1px solid #fecdd3; margin: 16px 0;'>
        <p style='margin-bottom: 0;'><strong>Solution :</strong> Veuillez importer le fichier <code>session-1/script.sql</code> dans votre serveur MySQL (ex: via phpMyAdmin ou la ligne de commande MySQL) pour créer la base <code>restaurant_db</code>.</p>
    </div>
    ");
}