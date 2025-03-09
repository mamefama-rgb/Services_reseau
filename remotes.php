<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user information from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartTech - Services Distants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .service-card {
            transition: transform 0.3s ease;
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .icon-container {
            font-size: 3rem;
            margin-bottom: 1rem;
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
                        <a class="nav-link active" href="remotes.php">Services Distants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ftp.php">Transfert de Fichiers</a>
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
        <h1 class="mb-4">Services d'Accès Distant</h1>
        <p class="lead">Accédez aux machines et services du réseau via les options ci-dessous.</p>

        <div class="row mt-5">
            <!-- SSH Service -->
            <div class="col-md-4 mb-4">
                <div class="card service-card h-100">
                    <div class="card-body text-center">
                        <div class="icon-container text-primary">
                            <i class="fas fa-terminal"></i>
                        </div>
                        <h3 class="card-title">SSH</h3>
                        <p class="card-text">Accès en ligne de commande aux serveurs Linux.</p>
                        <div class="mt-4">
                            <form method="post" action="ssh_connects.php">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="ssh_host" placeholder="Adresse IP ou nom d'hôte" required>
                                </div>
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="ssh_username" placeholder="Nom d'utilisateur" required>
                                </div>
                                <div class="mb-3">
                                    <select class="form-control" name="ssh_auth_method">
                                        <option value="password">Mot de passe</option>
                                        <option value="key">Clé SSH</option>
                                    </select>
                                </div>
                                <div class="mb-3 ssh-password">
                                    <input type="password" class="form-control" name="ssh_password" placeholder="Mot de passe">
                                </div>
                                <button type="submit" class="btn btn-primary">Se connecter</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VNC/NoVNC Service -->
            <div class="col-md-4 mb-4">
                <div class="card service-card h-100">
                    <div class="card-body text-center">
                        <div class="icon-container text-success">
                            <i class="fas fa-desktop"></i>
                        </div>
                        <h3 class="card-title">VNC/NoVNC</h3>
                        <p class="card-text">Accès au bureau à distance des machines Linux.</p>
                        <div class="mt-4">
                            <form method="post" action="vnc_connects.php">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="vnc_host" placeholder="Adresse IP ou nom d'hôte" required>
                                </div>
                                <div class="mb-3">
                                    <input type="number" class="form-control" name="vnc_port" placeholder="Port (par défaut: 5900)" value="5900" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" class="form-control" name="vnc_password" placeholder="Mot de passe VNC">
                                </div>
                                <button type="submit" class="btn btn-success">Lancer NoVNC</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RDP Service -->
            <div class="col-md-4 mb-4">
                <div class="card service-card h-100">
                    <div class="card-body text-center">
                        <div class="icon-container text-info">
                            <i class="fas fa-window-restore"></i>
                        </div>
                        <h3 class="card-title">RDP</h3>
                        <p class="card-text">Accès au bureau à distance des machines Windows.</p>
                        <div class="mt-4">
                            <form method="post" action="rdp_connects.php">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="rdp_host" placeholder="Adresse IP ou nom d'hôte" required>
                                </div>
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="rdp_username" placeholder="Nom d'utilisateur" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" class="form-control" name="rdp_password" placeholder="Mot de passe">
                                </div>
                                <div class="mb-3">
                                    <select class="form-control" name="rdp_screen">
                                        <option value="fullscreen">Plein écran</option>
                                        <option value="1024x768">1024x768</option>
                                        <option value="1280x720">1280x720</option>
                                        <option value="1920x1080">1920x1080</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-info text-white">Lancer RDP</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Connection History Table -->
        <div class="mt-5">
            <h3>Historique des connexions</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Service</th>
                            <th>Hôte</th>
                            <th>Utilisateur</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ici, vous pourriez récupérer l'historique des connexions depuis la base de données
                        // Exemple fictif:
                        $connections = [
                            ['2025-03-07 14:23:05', 'SSH', 'srv-linux-01.smarttech.local', 'admin', 'Réussie'],
                            ['2024-03-08 10:45:32', 'VNC', 'srv-app-02.smarttech.local', 'admin', 'Réussie'],
                            ['2024-03-08 16:12:18', 'RDP', 'win-srv-01.smarttech.local', 'administrateur', 'Échouée']
                        ];

                        foreach ($connections as $conn) {
                            echo '<tr>';
                            echo '<td>' . $conn[0] . '</td>';
                            echo '<td>' . $conn[1] . '</td>';
                            echo '<td>' . $conn[2] . '</td>';
                            echo '<td>' . $conn[3] . '</td>';
                            echo '<td>' . ($conn[4] === 'Réussie' ? 
                                '<span class="badge bg-success">Réussie</span>' : 
                                '<span class="badge bg-danger">Échouée</span>') . '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p>© <?php echo date('Y'); ?> SmartTech - Plateforme de Services Réseau</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle SSH password field based on authentication method
            const sshAuthSelect = document.querySelector('select[name="ssh_auth_method"]');
            const sshPasswordField = document.querySelector('.ssh-password');
            
            sshAuthSelect.addEventListener('change', function() {
                if (this.value === 'password') {
                    sshPasswordField.style.display = 'block';
                } else {
                    sshPasswordField.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
