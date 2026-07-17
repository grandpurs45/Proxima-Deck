# Installation

## Prerequis

- PHP 8.2 ou plus recent avec l extension cURL.
- Apache avec `mod_rewrite` pour XAMPP ou image Docker fournie.
- Docker et Docker Compose pour un deploiement conteneurise.

## Installation avec XAMPP

1. Placez le projet dans `C:\dev\xampp\htdocs\proximadeck`.
2. Configurez le virtual host `proximadeck.local` vers la racine du projet ou vers `public/`.
3. Verifiez que `mod_rewrite` est actif si le virtual host pointe vers la racine.
4. Modifiez `config/applications.yaml`.
5. Ouvrez `http://proximadeck.local`.

Le format complet des tuiles est decrit dans `docs/APPLICATIONS.md`.

Vous pouvez valider la configuration avec :

```bash
php tools/validate-config.php
```

Puis verifier les comportements reseau, la visibilite et les icones avec :

```bash
php tools/run-tests.php
```

## Installation avec Docker

```bash
docker compose up -d --build
```

Le service expose l'application sur `http://localhost:8080`.

## Variables d'environnement

| Variable | Description | Valeur par defaut |
| --- | --- | --- |
| `PROXIMADECK_CONFIG` | Chemin du fichier YAML | `config/applications.yaml` |
| `PROXIMADECK_NETWORK_CONTEXT` | Force `internal` ou `external` | detection automatique |
| `PROXIMADECK_HEALTH_CACHE_TTL` | Duree du cache des temoins, en secondes | `60` |
| `PROXIMADECK_HEALTH_TIMEOUT_MS` | Timeout maximal d un controle, en millisecondes | `1500` |

## Diagnostic reseau

Pendant le developpement, vous pouvez forcer le contexte depuis l'URL si le mode diagnostic est active :

```env
PROXIMADECK_DIAGNOSTIC_MODE=true
```

```text
http://proximadeck.local/?context=internal
http://proximadeck.local/?context=external
```

Le meme parametre est disponible cote API :

```text
/api/apps.php?context=external
```

Ne laissez pas `PROXIMADECK_DIAGNOSTIC_MODE=true` en production. Sans cette variable, les parametres `?context=internal` et `?context=external` sont ignores.

## Reverse proxy

ProximaDeck ne depend pas d'un reverse proxy specifique. Pour forcer le contexte via proxy, envoyez l'en-tete :

```text
X-ProximaDeck-Context: internal
```

ou :

```text
X-ProximaDeck-Context: external
```
