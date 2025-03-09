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
$document = [
    'id' => '',
    'title' => '',
    'description' => '',
    'filePath' => '',
    'uploadedBy' => $_SESSION['user_id'],
    'documentType' => ''
];

// Récupérer la liste des employés pour le formulaire
$stmt = $pdo->query("SELECT id, CONCAT(firstName, ' ', lastName) AS employeeName FROM employees ORDER BY lastName, firstName");
$employees = $stmt->fetchAll();

// Éditer un document existant
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $document_data = $stmt->fetch();
    
    if ($document_data) {
        $document = $document_data;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $document = [
        'id' => $_POST['id'] ?? '',
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'uploadedBy' => $_POST['uploadedBy'] ?? null,
        'documentType' => $_POST['documentType'] ?? ''
    ];
    
    // Validation
    if (empty($document['title'])) {
        $error = "Le titre du document est obligatoire.";
    } else {
        $filePath = '';
        $fileUploaded = false;
        
        // Traitement du fichier si un fichier a été téléchargé
        if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
            $uploadDir = '/var/www/html/assets/uploads/';
            
            // Créer le répertoire s'il n'existe pas
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileInfo = pathinfo($_FILES['document']['name']);
            $fileName = time() . '_' . basename($_FILES['document']['name']);
            $uploadFile = $uploadDir . $fileName;
            $fileExtension = strtolower($fileInfo['extension']);
            
            // Vérifier l'extension du fichier
            $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];
            if (in_array($fileExtension, $allowedExtensions)) {
                if (move_uploaded_file($_FILES['document']['tmp_name'], $uploadFile)) {
                    $filePath = '../assets/uploads/' . $fileName;
                    $fileUploaded = true;
                } else {
                    $error = "Erreur lors du téléchargement du fichier.";
                }
            } else {
                $error = "Type de fichier non autorisé.";
            }
        }
        
        if (empty($error)) {
            if (empty($document['id'])) {
                // Vérifier qu'un fichier a été téléchargé pour un nouveau document
                if (!$fileUploaded) {
                    $error = "Vous devez télécharger un fichier.";
                } else {
                    // Ajouter un nouveau document
                    $stmt = $pdo->prepare("INSERT INTO documents (title, description, filePath, uploadedBy, documentType) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$document['title'], $document['description'], $filePath, $document['uploadedBy'], $document['documentType']]);
                    $success = "Document ajouté avec succès!";
                    // Vider le formulaire
                    $document = [
                        'id' => '',
                        'title' => '',
                        'description' => '',
                        'filePath' => '',
                        'uploadedBy' => $_SESSION['user_id'],
                        'documentType' => ''
                    ];
                }
            } else {
                // Mettre à jour un document existant
                if ($fileUploaded) {
                    // Si un nouveau fichier a été téléchargé, mettre à jour le chemin
                    $stmt = $pdo->prepare("UPDATE documents SET title = ?, description = ?, filePath = ?, uploadedBy = ?, documentType = ? WHERE id = ?");
                    $stmt->execute([$document['title'], $document['description'], $filePath, $document['uploadedBy'], $document['documentType'], $document['id']]);
                } else {
                    // Sinon, conserver l'ancien chemin
                    $stmt = $pdo->prepare("UPDATE documents SET title = ?, description = ?, uploadedBy = ?, documentType = ? WHERE id = ?");
                    $stmt->execute([$document['title'], $document['description'], $document['uploadedBy'], $document['documentType'], $document['id']]);
                }
                $success = "Document mis à jour avec succès!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smarttech - <?= empty($document['id']) ? 'Ajouter' : 'Modifier' ?> un document</title>
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
                        <a class="nav-link" href="../index.php">Accueil</a>
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
        <h2><?= empty($document['id']) ? 'Ajouter' : 'Modifier' ?> un document</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($document['id']) ?>">
            
            <div class="mb-3">
                <label for="title" class="form-label">Titre*</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($document['title']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($document['description']) ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="documentType" class="form-label">Type de document</label>
                <select class="form-select" id="documentType" name="documentType">
                    <option value="">Sélectionner un type</option>
                    <option value="Contrat" <?= $document['documentType'] == 'Contrat' ? 'selected' : '' ?>>Contrat</option>
                    <option value="Facture" <?= $document['documentType'] == 'Facture' ? 'selected' : '' ?>>Facture</option>
                    <option value="Rapport" <?= $document['documentType'] == 'Rapport' ? 'selected' : '' ?>>Rapport</option>
                    <option value="Présentation" <?= $document['documentType'] == 'Présentation' ? 'selected' : '' ?>>Présentation</option>
                    <option value="Autre" <?= $document['documentType'] == 'Autre' ? 'selected' : '' ?>>Autre</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="uploadedBy" class="form-label">Téléchargé par</label>
                <select class="form-select" id="uploadedBy" name="uploadedBy">
                    <option value="">Sélectionner un employé</option>
                    <?php foreach ($employees as $employee): ?>
                    <option value="<?= $employee['id'] ?>" <?= $document['uploadedBy'] == $employee['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($employee['employeeName']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="document" class="form-label">Fichier<?= empty($document['id']) ? '*' : '' ?></label>
                <input type="file" class="form-control" id="document" name="document" <?= empty($document['id']) ? 'required' : '' ?>>
                <?php if (!empty($document['filePath'])): ?>
                <div class="form-text">Fichier actuel: <a href="<?= htmlspecialchars($document['filePath']) ?>" target="_blank"><?= basename($document['filePath']) ?></a></div>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <button type="submit" class="btn btn-primary"><?= empty($document['id']) ? 'Ajouter' : 'Mettre à jour' ?></button>
                <a href="documents.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
