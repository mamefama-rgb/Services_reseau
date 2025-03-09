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

// Récupérer les paramètres de connexion SSH
$ssh_host = $_POST['ssh_host'] ?? '';
$ssh_username = $_POST['ssh_username'] ?? '';
$ssh_auth_method = $_POST['ssh_auth_method'] ?? 'password';
$ssh_password = $_POST['ssh_password'] ?? '';

// Valider les entrées
if (empty($ssh_host) || empty($ssh_username)) {
    $_SESSION['error'] = "L'hôte et le nom d'utilisateur sont requis pour la connexion SSH.";
    header('Location: remotes.php');
    exit;
}

// Si méthode d'authentification par mot de passe, vérifier que le mot de passe est fourni
if ($ssh_auth_method === 'password' && empty($ssh_password)) {
    $_SESSION['error'] = "Le mot de passe est requis pour l'authentification.";
    header('Location: remotes.php');
    exit;
}

// Enregistrer la tentative de connexion dans la base de données (à implémenter)
// logConnectionAttempt($user_id, 'SSH', $ssh_host, $ssh_username);

// Configuration du client SSH via WebSSH (utilisation d'un proxy WebSocket)
$webssh_url = "http://localhost:8888/ssh/host/{$ssh_host}/";

// Générer un jeton d'authentification (à adapter selon votre système)
$auth_token = md5($user_id . time() . "webssh_secret_key");
$_SESSION['ssh_auth_token'] = $auth_token;

// Stocker les paramètres de connexion en session
$_SESSION['ssh_params'] = [
    'host' => $ssh_host,
    'username' => $ssh_username,
    'auth_method' => $ssh_auth_method,
    // Ne pas stocker le mot de passe en clair en session
    'has_password' => !empty($ssh_password)
];

// Si un mot de passe est fourni, le stocker temporairement (pas recommandé en production)
// En production, utilisez un système de gestion de secrets plus sécurisé
if (!empty($ssh_password)) {
    // Cette approche est simplifiée pour l'exemple. En production, utilisez des méthodes plus sécurisées.
    $_SESSION['ssh_temp_password'] = $ssh_password;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartTech - Connexion SSH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }
        .terminal-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #000;
            color: #fff;
        }
        .terminal-header {
            background-color: #343a40;
            color: white;
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .terminal-content {
            flex-grow: 1;
            padding: 0;
            overflow: hidden;
        }
        .terminal-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .btn-terminal {
            background-color: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
        }
        .btn-terminal:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
    </style>
</head>
<body>
    <div class="terminal-container">
        <div class="terminal-header">
            <div>
                <strong>Connexion SSH:</strong> 
                <?php echo htmlspecialchars($ssh_username) . '@' . htmlspecialchars($ssh_host); ?>
            </div>
            <div>
                <button class="btn btn-sm btn-terminal" onclick="copyToClipboard()" title="Copier la sélection">
                    <i class="fas fa-copy"></i>
                </button>
                <button class="btn btn-sm btn-terminal" onclick="pasteFromClipboard()" title="Coller">
                    <i class="fas fa-paste"></i>
                </button>
                <button class="btn btn-sm btn-terminal" onclick="window.location.href='remotes.php'" title="Fermer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="terminal-content">
            <?php
            // Différentes options d'intégration possibles selon votre configuration :
            
            // Option 1: Intégration via iframe d'un service WebSSH comme Shellinabox, WebSSH2, etc.
            echo '<iframe class="terminal-iframe" src="' . htmlspecialchars($webssh_url) . '?token=' . htmlspecialchars($auth_token) . '"></iframe>';
            
            // Option 2: Intégration de terminal.js ou xterm.js (commenté, à décommenter si utilisé)
           
            echo '<div id="terminal"></div>';
            
            ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction pour copier la sélection dans le presse-papiers
        function copyToClipboard() {
            if (document.getSelection) {
                const selection = document.getSelection();
                const text = selection.toString();
                if (text) {
                    navigator.clipboard.writeText(text)
                        .then(() => console.log("Texte copié avec succès"))
                        .catch(err => console.error("Erreur lors de la copie : ", err));
                }
            }
        }

        // Fonction pour coller depuis le presse-papiers (requiert des permissions)
        function pasteFromClipboard() {
            // Cette fonctionnalité nécessite que la page soit servie via HTTPS
            navigator.clipboard.readText()
                .then(text => {
                    // Envoyer le texte au terminal (nécessite une implémentation spécifique selon le terminal utilisé)
                    const iframe = document.querySelector('.terminal-iframe');
                    if (iframe && iframe.contentWindow) {
                        iframe.contentWindow.postMessage({
                            type: 'paste',
                            text: text
                        }, '*');
                    }
                })
                .catch(err => {
                    console.error("Erreur lors de la lecture du presse-papiers : ", err);
                    alert("Impossible d'accéder au presse-papiers. Vérifiez que vous avez accordé les permissions nécessaires.");
                });
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
                    service: 'SSH',
                    host: '<?php echo htmlspecialchars($ssh_host); ?>',
                    username: '<?php echo htmlspecialchars($ssh_username); ?>',
                    status: 'initiated'
                })
            }).catch(error => console.error('Erreur:', error));
        });
    </script>
</body>
</html>
