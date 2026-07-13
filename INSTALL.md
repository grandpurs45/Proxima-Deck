# Installation

## Prerequis

- PHP 8.2 ou plus recent.
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

## Reverse proxy

ProximaDeck ne depend pas d'un reverse proxy specifique. Pour forcer le contexte via proxy, envoyez l'en-tete :

```text
X-ProximaDeck-Context: internal
```

ou :

```text
X-ProximaDeck-Context: external
```
