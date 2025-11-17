# 🧪 Guide de Test - Supprimer SA

## ✅ Tests réussis ! 

Tous les 9 tests passent avec succès (9/9 - 100%) ✨

## 🚀 Exécution rapide

```powershell
cd c:\Users\KoX\smart-campus\stack-php-smart_campus
docker exec -it -w /var/www/html/projet_symfony smart_campus_php php bin/phpunit tests/Controller/SaController/SupprimerSaControllerTest.php --testdox
```

Ou utilisez le script PowerShell :
```powershell
.\run-tests-supprimer-sa.ps1
```

## 📋 Ce qui a été testé

1. ✔️ **Accès à la page** - La page `/supprimer-sa` s'affiche correctement
2. ✔️ **Suppression réussie** - Un SA existant est supprimé avec succès
3. ✔️ **SA inexistant** - Message d'erreur si l'ID n'existe pas
4. ✔️ **ID vide** - Validation d'un ID vide
5. ✔️ **ID négatif** - Gestion des IDs négatifs
6. ✔️ **Suppressions multiples** - 3 SA supprimés successivement
7. ✔️ **GET sécurisé** - Une requête GET ne supprime pas de SA
8. ✔️ **Double suppression** - Impossible de supprimer deux fois le même SA
9. ✔️ **ID string** - Gestion des IDs en format string numérique

## 📊 Résultat final

```
.........                                                           9 / 9 (100%)

Supprimer Sa Controller (App\Tests\Controller\SaController\SupprimerSaController)
 ✔ Page supprimer sa est accessible
 ✔ Supprimer un sa existant
 ✔ Supprimer un sa inexistant
 ✔ Supprimer avec id vide
 ✔ Supprimer avec id negatif
 ✔ Supprimer plusieurs sa successifs
 ✔ Get ne supprime pas de sa
 ✔ Double suppression
 ✔ Supprimer avec id string numerique

OK (9 tests, 48 assertions)
```

**48 assertions** ont été vérifiées avec succès ! 🎉

## 🔍 Scénarios de test détaillés

### Test 1: Accès à la page
- Vérifie que la page GET est accessible
- Vérifie la présence du titre "Supprimer un SA existant"

### Test 2: Suppression d'un SA existant
- Crée un SA dans la base
- Soumet le formulaire avec son ID
- Vérifie le message de succès
- Vérifie que le SA n'existe plus dans la base

### Test 3: SA inexistant
- Tente de supprimer un ID qui n'existe pas (ID max + 9999)
- Vérifie le message d'erreur approprié
- Vérifie qu'aucune donnée n'est modifiée

### Test 4: ID vide
- Soumet le formulaire sans ID
- Vérifie le message "Veuillez saisir un ID valide"

### Test 5: ID négatif
- Soumet le formulaire avec ID = -1
- Vérifie la gestion correcte des valeurs négatives

### Test 6: Suppressions multiples
- Crée 3 SA
- Les supprime un par un
- Vérifie que chaque suppression est correcte

### Test 7: Sécurité GET
- Vérifie qu'une simple visite de la page ne supprime rien

### Test 8: Double suppression
- Supprime un SA une première fois (succès)
- Tente de le supprimer à nouveau (erreur)

### Test 9: ID string numérique
- Vérifie la conversion automatique string → int

## 📁 Fichiers créés

- `tests/Controller/SaController/SupprimerSaControllerTest.php` - Classe de tests
- `run-tests-supprimer-sa.ps1` - Script d'exécution rapide

## 💡 Points techniques

### Classes CSS utilisées
- `.message.success` pour les messages de succès
- `.message.error` pour les messages d'erreur

### Méthodes testées
- **GET** `/supprimer-sa` - Affichage du formulaire
- **POST** `/supprimer-sa` - Suppression d'un SA

### Helper créé
- `createSa()` - Méthode pour créer rapidement un SA dans les tests

## 🎯 Couverture de code

Les tests couvrent :
- ✅ Routes GET et POST
- ✅ Validation des entrées
- ✅ Gestion des erreurs
- ✅ Messages flash (succès/erreur)
- ✅ Intégrité de la base de données
- ✅ Cas limites et cas d'erreur

---

**Total : 48 assertions validées avec succès !** 🚀
