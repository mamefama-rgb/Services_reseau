<?php
session_start();
// Rediriger vers login si non connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smarttech - Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
        body {
            background-color: #f8f9fa;
        }
        .upload-container {
            margin-top: 50px;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
    </style>
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
                        <a class="nav-link active" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="employees.php">Employés</a>
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
     <div class="container">
    <div class="container upload-container">
        <h1 class="text-center">Téléversement de Fichiers</h1>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Titre :</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
                         <div class="form-group">
                <label for="description">Description :</label>
                <input type="text" class="form-control" id="description" name="description" required>
                 </div>
            <div class="form-group">
                <label for="documentType">Type de document :</label>
                <input type="text" class="form-control" id="documentType" name="documentType" required>
            </div>
            <div class="form-group">
                <label for="employeeName">Nom de l'employé :</label>
                <select class="form-control" id="employeeName" name="employeeName" required>
                </select>
            </div>
            <div class="form-group">
                <label for="fileInput">Choisissez un fichier :</label>
                <input type="file" class="form-control-file" id="fileInput" name="file" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Uploader</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
<?php

                    include 'db_connect.php';
                    // Requête pour récupérer les noms des employés
                    $sql = "SELECT firtsName, lastName FROM employees";
                    $result = $conn->query($sql);

                    // Vérifier s'il y a des résultats
                    if ($result->num_rows > 0) {
                        // Afficher chaque nom dans la liste déroulante
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['firstName']) . "'>" . htmlspecialchars($row['lastName']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Aucun employé trouvé</option>";
  }

                    // Fermer la connexion
 $conn->close();
?>
