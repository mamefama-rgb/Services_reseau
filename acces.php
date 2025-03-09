<?php
// remote_controller.php - Gère les connexions distantes

require_once 'db_connect.php';
require_once 'services.php';
require_once 'login.php';

class RemoteController {
    private $db;
    private $auth;
    private $remoteManager;
    
    public function __construct() {
        global $db;
        $this->db = $db;
        $this->auth = new Auth($db);
        $this->remoteManager = new RemoteAccessManager($db);
        
        // Vérifier l'authentification pour toutes les routes
        if (!$this->auth->isLoggedIn()) {
            $this->redirectToLogin();
        }
    }
    
    // Route pour la connexion SSH
    public function handleSSH() {
        $token = $this->getTokenParam();
        if (!$token) {
            $this->showError("Token de connexion manquant");
            return;
        }
        
        $connectionParams = $this->validateToken($token);
        if (!$connectionParams || $connectionParams['type'] !== 'ssh') {
            $this->showError("Token de connexion invalide ou expiré");
            return;
        }
        
        // Configurer WebSSH avec les paramètres
        $this->renderSSHClient($connectionParams);
    }
    
    // Route pour la connexion VNC
    public function handleVNC() {
        $token = $this->getTokenParam();
        if (!$token) {
            $this->showError("Token de connexion manquant");
            return;
        }
        
        $connectionParams = $this->validateToken($token);
        if (!$connectionParams || $connectionParams['type'] !== 'vnc') {
            $this->showError("Token de connexion invalide ou expiré");
            return;
        }
        
        // Configurer NoVNC avec les paramètres
        $this->renderVNCClient($connectionParams);
    }
    
    // Route pour la connexion RDP
    public function handleRDP() {
        $token = $this->getTokenParam();
        if (!$token) {
            $this->showError("Token de connexion manquant");
            return;
        }
        
        $connectionParams = $this->validateToken($token);
        if (!$connectionParams || $connectionParams['type'] !== 'rdp') {
            $this->showError("Token de connexion invalide ou expiré");
            return;
        }
        
        // Configurer le client RDP (Guacamole) avec les paramètres
        $this->renderRDPClient($connectionParams);
    }
    
    // Récupérer le paramètre de token depuis l'URL
    private function getTokenParam() {
        return isset($_GET['token']) ? $_GET['token'] : null;
    }
    
    // Valider le token et récupérer les paramètres de connexion
    private function validateToken($token) {
        $stmt = $this->db->prepare("SELECT params FROM connection_tokens 
                                   WHERE token = ? AND expires_at > NOW() AND used = 0");
        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return false;
        }
        
        // Marquer le token comme utilisé
        $updateStmt = $this->db->prepare("UPDATE connection_tokens 
                                         SET used = 1, used_at = NOW() 
                                         WHERE token = ?");
        $updateStmt->execute([$token]);
        
        return json_decode($result['params'], true);
    }
    
    // Rediriger vers la page de connexion
    private function redirectToLogin() {
        header('Location: login.php');
        exit;
    }
    
    // Afficher une page d'erreur
    private function showError($message) {
        header('HTTP/1.1 400 Bad Request');
        include 'templates/error.php';
        exit;
    }
    
    // Afficher le client SSH (WebSSH)
    private function renderSSHClient($params) {
        $host = $params['host'];
        $port = $params['port'];
        $username = $params['username'];
        
        // Configuration de WebSSH
        $websshConfig = [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'authentication' => 'password' // ou 'key' selon la configuration
        ];
        
        // Inclure le template
        $pageTitle = "Connexion SSH - " . htmlspecialchars($host);
        include 'templates/ssh_client.php';
    }
    
    // Afficher le client VNC (NoVNC)
    private function renderVNCClient($params) {
        $host = $params['host'];
        $port = $params['port'];
        $password = $params['password'];
        
        // Configuration de NoVNC
        $novncConfig = [
            'host' => $host,
            'port' => $port,
            'password' => $password,
            'path' => '/websockify'
        ];
        
        // Inclure le template
        $pageTitle = "Connexion VNC - " . htmlspecialchars($host);
        include 'templates/vnc_client.php';
    }
    
    // Afficher le client RDP (Guacamole)
    private function renderRDPClient($params) {
        $host = $params['host'];
        $port = $params['port'];
        $username = $params['username'];
        
        // Configuration de Guacamole
        $rdpConfig = [
            'host' => $host,
            'port' => $port,
            'username' => $username
        ];
        
        // Inclure le template
        $pageTitle = "Connexion RDP - " . htmlspecialchars($host);
        include 'templates/rdp_client.php';
    }
}

// Router simple pour les différentes routes de connexion
$controller = new RemoteController();

// URL: /remote/ssh?token=TOKEN
if (strpos($_SERVER['REQUEST_URI'], '/remote/ssh') !== false) {
    $controller->handleSSH();
}
// URL: /remote/novnc?token=TOKEN
elseif (strpos($_SERVER['REQUEST_URI'], '/remote/novnc') !== false) {
    $controller->handleVNC();
}
// URL: /remote/rdp?token=TOKEN
elseif (strpos($_SERVER['REQUEST_URI'], '/remote/rdp') !== false) {
    $controller->handleRDP();
}
else {
    header('HTTP/1.1 404 Not Found');
    echo "Page not found";
}
