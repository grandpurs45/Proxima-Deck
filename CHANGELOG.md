# Changelog

Toutes les evolutions notables de ProximaDeck seront documentees ici.

Le projet suit le versioning semantique.

## [0.7.0] - 2026-07-16

### Ajoute

- Monogrammes automatiques avec couleur stable lorsqu aucune icone de service n est disponible.
- Filtres de categories avec compteur d applications.

### Ameliore

- Grille unique de tuiles compactes adaptee aux tableaux de bord contenant de nombreux liens.
- Le champ `icon` est maintenant facultatif sans fallback generique a gerer.
- Une icone absente bascule silencieusement sur le monogramme automatique.
- Affichage plus dense du nom, de la categorie, de la cible et du host.

## [0.6.1] - 2026-07-14

### Ameliore

- Affichage desktop compacte avec les categories reparties en colonnes.
- Reduction de la hauteur de l en-tete, du diagnostic et des tuiles.
- Les quatre premiers services tiennent maintenant dans un ecran de 1280 x 720.

## [0.6.0] - 2026-07-14

### Ajoute

- Suite de tests PHP autonome lancee avec `php tools/run-tests.php`.
- Couverture de la detection reseau, du verrouillage du diagnostic, de la visibilite des applications, de la validation YAML et des fallbacks d'icones.

### Securite

- Une application sans `external_url` est toujours masquee en contexte externe.
- Suppression du fallback d'une URL externe vers une URL interne.

## [0.5.1] - 2026-07-14

### Securite

- Le mode diagnostic `?context=internal|external` est maintenant desactive par defaut.
- Ajout de `PROXIMADECK_DIAGNOSTIC_MODE=true` pour activer explicitement le diagnostic en local.
- L'interface indique lorsque le diagnostic est desactive.

## [0.5.0] - 2026-07-14

### Ajoute

- Mode diagnostic reseau via `?context=internal` ou `?context=external`.
- Barre de diagnostic dans l'interface pour tester Auto, LAN et Internet.
- Transmission du contexte diagnostic a l'API pour valider le filtrage et la resolution des URLs.

### Corrige

- Remplacement de caracteres de separation sensibles a l'encodage dans les messages frontend.

## [0.4.0] - 2026-07-13

### Ajoute

- Validation structuree de `config/applications.yaml`.
- Affichage des erreurs et avertissements de configuration dans l'interface.
- Commande CLI `php tools/validate-config.php`.
- Detection des ids manquants, ids dupliques, visibilites invalides, URLs invalides et icones absentes.

## [0.3.0] - 2026-07-13

### Ajoute

- Resolution serveur des icones avec fallback par service, categorie, puis icone par defaut.
- Icones SVG de categories pour Web, Monitoring, Infrastructure, Domotique, Reseau, Stockage, Developpement, Multimedia et Outils.
- Metadata `icon_source` et `icon_label` exposees par l'API pour diagnostiquer la source de l'icone.

### Securite

- Validation du nom de fichier d'icone pour refuser les chemins ou noms inattendus.

## [0.2.1] - 2026-07-13

### Ajoute

- Icone SVG dediee pour Umami.

### Modifie

- Configuration d'exemple remplacee par les services homelab Umami et Uptime Kuma.

## [0.2.0] - 2026-07-13

### Ajoute

- Icones SVG dediees pour Proxmox, Uptime Kuma, Home Assistant et Vaultwarden.
- Affichage du host cible sur chaque tuile.
- Libelles lisibles pour la visibilite et le contexte de l'URL resolue.
- Fallback automatique vers `default.svg` si une icone est manquante.

### Corrige

- Remplacement de caracteres d'interface sensibles a l'encodage par des variantes ASCII robustes.

## [0.1.1] - 2026-07-13

### Corrige

- Ajout de la version aux URLs des assets CSS/JS pour limiter les problemes de cache navigateur apres mise a jour.

## [0.1.0] - 2026-07-12

### Ajoute

- Socle initial PHP sans dependance externe.
- Dashboard responsive en une page.
- Version affichee dans l'interface.
- API JSON exposant les applications visibles.
- Lecture d'un fichier `config/applications.yaml`.
- Detection du contexte reseau par IP privee, variable d'environnement ou en-tete reverse proxy.
- Resolution automatique entre URL interne et URL externe.
- Recherche instantanee et categories repliables.
- Dockerfile et `docker-compose.yml` generiques.
- Documentation initiale.
