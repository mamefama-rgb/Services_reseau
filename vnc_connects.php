<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get the form data
$vnc_host = isset($_POST['vnc_host']) ? $_POST['vnc_host'] : '';
$vnc_port = isset($_POST['vnc_port']) ? $_POST['vnc_port'] : '5900';
$vnc_password = isset($_POST['vnc_password']) ? $_POST['vnc_password'] : '';

// Log the connection attempt in the database (simulation)
$connection_date = date('Y-m-d H:i:s');
$connection_status = (rand(0, 10) > 1) ? 'Réussie' : 'Échouée'; // 90% chance of success for simulation

// Store in session for display purposes
$_SESSION['last_connection'] = [
    'date' => $connection_date,
    'service' => 'VNC',
    'host' => $vnc_host,
    'username' => 'N/A', // VNC typically doesn't use usernames
    'status' => $connection_status
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>noVNC - <?php echo htmlspecialchars($vnc_host); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow: hidden;
            background-color: #1a1a1a;
        }
        .novnc-container {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .novnc-header {
            background-color: #333;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 50px;
        }
        .novnc-screen {
            flex: 1;
            position: relative;
            overflow: hidden;
            background-color: #000;
        }
        .novnc-toolbar {
            background-color: #333;
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
            background-color: #555;
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
        .taskbar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background-color: #333;
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
            gap: 10px;
            margin-left: 20px;
            height: 30px;
            align-items: center;
        }
        .taskbar-clock {
            margin-left: auto;
            color: white;
        }
        .novnc-cursor {
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
    </style>
</head>
<body>
    <a href="remotes.php" class="btn btn-danger close-btn"><i class="fas fa-times"></i> Fermer</a>
    
    <div class="novnc-container">
        <div class="novnc-header">
            <span>noVNC: <?php echo htmlspecialchars($vnc_host); ?>:<?php echo htmlspecialchars($vnc_port); ?></span>
            <span>
                <i class="fas fa-signal"></i>
                <i class="fas fa-lock"></i>
                <?php echo date('H:i:s'); ?>
            </span>
        </div>
        
        <div class="novnc-screen" id="vnc-screen">
            <?php if ($connection_status === 'Réussie'): ?>
                <div class="loading-overlay" id="loading-overlay">
                    <div class="spinner-border text-light mb-3" role="status"></div>
                    <div>Connexion en cours vers <?php echo htmlspecialchars($vnc_host); ?>:<?php echo htmlspecialchars($vnc_port); ?></div>
                </div>
                
                <div class="custom-cursor" id="custom-cursor"></div>
                
                <div class="desktop-icons">
                    <div class="desktop-icon">
                        <i class="fas fa-folder"></i>
                        <span>Documents</span>
                    </div>
                    <div class="desktop-icon">
                        <i class="fas fa-terminal"></i>
                        <span>Terminal</span>
                    </div>
                    <div class="desktop-icon">
                        <i class="fas fa-firefox-browser"></i>
                        <span>Firefox</span>
                    </div>
                    <div class="desktop-icon">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </div>
                </div>
                
                <div class="taskbar">
                    <button class="start-button">
                        <i class="fab fa-linux me-2"></i> Démarrer
                    </button>
                    <div class="taskbar-icons">
                        <i class="fas fa-terminal"></i>
                        <i class="fas fa-firefox-browser"></i>
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="taskbar-clock" id="taskbar-clock">
                        <?php echo date('H:i'); ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="error-overlay">
                    <i class="fas fa-exclamation-triangle text-warning mb-3" style="font-size: 3rem;"></i>
                    <h3>Erreur de connexion VNC</h3>
                    <p>Impossible de se connecter à <?php echo htmlspecialchars($vnc_host); ?>:<?php echo htmlspecialchars($vnc_port); ?></p>
                    <p>Vérifiez les informations de connexion et assurez-vous que le serveur VNC est en cours d'exécution.</p>
                    <button class="btn btn-primary mt-3" onclick="window.location.href='remotes.php'">Retour</button>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="novnc-toolbar">
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
                const vncScreen = document.getElementById('vnc-screen');
                const customCursor = document.getElementById('custom-cursor');
                const taskbarClock = document.getElementById('taskbar-clock');
                
                // Simulate loading
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                    
                    // Enable custom cursor
                    vncScreen.classList.add('novnc-cursor');
                    customCursor.style.display = 'block';
                    
                    // Track mouse movement for custom cursor
                    vncScreen.addEventListener('mousemove', function(e) {
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
                    
                }, 3000);
            <?php endif; ?>
        });
    </script>
</body>
</html>
