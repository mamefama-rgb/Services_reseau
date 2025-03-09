<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Informations utilisateur de la session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Configuration du serveur FTP
$ftp_server = "localhost"; // Remplacez par votre serveur FTP
$ftp_port = 21;
$ftp_conn = null;
$current_dir = "/";
$message = "";
$ftp_files = [];

// Fonction pour se connecter au serveur FTP
function connectFTP($server, $port, $username, $password) {
    // Vérifiez si la fonction ftp_connect existe
    if (!function_exists('ftp_connect')) {
        die('La fonction ftp_connect n\'est pas définie. Vérifiez votre installation de PHP.');
    }

    $conn = ftp_connect($server, $port);
    if ($conn && ftp_login($conn, $username, $password)) {
        ftp_pasv($conn, true); // Mode passif souvent nécessaire
        return $conn;
    }
    return false;
}

// Traitement du formulaire de connexion FTP
if (isset($_POST['ftp_connect'])) {
    $ftp_user = $_POST['ftp_username'];
    $ftp_pass = $_POST['ftp_password'];
    
    $ftp_conn = connectFTP($ftp_server, $ftp_port, $ftp_user, $ftp_pass);
    
    if ($ftp_conn) {
        $_SESSION['ftp_conn'] = true;
        $_SESSION['ftp_user'] = $ftp_user;
        $message = '<div class="alert alert-success">Connexion au serveur FTP réussie.</div>';
    } else {
        $message = '<div class="alert alert-danger">Échec de connexion au serveur FTP. Vérifiez vos identifiants.</div>';
    }
}

// Gestion de la déconnexion FTP
if (isset($_GET['disconnect']) && $_GET['disconnect'] == 1) {
    if (isset($_SESSION['ftp_conn'])) {
        unset($_SESSION['ftp_conn']);
        unset($_SESSION['ftp_user']);
        $message = '<div class="alert alert-info">Déconnexion du serveur FTP réussie.</div>';
    }
}

// Vérifier si une session FTP est active
if (isset($_SESSION['ftp_conn']) && $_SESSION['ftp_conn'] === true) {
    // Ne pas reconnecter si déjà connecté
    if (!isset($ftp_conn)) {
        $ftp_conn = connectFTP($ftp_server, $ftp_port, $_SESSION['ftp_user'], $_POST['ftp_password'] ?? '');
    }
}

// Navigation dans les répertoires
if (isset($_GET['dir']) && $ftp_conn) {
    $current_dir = $_GET['dir'];
    if (ftp_chdir($ftp_conn, $current_dir)) {
        // Succès
    } else {
        $message = '<div class="alert alert-danger">Impossible d\'accéder au répertoire.</div>';
    }
}

// Upload de fichier
if (isset($_POST['upload']) && $ftp_conn && isset($_FILES['file'])) {
    $temp_file = $_FILES['file']['tmp_name'];
    $file_name = $_FILES['file']['name'];
    
    if (ftp_put($ftp_conn, $file_name, $temp_file, FTP_BINARY)) {
        $message = '<div class="alert alert-success">Fichier uploadé avec succès.</div>';
    } else {
        $message = '<div class="alert alert-danger">Échec de l\'upload du fichier.</div>';
    }
}

// Création de dossier
if (isset($_POST['create_folder']) && $ftp_conn && isset($_POST['folder_name'])) {
    $folder_name = $_POST['folder_name'];
    
    if (ftp_mkdir($ftp_conn, $folder_name)) {
        $message = '<div class="alert alert-success">Dossier créé avec succès.</div>';
    } else {
        $message = '<div class="alert alert-danger">Échec de la création du dossier.</div>';
    }
}

// Suppression de fichier ou dossier
if (isset($_GET['delete']) && $ftp_conn) {
    $delete_path = $_GET['delete'];
    
    if (isset($_GET['is_dir']) && $_GET['is_dir'] == 1) {
        if (ftp_rmdir($ftp_conn, $delete_path)) {
            $message = '<div class="alert alert-success">Dossier supprimé avec succès.</div>';
        } else {
            $message = '<div class="alert alert-danger">Échec de la suppression du dossier.</div>';
        }
    } else {
        if (ftp_delete($ftp_conn, $delete_path)) {
            $message = '<div class="alert alert-success">Fichier supprimé avec succès.</div>';
        } else {
            $message = '<div class="alert alert-danger">Échec de la suppression du fichier.</div>';
        }
    }
}

// Récupération des fichiers et dossiers
if ($ftp_conn) {
    $current_dir = ftp_pwd($ftp_conn);
    $ftp_files = ftp_nlist($ftp_conn, ".");
}

// HTML pour l'interface utilisateur
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartTech - Transfert de Fichiers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .file-list {
            margin-top: 20px;
        }
        .file-item {
            display: flex;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .file-item:hover {
            background-color: #f8f9fa;
        }
        .file-icon {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        .file-name {
            flex-grow: 1;
        }
        .file-actions {
            white-space: nowrap;
        }
        .breadcrumb-item a {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">SmartTech</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="remotes.php">Services Distants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="ftp.php">Transfert de Fichiers</a>
                    </li>
                </ul>
                <span class="navbar-text">
                    Connecté en tant que: <?php echo htmlspecialchars($username); ?> | 
                    <a href="logout.php" class="text-white">Déconnexion</a>
                </span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <h1 class="mb-4">Transfert de Fichiers (FTP)</h1>
        
        <?php echo $message; ?>
        
        <?php if (!isset($_SESSION['ftp_conn']) || $_SESSION['ftp_conn'] !== true): ?>
            <!-- Formulaire de connexion FTP -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Connexion au serveur FTP</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="ftp.php">
                        <div class="mb-3">
                            <label for="ftp_username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="ftp_username" name="ftp_username" required>
                        </div>
                        <div class="mb-3">
                            <label for="ftp_password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="ftp_password" name="ftp_password" required>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">
                                Serveur: <?php echo htmlspecialchars($ftp_server); ?> | 
                                Port: <?php echo htmlspecialchars($ftp_port); ?>
                            </small>
                        </div>
                        <button type="submit" name="ftp_connect" class="btn btn-primary">Se connecter</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Interface de gestion des fichiers -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5>Connecté en tant que: <?php echo htmlspecialchars($_SESSION['ftp_user']); ?></h5>
                </div>
                <div>
                    <a href="ftp.php?disconnect=1" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion FTP
                    </a>
                </div>
            </div>
            
            <!-- Fil d'Ariane pour la navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <?php
                    $path_parts = explode('/', $current_dir);
                    $build_path = '';
                    
                    foreach ($path_parts as $index => $part) {
                        if (empty($part)) {
                            if ($index === 0) {
                                echo '<li class="breadcrumb-item"><a href="ftp.php?dir=/">/</a></li>';
                            }
                            continue;
                        }
                        
                        $build_path .= '/' . $part;
                        if ($index === count($path_parts) - 1) {
                            echo '<li class="breadcrumb-item active">' . htmlspecialchars($part) . '</li>';
                        } else {
                            echo '<li class="breadcrumb-item"><a href="ftp.php?dir=' . htmlspecialchars($build_path) . '">' . htmlspecialchars($part) . '</a></li>';
                        }
                    }
                    ?>
                </ol>
            </nav>
            
            <!-- Actions sur les fichiers -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Upload de fichier</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="ftp.php" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="file" class="form-label">Sélectionner un fichier</label>
                                    <input type="file" class="form-control" id="file" name="file" required>
                                </div>
                                <button type="submit" name="upload" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Uploader
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Créer un dossier</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="ftp.php">
                                <div class="mb-3">
                                    <label for="folder_name" class="form-label">Nom du dossier</label>
                                    <input type="text" class="form-control" id="folder_name" name="folder_name" required>
                                </div>
                                <button type="submit" name="create_folder" class="btn btn-success">
                                    <i class="fas fa-folder-plus"></i> Créer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Liste des fichiers et dossiers -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Contenu du répertoire</h5>
                </div>
                <div class="card-body p-0">
                    <div class="file-list">
                        <?php if (!empty($ftp_files)): ?>
                            <?php foreach ($ftp_files as $file): ?>
                                <?php
                                // Ignorer les entrées . et ..
                                if ($file === '.' || $file === '..') continue;
                                
                                // Déterminer si c'est un dossier ou un fichier
                                $is_dir = false;
                                if (substr($file, 0, 1) !== '.') {
                                    $is_dir = !strpos($file, '.');
                                }
                                
                                $file_name = basename($file);
                                ?>
                                <div class="file-item">
                                    <div class="file-icon">
                                        <?php if ($is_dir): ?>
                                            <i class="fas fa-folder text-warning"></i>
                                        <?php else: ?>
                                            <i class="fas fa-file text-primary"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="file-name">
                                        <?php if ($is_dir): ?>
                                            <a href="ftp.php?dir=<?php echo urlencode($current_dir . '/' . $file_name); ?>">
                                                <?php echo htmlspecialchars($file_name); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($file_name); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="file-actions">
                                        <?php if (!$is_dir): ?>
                                            <a href="download.php?file=<?php echo urlencode($current_dir . '/' . $file_name); ?>" class="btn btn-sm btn-outline-primary me-1" title="Télécharger">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="ftp.php?delete=<?php echo urlencode($file_name); ?>&is_dir=<?php echo $is_dir ? '1' : '0'; ?>" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-3 text-center text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3"></i>
                                <p>Le répertoire est vide.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p>© <?php echo date('Y'); ?> SmartTech - Plateforme de Services Réseau</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
