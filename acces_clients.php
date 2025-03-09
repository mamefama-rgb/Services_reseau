<?php
// templates/ssh_client.php - Template pour le client SSH
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/remote.css">
    <!-- Inclure les bibliothèques WebSSH -->
    <script src="assets/js/xterm.js"></script>
    <script src="assets/js/webssh.js"></script>
</head>
<body class="bg-dark">
    <div class="container-fluid p-0">
        <div class="remote-header bg-secondary text-white p-2 d-flex justify-content-between">
            <h4><?php echo htmlspecialchars("SSH: {$websshConfig['host']}"); ?></h4>
            <div>
                <button id="disconnect" class="btn btn-sm btn-danger">Déconnecter</button>
                <button id="fullscreen" class="btn btn-sm btn-info">Plein écran</button>
            </div>
        </div>
        
        <div id="terminal-container" class="p-0"></div>
    </div>

    <script>
        // Configuration WebSSH
        const config
