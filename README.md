# Gestion des Notes de Frais Professionnels

## Description

Cette application permet de gérer les notes dssss tmax 530 m3a la7nech a fonds sds frais professionnels. Elle est construite avec Symfony 6.4 et API Platform 3.4, en utilisant LexikJWT pour l'authentification. Des fixtures ont été utilisées pour générer des données initiales dans la base de données test de commit .

## Fonctionnalités principales

### Notes de Frais

- **Créer une note de frais** : `POST /api/frais`
- **Consulter une note de frais spécifique** : `GET /api/frais/{id}`
- **Modifier une note de frais** : `PUT /api/frais/{id}`
- **Supprimer une note de frais** : `DELETE /api/frais/{id}`
- **Lister les notes de frais de l'utilisateur connecté** : `GET /api/mes-frais`

## Prérequis

- PHP >= 8.1.5
- Symfony CLI
- Composer
- Une base de données compatible MySQL/POST

## Technologies utilisées

- Symfony 6.4
- API Platform ^3.4
- LexikJWTAuthenticationBundle
- Doctrine ORM
- DataFixtures
- PHPUnit

## Installation

### Étapes de configuration

1. Clonez le projet :

   ```bash
   git clone https://github.com/yassinsaied/gestion-frais.git
   cd gestion-frais
   ```

2. Installez les dépendances PHP :

   ```bash
   composer install
   ```

3. Configurez vos variables d'environnement dans le fichier `.env` :

   ```dotenv
   DATABASE_URL=mysql://root:@127.0.0.1:3306/<nome de la base dennées>
   JWT_SECRET_KEY=config/jwt/private.pem
   JWT_PUBLIC_KEY=config/jwt/public.pem
   JWT_PASSPHRASE=<phras secret>
   ```

   tmax 530

4. Générez les clés JWT :

   ```bash
   php bin/console lexik:jwt:generate-keypair
   ```

5. Configurez la base de données et appliquez les migrations :

   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

6. Configurez l'environnement de test dans le fichier `.env.test` :

   ```dotenv
   DATABASE_URL="mysql://root:@127.0.0.1:3306/expense_management"
   SYMFONY_DEPRECATIONS_HELPER=disabled=1
   ```

7. Chargez les données de la base de données :

   ```bash
   php bin/console doctrine:fixtures:load
   ```

8. Chargez les données de test :
   ```bash
   php bin/console doctrine:fixtures:load --env=test --no-interaction
   ```

## Lancement du serveur de développement

Démarrez le serveur Symfony :

```bash
symfony server:start
```

Accédez à l'application à l'adresse [http://localhost:8000](http://localhost:8000).

## Authentification

L'authentification est gérée avec LexikJWT. Pour obtenir un jeton JWT, envoyez une requête `POST` à l'endpoint `/api/login_check` avec les informations d'identification de l'utilisateur :

```json
{
  "username": "votre-email@example.com",
  "password": "votre-mot-de-passe"
}
```

## Tests

Exécutez les tests avec PHPUnit et test integration done :

```bash
php bin/phpunit tests/Api/FraisApiTest.php
```

## Endpoints

### Notes de Frais

- `POST /api/frais` : Création d'une note de frais
- `GET /api/frais/{id}` : Consultation d'une note de frais spécifique
- `PUT /api/frais/{id}` : Modification d'une note de frais
- `DELETE /api/frais/{id}` : Suppression d'une note de frais
- `GET /api/mes-frais` : Liste des notes de frais de l'utilisateur connecté

## Documentation API

Une collection Postman complète est disponible à la racine du projet (`frais_collection.json`). Pour l'utiliser :

1. Ouvrez Postman
2. Cliquez sur "Import"
3. Glissez-déposez le fichier `frais_collection.json` ou sélectionnez-le depuis votre ordinateur
4. La collection sera importée avec tous les endpoints configurés :
   - Authentication (Login) .
   - Création, consultation, modification et suppression des notes de frais .
   - Liste des frais personnalisée pour l'utilisateur connecté .

Variables d'environnement à configurer dans Postman :

- `base_url` : URL de base de votre API (par défaut : http://votre-api.com/api)
- `token` : JWT token (sera automatiquement rempli après l'authentification)
