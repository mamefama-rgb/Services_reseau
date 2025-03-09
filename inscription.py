from flask import Flask, render_template, request, redirect, url_for, flash
import smtplib
import sqlite3
from werkzeug.security import generate_password_hash

app = Flask(__name__)
app.secret_key = 'votre_cle_secrete'  # Remplacez par une clé secrète

# Fonction pour se connecter à la base de données
def get_db_connection():
    conn = sqlite3.connect('users.db')
    conn.row_factory = sqlite3.Row
    return conn

# Route pour la page d'inscription
@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        email = request.form['email']
        role = request.form.get('role', 'employee')

        if not username or not password or not email:
            flash("Veuillez remplir tous les champs.")
        else:
            hashed_password = generate_password_hash(password)
            conn = get_db_connection()
            conn.execute('INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)',
                         (username, hashed_password, email, role))
            conn.commit()
            conn.close()

            send_confirmation_email(email, username, password)

            flash("Inscription réussie ! Un e-mail de confirmation a été envoyé.")
            return redirect(url_for('register'))

    return render_template('register.html')

# Fonction pour envoyer l'e-mail
def send_confirmation_email(to, username, password):
    smtp_host = 'localhost'  # ou l'adresse de votre serveur SMTP
    smtp_port = 587  # Port SMTP
    smtp_user = 'fama@mai.smattech.sn'  # Adresse e-mail de l'expéditeur
    smtp_pass = 'Passer@123'  # Mot de passe de l'adresse e-mail

    subject = 'Confirmation d\'inscription'
    message = f"Bienvenue sur la plateforme Smarttech!\n\nVoici vos informations de connexion :\nNom d'utilisateur : {username}\nMot de passe : {password}\n"

    try:
        with smtplib.SMTP(smtp_host, smtp_port) as server:
            server.starttls()
            server.login(smtp_user, smtp_pass)
            server.sendmail(smtp_user, to, f'Subject: {subject}\n\n{message}')
    except Exception as e:
        print(f'Erreur lors de l\'envoi de l\'email : {e}')

# Page de démarrage
@app.route('/')
def index():
    return redirect(url_for('register'))

if __name__ == '__main__':
    app.run(debug=True)
