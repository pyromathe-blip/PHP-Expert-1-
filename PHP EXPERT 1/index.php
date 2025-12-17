<?php
require_once 'pdoEcole.php';

$db_handler = new pdoEcole();
$pdo = $db_handler->getPdo();

// Initialiser les données fixes
$db_handler->initialiserDonnees();

// Réinitialiser les élèves et leurs pratiques sportives
$db_handler->reinitialiserDonneesVariables();

// Début de la génération de données aléatoires

$ecoles_ids = $pdo->query("SELECT id FROM Ecoles")->fetchAll(PDO::FETCH_COLUMN);
$sql_insert_eleve = "INSERT INTO Eleves (nom_complet, ecole_id) VALUES (?, ?)";
$stmt_eleve = $pdo->prepare($sql_insert_eleve);

foreach ($ecoles_ids as $ecole_id) {
    // Le nombre d'élèves est variable entre 50 et 100
    $nombre_eleves = rand(50, 200);

    for ($i = 0; $i < $nombre_eleves; $i++) {
        $nom_eleve = "Eleve_" . ($i + 1) . "_Ecole_" . $ecole_id;

        $stmt_eleve->execute([$nom_eleve, $ecole_id]);
    }
};

$tous_eleves_id = $pdo->query("SELECT id FROM Eleves")->fetchAll(PDO::FETCH_COLUMN);
$tous_sports_id = $pdo->query("SELECT id FROM Sports")->fetchAll(PDO::FETCH_COLUMN);
$sql_insert_pratique = "INSERT IGNORE INTO Pratique_Sport (eleve_id, sport_id) VALUES (?, ?)";
$stmt = $pdo->prepare($sql_insert_pratique);

foreach ($tous_eleves_id as $eleve_id) {

    //Déterminer aléatoirement combien de sports l'élève pratiquera (0, 1, 2, ou 3)
    $nb_sports_a_pratiquer = rand(0, 3);

    if ($nb_sports_a_pratiquer > 0) {
        //Tirer au sort des sports sans doublons
        $sports_selectionnes_keys = array_rand($tous_sports_id, $nb_sports_a_pratiquer);

        // Si 1 seul sport est tiré au sort, array_rand retourne une valeur, pas un tableau
        if (!is_array($sports_selectionnes_keys)) {
            $sports_selectionnes_keys = [$sports_selectionnes_keys];
        }

        foreach ($sports_selectionnes_keys as $key) {
            $sport_id = $tous_sports_id[$key];
            $stmt->execute([$eleve_id, $sport_id]);
        }
    }
    // Si $nb_sports_a_pratiquer est 0, on ne fait rien, respectant ainsi la contrainte.
}
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Génération de contenus et statistiques</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            text-align: center;
        }

        .stat-list {
            padding-left: 0;
            margin-left: 0;
        }

        .stat-list li {
            margin-left: 50px;
        }

        h2 {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>Génération de contenus et statistiques</h1>
    <?php
    foreach ($ecoles_ids as $ecole_id) {
        $count_eleves = $pdo->query("SELECT COUNT(*) FROM Eleves WHERE ecole_id = $ecole_id")->fetchColumn();
        $count_eleves_sports = $pdo->query("
                SELECT COUNT(DISTINCT ps.eleve_id)
                FROM Eleves e
                JOIN Pratique_Sport ps ON e.id = ps.eleve_id
                WHERE e.ecole_id = $ecole_id")->fetchColumn();
        $count_activites = $pdo->query("
                SELECT COUNT(*)
                FROM Pratique_Sport ps
                JOIN Eleves e ON ps.eleve_id = e.id
                WHERE e.ecole_id = $ecole_id")->fetchColumn();
        $count_sports = $pdo->query("
            SELECT S.nom AS sport_nom, COUNT(PS.eleve_id) As nb_pratiquants 
            FROM Sports S
            LEFT JOIN Pratique_Sport PS ON PS.sport_id = S.id
            WHERE PS.eleve_id IN (
                SELECT E.id 
                FROM Eleves E 
                WHERE E.ecole_id = $ecole_id
            )
            GROUP BY S.nom
            ORDER BY 
            nb_pratiquants ASC;")->fetchAll(PDO::FETCH_ASSOC);
        echo "<h2>École $ecole_id</h2> <p>$count_eleves_sports élèves pratiquent au moins un sport sur $count_eleves élèves au total.</p>";
        echo "<p>Nombre total d'activités sportives pratiquées : $count_activites.</p>";
        echo "<p>Répartition des pratiquants par sport :</p><ul class='stat-list'>";
        foreach ($count_sports as $sport) {
            echo "<li>" . htmlspecialchars($sport['sport_nom']) . " : " . $sport['nb_pratiquants'] . " pratiquants</li>";
        }
    }
    ?>
</body>

</html>