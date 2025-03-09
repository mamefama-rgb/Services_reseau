<?php
// Exemple de code pour l'intégration des services d'accès distant dans la plateforme web

class RemoteAccessManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Récupérer la liste des machines disponibles
    public function getAvailableMachines($type = null) {
        $query = "SELECT * FROM machines";
        if ($type) {
            $query .= " WHERE os_type = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$type]);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Générer un lien SSH (via WebSSH)
    public function generateSSHLink($machineId, $userId) {
        $machine = $this->getMachineById($machineId);
        if (!$machine || $machine['os_type'] != 'linux') {
            return false;
        }
        
        // Vérifier les autorisations
        if (!$this->checkPermission($userId, $machineId, 'ssh')) {
            return ['error' => 'Permission refusée'];
        }
        
        // Enregistrer la tentative de connexion
        $this->logAccess($userId, $machineId, 'ssh');
        
        // Générer un token temporaire pour la session
        $token = $this->generateSecureToken();
        
        // Stocker le token avec les paramètres de connexion
        $this->storeConnectionParams($token, [
            'host' => $machine['ip_address'],
            'port' => $machine['ssh_port'],
            'username' => $machine['default_user'],
            'type' => 'ssh'
        ]);
        
        return [
            'url' => '/remote/ssh?token=' . $token,
            'machine' => $machine['name']
        ];
    }
    
    // Générer un lien VNC/NoVNC
    public function generateVNCLink($machineId, $userId) {
        $machine = $this->getMachineById($machineId);
        if (!$machine || $machine['os_type'] != 'linux') {
            return false;
        }
        
        // Vérifier les autorisations
        if (!$this->checkPermission($userId, $machineId, 'vnc')) {
            return ['error' => 'Permission refusée'];
        }
        
        // Enregistrer la tentative de connexion
        $this->logAccess($userId, $machineId, 'vnc');
        
        // Générer un token temporaire pour la session
        $token = $this->generateSecureToken();
        
        // Stocker le token avec les paramètres de connexion
        $this->storeConnectionParams($token, [
            'host' => $machine['ip_address'],
            'port' => $machine['vnc_port'],
            'password' => $this->getEncryptedVNCPassword($machine['id']),
            'type' => 'vnc'
        ]);
        
        return [
            'url' => '/remote/novnc?token=' . $token,
            'machine' => $machine['name']
        ];
    }
    
    // Générer un lien RDP
    public function generateRDPLink($machineId, $userId) {
        $machine = $this->getMachineById($machineId);
        if (!$machine || $machine['os_type'] != 'windows') {
            return false;
        }
        
        // Vérifier les autorisations
        if (!$this->checkPermission($userId, $machineId, 'rdp')) {
            return ['error' => 'Permission refusée'];
        }
        
        // Enregistrer la tentative de connexion
        $this->logAccess($userId, $machineId, 'rdp');
        
        // Générer un token temporaire pour la session
        $token = $this->generateSecureToken();
        
        // Stocker le token avec les paramètres de connexion
        $this->storeConnectionParams($token, [
            'host' => $machine['ip_address'],
            'port' => $machine['rdp_port'],
            'username' => $machine['default_user'],
            'type' => 'rdp'
        ]);
        
        return [
            'url' => '/remote/rdp?token=' . $token,
            'machine' => $machine['name']
        ];
    }
    
    // Récupérer les détails d'une machine
    private function getMachineById($machineId) {
        $stmt = $this->db->prepare("SELECT * FROM machines WHERE id = ?");
        $stmt->execute([$machineId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Vérifier les autorisations d'accès
    private function checkPermission($userId, $machineId, $accessType) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM access_permissions 
                                    WHERE user_id = ? AND machine_id = ? AND access_type = ? AND active = 1");
        $stmt->execute([$userId, $machineId, $accessType]);
        return $stmt->fetchColumn() > 0;
    }
    
    // Enregistrer les accès pour l'audit
    private function logAccess($userId, $machineId, $accessType) {
        $stmt = $this->db->prepare("INSERT INTO access_logs (user_id, machine_id, access_type, access_time, ip_address) 
                                    VALUES (?, ?, ?, NOW(), ?)");
        $stmt->execute([$userId, $machineId, $accessType, $_SERVER['REMOTE_ADDR']]);
    }
    
    // Générer un token sécurisé
    private function generateSecureToken() {
        return bin2hex(random_bytes(32));
    }
    
    // Stocker les paramètres de connexion temporairement
    private function storeConnectionParams($token, $params) {
        $stmt = $this->db->prepare("INSERT INTO connection_tokens (token, params, created_at, expires_at) 
                                    VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
        $stmt->execute([$token, json_encode($params)]);
    }
    
    // Récupérer le mot de passe VNC (déchiffré à la volée)
    private function getEncryptedVNCPassword($machineId) {
        $stmt = $this->db->prepare("SELECT encrypted_vnc_password FROM machine_credentials WHERE machine_id = ?");
        $stmt->execute([$machineId]);
        $encryptedPassword = $stmt->fetchColumn();
        
        // Dans un environnement réel, utilisez une méthode de déchiffrement sécurisée
        // Ceci est un exemple simplifié
        return $this->decryptPassword($encryptedPassword);
    }
    
    // Déchiffrer un mot de passe (exemple simplifié)
    private function decryptPassword($encryptedPassword) {
        // Implémentez votre logique de déchiffrement sécurisée ici
        // Utilisez une bibliothèque cryptographique comme sodium
        return "decrypted_password"; // Exemple simplifié
    }
}
