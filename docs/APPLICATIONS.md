# Configuration des applications

Les tuiles affichees par ProximaDeck sont definies dans `config/applications.yaml`.

## Format

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

## Champs

| Champ | Obligatoire | Description |
| --- | --- | --- |
| `id` | oui | Identifiant unique, sans espace. |
| `name` | oui | Nom affiche sur la tuile. |
| `category` | non | Groupe dans lequel afficher l'application. |
| `description` | non | Description courte. |
| `visibility` | non | `internal`, `external` ou `both`. Defaut : `both`. |
| `internal_url` | non | URL utilisee depuis le LAN. |
| `external_url` | non | URL utilisee depuis Internet. |
| `icon` | non | Nom du fichier SVG dans `public/assets/icons/`. |
| `order` | non | Ordre d'affichage dans la categorie. Defaut : `100`. |

## Visibilite

- `internal` : visible uniquement depuis le reseau interne.
- `external` : visible uniquement depuis Internet.
- `both` : visible dans les deux contextes.

## Resolution des URLs

Si le visiteur est detecte sur le LAN, ProximaDeck utilise `internal_url`.

Si le visiteur est detecte depuis Internet, ProximaDeck utilise `external_url`.

Si l'URL prioritaire est absente mais que l'autre URL existe, ProximaDeck l'utilise comme secours et marque la tuile en `fallback`.

## Icones

Placez les icones SVG dans `public/assets/icons/`, puis referencez seulement le nom du fichier :

```yaml
icon: homeassistant.svg
```

Le fichier `default.svg` sert d'icone par defaut.

ProximaDeck resout les icones dans cet ordre :

1. icone configuree dans `applications.yaml`, si le fichier existe ;
2. icone connue du service, basee sur `id` ;
3. icone de categorie, basee sur `category` ;
4. `default.svg`.

Les noms d'icones doivent etre des fichiers SVG simples, sans chemin :

```yaml
icon: umami.svg
```

Exemples refuses :

```yaml
icon: ../secret.svg
icon: icon.png
```

Icones fournies dans le socle actuel :

- `default.svg`
- `category-development.svg`
- `category-home.svg`
- `category-infrastructure.svg`
- `category-media.svg`
- `category-monitoring.svg`
- `category-network.svg`
- `category-storage.svg`
- `category-tools.svg`
- `category-web.svg`
- `proxmox.svg`
- `umami.svg`
- `uptime-kuma.svg`
- `homeassistant.svg`
- `vaultwarden.svg`

## Inventaire homelab

Pour remplacer les exemples par votre vraie configuration, ajoutez une entree par service avec au minimum :

- nom
- categorie
- visibilite attendue
- URL LAN
- URL externe si elle existe
- icone souhaitee

## Validation

Validez le fichier YAML avant de publier une modification :

```bash
php tools/validate-config.php
```

La validation detecte notamment :

- `id` manquant ou duplique ;
- `name` manquant ;
- `visibility` invalide ;
- URLs invalides ;
- URL obligatoire manquante selon la visibilite ;
- nom d'icone invalide ;
- fichier d'icone introuvable.
