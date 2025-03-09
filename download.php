<?php
session_start();
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Vérifier si une session FTP est active
if (!isset($_SESSION['ftp_conn']) || $_SESSION['ftp_conn'] !== true) {
    header('Location: ftp.php');
    exit;
}

// Récupérer le chemin du fichier à télécharger
$file_path = $_GET['file'] ?? '';

if (empty($file_path)) {
    die("Erreur: Aucun fichier spécifié pour le téléchargement.");
}

// Configuration du serveur FTP
$ftp_server = "localhost"; // Remplacer par votre serveur FTP
$ftp_port = 21;
$ftp_user = $_SESSION['ftp_user'];
$ftp_pass = $_SESSION['ftp_temp_password'] ?? '';

// Connexion au serveur FTP
$ftp_conn = ftp_connect($ftp_server, $ftp_port);
if (!$ftp_conn) {
    die("Erreur: Impossible de se connecter au serveur FTP.");
}

// Tentative de connexion avec les identifiants
if (!ftp_login($ftp_conn, $ftp_user, $ftp_pass)) {
    ftp_close($ftp_conn);
    die("Erreur: Identifiants FTP invalides. Veuillez vous reconnecter.");
}

// Activer le mode passif
ftp_pasv($ftp_conn, true);

// Obtenir le nom de fichier à partir du chemin
$file_name = basename($file_path);

// Créer un fichier temporaire
$temp_file = tempnam(sys_get_temp_dir(), 'ftp_');

// Télécharger le fichier du serveur FTP
if (!ftp_get($ftp_conn, $temp_file, $file_path, FTP_BINARY)) {
    ftp_close($ftp_conn);
    unlink($temp_file);
    die("Erreur: Impossible de télécharger le fichier depuis le serveur FTP.");
}

// Fermer la connexion FTP
ftp_close($ftp_conn);

// Obtenir la taille du fichier et le type MIME
$file_size = filesize($temp_file);
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$file_type = finfo_file($finfo, $temp_file) ?: 'application/octet-stream';
finfo_close($finfo);

// Enregistrer l'activité de téléchargement (à implémenter)
// logFileDownload($_SESSION['user_id'], $file_path, $
// Enregistrer l'activité de téléchargement (à implémenter)
function logFileDownload($user_id, $file_path) {
    // Exemple d'enregistrement dans un fichier log
    $log_entry = sprintf("%s - User ID: %s downloaded %s\n", date('Y-m-d H:i:s'), $user_id, $file_path);
    file_put_contents('downloads.log', $log_entry, FILE_APPEND);
}

// Enregistrer l'activité de téléchargement
logFileDownload($_SESSION['user_id'], $file_path);

// Définir les en-têtes pour le téléchargement du fichier
header('Content-Description: File Transfer');
header('Content-Type: ' . $file_type);
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . $file_size);
header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Connection: Keep-Alive');

// Lire le fichier temporaire et le renvoyer à l'utilisateur
readfile($temp_file);

// Supprimer le fichier temporaire après le téléchargement
unlink($temp_file);
exit;
