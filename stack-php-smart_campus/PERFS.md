# Optimisation de performances avec Symfony

## Quel est le symptôme ?

...Si vous êtes là, c'est que vous connaissez déjà le symptôme ;-)  
Les pages sous symfony rrrraamment sévèrement, sur les postes de travail utilisant Windows.  
(Linux n'a pas le problème, parce que linux, c'est cool.)

Sur certains PC, on est à 15, 20, 30 secondes, ça n'est pas utilisable.

## D'où vient le problème ?

Le problème vient _globalement_ du fait que Docker (sous mac ou sous windows) utilise une technologie de virtualisation, qui nécessite des entrées sorties entre l'hôte (l'OS Windows ou MacOS) et le container qui s'occupe du PHP.  
Les fichiers utilisés pour générer les pages PHP sont sur l'hôte (parce que nous n'avons pas encore fait d'optimisations), et sont lus en masse lors de _chaque_ interprétation d'un script PHP par le container iut-php, et tout ça représente BEAUCOUP de fichiers de petite taille, qui doivent tous passer par le "truc" qui gère les IO entre les containers et l'hôte, qui constitue un goulet d'étranglement.  

Les fichiers concernés sont principalement dans deux répertoires de notre projet symfony : `var` et `vendor` 
(le code que nous générons, dans `src` représente une toute petite partie du code chargé, et n'a donc quasiment pas d'influence sur les performances)

## Comment on améliore ça ?

Une solution facile et extrèmement efficace : utiliser linux nativement sur la machine.  
Bon, ok 🚪

Si on souhaite garder nos habitudes, on va devoir minimiser les I/O entre l'hôte et le container.

La solution est assez proche de celle proposée ci-dessus : on va utiliser au maximum Linux avec WSL2, et minimiser les accès aux fichiers sur le système de fichiers Windows.

En effet, Docker sous Windows utilise WSL2, et WSL2 est une VM linux.

Donc le principe, c'est mettre le code du projet dans WSL2, et ainsi faire en sorte que le container PHP accède aux fichiers du projet via le système de fichiers linux (et pas Windows) de WSL2.

J'ai fait des tests, en installant une Ubuntu.

J'ouvre un powershell, et j'installe une Ubuntu (si ce n'est pas déjà fait), en la rendant la VM par défaut pour WSL2 :

```powershell
wsl --install -d Ubuntu
```

Ici on me demdande de créer un utilisateur et un mot de passe pour cette Ubuntu : mettez le même nom d'utilisateur que votre compte Windows, et un mot de passe que vous n'oublierez pas.

 Ensuite, je lance l'Ubuntu (depuis le menu démarrer de Windows), et je me retrouve dans un terminal linux.
 
```powershell
wsl
```

Je me place dans mon répertoire utilisateur linux, et je clone le dépôt git de la stack et de mon projet symfony :

**Attention**, avant de clone assurez vous _surtout_ de ne pas être dans un sous répertoire de Windows, sinon on revient au problème initial.
Je peux vérifier que je suis bien dans le répertoire linux en faisant un `pwd` (print working directory) :

```bash
pwd
```

Je dois voir un truc du genre `/home/mon_nom_utilisateur/MON_DEPOT_GIT` et **surtout pas** un truc du genre `/mnt/c/Users/mon_nom_utilisateur/...`

```bash
cd ~
git clone MON_DEPOT_GIT
cd MON_DEPOT_GIT
```

Je peux maintenant lancer docker compose, depuis ce répertoire linux :

```bash
docker compose up --build -d
```

Le containers sont partagés entre Windows et WSL2, donc je peux toujours y accéder depuis un powershell Windows, par exemple pour exécuter des commandes dans le container php :

```powershell
docker exec -it iut-php bash
```

Et je peux aussi accéder au serveur web depuis Windows, en ouvrant un navigateur et en allant sur http://localhost:8888
Le répertoire de mon projet symfony est dans WSL2, et le container php y accède via le système de fichiers linux, donc les performances sont excellentes.

## Et pour utiliser mon IDE ?

J'utilise PHPStorm, et PHPStorm sait accéder aux fichiers dans WSL2, pour ouvrir un explorateur windows dans le répertoire de mon projet dans WSL2, je fais, **depuis mon terminal linux** :

```powershell
explorer.exe .
```
(attention au . à la fin de la commande : c'est le répertoire _courant_)

Une fois dans l'explorateur windows, j'ouvre le répertoire dans PHPStorm.



