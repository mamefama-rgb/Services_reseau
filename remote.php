<?php
session_start();
require_once 'db_connect.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Récupération des connexions distantes
$stmt = $pdo->prepare("SELECT r.*, c.companyName, c.contactName 
                      FROM remote_connections r
                      LEFT JOIN clients c ON r.clientId = c.id
                      ORDER BY r.created_at DESC");
$stmt->execute();
$connections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération de tous les clients (pour le formulaire)
$stmt_clients = $pdo->query("SELECT id, companyName, contactName FROM clients ORDER BY companyName ASC");
$clients = $stmt_clients->fetchAll(PDO::FETCH_ASSOC);

// Types de connexions distantes
$connection_types = [
    'rdp' => 'Bureau à distance (RDP)',
    'vnc' => 'VNC',
    'ssh' => 'SSH',
    'ftp' => 'FTP/SFTP',
    'db' => 'Base de données',
    'other' => 'Autre'
];

// Traitement du formulaire d'ajout de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_connexion'])) {
    $clientId = !empty($_POST['clientId']) ? intval($_POST['clientId']) : null;
    $connectionName = trim($_POST['connectionName']);
    $connectionType = trim($_POST['connectionType']);
    $serverAddress = trim($_POST['serverAddress']);
    $port = trim($_POST['port']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $notes = trim($_POST['notes']);
    
    // Validation des données
    $erreurs = [];
    if (empty($connectionName)) $erreurs[] = "Le nom de la connexion est obligatoire";
    if (empty($serverAddress)) $erreurs[] = "L'adresse du serveur est obligatoire";
    
    if (empty($erreurs)) {
        try {
            // Cryptage du mot de passe si fourni
            $secured_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
            
            $stmt = $pdo->prepare("INSERT INTO remote_connections (clientId, connectionName, connectionType, serverAddress, port, username, password, notes) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$clientId, $connectionName, $connectionType, $serverAddress, $port, $username, $secured_password, $notes]);
            
            $_SESSION['message'] = "La connexion distante a été ajoutée avec succès";
            header('Location: remote.php');
            exit;
        } catch (PDOException $e) {
            $erreurs[] = "Erreur lors de l'ajout de la connexion: " . $e->getMessage();
        }
    }
}

// Traitement de la suppression d'une connexion
if (isset($_GET['supprimer']) && is_numeric($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM remote_connections WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['message'] = "La connexion distante a été supprimée avec succès";
    } catch (PDOException $e) {
        $_SESSION['erreur'] = "Erreur lors de la suppression: " . $e->getMessage();
    }
    
    header('Location: remote.php');
    exit;
}

// Inclusion du header
include 'header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smarttech - Accès Distant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstr>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Smarttech</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" d>
                <span class="navbar-toggler-icon"></span>

    <h1>Gestion des accès distants</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['message']; 
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['erreur'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['erreur']; 
            unset($_SESSION['erreur']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($erreurs) && !empty($erreurs)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($erreurs as $erreur): ?>
                    <li><?= $erreur ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>


<div class="container mt-4">
    
    <!-- Formulaire d'ajout de connexion -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Ajouter une connexion distante</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="clientId" class="form-label">Client</label>
                    <select class="form-select" id="clientId" name="clientId">
                        <option value="">Pas de client spécifique</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id'] ?>">
                                <?= htmlspecialchars($client['companyName']) ?> - <?= htmlspecialchars($client['contactName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="connectionName" class="form-label">Nom de la connexion *</label>
                        <input type="text" class="form-control" id="connectionName" name="connectionName" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="connectionType" class="form-label">Type de connexion</label>
                        <select class="form-select" id="connectionType" name="connectionType">
                            <?php foreach ($connection_types as $key => $value): ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="serverAddress" class="form-label">Adresse du serveur/IP *</label>
                        <input type="text" class="form-control" id="serverAddress" name="serverAddress" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="port" class="form-label">Port</label>
                        <input type="text" class="form-control" id="port" name="port">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="username" name="username">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <div class="form-text">Les mots de passe sont cryptés avant d'être stockés.</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes additionnelles</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <button type="submit" name="ajouter_connexion" class="btn btn-primary">Ajouter la connexion</button>
            </form>
        </div>
    </div>
    
    <!-- Liste des connexions -->
    <div class="card">
        <div class="card-header">
            <h5>Liste des connexions distantes</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Serveur</th>
                            <th>Port</th>
                            <th>Utilisateur</th>
                            <th>Date d'ajout</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($connections as $connection): ?>
                            <tr>
                                <td><?= $connection['id'] ?></td>
                                <td>
                                    <?php if ($connection['clientId']): ?>
                                        <?= htmlspecialchars($connection['companyName']) ?><br>
                                        <small><?= htmlspecialchars($connection['contactName']) ?></small>
                                    <?php else: ?>
                                        <em>Non spécifié</em>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($connection['connectionName']) ?></td>
                                <td>
                                    <?= isset($connection_types[$connection['connectionType']]) ? $connection_types[$connection['connectionType']] : $connection['connectionType'] ?>
                                </td>
                                <td><?= htmlspecialchars($connection['serverAddress']) ?></td>
                                <td><?= htmlspecialchars($connection['port']) ?></td>
                                <td><?= htmlspecialchars($connection['username']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($connection['created_at'])) ?></td>
                                <td>
                                    <a href="remote_details.php?id=<?= $connection['id'] ?>" class="btn btn-info btn-sm">Voir</a>
                                    <a href="remote_edit.php?id=<?= $connection['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                                    <a href="remote.php?supprimer=<?= $connection['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette connexion ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($connections)): ?>
                            <tr>
                                <td colspan="9" class="text-center">Aucune connexion distante enregistrée</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
