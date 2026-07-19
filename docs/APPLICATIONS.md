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
    icon: home-assistant
    healthcheck: true
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
| `icon` | non | Nom Dashboard Icons ou nom d un fichier local. Extensions `.svg`, `.png` et `.webp` acceptees. |
| `healthcheck` | non | Active le temoin de disponibilite. Defaut : `true`. Utilisez `false` pour afficher `unknown` sans sonder le service. |
| `order` | non | Ordre d'affichage dans la categorie. Defaut : `100`. |

## Visibilite

- `internal` : visible uniquement depuis le reseau interne.
- `external` : visible uniquement depuis Internet.
- `both` : visible dans les deux contextes.

## Resolution des URLs

Si le visiteur est detecte sur le LAN, ProximaDeck utilise `internal_url`.

Si le visiteur est detecte depuis Internet, ProximaDeck utilise `external_url`.

Depuis le LAN, si `internal_url` est absente mais que `external_url` existe, ProximaDeck utilise l'URL publique comme secours et marque la tuile en `fallback`.

Depuis Internet, une URL interne n'est jamais utilisee comme secours. Une application sans `external_url` reste masquee.

## Icones

ProximaDeck utilise le meme principe que Homepage. Indiquez le nom disponible sur [dashboardicons.com](https://dashboardicons.com/) :

```yaml
icon: proxmox
```

Les extensions compatibles avec Homepage sont egalement acceptees :

```yaml
icon: uptime-kuma.svg
```

La resolution respecte cet ordre :

1. fichier local existant dans `public/assets/icons/` ;
2. Dashboard Icons via l API locale `/api/icon.php` ;
3. monogramme automatique si l icone est absente ou indisponible.

Les icones Dashboard Icons sont recuperees par le serveur puis conservees 30 jours dans le repertoire temporaire. Le navigateur ne contacte pas directement le CDN. Les noms sont strictement valides et ne peuvent pas contenir de chemin ou d URL.

Parametres optionnels :

```env
PROXIMADECK_ICON_CACHE=/chemin/vers/le/cache
PROXIMADECK_ICON_CACHE_TTL=2592000
PROXIMADECK_ICON_TIMEOUT_MS=3000
```

## Disponibilite

Le temoin reste volontairement simple :

- vert : `up`, le service a repondu ;
- rouge : `down`, le service ne repond pas, depasse le timeout ou retourne une erreur serveur ;
- gris : `unknown`, le controle est desactive ou indisponible.

Les redirections, pages protegees, reponses HTTP inferieures a 500 et reponses `501` au controle `HEAD` sont considerees comme `up`. ProximaDeck ne collecte ni historique, ni temps de reponse, ni detail d erreur.

Le controle est realise en arriere-plan uniquement sur les applications visibles dans le contexte reseau courant. Les temoins sont actualises toutes les 60 secondes et les resultats sont mis en cache pendant 60 secondes par defaut.

Pour desactiver le controle d une application :

```yaml
healthcheck: false
```

## Inventaire homelab

Pour remplacer les exemples par votre vraie configuration, ajoutez une entree par service avec au minimum :

- nom
- categorie
- visibilite attendue
- URL LAN
- URL externe si elle existe
- icone souhaitee, uniquement si le monogramme ou l icone automatique ne convient pas

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
- format du nom d'icone invalide.
- valeur `healthcheck` invalide.
