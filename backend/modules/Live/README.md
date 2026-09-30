# Module Live - Streaming en Direct

## Vue d'Ensemble

Le Module Live gère la diffusion en direct pour l'Institut Nujum Al-Huda en utilisant MediaMTX pour le streaming RTMP/WHEP/HLS et Laravel Reverb pour le chat temps réel.

## Installation

### 1. Enregistrer le Service Provider

Ajoutez dans `config/app.php`:

```php
'providers' => [
    // ...
    Modules\Live\LiveServiceProvider::class,
],
```

### 2. Exécuter les Migrations

```bash
php artisan migrate
```

### 3. Seeder les Données Initiales

```bash
php artisan db:seed --class=Modules\\Live\\Database\\Seeders\\LiveChannelSeeder
php artisan db:seed --class=Modules\\Live\\Database\\Seeders\\LiveStreamSeeder
php artisan db:seed --class=Modules\\Live\\Database\\Seeders\\VodSeeder
```

### 4. Configuration MediaMTX

Configurez vos URLs MediaMTX dans `.env`:

```env
MEDIAMTX_BASE_URL=http://localhost:9997
MEDIAMTX_API_KEY=your-api-key
MEDIAMTX_RTMP_INGEST_URL=rtmp://ingest.nujumalhuda.com:1935
MEDIAMTX_WHEP_URL=https://stream.nujumalhuda.com/whep
MEDIAMTX_HLS_URL=https://stream.nujumalhuda.com/hls
MEDIAMTX_VOD_BASE_URL=https://vod.nujumalhuda.com
MEDIAMTX_WEBHOOK_SECRET=your-webhook-secret
```

### 5. Configurer Laravel Reverb

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
```

### 6. Démarrer les Workers Queue

```bash
php artisan queue:work
```

### 7. Démarrer Reverb (WebSocket)

```bash
php artisan reverb:start
```

## Architecture

### Canaux de Streaming

- **main**: Canal principal (khutbas, conférences, événements)
- **recitation**: Sessions de récitation du Coran
- **audio**: Stream audio uniquement (faible bande passante)

### Cycle de Vie d'un Stream

1. **scheduled**: Stream créé et programmé
2. **live**: Stream actif (déclenché par webhook MediaMTX)
3. **ended**: Stream terminé (déclenché par webhook MediaMTX)
4. **archived**: Stream archivé automatiquement après X jours

### Flux de Données

```
OBS Studio → RTMP Ingest → MediaMTX → WHEP/HLS → Viewers
                ↓
            Webhook
                ↓
         Laravel API
                ↓
         Broadcasting (Reverb)
                ↓
         Frontend (WebSocket)
```

## Utilisation

### Créer un Stream (Admin)

```bash
POST /api/v1/live/streams
{
  "channel_id": "01JBXXXXXXXXXXXXXXXX",
  "title": {
    "fr": "Khutba du Vendredi",
    "en": "Friday Sermon",
    "ar": "خطبة الجمعة"
  },
  "type": "khutba",
  "scheduled_at": "2024-01-05 13:30:00",
  "enable_chat": true,
  "enable_reactions": true,
  "is_featured": true
}
```

### Configuration OBS Studio

1. Récupérer l'URL RTMP depuis Filament Admin
2. Configurer OBS:
   - **Server**: `rtmp://ingest.nujumalhuda.com:1935/main`
   - **Stream Key**: `[clé générée automatiquement]`

### Rejoindre un Stream (Frontend)

```javascript
// 1. Rejoindre le stream
const response = await fetch('/api/v1/live/streams/{id}/join', {
  method: 'POST',
  body: JSON.stringify({
    session_token: generateToken()
  })
});

const { session_token, stream_urls } = await response.json();

// 2. Initialiser le lecteur WHEP
const player = new WHEPPlayer(stream_urls.whep);

// 3. Se connecter au chat WebSocket
Echo.channel(`live-stream.${streamId}.chat`)
  .listen('chat.message', (message) => {
    console.log(message);
  });
```

### Poster un Message Chat

```javascript
await fetch(`/api/v1/live/streams/${streamId}/chat`, {
  method: 'POST',
  body: JSON.stringify({
    message: "Assalamu alaikum",
    session_token: sessionToken
  })
});
```

### Envoyer une Réaction

```javascript
await fetch(`/api/v1/live/streams/${streamId}/chat/reactions`, {
  method: 'POST',
  body: JSON.stringify({
    reaction: "pray", // like, love, clap, pray, mashallah
    session_token: sessionToken
  })
});
```

## Commandes Artisan

### Vérifier Santé des Streams

```bash
php artisan live:check-health
```

Collecte les métriques de santé des streams actifs.

### Archiver Anciens Streams

```bash
php artisan live:archive-old --days=30
```

Archive les streams terminés il y a plus de 30 jours.

### Nettoyer Anciens Messages Chat

```bash
php artisan live:cleanup-chats --days=30
```

Supprime les messages chat des streams archivés de plus de 30 jours.

## Modération Chat (Filament)

### Accéder à la Modération

1. Aller dans **Live Streaming** → **Chat Moderation**
2. Voir les messages flaggés avec score spam
3. Actions disponibles:
   - **Hide**: Masquer le message
   - **Approve**: Approuver un message flaggé
   - **Delete**: Supprimer définitivement

### Auto-Modération

Les messages sont automatiquement flaggés si:
- Score spam ≥ 70%
- Contient des mots bannis
- Plus de 2 liens
- Plus de 70% en majuscules
- Caractères répétés excessivement

## Webhooks MediaMTX

Configurez MediaMTX pour envoyer les webhooks vers:

```
POST https://nujumalhuda.com/api/v1/webhooks/mediamtx
Header: X-Webhook-Secret: [votre-secret]
```

Événements supportés:
- `publish`: Stream démarré
- `publishDone`: Stream terminé
- `read`: Viewer rejoint
- `readDone`: Viewer quitte

## Analytics

Les métriques suivantes sont collectées automatiquement:

- **Viewers**: Nombre de viewers concurrents
- **Peak Viewers**: Maximum de viewers atteint
- **Total Views**: Nombre total de vues
- **Chat Activity**: Messages et réactions
- **Technical**: Bitrate, frame rate, buffering ratio
- **Demographics**: Pays, devices, browsers

Accessible via:
```bash
GET /api/v1/live/streams/{id}/analytics
```

## VOD (Video On Demand)

Les enregistrements VOD sont créés automatiquement après la fin d'un stream.

### Accéder aux VOD

```bash
GET /api/v1/vod/recordings
GET /api/v1/vod/recordings/popular
GET /api/v1/vod/recordings/{slug}
```

### Configurer Chapitres

Dans Filament Admin, ajouter des chapitres:
```
0: Introduction
300: Sujet Principal (5 minutes)
900: Questions & Réponses (15 minutes)
```

## Sécurité

### Rotation des Clés

Pour des raisons de sécurité, vous pouvez régénérer la clé d'un stream:

```bash
POST /api/v1/live/streams/{id}/rotate-key
```

⚠️ Cela invalidera l'ancienne clé immédiatement.

### Rate Limiting

Le chat est rate-limité à **5 messages par minute** par défaut. Configurable dans `config/mediamtx.php`.

## Troubleshooting

### Le stream ne démarre pas

1. Vérifier la clé RTMP dans OBS
2. Vérifier les logs MediaMTX
3. Tester la connexion: `ffmpeg -re -i test.mp4 -c copy -f flv rtmp://...`

### Les viewers ne voient pas le stream

1. Vérifier CORS sur MediaMTX
2. Vérifier certificats SSL
3. Tester WHEP URL dans navigateur

### Les messages chat n'apparaissent pas

1. Vérifier Laravel Reverb est démarré
2. Vérifier configuration broadcasting
3. Vérifier connexion WebSocket dans browser console

## Support

- Documentation: `MODULE-LIVE-100-COMPLETE.md`
- Issues: Créer un ticket avec logs détaillés
- MediaMTX Docs: https://github.com/bluenviron/mediamtx

## License

Propriétaire - Nujum Al-Huda Institute Center © 2026
