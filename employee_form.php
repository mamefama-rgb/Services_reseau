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
$employee = [
    'id' => '',
    'firstName' => '',
    'lastName' => '',
    'email' => '',
    'phone' => '',
    'position' => '',
    'department' => '',
    'hireDate' => ''
];

// Éditer un employé existant
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $employee_data = $stmt->fetch();
    
    if ($employee_data) {
        $employee = $employee_data;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee = [
        'id' => $_POST['id'] ?? '',
        'firstName' => $_POST['firstName'] ?? '',
        'lastName' => $_POST['lastName'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'position' => $_POST['position'] ?? '',
        'department' => $_POST['department'] ?? '',
        'hireDate' => $_POST['hireDate'] ?? ''
    ];
    
    // Validation
    if (empty($employee['firstName']) || empty($employee['lastName']) || empty($employee['email'])) {
        $error = "Les champs Nom, Prénom et Email sont obligatoires.";
    } else {
        if (empty($employee['id'])) {
            // Ajouter un nouvel employé
            $stmt = $pdo->prepare("INSERT INTO employees (firstName, lastName, email, phone, position, department, hireDate) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$employee['firstName'], $employee['lastName'], $employee['email'], $employee['phone'], $employee['position'], $employee['department'], $employee['hireDate']]);
            $success = "Employé ajouté avec succès!";
            // Vider le formulaire
            $employee = [
                'id' => '',
                'firstName' => '',
                'lastName' => '',
                'email' => '',
                'phone' => '',
                'position' => '',
                'department' => '',
                'hireDate' => ''
            ];
        } else {
            // Mettre à jour un employé existant
            $stmt = $pdo->prepare("UPDATE employees SET firstName = ?, lastName = ?, email = ?, phone = ?, position = ?, department = ?, hireDate = ? WHERE id = ?");
            $stmt->execute([$employee['firstName'], $employee['lastName'], $employee['email'], $employee['phone'], $employee['position'], $employee['department'], $employee['hireDate'], $employee['id']]);
            $success = "Employé mis à jour avec succès!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smarttech - <?= empty($employee['id']) ? 'Ajouter' : 'Modifier' ?> un employé</title>
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
                        <a class="nav-link active" href="employees.php">Employés</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clients.php">Clients</a>
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
        <h2><?= empty($employee['id']) ? 'Ajouter' : 'Modifier' ?> un employé</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <input type="hidden" name="id" value="<?= htmlspecialchars($employee['id']) ?>">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="lastName" class="form-label">Nom*</label>
                    <input type="text" class="form-control" id="lastName" name="lastName" value="<?= htmlspecialchars($employee['lastName']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="firstName" class="form-label">Prénom*</label>
                    <input type="text" class="form-control" id="firstName" name="firstName" value="<?= htmlspecialchars($employee['firstName']) ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email*</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($employee['email']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label">Téléphone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($employee['phone']) ?>">
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="position" class="form-label">Poste</label>
                    <input type="text" class="form-control" id="position" name="position" value="<?= htmlspecialchars($employee['position']) ?>">
                </div>
                <div class="col-md-6">
                    <label for="department" class="form-label">Département</label>
                    <input type="text" class="form-control" id="department" name="department" value="<?= htmlspecialchars($employee['department']) ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="hireDate" class="form-label">Date d'embauche</label>
                <input type="date" class="form-control" id="hireDate" name="hireDate" value="<?= htmlspecialchars($employee['hireDate']) ?>">
            </div>
            
            <div class="mb-3">
<button type="submit" class="btn btn-primary"><?= empty($employee['id']) ? 'Ajouter' : 'Modifier' ?></button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
