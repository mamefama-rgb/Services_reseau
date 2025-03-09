<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Téléversement de Fichiers</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Mon Application</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">À propos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Contact</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container upload-container">
        <h1 class="text-center">Téléversement de Fichiers</h1>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Titre :</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description :</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            <div class="form-group">
                <label for="documentType">Type de document :</label>
                <input type="text" class="form-control" id="documentType" name="documentType" required>
            </div>
            <div class="form-group">
                <label for="employeeName">Nom de l'employé :</label>
                <select class="form-control" id="employeeName" name="employeeName" required>
                    <?php
                    // Connexion à la base de données
                    require_once 'db_connect.php';

                    // Requête pour récupérer les noms des employés
                    $sql = "SELECT firstName, lastName FROM employee";
                    $result = $conn->query($sql);

                    // Vérifier s'il y a des résultats
                    if ($result->num_rows > 0) {
                        // Afficher chaque nom dans la liste déroulante
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Aucun employé trouvé</option>";
                    }

                    // Fermer la connexion
                    $conn->close();
                    ?>
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
