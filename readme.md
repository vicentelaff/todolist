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