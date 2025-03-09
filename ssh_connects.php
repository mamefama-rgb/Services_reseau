<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartTech - Services Distants</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            height: 100vh; /* Prendre toute la hauteur de la fenêtre */
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background-color: #0d6efd;
            color: white;
            padding: 10px 0;
            margin-bottom: 30px;
        }
        .navbar-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }
        .navbar-nav {
            display: flex;
            list-style: none;
        }
        .nav-item {
            margin-right: 20px;
        }
        .nav-link {
            color: white;
            text-decoration: none;
        }
        .nav-link.active {
            font-weight: bold;
            text-decoration: underline;
        }
        .terminal-window {
            background-color: #000;
            color: #0f0;
            border-radius: 5px;
            padding: 20px;
            margin: 0; /* Supprimer la marge */
            font-family: monospace;
            height: calc(100% - 100px); /* Ajuster la hauteur pour prendre toute la page */
            overflow: auto;
            flex-grow: 1; /* Prendre toute la hauteur restante */
        }
        .window-title {
            background-color: #333;
            color: white;
            padding: 10px;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
            margin: 0;
            font-weight: bold;
        }
        .prompt {
            margin-bottom: 10px;
        }
        .command {
            margin-bottom: 15px;
        }
        .response {
            margin-bottom: 15px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="terminal-window">
        <div class="window-title">Session SSH - Terminal</div>
        <div class="prompt">$ <span style="color: #0d6efd;">ssh</span> mamefama@mamefama</div>
        <div class="response">The authenticity of host 'mamefama' can't be established.<br>
        ECDSA key fingerprint is <strong>SHA256:h8JdK2lMn5pQy7zXkL8dF3hGfr5Hj9sK</strong>.<br>
        Are you sure you want to continue connecting (yes/no/[fingerprint])? <span style="color: #0d6efd;">yes</span></div>
        <div class="prompt">Warning: Permanently added 'mamefama' (ECDSA) to the list of known hosts.</div>
        <div class="prompt">mamefama@mamefama's password: </div>
        <div class="response">Welcome to Ubuntu 22.04.3 LTS (GNU/Linux 5.15.0-91-generic x86_64)<br>
        <em>* Documentation:</em> <a href="https://help.ubuntu.com" style="color: #0d6efd;">https://help.ubuntu.com</a><br>
        <em>* Management:</em> <a href="https://landscape.canonical.com" style="color: #0d6efd;">https://landscape.canonical.com</a><br>
        <em>* Support:</em> <a href="https://ubuntu.com/advantage" style="color: #0d6efd;">https://ubuntu.com/advantage</a><br>
        Last login: Sun Mar 9 14:32:24 2025 from 192.168.1.45</div>
        <div class="prompt">mamefama@mamefama:~$ </div>
        <div class="command">ls -la</div>
        <div class="response">total 32<br>
        drwxr-xr-x 4 mamefama mamefama 4096 Mar  9 14:32 .<br>
        drwxr-xr-x 3 root    root    4096 Feb 15 09:23 ..<br>
        -rw------- 1 mamefama mamefama  287 Mar  9 14:32 .bash_history<br>
        -rw-r--r-- 1 mamefama mamefama  220 Feb 15 09:23 .bash_logout<br>
        -rw-r--r-- 1 mamefama mamefama  3771 Feb 15 09:23 .bashrc<br>
        drwxrwxr-x 3 mamefama mamefama 4096 Feb 15 10:45 Documents<br>
        drwxrwxr-x 2 mamefama mamefama 4096 Feb 28 16:32 Projects<br>
        -rw-r--r-- 1 mamefama mamefama  807 Feb 15 09:23 .profile</div>
        <div class="prompt">mamefama@mamefama:~$ </div>
    </div>
</body>
</html>
