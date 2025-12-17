CREATE TABLE IF NOT EXISTS Ecoles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS Sports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS Eleves (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom_complet VARCHAR(200) NOT NULL,
    ecole_id INT NOT NULL,
    FOREIGN KEY (ecole_id) REFERENCES Ecoles(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Pratique_Sport (
    eleve_id INT NOT NULL,
    sport_id INT NOT NULL,

    FOREIGN KEY (eleve_id) REFERENCES Eleves(id) ON DELETE CASCADE,
    FOREIGN KEY (sport_id) REFERENCES Sports(id) ON DELETE CASCADE,

    -- Clé Primaire Composite : un élève ne peut pas pratiquer le même sport deux fois
    PRIMARY KEY (eleve_id, sport_id)
);

SELECT 
    E.nom AS ecole_nom, 
    COUNT(L.id) AS nb_total_eleves,
    --Compte tous les ID d'élèves
    COUNT(DISTINCT PS.eleve_id) AS nb_pratiquants, 
    --Compte les ID d'élèves uniques qui ont une correspondance dans Pratique_Sport
    COUNT(DISTINCT PS.sport_id) AS nb_sports_pratiques 
    --Compte les ID de sports uniques qui ont une correspondance
FROM 
    Ecoles E
JOIN 
    Eleves L ON L.ecole_id = E.id
LEFT JOIN 
    Pratique_Sport PS ON PS.eleve_id = L.id
GROUP BY 
    E.nom;

SELECT 
    S.nom AS sport_nom, 
    COUNT(PS.eleve_id) AS nb_pratiquants 
FROM 
    Sports S
LEFT JOIN 
    Pratique_Sport PS ON PS.sport_id = S.id
GROUP BY 
    S.nom
ORDER BY 
nb_pratiquants ASC;