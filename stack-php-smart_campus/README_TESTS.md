# 🧪 Tests - Smart Campus

## 📁 Organisation des tests

Tous les **scripts PowerShell** et la **documentation des tests** sont organisés dans le dossier **`tests-docs/`**.

### 🗂️ Structure

```
stack-php-smart_campus/
├── tests-docs/                          ← Documentation et scripts ici !
│   ├── README.md                        Guide principal
│   ├── TESTS_SA_COMPLET.md             Documentation complète (17 tests)
│   ├── TESTS_AJOUTER_SA.md             Guide ajout SA (8 tests)
│   ├── TESTS_SUPPRIMER_SA.md           Guide suppression SA (9 tests)
│   ├── run-all-tests-sa.ps1            Script pour tous les tests
│   ├── run-tests-ajouter-sa.ps1        Script pour tests ajout
│   └── run-tests-supprimer-sa.ps1      Script pour tests suppression
│
└── projet_symfony/
    └── tests/
        └── Controller/
            └── SaController/
                ├── AjouterSaControllerTest.php      (8 tests)
                ├── SupprimerSaControllerTest.php    (9 tests)
                └── README_TESTS.md
```

## 🚀 Exécution rapide

### Depuis le dossier tests-docs
```powershell
cd tests-docs
.\run-all-tests-sa.ps1
```

### Directement avec Docker (recommandé)
```powershell
docker exec -it -w /var/www/html/projet_symfony smart_campus_php php bin/phpunit tests/Controller/SaController/ --testdox
```

## 📊 Résumé

✅ **17 tests** passent avec succès  
✅ **85 assertions** validées  
✅ **100%** de réussite  

Pour plus de détails, consultez **`tests-docs/README.md`** 📖
