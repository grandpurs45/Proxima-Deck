# ProximaDeck

ProximaDeck est un portail web open source auto-heberge pour homelab. Il sert de point d'entree unique vers les applications d'une infrastructure, avec une difference centrale : chaque tuile peut avoir une URL interne et une URL externe, et ProximaDeck choisit automatiquement la bonne URL selon le contexte reseau detecte.

## Fonctionnalites disponibles

- Dashboard web responsive en une page.
- Applications affichees sous forme de cartes.
- Icones SVG par service avec fallback automatique.
- Fallback d'icones par service, categorie, puis icone par defaut.
- Host cible affiche sur chaque carte.
- Categories repliables.
- Recherche instantanee.
- Configuration par fichier YAML.
- Visibilite par application : `internal`, `external`, `both`.
- Detection reseau extensible : IP privee, variable d'environnement, en-tete reverse proxy.
- Mode diagnostic pour tester `internal` et `external` depuis l'interface.
- Version affichee dans l'interface depuis le fichier `VERSION`.
- Validation de configuration avec erreurs lisibles dans l'interface.
- Dockerfile et `docker-compose.yml` generiques.

## Structure

```text
config/              Configuration YAML
public/              Racine web publique
public/api/          API JSON
public/assets/       CSS, JavaScript, icones
src/                 Code PHP applicatif
```

## Configuration rapide

Modifiez `config/applications.yaml` :

```yaml
applications:
  - id: homeassistant
    name: Home Assistant
    category: Domotique
    description: Controle de la maison
    visibility: both
    internal_url: http://ha.lan:8123
    external_url: https://ha.example.com
    icon: default.svg
    order: 10
```

Le detail des champs est documente dans `docs/APPLICATIONS.md`.

Validez la configuration en ligne de commande :

```bash
php tools/validate-config.php
```

Lancez la suite de tests automatisee :

```bash
php tools/run-tests.php
```

Testez le contexte reseau sans changer de reseau :

```text
http://proximadeck.local/?context=internal
http://proximadeck.local/?context=external
```

Le diagnostic est desactive par defaut. Activez-le seulement en local :

```env
PROXIMADECK_DIAGNOSTIC_MODE=true
```

## Developpement local

Avec XAMPP, utilisez l'URL locale prevue :

```text
http://proximadeck.local
```

Le virtual host peut pointer vers la racine du projet ou directement vers `public/`. Le fichier `.htaccess` redirige vers `public/` lorsque le virtual host pointe vers la racine.

## Docker

```bash
docker compose up -d --build
```

Puis ouvrez :

```text
http://localhost:8080
```

## Licence

Licence a definir avant publication stable.
