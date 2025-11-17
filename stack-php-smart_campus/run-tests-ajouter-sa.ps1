# Script pour exécuter les tests de AjouterSaController dans Docker

Write-Host "=== Exécution des tests pour AjouterSaController ===" -ForegroundColor Green
Write-Host ""

# Se positionner dans le répertoire du projet
Set-Location "c:\Users\KoX\smart-campus\stack-php-smart_campus"

# Vérifier que les conteneurs Docker sont en cours d'exécution
Write-Host "Vérification des conteneurs Docker..." -ForegroundColor Cyan
docker-compose ps

Write-Host ""
Write-Host "Exécution des tests dans le conteneur PHP..." -ForegroundColor Cyan
Write-Host ""

# Exécuter les tests dans le conteneur PHP
docker exec -it smart_campus_php php projet_symfony/bin/phpunit tests/Controller/SaController/AjouterSaControllerTest.php

Write-Host ""
Write-Host "=== Tests terminés ===" -ForegroundColor Green
