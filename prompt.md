# SITE WEB DE RESTAURANT — MODERN WEB UI/UX DESIGN & GESTION DU MENU (RESTAURANT UNIQUE)

Concevoir et développer le site web officiel d'un **Restaurant Unique (Single Restaurant Website)** en **PHP / MySQL / PDO**.

Le but est de proposer une interface web moderne, chaleureuse, gourmande et réactive pour un **seul et unique restaurant** (ex : *Restaurant Le Gourmet*), permettant aux clients de découvrir la carte, les spécialités du chef, et de réserver une table, tout en offrant au gérant la possibilité de gérer les plats du menu (CRUD simple et efficace).

---

# IMPORTANT DEVELOPMENT CONSTRAINT

L'interface et l'implémentation doivent être centrées sur **un seul établissement de restauration** (aucun système multi-restaurants type UberEats, Deliveroo ou Glovo).

## DO NOT:

* Créer une plateforme multi-établissements (pas de choix de restaurant : le site appartient à un seul restaurant).
* Ajouter une authentification complexe ou obligatoire pour la consultation publique et la réservation.
* Ajouter des passerelles de paiement bancaire compliquées (le paiement se fait sur place au restaurant).
* Remplacer PHP par une autre technologie backend.
* Remplacer MySQL.
* Utiliser React, Vue, Angular, Laravel, Symfony, Bootstrap, Tailwind ou d'autres frameworks externes.
* Transformer le projet en une architecture MVC surdimensionnée.

Le projet doit rester simple, propre, performant et compréhensible pour un projet de formation (Projet Fil Rouge).

---

# 1. APPLICATION & BASE DE DONNÉES

Le projet est structuré comme suit :

```text
Gestion-Restaurant/
│
├── connexion.php      (Connexion PDO MySQL)
├── index.php          (Page d'accueil, présentation du restaurant, carte & réservation)
├── plat.php           (Fiche détaillée d'un plat)
├── article.php        (Redirection de compatibilité vers plat.php)
├── ajouter.php        (Formulaire d'ajout d'un plat avec upload d'image)
├── modifier.php       (Formulaire de modification d'un plat)
├── supprimer.php      (Action sécurisée de suppression d'un plat)
├── reserver.php       (Traitement et carnet des réservations de tables)
├── style.css          (Feuille de styles moderne et gastronomique)
└── uploads/           (Dossier de stockage des photos de plats)
```

La base de données principale est :

```text
restaurant_db
```

Les entités fondamentales sont :

```text
utilisateur        (Gérant / Chef / Administrateur)
categorie_plat     (Entrées, Plats Principaux, Poissons, Pâtes, Desserts, Boissons)
plat               (Nom, description, ingrédients, prix, temps_prep, image, statut, catégorie)
reservation        (Nom client, email, téléphone, nb_personnes, date, heure, remarques, statut)
```

---

# 2. WORKFLOW UTILISATEUR & CLIENT

Le site offre une expérience fluide pour deux profils (clients et gestionnaire du restaurant) :

```text
Site Web du Restaurant
    │
    ├── 1. Découvrir le Restaurant (Ambiance, horaires, coordonnées, histoire du chef)
    │
    ├── 2. Consulter la Carte du Menu (Filtrage par catégories gourmandes)
    │
    ├── 3. Voir les Détails d'un Plat (Ingrédients, prix en DH, temps de préparation, allergènes)
    │
    ├── 4. Réserver une Table (Nom, téléphone, nombre de personnes, date et heure)
    │
    └── 5. Gestion de la Carte (Ajouter, modifier ou retirer un plat avec photo)
```

---

# 3. PAGE PRINCIPALE (index.php)

La page d'accueil doit refléter l'identité visuelle prestigieuse d'un restaurant gastronomique.

Titre de la page :
**Restaurant Le Gourmet — Carte & Réservations**

La page comporte :
1. **Un Header élégant** avec le logo du restaurant, les liens de navigation ancre et le bouton d'action rapide.
2. **Une Hero Section percutante** avec un slogan valorisant le fait-maison et les produits frais, un appel à l'action pour la carte et la réservation, ainsi que les horaires et l'adresse.
3. **Des Filtres par Catégorie** (Tous, Entrées, Plats Principaux, Poissons & Fruits de Mer, Pâtes & Spécialités, Desserts, Boissons).
4. **La Grille des Plats** sous forme de cartes appétissantes.
5. **Le Module de Réservation de Table** avec formulaire complet et avantages du restaurant.
6. **La Section "Notre Histoire"** valorisant le savoir-faire du chef et les producteurs locaux.
7. **Un Footer complet** avec horaires détaillés, mentions légales et coordonnées.

---

# 4. EN-TÊTE / HEADER

Afficher :
* Logo : **🍽️ Le Gourmet**
* Sous-titre : **Cuisine Raffinée & Saveurs du Monde**
* Navigation :
  * Notre Carte
  * Le Restaurant
  * Réserver une table
  * **+ Ajouter un plat** (Bouton d'action principal mis en évidence)

---

# 5. CARTES DES PLATS (DISH CARDS)

Chaque plat de la carte est présenté sous forme de carte moderne comprenant :

### Photo du plat
* Image au format 16:9 ou ratio gourmand avec effet de zoom subtil au survol.
* Si aucune image n'est renseignée : affichage d'un placeholder élégant avec l'icône de la catégorie.

### Badges informatifs
* Badge Catégorie (ex: 🥗 Entrées, 🥩 Plats Principaux)
* Badge Statut :
  * `⭐ Plat du jour` (Mise en avant dorée)
  * `✓ Disponible` (Vert frais)
  * `✕ Épuisé` (Gris neutre / rouge discret)

### Titre du plat
* Typographie raffinée (ex: Playfair Display), cliquable vers la fiche détaillée.

### Ingrédients clés
* Ligne synthétique mentionnant les ingrédients phares pour guider le choix du client.

### Description
* Texte court et évocateur mettant l'eau à la bouche.

### Prix & Temps de préparation
* Prix mis en valeur avec devise claire : ex : `85,00 DH`.
* Temps de préparation moyen : ex : `⏱️ 20 min`.

### Actions rapides
* Bouton `👁️ Détails` (ouvre `plat.php?id=...`)
* Bouton `✏️ Modifier` (ouvre `modifier.php?id=...`)
* Bouton `🗑️` (déclenche la modale de suppression sécurisée)

---

# 6. STATUTS DES PLATS

Trois statuts clairs et fonctionnels :
* **disponible** : Le plat est disponible au service.
* **plat_du_jour** : Le plat est la suggestion du jour du chef (mis en évidence en tête de carte).
* **epuise** : Le plat est temporairement en rupture de stock en cuisine.

---

# 7. AJOUT D'UN PLAT (ajouter.php)

Formulaire ergonomique permettant d'enregistrer un nouveau plat :
* Nom du plat (champ texte obligatoire)
* Catégorie (sélection dynamique depuis la table `categorie_plat`)
* Statut (Disponible, Plat du jour, Épuisé)
* Prix (en DH, champ numérique avec décimales)
* Temps de préparation (en minutes)
* Ingrédients (séparés par des virgules)
* Description gourmande (zone de texte)
* Photo du plat (champ fichier avec zone de glisser-déposer et **prévisualisation instantanée en direct**)

---

# 8. GESTION DES PHOTOS (UPLOADS)

* Stockage dans le dossier local `uploads/`.
* Validation des types MIME autorisés : `image/jpeg`, `image/png`, `image/webp`, `image/gif`.
* Taille maximale : 5 Mo.
* Génération d'un nom de fichier unique (`uniqid('plat_')`).
* Suppression automatique de l'ancienne image lors d'un remplacement ou de la suppression d'un plat.

---

# 9. MODIFICATION D'UN PLAT (modifier.php)

* Pré-remplissage de toutes les données actuelles du plat.
* Affichage de la photo actuelle avec miniature.
* Possibilité de conserver la photo existante ou d'en téléverser une nouvelle.
* Validation serveur et mise à jour en base de données.
* Redirection avec message toast de confirmation.

---

# 10. SUPPRESSION D'UN PLAT (supprimer.php)

* Suppression exclusivement traitée en méthode **POST** pour la sécurité.
* Déclenchée via une modale de confirmation soignée empêchant toute suppression accidentelle.
* Suppression de l'entrée dans la table `plat`.
* Suppression du fichier image correspondant dans `uploads/`.

---

# 11. RÉSERVATION DE TABLES (reserver.php)

Module de réservation intégré pour les clients du restaurant :
* Nom complet du client
* Numéro de téléphone (obligatoire pour confirmation)
* Adresse email
* Nombre de personnes (1 à 12+ convives)
* Date souhaitée (avec blocage des dates passées)
* Heure souhaitée (services du midi et du soir)
* Remarques / Demandes particulières (terrasse, anniversaire, chaise haute bébé)
* Enregistrement en base de données dans la table `reservation` avec le statut `en_attente`.
* Vue récapitulative (carnet de réservations) accessible par le gérant.

---

# 12. STYLE VISUEL & DIRECTION ARTISTIQUE

L'univers visuel doit évoquer un restaurant de standing, chaleureux et accueillant :

### Ambiance
* Gastronomique
* Moderne
* Chaleureux
* Épuré
* Appétissant

### Palette de Couleurs
* **Primaire (Espresso / Anthracite chaud)** : `#1c1917` — évoque l'élégance et le confort nocturne.
* **Accent (Caramel doré / Ambre)** : `#b45309`, `#d97706`, `#f59e0b` — évoque la cuisine dorée et le raffinement.
* **Fond (Parchment / Crème léger)** : `#fafaf9` — reposant pour les yeux et valorisant les photos.
* **Surfaces (Blanc pur)** : `#ffffff` — cartes bien détachées avec ombres douces.
* **Succès (Vert herbes fraîches)** : `#15803d` — disponibilité des plats et réservations validées.
* **Danger (Rouge piment doux)** : `#dc2626` — alertes et suppression.

### Typographie
* **Titres** : `Playfair Display` (serif élégant et intemporel).
* **Textes et contrôles** : `Plus Jakarta Sans` ou `Inter` (sans-serif moderne, lisible et net).

---

# 13. RESPONSIVE DESIGN

L'interface doit être irréprochable sur tous les écrans :
* **Desktop (>= 1200px)** : Cartes en 3 colonnes, mise en page équilibrée, navigation complète.
* **Tablette (768px - 1199px)** : Cartes en 2 colonnes, formulaire de réservation adapté.
* **Mobile (< 768px)** : Navigation adaptée, cartes en 1 colonne pleine largeur, boutons tactiles confortables.

---

# 14. OBJECTIF FINAL

Offrir une solution complète, élégante et prête à l'emploi pour le site web d'un **restaurant unique**, parfaitement alignée avec le programme et les standards de développement web PHP / MySQL.
