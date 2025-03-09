<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portail Intranet - Smarttech</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            font-weight: bold;
        }
        .service-icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Smarttech - Portail Intranet</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/employees/">Gestion des employés</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/documents/">Documents</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/clients/">Clients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/profile/">Mon profil</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="text-center mb-4">Services internes Smarttech</h1>
                <p class="text-center">Bienvenue sur le portail intranet de Smarttech. Accédez à tous les services depuis cette interface centralisée.</p>
            </div>
        </div>

        <div class="row">
            <!-- Application Web -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">Application de gestion</div>
                    <div class="card-body text-center">
                        <div class="service-icon">📊</div>
                        <h5 class="card-title">Gestion des employés et clients</h5>
                        <p class="card-text">Accédez à l'application web pour gérer les employés, les clients et les documents.</p>
                        <a href="/employees/" class="btn btn-outline-success">Accéder</a>
                    </div>
                </div>
            </div>
            
            <!-- Serveur FTP -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">Partage de fichiers</div>
                    <div class="card-body text-center">
                        <div class="service-icon">📁</div>
                        <h5 class="card-title">Serveur FTP</h5>
                        <p class="card-text">Téléchargez et partagez des fichiers via notre serveur FTP sécurisé.</p>
                        <a href="ftp://ftp.smarttech.local" class="btn btn-outline-primary">Accéder via FTP</a>
                        <a href="/web-ftp/" class="btn btn-outline-primary mt-2">Accès Web</a>
                    </div>
                </div>
            </div>
            
            <!-- Messagerie -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">Messagerie</div>
                    <div class="card-body text-center">
                        <div class="service-icon">✉️</div>
                        <h5 class="card-title">iRedMail</h5>
                        <p class="card-text">Consultez vos emails professionnels et communiquez avec vos collègues.</p>
                        <a href="https://mail.smarttech.local" class="btn btn-outline-info">Webmail</a>
                    </div>
                </div>
            </div>
            
            <!-- SSH -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-dark text-white">Terminal distant</div>
                    <div class="card-body text-center">
                        <div class="service-icon">💻</div>
                        <h5 class="card-title">SSH</h5>
                        <p class="card-text">Connexion sécurisée pour administrer les serveurs à distance.</p>
                        <a href="/web-ssh/" class="btn btn-outline-dark">Web SSH</a>
                        <p class="mt-2 small">Ou utilisez votre client SSH : ssh.smarttech.local</p>
                    </div>
                </div>
            </div>
            
            <!-- VNC/NoVNC -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">Bureau à distance</div>
                    <div class="card-body text-center">
                        <div class="service-icon">🖥️</div>
                        <h5 class="card-title">VNC/NoVNC</h5>
                        <p class="card-text">Accédez à un bureau distant via votre navigateur web.</p>
                        <a href="/novnc/" class="btn btn-outline-warning">NoVNC (Web)</a>
                        <p class="mt-2 small">Ou utilisez votre client VNC : vnc.smarttech.local</p>
                    </div>
                </div>
            </div>
            
            <!-- RDP -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-danger text-white">Bureau Windows</div>
                    <div class="card-body text-center">
                        <div class="service-icon">🪟</div>
                        <h5 class="card-title">RDP</h5>
                        <p class="card-text">Connexion aux postes Windows via le protocole RDP.</p>
                        <a href="/web-rdp/" class="btn btn-outline-danger">Web RDP</a>
                        <p class="mt-2 small">Ou utilisez votre client RDP : rdp.smarttech.local</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Smarttech</h5>
                    <p>Plateforme intranet pour la gestion des employés et le partage de fichiers.</p>
                </div>
                <div class="col-md-3">
                    <h5>Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li><a href="/help/" class="text-white">Aide</a></li>
                        <li><a href="/support/" class="text-white">Support technique</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li>Email: support@smarttech.local</li>
                        <li>Tel: 0123456789</li>
                    </ul>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <p class="mb-0">© 2025 Smarttech. Tous droits réservés.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
