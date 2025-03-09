<?php
session_start();
require_once 'db_connect.php'; // Assurez-vous d'avoir une connexion PDO ici
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'employee'; // Par défaut, le rôle est 'employee'
    
    if (empty($username) || empty($password) || empty($email)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Hacher le mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Préparer la requête d'insertion
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES(?, ?, ?, ?)";

            // Exécuter la requête
            if ($stmt->execute([$username, $hashed_password, $email, $role])) {
                // Envoi de l'e-mail de confirmation
                if (sendConfirmationEmail($email, $username, $password)) {
                    $success = "Inscription réussie ! Un e-mail de confirmation a été envoyé.";
                } else {
                    $success = "Inscription réussie ! Mais l'envoi de l'e-mail a échoué.";
                }
            } else {
                $error = "Erreur lors de l'inscription. Veuillez réessayer.";
  }
        } catch (PDOException $e) {
            $error = "Erreur de base de données: " . $e->getMessage();
        }
    }
}

function sendConfirmationEmail($to, $username, $password) {
    $from = "smarttech@smattech.sn";
    $subject = 'Confirmation d\'inscription';
    
    // Message en HTML pour une meilleure présentation
    $message_html = '
    <html>
    <head>
        <title>Confirmation d\'inscription</title>
    </head>
    <body>
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h2 style="color: #333;">Bienvenue sur la plateforme Smarttech!</h2>
            <p>Votre compte a été créé avec succès.</p>
            <p>Voici vos informations de connexion :</p>
            <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <p><strong>Nom d\'utilisateur :</strong> '.$username.'</p>
                <p><strong>Mot de passe :</strong> '.$password.'</p>
            </div>
            <p>Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe aprés votre premiére connexion.</p>
            <p>Cordialement,<br>L\'équipe Smarttech</p>  </div>
    </body>
    </html>';
    
    // Message alternatif en texte brut pour les clients qui ne supportent pas le HT>
    $message_text = "Bienvenue sur la plateforme Smarttech!\n\n";
    $message_text .= "Voici vos informations de connexion :\n";
    $message_text .= "Nom d'utilisateur : $username\n";
    $message_text .= "Mot de passe : $password\n\n";
    $message_text .= "Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe aprés votre premiére connexion.\n\n";
    $message_text .= "Cordialement,\nL'équipe Smarttech";
    
    // En-têtes de l'e-mail
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"boundary\"\r\n";
    
    // Corps du message avec les deux versions (texte et HTML)
    $body = "--boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $message_text . "\r\n\r\n";
    $body .= "--boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $message_html . "\r\n\r\n";
    $body .= "--boundary--";
    
    // Envoi de l'e-mail via la fonction mail()

  return mail($to, $subject, $body, $headers);
}
?>

<!-- Formulaire HTML -->
<?php if ($error): ?>
    <div style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color: green; margin-bottom: 15px;"><?php echo $success; ?></div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Smarttech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
		   <h3 class="text-center">Inscription</h3>
                    <div class="card-body">
                    <?php if ($error): ?>
                       <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                        <form action="" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Rôle</label>
                                <select name="role" class="form-select">
                                    <option value="employee">Employé</option>
                                    <option value="admin">Administrateur</option>
                                    <option value="manager">Manageur</option>       
                         </select>
                            </div>
                         <div class="d-grid">
                            <button type="submit" class="btn btn-primary">S'inscrire</button>
                          </div> 
                       </form>
                    </div>
                </div>
            </div>
        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
