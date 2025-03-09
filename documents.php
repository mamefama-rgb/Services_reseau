<?php
session_start();
require_once 'db_connect.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Supprimer un document
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    // Récupérer le chemin du fichier avant suppression
    $stmt = $pdo->prepare("SELECT filePath FROM documents WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $document = $stmt->fetch();
    
    if ($document && file_exists($document['filePath'])) {
        unlink($document['filePath']); // Supprimer le fichier
    }
    
    // Supprimer l'entrée de la base de données
    $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    
    header("Location: documents.php");
    exit;
}

// Récupérer tous les documents avec le nom de l'employé qui l'a téléchargé
$stmt = $pdo->query("
    SELECT d.*, CONCAT(e.firstName, ' ', e.lastName) AS employeeName 
    FROM documents d 
    LEFT JOIN employees e ON d.uploadedBy = e.id 
    ORDER BY d.created_at DESC
");
$documents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smarttech - Gestion des Documents</title>
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
                        <a class="nav-link" href="clients.php">Clients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="documents.php">Documents</a>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des Documents</h2>
            <a href="document_form.php" class="btn btn-success">Ajouter un document</a>
        </div>
        
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Téléchargé par</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $document): ?>
                <tr>
                    <td><?= htmlspecialchars($document['id']) ?></td>
                    <td><?= htmlspecialchars($document['title']) ?></td>
                    <td><?= htmlspecialchars($document['description']) ?></td>
                    <td><?= htmlspecialchars($document['documentType']) ?></td>
                    <td><?= htmlspecialchars($document['employeeName'] ?? 'N/A') ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($document['created_at'])) ?></td>
                    <td>
                        <a href="<?= htmlspecialchars($document['filePath']) ?>" class="btn btn-sm btn-info" target="_blank">Voir</a>
                        <a href="document_form.php?id=<?= $document['id'] ?>" class="btn btn-sm btn-primary">Modifier</a>
                        <a href="documents.php?delete=<?= $document['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
