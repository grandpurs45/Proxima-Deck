# Changelog

Toutes les evolutions notables de ProximaDeck seront documentees ici.

Le projet suit le versioning semantique.

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
