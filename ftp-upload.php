<?php
// Initialiser la session si nécessaire
session_start();

// Variables pour les messages d'erreur ou de succès
$message = '';
$messageType = '';

// Traitement du formulaire d'upload direct
if (isset($_POST['submit_direct'])) {
    // Vérifier si un fichier a été sélectionné
    if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] == 0) {
        $target_dir = "/var/www/html/assets/";
        $target_file = $target_dir . basename($_FILES['fileToUpload']['name']);
        
        // Tenter de déplacer le fichier uploadé
        if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_file)) {
            $message = "Le fichier " . basename($_FILES['fileToUpload']['name']) . " a été uploadé avec succès.";
            $messageType = "success";
        } else {
            $message = "Erreur lors de l'upload du fichier.";
            $messageType = "danger";
        }
    } else {
        $message = "Veuillez sélectionner un fichier à uploader.";
        $messageType = "warning";
    }
}

// Fonction pour tester la connexion FTP
function testFtpConnection($server, $username, $password) {
    // Tenter d'établir une connexion FTP
    $conn_id = @ftp_connect($server);
    if ($conn_id) {
        // Tenter de se connecter avec les identifiants
        $login_result = @ftp_login($conn_id, $username, $password);
        if ($login_result) {
            ftp_close($conn_id);
            return true;
        }
        ftp_close($conn_id);
    }
    return false;
}

// Traitement du test de connexion FTP
if (isset($_POST['test_connection'])) {
    $server = $_POST['ftp_server'];
    $username = $_POST['ftp_username'];
    $password = $_POST['ftp_password'];
    
    if (testFtpConnection($server, $username, $password)) {
        $message = "Connexion FTP réussie! Vous pouvez maintenant utiliser ces identifiants dans votre client FTP.";
        $messageType = "success";
    } else {
        $message = "Échec de la connexion FTP. Veuillez vérifier vos identifiants.";
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smarttech - Upload de fichiers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .upload-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #e9ecef;
            border-left: 4px solid #6c757d;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4 text-center">Smarttech - Plateforme de partage de fichiers</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="upload-section">
                    <h3>Upload direct</h3>
                    <p>Uploadez directement des fichiers sur le serveur:</p>
                    
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="fileToUpload" class="form-label">Sélectionnez un fichier:</label>
                            <input type="file" class="form-control" name="fileToUpload" id="fileToUpload" required>
                        </div>
                        <button type="submit" name="submit_direct" class="btn btn-primary">Uploader</button>
                    </form>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="upload-section">
                    <h3>Connexion FTP</h3>
                    <p>Utilisez ces identifiants pour vous connecter via un client FTP:</p>
                    
                    <div class="info-box">
                        <p><strong>Identifiants par défaut:</strong></p>
                        <ul>
                            <li>Serveur: <span id="server-address"><?php echo $_SERVER['SERVER_NAME']; ?></span></li>
                            <li>Nom d'utilisateur: <strong>ftpuser</strong></li>
                            <li>Mot de passe: <strong>passer</strong></li>
                            <li>Répertoire: <strong>/var/www/html/assets</strong></li>
                        </ul>
                    </div>
                    
                    <h4>Tester la connexion FTP</h4>
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="ftp_server" class="form-label">Serveur FTP:</label>
                            <input type="text" class="form-control" id="ftp_server" name="ftp_server" value="<?php echo $_SERVER['SERVER_NAME']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="ftp_username" class="form-label">Nom d'utilisateur:</label>
                            <input type="text" class="form-control" id="ftp_username" name="ftp_username" value="ftpuser" required>
                        </div>
                        <div class="mb-3">
                            <label for="ftp_password" class="form-label">Mot de passe:</label>
                            <input type="password" class="form-control" id="ftp_password" name="ftp_password" value="passer" required>
                        </div>
                        <button type="submit" name="test_connection" class="btn btn-secondary">Tester la connexion</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <h3>Comment utiliser le FTP?</h3>
            <p>Vous pouvez utiliser n'importe quel client FTP comme FileZilla, WinSCP ou CyberDuck pour vous connecter:</p>
            <ol>
                <li>Téléchargez et installez un client FTP (ex: <a href="https://filezilla-project.org/" target="_blank">FileZilla</a>)</li>
                <li>Entrez les informations de connexion (serveur, nom d'utilisateur, mot de passe)</li>
                <li>Connectez-vous et glissez-déposez vos fichiers dans le répertoire /var/www/html/assets</li>
            </ol>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
