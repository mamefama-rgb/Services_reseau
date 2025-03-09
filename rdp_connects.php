<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get the form data
$rdp_host = isset($_POST['rdp_host']) ? $_POST['rdp_host'] : '';
$rdp_port = isset($_POST['rdp_port']) ? $_POST['rdp_port'] : '3389';
$rdp_username = isset($_POST['rdp_username']) ? $_POST['rdp_username'] : '';
$rdp_password = isset($_POST['rdp_password']) ? $_POST['rdp_password'] : '';

// Log the connection attempt in the database (simulation)
$connection_date = date('Y-m-d H:i:s');
$connection_status = (rand(0, 10) > 1) ? 'Réussie' : 'Échouée'; // 90% chance of success for simulation

// Store in session for display purposes
$_SESSION['last_connection'] = [
    'date' => $connection_date,
    'service' => 'RDP',
    'host' => $rdp_host,
    'username' => $rdp_username,
    'status' => $connection_status
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RDP - <?php echo htmlspecialchars($rdp_host); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow: hidden;
            background-color: #1a1a1a;
        }
        .rdp-container {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .rdp-header {
            background-color: #0067b8;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 50px;
        }
        .rdp-screen {
            flex: 1;
            position: relative;
            overflow: hidden;
            background-color: #000;
        }
        .rdp-toolbar {
            background-color: #0067b8;
            color: white;
            padding: 5px;
            display: flex;
            justify-content: center;
            gap: 10px;
            height: 40px;
        }
        .toolbar-button {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .toolbar-button:hover {
            background-color: #005aa3;
        }
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 1.5rem;
            z-index: 1000;
        }
        .error-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 20px;
            z-index: 1000;
        }
        .desktop-icons {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .desktop-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: white;
            cursor: pointer;
            width: 80px;
            text-align: center;
        }
        .desktop-icon i {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        .desktop-icon span {
            font-size: 0.8rem;
        }
        .windows-taskbar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background-color: #191919;
            display: flex;
            align-items: center;
            padding: 0 10px;
        }
        .start-button {
            background-color: #0078d7;
            color: white;
            border: none;
            padding: 5px 15px;
            margin-right: 10px;
            cursor: pointer;
            height: 30px;
            display: flex;
            align-items: center;
        }
        .taskbar-icons {
            display: flex;
            gap: 15px;
            margin-left: 20px;
            height: 30px;
            align-items: center;
        }
        .taskbar-clock {
            margin-left: auto;
            color: white;
            background-color: #333;
            padding: 2px 10px;
            border-radius: 4px;
        }
        .rdp-cursor {
            cursor: none;
        }
        .custom-cursor {
            position: absolute;
            width: 20px;
            height: 20px;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><polygon points="0,0 0,10 10,0" fill="white" stroke="black"/></svg>');
            pointer-events: none;
            z-index: 9999;
            transform: translate(-2px, -2px);
            display: none;
        }
        .close-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1001;
        }
        /* Windows 10 specific styling */
        .win-wallpaper {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #0c2d57 0%, #1e5372 100%);
            z-index: -1;
        }
        .win-search-bar {
            background-color: #333;
            border-radius: 4px;
            display: flex;
            align-items: center;
            padding: 2px 10px;
            margin-left: 10px;
            width: 200px;
        }
        .win-search-bar i {
            margin-right: 10px;
        }
        .win-search-bar span {
            color: #aaa;
            font-size: 0.9rem;
        }
        .notification-area {
            display: flex;
            gap: 15px;
            margin-right: 10px;
            color: white;
        }
    </style>
</head>
<body>
    <a href="remotes.php" class="btn btn-danger close-btn"><i class="fas fa-times"></i> Fermer</a>
    
    <div class="rdp-container">
        <div class="rdp-header">
            <span>RDP: <?php echo htmlspecialchars($rdp_host); ?>:<?php echo htmlspecialchars($rdp_port); ?> (<?php echo htmlspecialchars($rdp_username); ?>)</span>
            <span>
                <i class="fas fa-signal"></i>
                <i class="fas fa-lock"></i>
                <?php echo date('H:i:s'); ?>
            </span>
        </div>
        
        <div class="rdp-screen" id="rdp-screen">
            <?php if ($connection_status === 'Réussie'): ?>
                <div class="loading-overlay" id="loading-overlay">
                    <div class="spinner-border text-light mb-3" role="status"></div>
                    <div>Connexion en cours vers <?php echo htmlspecialchars($rdp_host); ?>:<?php echo htmlspecialchars($rdp_port); ?></div>
                    <div class="mt-2">Utilisateur: <?php echo htmlspecialchars($rdp_username); ?></div>
                </div>
                
                <div class="custom-cursor" id="custom-cursor"></div>
                <div class="win-wallpaper"></div>
                
                <div class="desktop-icons">
                    <div class="desktop-icon">
                        <i class="fas fa-user"></i>
                        <span>Ce PC</span>
                    </div>
                    <div class="desktop-icon">
                        <i class="fas fa-trash"></i>
                        <span>Corbeille</span>
                    </div>
                    <div class="desktop-icon">
                        <i class="fas fa-folder"></i>
                        <span>Documents</span>
                    </div>
                    <div class="desktop-icon">
                        <i class="fab fa-microsoft"></i>
                        <span>Edge</span>
                    </div>
                    <div class="desktop-icon">
                        <i class="fas fa-file-word"></i>
                        <span>Word</span>
                    </div>
                    <div class="desktop-icon">
                        <i class="fas fa-file-excel"></i>
                        <span>Excel</span>
                    </div>
                </div>
                
                <div class="windows-taskbar">
                    <button class="start-button">
                        <i class="fab fa-windows me-2"></i> 
                    </button>
                    <div class="win-search-bar">
                        <i class="fas fa-search"></i>
                        <span>Rechercher dans Windows</span>
                    </div>
                    <div class="taskbar-icons">
                        <i class="fab fa-microsoft"></i>
                        <i class="fas fa-file-word"></i>
                        <i class="fas fa-file-excel"></i>
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="notification-area">
                        <i class="fas fa-volume-up"></i>
                        <i class="fas fa-wifi"></i>
                        <i class="fas fa-battery-full"></i>
                    </div>
                    <div class="taskbar-clock" id="taskbar-clock">
                        <?php echo date('H:i'); ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="error-overlay">
                    <i class="fas fa-exclamation-triangle text-warning mb-3" style="font-size: 3rem;"></i>
                    <h3>Erreur de connexion RDP</h3>
                    <p>Impossible de se connecter à <?php echo htmlspecialchars($rdp_host); ?>:<?php echo htmlspecialchars($rdp_port); ?></p>
                    <p>Vérifiez les informations de connexion et assurez-vous que le serveur RDP est en cours d'exécution.</p>
                    <p>Utilisateur: <?php echo htmlspecialchars($rdp_username); ?></p>
                    <button class="btn btn-primary mt-3" onclick="window.location.href='remotes.php'">Retour</button>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="rdp-toolbar">
            <button class="toolbar-button" title="Plein écran"><i class="fas fa-expand"></i></button>
            <button class="toolbar-button" title="Ctrl+Alt+Del"><i class="fas fa-keyboard"></i></button>
            <button class="toolbar-button" title="Rafraîchir"><i class="fas fa-sync-alt"></i></button>
            <button class="toolbar-button" title="Paramètres"><i class="fas fa-cog"></i></button>
            <button class="toolbar-button" title="Information"><i class="fas fa-info-circle"></i></button>
            <button class="toolbar-button" title="Déconnecter" onclick="window.location.href='remotes.php'">
                <i class="fas fa-power-off"></i>
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($connection_status === 'Réussie'): ?>
                const loadingOverlay = document.getElementById('loading-overlay');
                const rdpScreen = document.getElementById('rdp-screen');
                const customCursor = document.getElementById('custom-cursor');
                const taskbarClock = document.getElementById('taskbar-clock');
                
                // Simulate Windows login screens
                setTimeout(() => {
                    loadingOverlay.innerHTML = '<div class="spinner-border text-light mb-3" role="status"></div>' +
                                               '<div>Préparation de Windows...</div>';
                }, 2000);
                
                setTimeout(() => {
                    loadingOverlay.innerHTML = '<div class="spinner-border text-light mb-3" role="status"></div>' +
                                               '<div>Bienvenue <?php echo htmlspecialchars($rdp_username); ?></div>';
                }, 4000);
                
                // Complete loading
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                    
                    // Enable custom cursor
                    rdpScreen.classList.add('rdp-cursor');
                    customCursor.style.display = 'block';
                    
                    // Track mouse movement for custom cursor
                    rdpScreen.addEventListener('mousemove', function(e) {
                        customCursor.style.left = e.clientX + 'px';
                        customCursor.style.top = e.clientY + 'px';
                    });
                    
                    // Update clock
                    setInterval(() => {
                        const now = new Date();
                        const hours = String(now.getHours()).padStart(2, '0');
                        const minutes = String(now.getMinutes()).padStart(2, '0');
                        taskbarClock.textContent = `${hours}:${minutes}`;
                    }, 60000);
                    
                }, 5000);
            <?php endif; ?>
        });
    </script>
</body>
</html>
