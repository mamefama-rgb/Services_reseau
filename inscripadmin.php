<?php
// Configuration de la base de données
$host = 'localhost'; // ou l'adresse de votre serveur
$db   = 'smarttech';
$user = 'root';
$pass = 'passer';
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Données à insérer
$username = 'mamefama';
$password = 'passer';

// Hachage du mot de passe
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Préparation de la requête d'insertion
$stmt = $pdo->prepare('INSERT INTO admins (username, password) VALUES (:username, :password)');
$stmt->execute(['username' => $username, 'password' => $hashed_password]);

echo "Admin inséré avec succès !";
?>
