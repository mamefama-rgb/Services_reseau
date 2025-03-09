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

// Récupérer les paramètres de connexion RDP
$rdp_host = $_POST['rdp_host'] ?? '';
$rdp_username = $_POST['rdp_username'] ?? '';
$rdp_password = $_POST['rdp_password'] ?? '';
$rdp_screen = $_POST['rdp_screen'] ?? 'fullscreen';

// Valider les entrées
if (empty($rdp_host) || empty($rdp_username)) {
    $_SESSION['error'] = "L'hôte et le nom d'utilisateur sont requis pour la connexion RDP.";
    header('Location: remotes.php');
    exit;
}

// Enregistrer la tentative de connexion dans la base de données (à implémenter)
// logConnectionAttempt($user_id, 'RDP', $rdp_host, $rdp_username);

// Configuration de Guacamole ou autre service WebRDP
// Adapter ces paramètres selon votre configuration
$webrdp_base_url = "http://localhost:8080/guacamole/";
$webrdp_api_path = "api/tokens";

// Générer un jeton d'authentification (à adapter selon votre système)
$auth_token = md5($user_id . time() . "webrdp_secret_key");
$_SESSION['rdp_auth_token'] = $auth_token;

// Stocker les paramètres de connexion en session
$_SESSION['rdp_params'] = [
    'host' => $rdp_host,
    'username' => $rdp_username,
    'screen' => $rdp_screen,
    'has_password' => !empty($rdp_password)
];

// Si un mot de passe est fourni, le stocker temporairement (pas recommandé en production)
// En production, utilisez un système de gestion de secrets plus sécurisé
if (!empty($rdp_password)) {
    // Cette approche est simplifiée pour l'exemple. En production, utilisez des méthodes plus sécurisées.
    $_SESSION['rdp_temp_password'] = $rdp_password;
}

// Déterminer la résolution d'écran
$screen_resolution = "1280x720";
switch ($rdp_screen) {
    case 'fullscreen':
        $screen_resolution = "fullscreen";
        break;
    case '1024x768':
        $screen_resolution = "1024x768";
        break;
    case '1280x720':
        $screen_resolution = "1280x720";
        break;
    case '1920x1080':
        $screen_resolution = "1920x1080";
        break;
}

// Construire l'URL de connexion RDP via Guacamole ou un service similaire
// Ceci est un exemple, à adapter selon votre configuration
$webrdp_connect_url = $webrdp_base_url . "client/" . $auth_token . "?" .
                     "id=c%2F" . urlencode($rdp_host) . "&" .
                     "username=" . urlencode($rdp_username) . "&" .
                     "password=" . urlencode($rdp_password) . "&" .
                     "width=" . urlencode($screen_resolution === "fullscreen" ? "100%" : explode("x", $screen_resolution)[0]) . "&" .
                     "height=" . urlencode($screen_resolution === "fullscreen" ? "100%" : explode("x", $screen_resolution)[1]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartTech - Connexion RDP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }
        .rdp-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        .rdp-header {
            background-color: #343a40;
            color: white;
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .rdp-content {
            flex-grow: 1;
            padding: 0;
            overflow: hidden;
        }
        .rdp-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .btn-rdp {
            background-color: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
        }
        .btn-rdp:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            color: white;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <!-- Overlay de chargement -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner-border text-light mb-3" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
        <h4>Connexion à <?php echo htmlspecialchars($rdp_host); ?></h4>
        <p>Veuillez patienter pendant l'établissement de la connexion RDP...</p>
    </div>

    <div class="rdp-container">
        <div class="rdp-header">
            <div>
                <strong>Connexion RDP:</strong> 
                <?php echo htmlspecialchars($rdp_username) . '@' . htmlspecialchars($rdp_host); ?>
                (<?php echo htmlspecialchars($screen_resolution); ?>)
            </div>
            <div>
                <button class="btn btn-sm btn-rdp" onclick="toggleFullscreen()" title="Plein écran">
                    <i class="fas fa-expand"></i>
                </button>
                <button class="btn btn-sm btn-rdp" onclick="clipboardAccess()" title="Presse-papiers">
                    <i class="fas fa-clipboard"></i>
                </button>
                <button class="btn btn-sm btn-rdp" onclick="window.location.href='remotes.php'" title="Fermer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="rdp-content">
            <iframe id="rdpIframe" class="rdp-iframe" src="<?php echo htmlspecialchars($webrdp_connect_url); ?>"></iframe>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Masquer l'overlay de chargement une fois le contenu de l'iframe chargé
        document.getElementById('rdpIframe').onload = function() {
            // Attendre un court délai pour s'assurer que la connexion est établie
            setTimeout(function() {
                document.getElementById('loadingOverlay').style.display = 'none';
            }, 2000);
        };

        // Fonctions pour contrôler la connexion RDP
        function toggleFullscreen() {
            const iframe = document.getElementById('rdpIframe');
            
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
        
        function clipboardAccess() {
            // Cette fonction dépend de l'implémentation spécifique de votre service RDP web
            // Pour Guacamole, par exemple, cela peut envoyer un message au contenu de l'iframe
            const iframe = document.getElementById('rdpIframe');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.postMessage({
                    action: 'showClipboard'
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
                    service: 'RDP',
                    host: '<?php echo htmlspecialchars($rdp_host); ?>',
                    username: '<?php echo htmlspecialchars($rdp_username); ?>',
                    status: 'initiated'
                })
            }).catch(error => console.error('Erreur:', error));
        });
    </script>
</body>
</html>
