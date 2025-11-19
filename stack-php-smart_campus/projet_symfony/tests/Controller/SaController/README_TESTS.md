# Tests pour AjouterSaController

Ce document explique comment exécuter les tests pour la fonctionnalité d'ajout de système d'acquisition (SA).

## Prérequis

- Docker et Docker Compose installés
- Les conteneurs Docker doivent être en cours d'exécution
- PHPUnit installé (déjà fait via `composer require --dev phpunit/phpunit symfony/test-pack`)

## Configuration de la base de données de test

La base de données de test a déjà été configurée avec les commandes suivantes :

```powershell
# Créer la base de données de test avec les permissions appropriées
docker exec -it smart_campus_db mariadb -u root -psmart_campus_root_password -e "CREATE DATABASE IF NOT EXISTS smart_campus_test; GRANT ALL PRIVILEGES ON smart_campus_test.* TO 'smart_campus_user'@'%'; FLUSH PRIVILEGES;"

# Appliquer les migrations sur la base de données de test
docker exec -it smart_campus_php php projet_symfony/bin/console doctrine:migrations:migrate --env=test --no-interaction
```

## Exécution des tests

### Exécuter tous les tests de AjouterSaController

```powershell
cd c:\Users\KoX\smart-campus\stack-php-smart_campus
docker exec -it -w /var/www/html/projet_symfony smart_campus_php php bin/phpunit tests/Controller/SaController/AjouterSaControllerTest.php
```

### Exécuter avec une sortie détaillée (testdox)

```powershell
cd c:\Users\KoX\smart-campus\stack-php-smart_campus
docker exec -it -w /var/www/html/projet_symfony smart_campus_php php bin/phpunit tests/Controller/SaController/AjouterSaControllerTest.php --testdox
```

### Exécuter un test spécifique

```powershell
cd c:\Users\KoX\smart-campus\stack-php-smart_campus
docker exec -it -w /var/www/html/projet_symfony smart_campus_php php bin/phpunit tests/Controller/SaController/AjouterSaControllerTest.php --filter testAjouterUnSeulSa
```

### Utiliser le script PowerShell

Un script PowerShell a été créé pour faciliter l'exécution des tests :

```powershell
cd c:\Users\KoX\smart-campus\stack-php-smart_campus
.\run-tests-ajouter-sa.ps1
```

## Tests disponibles

Les tests suivants sont implémentés dans `AjouterSaControllerTest.php` :

1. **testPageAjouterSaEstAccessible** : Vérifie que la page GET /ajouter-sa est accessible
2. **testAjouterUnSeulSa** : Teste l'ajout d'un seul SA via POST
3. **testAjouterPlusieursSa** : Teste l'ajout de plusieurs SA (5) via POST
4. **testQuantiteTropPetite** : Teste la validation avec quantité = 0 (doit échouer)
5. **testQuantiteTropGrande** : Teste la validation avec quantité = 101 (doit échouer)
6. **testAjouterQuantiteMaximale** : Teste l'ajout avec quantité = 100 (maximum autorisé)
7. **testStatutParDefautEstInactif** : Vérifie que le statut par défaut des SA est "Inactif"
8. **testGetNAjoutePasDeSa** : Vérifie qu'une requête GET n'ajoute pas de SA

## Résultats attendus

Tous les tests doivent passer avec succès :

```
........                                                            8 / 8 (100%)

Ajouter Sa Controller (App\Tests\Controller\SaController\AjouterSaController)
 ✔ Page ajouter sa est accessible
 ✔ Ajouter un seul sa
 ✔ Ajouter plusieurs sa
 ✔ Quantite trop petite
 ✔ Quantite trop grande
 ✔ Ajouter quantite maximale
 ✔ Statut par defaut est inactif
 ✔ Get n ajoute pas de sa

OK (8 tests, 37 assertions)
```

## Structure du test

Les tests utilisent :
- **WebTestCase** : Pour simuler les requêtes HTTP
- **EntityManagerInterface** : Pour vérifier les données en base de données
- **Crawler** : Pour interagir avec le formulaire HTML
- **Assertions** : Pour vérifier les résultats attendus

## Nettoyage de la base de données de test

Si vous souhaitez réinitialiser la base de données de test :

```powershell
# Supprimer et recréer la base de données de test
docker exec -it smart_campus_db mariadb -u root -psmart_campus_root_password -e "DROP DATABASE IF EXISTS smart_campus_test; CREATE DATABASE smart_campus_test; GRANT ALL PRIVILEGES ON smart_campus_test.* TO 'smart_campus_user'@'%'; FLUSH PRIVILEGES;"

# Réappliquer les migrations
docker exec -it -w /var/www/html/projet_symfony smart_campus_php php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

## Fichiers créés

- `tests/Controller/SaController/AjouterSaControllerTest.php` : Le fichier de test principal
- `phpunit.xml.dist` : Configuration de PHPUnit
- `.env.test` : Configuration de l'environnement de test (base de données)
- `run-tests-ajouter-sa.ps1` : Script PowerShell pour exécuter les tests facilement

## Notes importantes

- Les tests utilisent une base de données de test séparée (`smart_campus_test`)
- Les données insérées pendant les tests persistent dans la base de test
- Le contrôleur utilise JavaScript pour la confirmation, mais les tests contournent cette confirmation
- Les messages flash sont affichés avec les classes CSS `.message` (succès) et `.error-message` (erreur)
