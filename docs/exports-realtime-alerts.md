# Exports, temps réel et alertes

## Exports des rapports

Les deux routes utilisent les mêmes filtres que le rapport JSON : `date_from`, `date_to` et `mode`.

- `GET /api/v1/restaurants/{restaurant}/reports/overview/export/pdf`
- `GET /api/v1/restaurants/{restaurant}/reports/overview/export/xlsx`

Elles nécessitent une session Sanctum valide, un e-mail vérifié et la permission de consulter les rapports.

## Événements temps réel

Le backend diffuse les événements suivants avec Laravel Reverb :

| Canal | Événement | Utilisation |
|---|---|---|
| `private-restaurants.{restaurantId}` | `.orders.created` | Nouvelle commande pour l’équipe |
| `private-restaurants.{restaurantId}` | `.orders.status.updated` | Évolution d’une commande |
| `private-restaurants.{restaurantId}` | `.assistance.requested` | Appel serveur ou demande d’addition |
| `orders.{trackingToken}` | `.orders.status.updated` | Suivi public d’une commande |

Le canal privé vérifie que l’utilisateur possède une adhésion active au restaurant. Le frontend authentifié doit appeler `/broadcasting/auth` avec ses cookies Sanctum.

Pour un repli par polling, la liste des commandes accepte `updated_after` avec une date ISO 8601 :

```text
GET /api/v1/restaurants/{restaurant}/orders?updated_after=2026-10-21T10:30:00Z
```

## Alertes automatiques

La commande `php artisan alerts:process` :

- signale une commande encore en préparation après `order_delay_minutes` (45 minutes par défaut) ;
- signale un abonnement qui expire dans les sept prochains jours ;
- passe automatiquement un abonnement expiré en période de grâce ou en statut expiré ;
- signale les paiements d’abonnement échoués qui n’ont pas encore été notifiés.

Chaque alerte est marquée en base afin de ne pas être envoyée deux fois. Le scheduler exécute cette commande toutes les cinq minutes.

## Processus à lancer en développement

Ouvrir quatre terminaux dans le projet :

```powershell
php artisan serve --host=127.0.0.1 --port=8000
php artisan reverb:start --host=127.0.0.1 --port=8080
php artisan queue:work
php artisan schedule:work
```

Après une modification du fichier `.env`, exécuter `php artisan config:clear`.
