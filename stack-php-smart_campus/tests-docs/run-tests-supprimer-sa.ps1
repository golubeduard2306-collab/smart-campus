# Script pour exécuter les tests de SupprimerSaController dans Docker

Write-Host "=== Exécution des tests pour SupprimerSaController ===" -ForegroundColor Green
Write-Host ""

# Se positionner dans le répertoire du projet (remonter d'un niveau depuis tests-docs)
Set-Location (Split-Path $PSScriptRoot -Parent)

# Vérifier que les conteneurs Docker sont en cours d'exécution
Write-Host "Vérification des conteneurs Docker..." -ForegroundColor Cyan
docker-compose ps

Write-Host ""
Write-Host "Exécution des tests dans le conteneur PHP..." -ForegroundColor Cyan
Write-Host ""

# Exécuter les tests dans le conteneur PHP
docker exec -it -w /var/www/html/projet_symfony smart_campus_php php bin/phpunit tests/Controller/SaController/SupprimerSaControllerTest.php --testdox

Write-Host ""
Write-Host "=== Tests terminés ===" -ForegroundColor Green
