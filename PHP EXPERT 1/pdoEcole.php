<?php

new pdoEcole();
class pdoEcole
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=ecole_sports', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("
        CREATE TABLE IF NOT EXISTS Ecoles (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nom VARCHAR(200) NOT NULL UNIQUE
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
        );");
    }

    public function reinitialiserDonneesVariables()
    {
        // Désactiver temporairement la vérification des clés étrangères pour TRUNCATE (plus rapide que DELETE FROM)
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        // Vider la table Pratique_Sport et Eleves pour éviter l'accumulation de données.
        // Le CASCADE pourrait rendre TRUNCATE Pratique_Sport facultatif si l'on utilisait DELETE FROM Eleves.
        $this->pdo->exec('TRUNCATE TABLE Pratique_Sport');
        $this->pdo->exec('TRUNCATE TABLE Eleves');

        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    public function initialiserDonnees()
    {
        $ecoles = [1, 2, 3];
        $sql = "INSERT IGNORE INTO Ecoles (nom) VALUES (?)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($ecoles as $ecole_id) {
            $stmt->execute(["Ecole_{$ecole_id}"]);
        }

        $sports = ['Boxe', 'Judo', 'Football', 'Natation', 'Cyclisme'];
        $sql = "INSERT IGNORE INTO Sports (nom) VALUES (?)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($sports as $sport) {
            $stmt->execute([$sport]);
        }
    }
    public function getPdo()
    {
        return $this->pdo;
    }
    public function __destruct()
    {
        $this->pdo = null;
    }
}
