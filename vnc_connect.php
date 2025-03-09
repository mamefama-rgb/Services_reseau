<?php
session_start();
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Récupérer les informations de l'utilisateur depuis la session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: remotes.php');
    exit;
}

// Récupérer les paramètres de connexion VNC
$vnc_host = '192.168.1.129' ?? '';
$vnc_port = $_POST['vnc_port'] ?? '5900';
$vnc_password = 'fama7' ?? '';

// Valider les entrées
if (empty($vnc_host)) {
    $_SESSION['error'] = "L'hôte est requis pour la connexion VNC.";
    header('Location: remotes.php');
    exit;
}

// Vérifier que le port est un nombre
if (!is_numeric($vnc_port)) {
    $_SESSION['error'] = "Le port doit être un nombre.";
    header('Location: remotes.php');
    exit;
}

// Enregistrer la tentative de connexion dans la base de données (à implémenter)
// logConnectionAttempt($user_id, 'VNC', $vnc_host, 'vnc_user');

// Configuration de NoVNC
// Adapter ces paramètres selon votre configuration de serveur NoVNC
$novnc_base_url = "http://localhost:6080/vnc.html";
$websockify_port = "6080";  // Port du proxy websockify

// Générer un jeton d'authentification (à adapter selon votre système)
$auth_token = md5($user_id . time() . "novnc_secret_key");
$_SESSION['vnc_auth_token'] = $auth_token;

// Stocker les paramètres de connexion en session
$_SESSION['vnc_params'] = [
    'host' => $vnc_host,
    'port' => $vnc_port,
    'has_password' => !empty($vnc_password)
];

// Si un mot de passe est fourni, le stocker temporairement (pas recommandé en production)
// En production, utilisez un système de gestion de secrets plus sécurisé
if (!empty($vnc_password)) {
    // Cette approche est simplifiée pour l'exemple. En production, utilisez des méthodes plus sécurisées.
    $_SESSION['vnc_temp_password'] = $vnc_password;
}

// Construire l'URL NoVNC
$novnc_url = $novnc_base_url . "?host=" . urlencode($vnc_host) . 
             "&port=" . urlencode($websockify_port) . 
             "&token=" . urlencode($auth_token) . 
             "&autoconnect=1" . 
             "&resize=scale";

// Si le mot de passe est fourni, l'ajouter à l'URL (cette méthode n'est pas sécurisée, à modifier selon votre config)
if (!empty($vnc_password)) {
    $novnc_url .= "&password=" . urlencode($vnc_password);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartTech - Connexion VNC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }
        .vnc-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        .vnc-header {
            background-color: #343a40;
            color: white;
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .vnc-content {
            flex-grow: 1;
            padding: 0;
            overflow: hidden;
        }
        .vnc-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .btn-vnc {
            background-color: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
        }
        .btn-vnc:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        .keyboard-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            opacity: 0.7;
        }
        .keyboard-btn:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="vnc-container">
        <div class="vnc-header">
            <div>
                <strong>Connexion VNC:</strong> 
                <?php echo htmlspecialchars($vnc_host) . ':' . htmlspecialchars($vnc_port); ?>
            </div>
            <div>
                <button class="btn btn-sm btn-vnc" onclick="toggleFullscreen()" title="Plein écran">
                    <i class="fas fa-expand"></i>
                </button>
                <button class="btn btn-sm btn-vnc" onclick="sendCtrlAltDel()" title="Envoyer Ctrl+Alt+Del">
                    <i class="fas fa-redo-alt"></i>
                </button>
                <button class="btn btn-sm btn-vnc" onclick="window.location.href='remotes.php'" title="Fermer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="vnc-content">
            <iframe id="vncIframe" class="vnc-iframe" src="<?php echo htmlspecialchars($novnc_url); ?>"></iframe>
        </div>
        
        <!-- Bouton clavier virtuel -->
        <button class="btn btn-dark keyboard-btn" onclick="showKeyboard()" title="Afficher le clavier">
            <i class="fas fa-keyboard"></i>
        </button>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonctions pour contrôler le VNC
        function toggleFullscreen() {
            const iframe = document.getElementById('vncIframe');
            
            if (iframe.requestFullscreen) {
                iframe.requestFullscreen();
            } else if (iframe.mozRequestFullScreen) { /* Firefox */
                iframe.mozRequestFullScreen();
            } else if (iframe.webkitRequestFullscreen) { /* Chrome, Safari & Opera */
                iframe.webkitRequestFullscreen();
            } else if (iframe.msRequestFullscreen) { /* IE/Edge */
                iframe.msRequestFullscreen();
            }
        }
        
        function sendCtrlAltDel() {
            const iframe = document.getElementById('vncIframe');
            if (iframe && iframe.contentWindow) {
                // Envoyer un message au contenu de l'iframe (NoVNC)
                iframe.contentWindow.postMessage({
                    action: 'sendKey',
                    keys: ['Control', 'Alt', 'Delete']
                }, '*');
            }
        }
        
        function showKeyboard() {
            const iframe = document.getElementById('vncIframe');
            if (iframe && iframe.contentWindow) {
                // Envoyer un message au contenu de l'iframe pour afficher le clavier virtuel
                iframe.contentWindow.postMessage({
                    action: 'showKeyboard'
                }, '*');
            }
        }

        // Enregistrer la connexion
        window.addEventListener('load', function() {
            // Enregistrer l'activité ou la connexion
            fetch('log_activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    service: 'VNC',
                    host: '<?php echo htmlspecialchars($vnc_host); ?>',
                    port: '<?php echo htmlspecialchars($vnc_port); ?>',
                    status: 'initiated'
                })
            }).catch(error => console.error('Erreur:', error));
        });
    </script>
</body>
</html>
