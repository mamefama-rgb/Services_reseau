<?php
session_start();
require_once 'db_connect.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';
$client = [
    'id' => '',
    'companyName' => '',
    'contactName' => '',
    'email' => '',
    'phone' => '',
    'address' => ''
];

// Éditer un client existant
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $client_data = $stmt->fetch();
    
    if ($client_data) {
        $client = $client_data;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client = [
        'id' => $_POST['id'] ?? '',
        'companyName' => $_POST['companyName'] ?? '',
        'contactName' => $_POST['contactName'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? ''
    ];
    
    // Validation
    if (empty($client['companyName']) || empty($client['contactName']) || empty($client['email'])) {
        $error = "Les champs Entreprise, Contact et Email sont obligatoires.";
    } else {
        if (empty($client['id'])) {
            // Ajouter un nouveau client
            $stmt = $pdo->prepare("INSERT INTO clients (companyName, contactName, email, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$client['companyName'], $client['contactName'], $client['email'], $client['phone'], $client['address']]);
            $success = "Client ajouté avec succès!";
            // Vider le formulaire
            $client = [
                'id' => '',
                'companyName' => '',
                'contactName' => '',
                'email' => '',
                'phone' => '',
                'address' => ''
            ];
        } else {
            // Mettre à jour un client existant
            $stmt = $pdo->prepare("UPDATE clients SET companyName = ?, contactName = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->execute([$client['companyName'], $client['contactName'], $client['email'], $client['phone'], $client['address'], $client['id']]);
            $success = "Client mis à jour avec succès!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smarttech - <?= empty($client['id']) ? 'Ajouter' : 'Modifier' ?> un client</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Smarttech</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="employees.php">Employés</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="clients.php">Clients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="documents.php">Documents</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2><?= empty($client['id']) ? 'Ajouter' : 'Modifier' ?> un client</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <input type="hidden" name="id" value="<?= htmlspecialchars($client['id']) ?>">
            
            <div class="mb-3">
                <label for="companyName" class="form-label">Entreprise*</label>
                <input type="text" class="form-control" id="companyName" name="companyName" value="<?= htmlspecialchars($client['companyName']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="contactName" class="form-label">Contact*</label>
                <input type="text" class="form-control" id="contactName" name="contactName" value="<?= htmlspecialchars($client['contactName']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email*</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($client['email']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label">Téléphone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($client['phone']) ?>">
            </div>
            
            <div class="mb-3">
                <label for="address" class="form-label">Adresse</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($client['address']) ?></textarea>
            </div>
            
            <div class="mb-3">
                <button type="submit" class="btn btn-primary"><?= empty($client['id']) ? 'Ajouter' : 'Mettre à jour' ?></button>
                <a href="clients.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
