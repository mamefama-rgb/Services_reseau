<?php
// dashboard.php - Interface principale pour l'accès aux services distants

// Inclure les fichiers nécessaires
require_once 'database.php';
require_once 'services.php';
require_once 'user.php';
require_once 'login.php';

// Vérifier l'authentification
$auth = new Auth($db);
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $auth->getCurrentUser();
$remoteManager = new RemoteAccessManager($db);

// Récupérer les machines accessibles par l'utilisateur
$linuxMachines = $remoteManager->getAvailableMachines('linux');
$windowsMachines = $remoteManager->getAvailableMachines('windows');

// Traiter les demandes de connexion
$connectionLink = null;
$connectionError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['connect']) && isset($_POST['machine_id']) && isset($_POST['connection_type'])) {
        $machineId = $_POST['machine_id'];
        $connectionType = $_POST['connection_type'];
        
        switch ($connectionType) {
            case 'ssh':
                $connectionLink = $remoteManager->generateSSHLink($machineId, $user['id']);
                break;
            case 'vnc':
                $connectionLink = $remoteManager->generateVNCLink($machineId, $user['id']);
                break;
            case 'rdp':
                $connectionLink = $remoteManager->generateRDPLink($machineId, $user['id']);
                break;
        }
        
        if (isset($connectionLink['error'])) {
            $connectionError = $connectionLink['error'];
            $connectionLink = null;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smarttech - Accès distant</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <h1>Accès Distant aux Machines</h1>
        
        <?php if ($connectionError): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($connectionError); ?></div>
        <?php endif; ?>
        
        <?php if ($connectionLink): ?>
            <div class="alert alert-success">
                Connexion établie avec <?php echo htmlspecialchars($connectionLink['machine']); ?>
                <a href="<?php echo htmlspecialchars($connectionLink['url']); ?>" class="btn btn-primary ms-3" target="_blank">
                    Ouvrir la connexion
                </a>
            </div>
        <?php endif; ?>
        
        <div class="row mt-4">
            <!-- Machines Linux -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h5 mb-0">Machines Linux</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($linuxMachines)): ?>
                            <p class="text-muted">Aucune machine Linux disponible</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($linuxMachines as $machine): ?>
                                    <div class="list-group-item">
                                        <h5><?php echo htmlspecialchars($machine['name']); ?></h5>
                                        <p class="small text-muted mb-2"><?php echo htmlspecialchars($machine['description']); ?></p>
                                        <form method="post" class="d-flex gap-2">
                                            <input type="hidden" name="machine_id" value="<?php echo $machine['id']; ?>">
                                            <button type="submit" name="connection_type" value="ssh" class="btn btn-sm btn-outline-dark">
                                                <i class="bi bi-terminal"></i> SSH
                                            </button>
                                            <button type="submit" name="connection_type" value="vnc" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-display"></i> VNC
                                            </button>
                                            <input type="hidden" name="connect" value="1">
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Machines Windows -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h2 class="h5 mb-0">Machines Windows</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($windowsMachines)): ?>
                            <p class="text-muted">Aucune machine Windows disponible</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($windowsMachines as $machine): ?>
                                    <div class="list-group-item">
                                        <h5><?php echo htmlspecialchars($machine['name']); ?></h5>
                                        <p class="small text-muted mb-2"><?php echo htmlspecialchars($machine['description']); ?></p>
                                        <form method="post">
                                            <input type="hidden" name="machine_id" value="<?php echo $machine['id']; ?>">
                                            <button type="submit" name="connection_type" value="rdp" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-windows"></i> RDP
                                            </button>
                                            <input type="hidden" name="connect" value="1">
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Historique des connexions -->
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                <h2 class="h5 mb-0">Historique des connexions</h2>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Machine</th>
                            <th>Type d'accès</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Les logs de connexion seraient affichés ici -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
