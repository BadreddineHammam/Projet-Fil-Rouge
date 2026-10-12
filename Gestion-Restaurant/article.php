<?php
/**
 * Alias de compatibilité pour plat.php
 * Redirige les anciens liens article.php vers plat.php
 */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    header("Location: plat.php?id=" . $id);
} else {
    header("Location: index.php");
}
exit;
