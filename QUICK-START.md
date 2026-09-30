# 🚀 Quick Start - Nujum Al-Huda Platform

## Installation Rapide

### 1. Prérequis
- PHP 8.2+
- PostgreSQL 15+
- Redis
- Composer
- Node.js 18+
- MediaMTX (pour streaming)

### 2. Installation Backend

```bash
cd backend

# Installer dépendances
composer install

# Copier .env
cp .env.example .env

# Configurer .env
# - Database (PostgreSQL)
# - Redis
# - MediaMTX URLs
# - Reverb (WebSocket)

# Générer clé application
php artisan key:generate

# Exécuter migrations
php artisan migrate

# Seeder données initiales
php artisan db:seed --class=Modules\\Mosque\\Database\\Seeders\\PrayerTimeSeeder
php artisan db:seed --class=Modules\\Mosque\\Database\\Seeders\\KhutbaSeeder
php artisan db:seed --class=Modules\\Mosque\\Database\\Seeders\\EventSeeder
php artisan db:seed --class=Modules\\Education\\Database\\Seeders\\ProgramSeeder
php artisan db:seed --class=Modules\\Education\\Database\\Seeders\\TeacherSeeder
php artisan db:seed --class=Modules\\Education\\Database\\Seeders\\PromotionSeeder
php artisan db:seed --class=Modules\\News\\Database\\Seeders\\CategorySeeder
php artisan db:seed --class=Modules\\News\\Database\\Seeders\\ArticleSeeder
php artisan db:seed --class=Modules\\Live\\Database\\Seeders\\LiveChannelSeeder
php artisan db:seed --class=Modules\\Live\\Database\\Seeders\\LiveStreamSeeder
php artisan db:seed --class=Modules\\Live\\Database\\Seeders\\VodSeeder

# Créer admin Filament
php artisan make:filament-user

# Démarrer serveur
php artisan serve

# Démarrer queues (terminal séparé)
php artisan queue:work

# Démarrer Reverb WebSocket (terminal séparé)
php artisan reverb:start
```

### 3. Installation Frontend

```bash
cd frontend

# Installer dépendances
npm install

# Configurer .env.local
cp .env.example .env.local

# NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
# NEXT_PUBLIC_WS_URL=ws://localhost:8080

# Démarrer dev server
npm run dev
```

### 4. Accès

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000/api/v1
- **Filament Admin**: http://localhost:8000/admin
- **Reverb WebSocket**: ws://localhost:8080

## 📁 Structure du Projet

```
nujumalhuda/
├── backend/                      # Laravel 11 Backend
│   ├── app/
│   ├── config/
│   │   └── mediamtx.php         # Config MediaMTX ✅
│   ├── database/
│   └── modules/                  # Modules
│       ├── Mosque/              # Module Mosquée ✅
│       │   ├── Models/
│       │   ├── Http/
│       │   ├── Filament/
│       │   ├── Services/
│       │   ├── Events/
│       │   └── Database/
│       ├── Education/           # Module Éducation ✅
│       │   ├── Models/
│       │   ├── Http/
│       │   ├── Filament/
│       │   ├── Services/
│       │   └── Database/
│       ├── News/                # Module Actualités ✅
│       │   ├── Models/
│       │   ├── Http/
│       │   ├── Filament/
│       │   ├── Services/
│       │   └── Database/
│       └── Live/                # Module Streaming ✅
│           ├── Models/
│           ├── Http/
│           ├── Filament/
│           ├── Services/
│           ├── Events/
│           └── Database/
├── frontend/                     # Next.js 14 Frontend
│   ├── src/
│   │   ├── app/
│   │   ├── components/
│   │   ├── hooks/
│   │   └── types/
│   └── public/
└── docs/                        # Documentation
    ├── MODULE-MOSQUE-100-COMPLETE.md     ✅
    ├── MODULE-EDUCATION-100-COMPLETE.md  ✅
    ├── MODULE-NEWS-100-COMPLETE.md       ✅
    ├── MODULE-LIVE-100-COMPLETE.md       ✅
    ├── PROJECT-STATUS.md                 ✅
    └── QUICK-START.md                    ✅
```

## 🎯 Modules Disponibles

### ✅ Module Mosque (Mosquée)
- Horaires de prière avec calcul automatique
- Khutbas (sermons)
- Événements avec inscriptions
- Export calendrier (iCal, Google)
- **Endpoints**: `/api/v1/mosque/*`
- **Admin**: `/admin/mosque/*`

### ✅ Module Education (Éducation)
- Programmes (Coran, Arabe, Sciences Islamiques)
- Inscriptions avec workflow d'approbation
- Enseignants et promotions
- Génération PDF (certificats, reçus)
- **Endpoints**: `/api/v1/education/*`
- **Admin**: `/admin/education/*`

### ✅ Module News (Actualités)
- Articles avec catégories
- Commentaires avec modération anti-spam
- Flux RSS
- **Endpoints**: `/api/v1/news/*`
- **Admin**: `/admin/news/*`

### ✅ Module Live (Streaming)
- Streaming en direct (RTMP/WHEP/HLS)
- Chat temps réel avec modération
- VOD (enregistrements)
- Analytics viewers
- **Endpoints**: `/api/v1/live/*`, `/api/v1/vod/*`
- **Admin**: `/admin/live/*`

## 🔧 Configuration MediaMTX

### Installation MediaMTX

```bash
# Télécharger MediaMTX
wget https://github.com/bluenviron/mediamtx/releases/download/v1.4.0/mediamtx_v1.4.0_linux_amd64.tar.gz

# Extraire
tar -xzf mediamtx_v1.4.0_linux_amd64.tar.gz

# Configurer mediamtx.yml
vim mediamtx.yml
```

### Configuration `mediamtx.yml`

```yaml
paths:
  all:
    publishUser: admin
    publishPass: secret
    publishIPs: []
    
  main:
    runOnPublish: curl -H "X-Webhook-Secret: your-secret" http://nujumalhuda.com/api/v1/webhooks/mediamtx -d '{"event":"publish","path":"main"}'
    runOnPublishDone: curl -H "X-Webhook-Secret: your-secret" http://nujumalhuda.com/api/v1/webhooks/mediamtx -d '{"event":"publishDone","path":"main"}'
    
  recitation:
    runOnPublish: curl -H "X-Webhook-Secret: your-secret" http://nujumalhuda.com/api/v1/webhooks/mediamtx -d '{"event":"publish","path":"recitation"}'
    runOnPublishDone: curl -H "X-Webhook-Secret: your-secret" http://nujumalhuda.com/api/v1/webhooks/mediamtx -d '{"event":"publishDone","path":"recitation"}'
    
  audio:
    runOnPublish: curl -H "X-Webhook-Secret: your-secret" http://nujumalhuda.com/api/v1/webhooks/mediamtx -d '{"event":"publish","path":"audio"}'
    runOnPublishDone: curl -H "X-Webhook-Secret: your-secret" http://nujumalhuda.com/api/v1/webhooks/mediamtx -d '{"event":"publishDone","path":"audio"}'

webrtc: yes
webrtcAddress: :8889

hls: yes
hlsAddress: :8888

api: yes
apiAddress: 127.0.0.1:9997
```

### Démarrer MediaMTX

```bash
./mediamtx
```

## 🎥 Configuration OBS Studio

1. **Ouvrir OBS Studio**
2. **Settings** → **Stream**
3. **Service**: Custom
4. **Server**: Récupérer depuis Filament Admin (Live Streams → View Stream)
5. **Stream Key**: Copier depuis Filament Admin
6. **Cliquer Start Streaming**

Le stream apparaîtra automatiquement comme "Live" dans l'application.

## 📱 Tests Rapides

### Test API Prayer Times
```bash
curl http://localhost:8000/api/v1/mosque/prayer-times
```

### Test API Programs
```bash
curl http://localhost:8000/api/v1/education/programs
```

### Test API Articles
```bash
curl http://localhost:8000/api/v1/news/articles
```

### Test API Live Streams
```bash
curl http://localhost:8000/api/v1/live/streams/live
```

## 🔐 Comptes par Défaut

### Filament Admin
- **Email**: Défini lors de `php artisan make:filament-user`
- **Password**: Choisi lors de la création

## 📚 Documentation Complète

Pour plus de détails, consulter:
- `MODULE-MOSQUE-100-COMPLETE.md` - Guide Mosque
- `MODULE-EDUCATION-100-COMPLETE.md` - Guide Education
- `MODULE-NEWS-100-COMPLETE.md` - Guide News
- `MODULE-LIVE-100-COMPLETE.md` - Guide Live Streaming
- `PROJECT-STATUS.md` - État global du projet
- `backend/modules/Live/README.md` - Guide technique Live

## 🆘 Troubleshooting

### Backend ne démarre pas
```bash
# Vérifier logs
tail -f storage/logs/laravel.log

# Nettoyer cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Frontend ne se connecte pas à l'API
```bash
# Vérifier CORS dans backend/config/cors.php
# Vérifier NEXT_PUBLIC_API_URL dans frontend/.env.local
```

### WebSocket ne fonctionne pas
```bash
# Vérifier Reverb est démarré
php artisan reverb:start

# Vérifier config broadcasting dans .env
BROADCAST_CONNECTION=reverb
```

### MediaMTX stream ne démarre pas
```bash
# Vérifier MediaMTX est démarré
./mediamtx

# Vérifier logs MediaMTX
# Tester avec ffmpeg
ffmpeg -re -i test.mp4 -c copy -f flv rtmp://localhost:1935/main/test-key
```

## 📞 Support

- **Documentation**: Fichiers .md dans le projet
- **Issues**: Créer un ticket avec logs
- **Email**: tech@nujumalhuda.com

---

**🎉 Bon développement !**
