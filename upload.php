<?php
include 'db_connect.php';

// Vérification si un fichier a été uploadé
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $documentType = $_POST['documentType'];
    $employeeName = $_POST['employeeName'];

    // Gestion de l'upload de fichier
    $target_dir = "assets/uploads/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Vérifier si le fichier existe déjà
    if (file_exists($target_file)) {
        echo "Désolé, le fichier existe déjà.";
        $uploadOk = 0;
    }

    // Vérifier la taille du fichier
    if ($_FILES["file"]["size"] > 50000000) { // Limite de 500 Ko
        echo "Désolé, votre fichier est trop grand.";
        $uploadOk = 0;
    }

    // Autoriser certains formats de fichiers
    if ($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg" && $fileType != "gif" && $fileType != "pdf") {
        echo "Désolé, seuls les fichiers JPG, JPEG, PNG, GIF & PDF sont autorisés.";
        $uploadOk = 0;
    }

    // Vérifier si $uploadOk est à 0 en raison d'une erreur
    if ($uploadOk == 0) {
        echo "Désolé, votre fichier n'a pas été uploadé.";
    } else {
        // Essayer de déplacer le fichier téléchargé dans le répertoire cible
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            // Préparer et lier
            $stmt = $conn->prepare("INSERT INTO documents (title, description, documentType, employeeName) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $title, $description, $documentType, $employeeName);

            // Exécuter la requête
            if ($stmt->execute()) {
                echo "Le fichier a été uploadé avec succès et les données ont été enregistrées.";
            } else {
                echo "Erreur : " . $stmt->error;
            }

            // Fermer la déclaration
            $stmt->close();
        } else {
            echo "Désolé, une erreur s'est produite lors de l'upload de votre fichier.";
        }
    }
}

// Fermer la connexion
$conn->close();
?>
