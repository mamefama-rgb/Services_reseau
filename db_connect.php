<?php
$host = 'localhost'; // Adresse IP de votre serveur MySQL
$db = 'smarttech';       // Nom de la base de données
$user = 'root';          // Nom d'utilisateur MySQL
$pass = 'passer';              // Mot de passe (assurez-vous qu'il est correct)
$charset = 'utf8mb4';    // Jeu de caractères

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Gérer les erreurs avec des exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Récupérer les résultats sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES => false, // Utiliser des requêtes préparées réelles
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
   // echo "Connexion réussie"; // Message de succès pour le débogage
} catch (PDOException $e) {
    // Afficher un message d'erreur
    echo "Erreur de connexion : " . $e->getMessage();
    // Optionnel : log l'erreur dans un fichier
    error_log($e->getMessage(), 3, '/var/log/php_errors.log');
}
?>
