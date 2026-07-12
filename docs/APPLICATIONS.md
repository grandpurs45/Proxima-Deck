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

## Icônes

Placez les icones SVG dans `public/assets/icons/`, puis referencez seulement le nom du fichier :

```yaml
icon: homeassistant.svg
```

Le fichier `default.svg` sert d'icone par defaut.
