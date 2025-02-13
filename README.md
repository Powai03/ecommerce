# 🛒 E-Commerce - Plateforme fictive en Symfony

Bienvenue sur le dépôt de mon projet **e-commerce** !  
Il s'agit d'une plateforme fictive développée avec **Symfony**, permettant de gérer des produits, des commandes et des utilisateurs.  

⚠️ **Note :** Ce projet est en développement et ne représente pas une plateforme e-commerce réelle.  

## 🚀 Technologies utilisées  

- 🐘 **PHP 8+** – Langage backend  
- 🎵 **Symfony 6+** – Framework MVC robuste et évolutif  
- 🗄️ **Doctrine ORM** – Gestion de la base de données   
- 🛢️ **MySQL** – Base de données 
- 🔐 **Security Bundle** – Gestion des utilisateurs et de l'authentification  

## 📦 Installation & Exécution  

Si vous souhaitez exécuter ce projet en local, voici les étapes :  

### 1️⃣ Cloner le dépôt  
```bash
git clone https://github.com/Powai03/ecommerce.git
cd ecommerce
```

### 2️⃣ Installer les dépendances  
```bash
composer install
npm install  
```

### 3️⃣ Configurer l’environnement  
Copiez le fichier `.env` et configurez votre base de données :  
```bash
cp .env .env.local
```
Modifiez ensuite `.env.local` pour ajouter vos informations de connexion à la base de données :  
```
DATABASE_URL="mysql://user:password@127.0.0.1:3306/ecommerce"
```

### 4️⃣ Créer la base de données et exécuter les migrations  
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5️⃣ Lancer le serveur Symfony  
```bash
symfony server:start
```
L'application sera accessible sur **http://127.0.0.1:8000**.  

## 📌 Fonctionnalités  

✅ Ajout, modification et suppression de produits  
✅ Gestion des utilisateurs et authentification  
✅ Panier d'achat et système de commande  
✅ Interface responsive avec **Tailwind CSS**  
✅ Sécurisation des routes et permissions  

## 🚧 Statut  

Le projet est **en développement**. De nouvelles fonctionnalités pourront être ajoutées prochainement.  

