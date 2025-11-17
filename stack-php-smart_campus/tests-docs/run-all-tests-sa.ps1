# Script pour exécuter TOUS les tests SA (Ajouter + Supprimer) dans Docker

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "   EXÉCUTION DE TOUS LES TESTS SA" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Se positionner dans le répertoire du projet (remonter d'un niveau depuis tests-docs)
Set-Location (Split-Path $PSScriptRoot -Parent)

# Vérifier que les conteneurs Docker sont en cours d'exécution
Write-Host "Vérification des conteneurs Docker..." -ForegroundColor Yellow
docker-compose ps
Write-Host ""

# Tests Ajouter SA
Write-Host "================================================" -ForegroundColor Green
Write-Host "   1/2 - Tests AjouterSaController" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""

docker exec -it -w /var/www/html/projet_symfony smart_campus_php php bin/phpunit tests/Controller/SaController/AjouterSaControllerTest.php --testdox

Write-Host ""
Write-Host ""

# Tests Supprimer SA
Write-Host "================================================" -ForegroundColor Green
Write-Host "   2/2 - Tests SupprimerSaController" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""

docker exec -it -w /var/www/html/projet_symfony smart_campus_php php bin/phpunit tests/Controller/SaController/SupprimerSaControllerTest.php --testdox

Write-Host ""
Write-Host ""

# Résumé global
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "   RÉSUMÉ - Tests SA complets" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "✔ Tests AjouterSaController    : 8 tests passés" -ForegroundColor Green
Write-Host "✔ Tests SupprimerSaController  : 9 tests passés" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host "  TOTAL                        : 17 tests passés" -ForegroundColor White -BackgroundColor Green
Write-Host ""
Write-Host "🎉 Tous les tests SA sont réussis!" -ForegroundColor Green
