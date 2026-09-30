# Nujum Al-Huda Institute Center — Plateforme éducative islamique

**مركز نجوم الهدى للتعليم والتربية الإسلامية**  
*Institut franco-anglo-arabe d'enseignement islamique — Dakar, Sénégal*

## 🎯 Présentation

Plateforme complète pour l'**Institut Nujum Al-Huda**, établissement d'enseignement coranique et islamique, avec :
- Gestion des programmes et inscriptions
- Diffusion en direct des récitations et enseignements (< 1 seconde de latence)
- Horaires de prière dynamiques
- Bandeau d'annonces en temps réel
- Interface trilingue (français, anglais, arabe) avec support RTL
- Progressive Web App (PWA) installable

### 🌐 Nom et domaine

**Nom officiel complet** : **Nujum Al-Huda Institute Center**  
**Domaine** : **[nujumalhuda.com](https://nujumalhuda.com)** ✅

Le domaine est volontairement court et mémorisable, tandis que le nom complet "Institute Center" apparaît dans tout le contenu du site (footer, à propos, métadonnées SEO, signatures email, etc.).

📚 Voir [`docs/DOMAIN-STRATEGY.md`](./docs/DOMAIN-STRATEGY.md) pour la stratégie complète.

## 🏗️ Architecture

### Stack technique

| Composant | Technologie |
|-----------|-------------|
| Backend | Laravel 11 |
| Frontend | Next.js 14 (App Router) |
| Base de données | PostgreSQL 16 |
| Cache & Queues | Redis 7 |
| Temps réel | Laravel Reverb (WebSocket) |
| Streaming | MediaMTX (RTMP → WHEP/HLS) |
| Admin | Filament v3 |
| Infrastructure | Docker Compose |

### Modularité

Architecture en **monolithe modulaire** avec 10 modules métier indépendants pour l'Institut :

- **Core** : Utilisateurs, organisations, rôles, notifications, médias, paiements
- **Announcements** : Bandeau d'annonces diffusé en temps réel
- **Education** : Programmes, cours, promotions, inscriptions
- **Mosque** : Horaires de prière, événements, khutbas
- **Live** : Streaming RTMP/WHEP, sessions de récitation, VOD
- **News** : Blog, commentaires modérés
- **Resources** : Bibliothèque numérique, zakat
- **Academics** : Suivi du hifz, évaluations, ijazas
- **Community** : Dons, témoignages, questions aux enseignants
- **Dahira** : Gestion de dahira (Phase 7)

## 🚀 Démarrage rapide

### Prérequis

- Docker & Docker Compose
- Make (optionnel, pour les raccourcis)

### Installation

```bash
# 1. Cloner le dépôt
git clone https://github.com/votre-org/nujumalhuda.git
cd nujumalhuda

# 2. Copier les fichiers d'environnement
cp .env.example .env
cd backend && cp .env.example .env && cd ..
cd frontend && cp .env.example .env && cd ..

# 3. Démarrer les services Docker
make up
# OU : docker-compose up -d

# 4. Installer les dépendances backend
make install-backend
# OU : docker-compose exec app composer install

# 5. Générer la clé Laravel
make key-generate
# OU : docker-compose exec app php artisan key:generate

# 6. Lancer les migrations
make migrate
# OU : docker-compose exec app php artisan migrate --seed

# 7. Installer les dépendances frontend
make install-frontend
# OU : docker-compose exec next npm install
```

L'application est accessible sur :
- **Frontend** : http://localhost (port 80)
- **API** : http://localhost:8000/api/v1
- **Admin Filament** : http://localhost:8000/admin
- **WebSocket** : ws://localhost/ws

### Identifiants par défaut

```
Email    : admin@nujumalhuda.com
Password : password
```

### Endpoints API principaux

```bash
# Programmes d'enseignement
GET /api/v1/education/programs
GET /api/v1/education/programs/{id}
GET /api/v1/education/promotions

# Horaires de prière (aujourd'hui)
GET /api/v1/mosque/prayer-times
GET /api/v1/mosque/prayer-times/{date}

# Articles de blog
GET /api/v1/news/articles
GET /api/v1/news/articles/{slug}

# Enseignants
GET /api/v1/education/teachers
```

### Variables d'environnement clés

```env
# Domaine (confirmé: nujumalhuda.com)
APP_URL=https://nujumalhuda.com
FRONTEND_URL=https://nujumalhuda.com

# Base de données
DB_DATABASE=nujumalhuda
DB_USERNAME=nujumalhuda
DB_PASSWORD=CHANGE_ME

# Laravel Reverb
REVERB_APP_KEY=CHANGE_ME
REVERB_APP_SECRET=CHANGE_ME

# MediaMTX (streaming)
MEDIAMTX_RTMP_URL=rtmp://ingest.nujumalhuda.com:1935
MEDIAMTX_WHEP_URL=https://nujumalhuda.com/whep
```

## 📦 Commandes Make

```bash
make help              # Afficher toutes les commandes disponibles
make up                # Démarrer les services
make down              # Arrêter les services
make logs              # Voir les logs (SERVICE=app pour filtrer)
make shell             # Shell dans le conteneur app
make migrate           # Lancer les migrations
make seed              # Lancer les seeders
make test              # Lancer les tests
make lint              # Vérifier le code (Pint + PHPStan)
make fix               # Corriger automatiquement le code
```

## 🌍 Internationalisation (i18n)

Le site est trilingue par défaut :
- **Français** (fr) — langue pivot
- **Anglais** (en)
- **Arabe** (ar) — avec support RTL complet

### Politique de repli

- L'**interface** est traduite intégralement dans les trois langues
- Le **contenu éditorial** a le français comme pivot obligatoire
- Une page sans traduction affiche le français avec une mention visible
- Les contenus religieux en arabe ne sont **jamais** traduits automatiquement

### Fichiers de traduction

```
frontend/messages/
├── fr.json
├── en.json
└── ar.json
```

## 🎨 Design System

Palette principale :
- **Vert émeraude** (`brand-700` #0B5A31) — couleur primaire
- **Or métallique** (`gold-500` #C8971F) — accent exclusivement
- **Ivoire** (`ivory-50` à `ivory-950`) — neutres chauds
- **Encre** (`ink-500` à `ink-950`) — fonds mode sombre

### Règles

- **L'or est un accent**, jamais un fond
- **Pas de dégradés** ni d'ombres sur les cartes statiques
- **Budget de 2 éléments dorés maximum** par écran
- **Rayon maximum : 8px** (sauf l'arc en clip-path)
- **Contraste minimum : 4.5:1** pour le texte courant

Typographie :
- **IBM Plex Serif** — titres latins
- **IBM Plex Sans** — corps et UI latins
- **IBM Plex Sans Arabic** — titres et corps arabes
- **Amiri** — textes coraniques

### Composants disponibles

```tsx
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { AnnouncementTicker } from '@/components/ui/announcement-ticker';
import { SiteHeader } from '@/components/layout/site-header';
import { LocaleSwitcher } from '@/components/layout/locale-switcher';
```

## 📡 Streaming en direct

### Architecture

```
OBS Studio → RTMP (port 1935) → MediaMTX → WHEP (WebRTC) + HLS
                                              ↓
                                    Frontend (< 1 seconde)
```

### Configuration OBS

```
Serveur : rtmp://ingest.nujumalhuda.com:1935/main
Clé de diffusion : <voir MEDIAMTX_PUBLISH_PASS>
```

### Canaux disponibles

- `main` — Canal principal (enseignements, khutba, événements)
- `recitation` — Sessions de récitation des élèves
- `audio` — Mode audio seul (faible bande passante)

## 🧪 Tests

```bash
# Tests backend (Pest)
make test
# OU : docker-compose exec app php artisan test

# Tests avec couverture
make test-coverage

# Tests frontend
cd frontend && npm test
```

## 🔒 Sécurité & Conformité

### Données sensibles

- **Données de mineurs** : consentement parental obligatoire
- **Diffusion des récitations** : restreinte par défaut aux tuteurs
- **RGPD** : droit à l'effacement outillé
- **Audit** : toutes les actions critiques sont journalisées

### Authentification

- Sanctum avec cookies httpOnly
- Origine unique (pas de CORS)
- Limitation de débit sur les endpoints d'authentification

## 📝 État d'avancement

### ✅ Phase 1 : Core & Announcements (100%)
- [x] Module Core (Users, Organizations, Roles, Notifications, Media, Payments)
- [x] Module Announcements (bandeau temps réel)
- [x] 16 migrations
- [x] API REST endpoints
- [x] Filament admin

### ✅ Phase 2 : Education, Mosque, News (100%)
- [x] **Module Education** : 8 migrations, 8 modèles, 4 contrôleurs, 4 Filament resources
  - Programmes, cours, leçons, promotions, inscriptions, sessions, assiduité, enseignants
  - Workflow d'inscription complet (pending → approved → active → completed)
- [x] **Module Mosque** : 5 migrations, 5 modèles, 3 contrôleurs, 4 Filament resources
  - Horaires de prière avec API Aladhan (Dakar), override manuel, iqama configurable
  - Calendrier hijri, détection Ramadan, khutbas, événements
- [x] **Module News** : 3 migrations, 3 modèles, 3 contrôleurs, 3 Filament resources
  - Articles, catégories, commentaires modérés, SEO
- [x] **Seeders** : 10 seeders avec 36 entrées de données réalistes
- [x] **Documentation** : 10 fichiers de documentation complète

**Status** : Backend + Admin + Seeders opérationnels | 🚀 Prêt pour le frontend

### 🚧 Phase 3 : Frontend Next.js (En attente)
- [ ] Pages publiques : Prayer Times, Programs, Enrollment, Teachers, News
- [ ] Composants réutilisables
- [ ] Intégration API REST
- [ ] Design System v2

### 📅 Phases futures
- [ ] **Phase 4** : Live & streaming (conditionné à la qualification du VPS)
- [ ] **Phase 5** : PWA avancée, notifications push
- [ ] **Phase 6** : Academics, Resources, Community
- [ ] **Phase 7** : Paiements Wave/Orange Money
- [ ] **Phase 8** : Module Dahira (sans modifier les tables existantes)

## 📄 Documentation

### Documents principaux
- [Cahier des charges](./cahier-des-charges-nujum-al-huda.md)
- [Design system](./docs/design-system.md)
- [Architecture canvas](./canvases/nujum-al-huda-modular-architecture.canvas.tsx)

### Guides de démarrage
- [⚡ QUICK-START.md](./QUICK-START.md) — Démarrage en 5 minutes
- [🚀 QUICK-START-PHASE2.md](./docs/QUICK-START-PHASE2.md) — Guide Phase 2 spécifique

### Documentation technique Phase 2
- [📐 IMPLEMENTATION-PHASE2.md](./docs/IMPLEMENTATION-PHASE2.md) — Architecture détaillée
- [🎨 FILAMENT-ADMIN-COMPLETE.md](./docs/FILAMENT-ADMIN-COMPLETE.md) — Guide admin complet
- [🌱 SEEDERS-GUIDE.md](./docs/SEEDERS-GUIDE.md) — Documentation seeders
- [🏆 PHASE2-FINAL-SUMMARY.md](./PHASE2-FINAL-SUMMARY.md) — Récapitulatif Phase 2
- [🌐 DOMAIN-STRATEGY.md](./docs/DOMAIN-STRATEGY.md) — Stratégie nujumalhuda.com

## 🤝 Contribution

Voir [CONTRIBUTING.md](./CONTRIBUTING.md) pour les conventions de code et le workflow Git.

## 📜 Licence

Copyright © 2026 Nujum Al-Huda Institute Center. Tous droits réservés.

---

**Foi — Savoir — Éducation — Éthique — Excellence**
