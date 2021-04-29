# Projet TodoList
## Objectifs:
 - Installation minimale du package Symfony.
 - Voir les composants requis au fur et à mesure.
 - Gestion de données avec syst. CRUD.
 - Class Formulaires et controles (contraintes).
 - 2 entités reliées en relation ManyToOne.
 - Déploiement sur Herku.
#
# Etape #1
```bash
# etape #0 - system check et installer composants:
 symfony check:requirements
 - https://packagist.org/
 - https://flex.symfony.com/
```

## Configuration DB:
db_dev_todolist
on a besoin de doctrine = ORM.
Voir la doc: https://symfony.com/doc/current/doctrine.html puis Installing Doctrine.
```bash
# On tape symfony console doctrine et on nous donne
 composer require symfony/orm-pack
# Le fichier .env a été modifié.
# Commenter la ligne postgresql et décommenter et régler celle-ci:
 DATABASE_URL="mysql://root:@127.0.0.1:3306/db_dev_todolist"
# Puis:
 symfony console doctrine:database:create
```

# Création des entités
## Principes de relations:
 - Une TodoList appartient à une catégorie.
 - Une catégorie contient 0 ou plusieurs todo.
## Entités:
 - Category(name(string))
 - Todo(title(string), content(text), created_at(datetime), updated_at(datetime), deadline(datetime), #category)
```bash
 symfony console make:entity # Donc Category, puis ensuite Todo
# Relation (#category):
 symfony console make:entity Todo
 # On ajoute le champ category, avec type "relation" et on se laisse guider.
```
## Migrations:
```bash
 symfony console make:migration
 symfony console doctrine:migrations:migrate
```
#
# Fixtures
```bash
 composer require orm-fixtures --dev
```
## Alimenter les tables:
__NB__:
 - Voir comment définir les dates de création et update dès la création d'une todo list.
 - Constructeur de la classe Todo.
## Exécuter fixtures:
```bash
 symfony console doctrine:fixtures:load
```
### Analyse:
1. La table Category doit être remplie en premier.
    - On part d'un tableau de catégories.
    - On veut enregistrer chaque catégorie dans la table physique
    - Sous symfony, tout passe par l'objet --> voir class Category.
2. La table todo.
    - On créé un objet Todo.
    - __NB__: la méthode `setCategory()` qui a besoin d'un objet Category comme argument.
#
# Controllers
## TestController:
L'objectif est de voir le format de rendu que propose le controller, sachant que Twig n'est pas installé.
```bash
 symfony console make:controller Test
```
## Installer Twig:
```bash
 composer require twig
```
## TodoController:
```bash
 symfony console make:controller Todo
# On a une vue créée dans le dossier Template (qui n'existait pas au préalable, contrairement à avec la version full de symfony).
```
### La page d'accueil des todos:
Le controller va récupérer notre premier enregistrement de la table Todo et le passer à la vue `todo/index`
La mise en forme est gérée par des tables Bootstrap

### La page détail:
1. Une méthode et sa route:
```php
# Le repository en injection de dépendance:
 public function details($id, TodoRepository $repo): Response
```
2. Une vue dans template Todo
3. Le lien au niveau du bouton "voir" de la page d'accueil

#
# Formulaires
## Installation
```bash
 composer require form validator
```
## Generate form
1. Génération de la classe du nom que l'on veut:
```bash
 symfony console make:form
# On a choisit TodoFormType
```
2. On créé une méthode dans TodoController `create()`. On va créer le lien du bouton pour tester le cheminement jusqu'à la vue `create.html.twig`.
<!-- Problématique des routes: -->
```bash
## Besoin d'installer le profiler pour débugger
 composer require --dev symfony/profiler-pack
 symfony console debug:router
 ```
 <!-- - Voir la forme des URLs. Ex: `/todo , /todo/1 , todo/1/edit`.
 - L'ordre des placements des méthodes (dans le code, dans controller) peut influer.
 - La possibilité d'ajouter un paramètre "priority" (symfony routing priority). -->
3. Gestion du formulaire dans la méthode adéquate du controller. Affichage du formulaire dans la vue:
    - Améliorer le visuel avec bootstrap: dans config>package>twig.yaml rajouter la ligne suivante
        `form_themes: ['bootstrap_4_layout.html.twig']`
    - Problématique du champ Category: Il fait référence à une relation avec une entité. On va ajouter des types à la `class TodoFormType`.
    - Ajouter d'autres types: Voir la doc, plusieurs options possibles.
## TodoController: edit()
    - On installe un bundle dont le rôle est de faire la correspondance entre une URL avec l'id d'un objet et l'objet passé en paramètre.
```bash
 composer req sensio/framework-extra-bundle
```
## Créer un msg Flash:
    - Voir la doc: `Flash messages`
    - Une partie dans le controller: la construction du msg
    - Une partie dans la vue `update.html.twig`: l'affichage est selon le choix pris dans la doc
## TodoController - Delete:
### Méthode 1:
    - Un lien depuis la page d'accueil.
    - Ici le lien.
### Méthode 2:
    - Lien dans la page update.
    - On ajoute une confirmation en JS.
    - __NB__: Attention à l'emplacement de `{% block javascripts %}`.
#
# Ajouter une navbar
    - Un fichier `navbar.html.twig` avec une navbar BootStrap: Bouton d'accueil, titre, menu déroulant.
    - Inclure dans base.html.twig dans un block `{% block navbar %}`.

#
# Contraintes de formulaires
## Dans TodoFormType:
Voir pour inhiber le contro^le HTML5:
```php
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Todo::class,
            "attr" => [
                "novalidate" => "novalidate"
            ]
        ]);
    }
```

Voir les contraintes des champs. Ici dans le cas ou un champ est considéré comme nullable = false dans la DB:
> voir empty_data.
```php
        $builder
            ->add('title', TextType::class, [
                "label" => "Titre",
                "empty_data" =>"",
```
## Dans l'entité Todo
Ne pas oublier d'importer la classe mais pas Mozart\Assert.
Copier coller depuis la doc.
Exemple:
```php
    # La classe à importer
    use Symfony\Component\Validator\Constraints as Assert;
    /**
     * @Assert\NotBlank(message="Ce champ ne peut pas être vide!")
     * @Assert\Length(
     *      min=5,
     *      minMessage="Au moins {{ limit }} caractères"
     * )
     * @ORM\Column(type="string", length=255)
     */
    private $title;
```

#
# Version de l'appli avec SQLite
## Procédure à suivre
 1. Installer SQLite Studio.
 2. Définir la collection dans le fichier .env .
 ```bash
 DATABASE_URL="sqlite:///%kernel.project_dir%/var/todo.db"
 ```
 3. Créer ce fichier:
 ```bash
 symfony console doctrine:database:create
```
4. Créer une migration pour DB SQLite.
```bash
# Effacer les migrations actuelles puis utiliser les commandes de migration:
 symfony console make:migration
 symfony console doctrine:migrations:migrate
```
5. Fixtures:
```bash
 symfony console doctrine:fixtures:load
```
6. Tester et voir dans SQLite Studio.

#
# PostGreSQL
#
## Installation:
 1. Install de PostrGreSQL:
 ```yaml
  url : https://www.postgresql.org/download/windows/
 ```
 2. DLL dans php.ini
 ```bash
 # 2 extensions à décommenter:
  extension=pdo_pgsql
  extension=pgsql
 ```
 3. Installer l'interface pgAdmin
 4. Configurer Symfony
 ```yaml
 # dans config/packages/doctrine.yaml, ajouter:
  dbal:
    driver: "pdo_pgsql"
    charset: utf8
```
 5. Connexion à PostGreSQL dans le fichier .env
```bash
 DATABASE_URL="postgresql://postgres:papito@127.0.0.1:5432/db_pg_todolist"
```
 6. Créer la DB:
```bash
 symfony console doctrine:database:create
```
 7. Créer une migration pour DB SQLite.
```bash
# Effacer les migrations actuelles puis utiliser les commandes de migration:
 symfony console make:migration
 symfony console doctrine:migrations:migrate
```
 8. Fixtures:
```bash
 symfony console doctrine:fixtures:load
```
## Migrations et fixtures en mode prod
Aller voir dans `config/bundles.php`
```php
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['all' => true],
```
Aller dans `composer.json` et décaler cette ligne dans "require":
```js
    "doctrine/doctrine-fixtures-bundle": "^3.4"
```
Rajouter les lignes suivantes dans le `composer.json`:
```js
        "scripts": {
        "compile":[
            "php bin/console doctrine:migrations:migrate",
            "php bin/console doctrine:fixtures:load --no-interaction --env=PROD"
        ],
```
__NB__: Comme on a modifié le composer.json:
```bash
 composer update
```
## Rewriting des URLs pour Heroku:
```bash
 composer req symfony/apache-pack
```
## Fichier Procfile:
Le fichier créé suivant va dire à Heroku quel webserver est utilisé:
```bash
 echo 'web: heroku-php-apache2 public/' > Procfile
```
## Compte Heroku:
 1. Créer un compte.
 2. Installer heroku cmd line.
    > https://devcenter.heroku.com/articles/heroku-cli
 3. Depuis un terminal, taper `heroku`
## Créer une application:
 1. heroku create
    > Se logger si besoin
 2. Config en mod prod:
    ```bash
     heroku config:set APP_ENV=prod
    ```
 3. PostGreSQL
    > Dans Heroku, on doit lui dire le SGBD à utiliser. Chez Heroku, on le trouve dans les addons.
    ```bash
    # Vois aussi l'interface utilisateur d'heroku
     heroku addons:create heroku-postgresql:hobby-dev
    ```
    Après l'install, normalement, il a créé une variable d'environnement DATABASE_URL.