# 📁 Documentation et Scripts de Tests

Ce dossier contient toute la documentation et les scripts PowerShell pour exécuter les tests du projet Smart Campus.

## 📄 Fichiers disponibles

### Documentation des tests
- **`TESTS_SA_COMPLET.md`** - Vue d'ensemble complète de tous les tests SA (17 tests)
- **`TESTS_AJOUTER_SA.md`** - Guide rapide pour les tests d'ajout de SA (8 tests)
- **`TESTS_SUPPRIMER_SA.md`** - Guide rapide pour les tests de suppression de SA (9 tests)

### Scripts PowerShell
- **`run-all-tests-sa.ps1`** - Exécute TOUS les tests SA (Ajouter + Supprimer)
- **`run-tests-ajouter-sa.ps1`** - Exécute uniquement les tests d'ajout de SA
- **`run-tests-supprimer-sa.ps1`** - Exécute uniquement les tests de suppression de SA

## 🚀 Utilisation rapide

### Tous les tests SA
```powershell
cd c:\Users\KoX\smart-campus\stack-php-smart_campus\tests-docs
.\run-all-tests-sa.ps1
```

### Tests spécifiques
```powershell
# Ajouter SA uniquement
.\run-tests-ajouter-sa.ps1

# Supprimer SA uniquement
.\run-tests-supprimer-sa.ps1
```

### Exécution directe (sans script)
```powershell
cd c:\Users\KoX\smart-campus\stack-php-smart_campus
docker exec -it -w /var/www/html/projet_symfony smart_campus_php php bin/phpunit tests/Controller/SaController/ --testdox
```

## 📊 Résumé des tests

| Type de test | Fichier de test | Nombre | Assertions |
|--------------|----------------|--------|------------|
| Ajouter SA | `AjouterSaControllerTest.php` | 8 tests | 37 assertions |
| Supprimer SA | `SupprimerSaControllerTest.php` | 9 tests | 48 assertions |
| **TOTAL** | | **17 tests** | **85 assertions** |

## ⚠️ Note importante

Si vous rencontrez l'erreur "l'exécution de scripts est désactivée", vous avez deux options :

1. **Option recommandée** : Exécuter directement les commandes Docker
2. **Modifier la politique d'exécution** (administrateur requis) :
   ```powershell
   Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
   ```

## 📂 Organisation du projet

```
stack-php-smart_campus/
├── tests-docs/                          ← Vous êtes ici
│   ├── README.md                        (Ce fichier)
│   ├── TESTS_SA_COMPLET.md             (Documentation complète)
│   ├── TESTS_AJOUTER_SA.md             (Guide ajout)
│   ├── TESTS_SUPPRIMER_SA.md           (Guide suppression)
│   ├── run-all-tests-sa.ps1            (Script global)
│   ├── run-tests-ajouter-sa.ps1        (Script ajout)
│   └── run-tests-supprimer-sa.ps1      (Script suppression)
│
└── projet_symfony/
    └── tests/
        └── Controller/
            └── SaController/
                ├── AjouterSaControllerTest.php
                ├── SupprimerSaControllerTest.php
                └── README_TESTS.md
```

## ✅ Statut actuel

🎉 **Tous les tests passent avec succès : 17/17 (100%)**
