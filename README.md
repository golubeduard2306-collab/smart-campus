# Roadmap du Projet - Suivi des Releases

Ce document détaille le plan de développement du projet, divisé en trois releases majeures (R1, R2, R3).

---

## Release 1 (R1) : Socle Technique et Administration
**Objectif :** Mise en place de l'architecture, de la base de données et des fonctionnalités de gestion pour le Chargé de Mission.

### Backend et Infrastructure
- [x] Mise en place du stockage de données (Base de Données)

### Gestion des Salles (Espace Chargé de Mission)
- [x] Permettre au CM d'ajouter facilement une nouvelle salle
- [x] Permettre au CM d'éditer les informations d'une salle existante
- [x] Supprimer une salle via un bouton de suppression
- [x] Afficher la liste de toutes les salles créées (avec redirection vers page dédiée)

### Gestion des SA (Systèmes d'Acquisition)
- [x] Ajouter un nouveau SA via un formulaire rapide
- [x] Modifier un SA via un formulaire de modification (Maquette à faire)
- [x] Supprimer un SA via un bouton de suppression (Maquette à faire)

### Gestion des Demandes
- [x] Créer une demande d'installation
- [x] Créer une demande de désinstallation
- [x] Consulter la liste des demandes

---

## Release 2 (R2) : Maintenance, Visualisation et Roles
**Objectif :** Introduction des rôles (Technicien), visualisation des données sur base simulée et outils de maintenance.


### Visualisation des Données (Données Simulées)
- [ ] Afficher des données simulées
- [ ] Sélectionner une salle pour accéder à la page de visualisation
- [ ] Ouvrir l'historique
- [ ] Changer la période concernée

### Espace Technicien et Maintenance
- [ ] Afficher la liste de toutes les salles et SA (Vue Technicien)
- [x] Accéder à l'espace demande installation


---

## Release 3 (R3) : UX/UI, Temps Reel et Tableau de Bord
**Objectif :** Expérience utilisateur fluide, intégration des données temps réel, tableau de bord complet et dimension écologique.

### Espace Technicien et Maintenance
- [ ] Passer en mode maintenance
- [ ] Effectuer une installation manuelle avec validation de test

### Authentification et Navigation
- [ ] Se connecter à son espace de travail
- [ ] Assurer la navigation globale sur le site

### Gestion des Alertes (Base)
- [ ] Accéder à l'interface alertes
- [ ] Filtrer par criticité/type

### UX/UI et Présentation
- [ ] Amélioration UX/UI et navigation fluide
- [ ] Afficher la page d'accueil
- [ ] Afficher la présentation du projet
- [ ] Afficher la section "Bons Gestes" et explications objectifs écologiques
- [ ] (Optionnel) Afficher un plan du bâtiment (IUT) avec les salles

### Dashboard et Données Avancées
- [ ] Intégration des données temps réel
- [ ] Afficher Tableau de Bord global (données salle, alertes, installation)
- [ ] Afficher Tableau de Bord des tâches à faire
- [ ] Accéder à l'historique général
- [ ] Utiliser un sélecteur de période précis

### Gestion Avancée des Incidents
- [ ] Définition et application des seuils d'alerte
- [ ] Voir la liste complète des alertes
- [ ] Créer un signalement manuel via un formulaire simple