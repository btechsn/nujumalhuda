# 🌐 Stratégie de domaine — Nujum Al-Huda Institute Center

**Date** : 27 septembre 2026  
**Décision validée** : ✅ CONFIRMÉ

---

## 🎯 Décision

### Nom officiel complet
**Nujum Al-Huda Institute Center**  
مركز نجوم الهدى للتعليم والتربية الإسلامية

### Nom de domaine
**`nujumalhuda.com`** ✅

---

## 📝 Rationale

### Pourquoi un domaine plus court que le nom officiel ?

**Avantages du domaine simple `nujumalhuda.com`** :

1. **✅ Mémorisation facile**
   - Pas de tirets : `nujumalhuda.com` vs ~~`nujum-al-huda-institute.com`~~
   - Court : 13 caractères
   - Phonétiquement simple

2. **✅ Communication orale**
   - Facile à épeler par téléphone
   - Pas de confusion sur les tirets
   - Pas d'ambiguïté sur "center" vs "centre"

3. **✅ Identité de marque**
   - Le nom "Nujum Al-Huda" est la partie distinctive
   - "Institute Center" = descripteur (pas besoin dans l'URL)
   - Cohérence avec les usages (exemple : `harvard.edu` pas `harvarduniversity.edu`)

4. **✅ SEO & Références**
   - URL courte plus facile à partager
   - Meilleur pour les réseaux sociaux
   - Pas de dilution avec des mots génériques ("institute", "center")

---

## 🌍 Configuration DNS

### Domaine principal
```
nujumalhuda.com
```

### Sous-domaines (optionnels, futurs)
```
www.nujumalhuda.com       → Redirection vers nujumalhuda.com
api.nujumalhuda.com       → API backend (si besoin Phase 3+)
admin.nujumalhuda.com     → Filament (si séparation Phase 3+)
ingest.nujumalhuda.com    → MediaMTX RTMP (streaming)
```

**Note** : Selon l'architecture validée, **origine unique** privilégiée :
- Tout passe par `nujumalhuda.com` avec routage Nginx par chemin
- Pas de sous-domaines dans un premier temps (évite CORS)

---

## 📧 Emails

### Format recommandé
```
contact@nujumalhuda.com
info@nujumalhuda.com
admissions@nujumalhuda.com
support@nujumalhuda.com
```

### Signatures email
**Format institutionnel** :

```
[Prénom NOM]
[Titre/Fonction]

Nujum Al-Huda Institute Center
Institut d'enseignement islamique
مركز نجوم الهدى للتعليم والتربية الإسلامية

📧 prenom.nom@nujumalhuda.com
🌐 www.nujumalhuda.com
📍 Dakar, Sénégal
```

---

## 🔗 URLs & Structure

### Pages principales
```
https://nujumalhuda.com/                    (Accueil)
https://nujumalhuda.com/fr/about            (À propos - français)
https://nujumalhuda.com/en/about            (About - english)
https://nujumalhuda.com/ar/about            (عن المركز - arabe)
https://nujumalhuda.com/fr/programs         (Programmes)
https://nujumalhuda.com/fr/admissions       (Inscriptions)
https://nujumalhuda.com/fr/contact          (Contact)
```

### API & Admin
```
https://nujumalhuda.com/api/v1              (API REST)
https://nujumalhuda.com/admin               (Filament)
https://nujumalhuda.com/ws                  (WebSocket Reverb)
```

### Streaming
```
rtmp://ingest.nujumalhuda.com:1935          (Ingestion RTMP)
https://nujumalhuda.com/hls                 (Diffusion HLS)
https://nujumalhuda.com/whep                (Diffusion WHEP)
```

---

## 📱 Réseaux sociaux

### Handles recommandés
- **Facebook** : `@NujumAlHudaInstitute` ou `@NujumAlHuda`
- **Instagram** : `@nujumalhuda` ou `@nujumalhuda.institute`
- **Twitter/X** : `@NujumAlHuda`
- **TikTok** : `@nujumalhuda`
- **YouTube** : `@NujumAlHudaInstitute`
- **LinkedIn** : `nujum-al-huda-institute-center`

**Bio recommandée** :
```
🕌 Institut Nujum Al-Huda
📚 Enseignement coranique & islamique
🌍 Dakar, Sénégal
🔗 nujumalhuda.com
```

---

## 🎨 Mentions dans le contenu

### Footer du site
```html
<footer>
  <p>
    © 2026 Nujum Al-Huda Institute Center
    <br>
    Institut d'enseignement islamique, Dakar
  </p>
  <p>
    <a href="https://nujumalhuda.com">nujumalhuda.com</a>
  </p>
</footer>
```

### Métadonnées SEO
```html
<title>Institut Nujum Al-Huda — Enseignement coranique à Dakar</title>
<meta name="description" content="Nujum Al-Huda Institute Center : Institut franco-anglo-arabe d'enseignement du Coran et de l'éducation islamique à Dakar, Sénégal" />
<link rel="canonical" href="https://nujumalhuda.com" />

<!-- Open Graph -->
<meta property="og:site_name" content="Nujum Al-Huda Institute" />
<meta property="og:url" content="https://nujumalhuda.com" />

<!-- Twitter Card -->
<meta name="twitter:url" content="https://nujumalhuda.com" />
```

---

## 📊 Cohérence Nom / Domaine

| Contexte | Format |
|----------|--------|
| **Nom officiel complet** | Nujum Al-Huda Institute Center |
| **Domaine** | nujumalhuda.com |
| **Email** | contact@nujumalhuda.com |
| **Réseaux sociaux** | @NujumAlHuda ou @NujumAlHudaInstitute |
| **Signatures officielles** | Nujum Al-Huda Institute Center + nujumalhuda.com |
| **PWA Manifest** | "Nujum Al-Huda Institute Center" |
| **Footer** | © Nujum Al-Huda Institute Center |

---

## ✅ Validation

### Nom vs Domaine
- ✅ **Nom** : Complet et institutionnel ("Institute Center")
- ✅ **Domaine** : Court et mémorisable (`nujumalhuda.com`)
- ✅ **Cohérence** : Le nom complet apparaît dans le contenu du site
- ✅ **Clarté** : Pas de confusion (domaine ≠ nom officiel)

### Exemples de références mondiales
- **Harvard University** → `harvard.edu` (pas `harvarduniversity.edu`)
- **Massachusetts Institute of Technology** → `mit.edu`
- **Stanford University** → `stanford.edu`
- **Al-Azhar University** → `azhar.edu.eg`

**Principe** : Le domaine capte l'identité distinctive, le nom complet donne le contexte institutionnel.

---

## 🚀 Configuration technique

### Fichiers à vérifier

1. **`docker-compose.yml`**
   ```yaml
   environment:
     - APP_URL=https://nujumalhuda.com
     - FRONTEND_URL=https://nujumalhuda.com
     - SESSION_DOMAIN=null
   ```

2. **`infra/nginx/conf.d/nujumalhuda.conf`**
   ```nginx
   server_name nujumalhuda.com www.nujumalhuda.com;
   ```

3. **`frontend/next.config.mjs`**
   ```js
   images: {
     domains: ['nujumalhuda.com'],
   }
   ```

4. **`frontend/public/manifest.json`**
   ```json
   {
     "name": "Nujum Al-Huda Institute Center",
     "start_url": "https://nujumalhuda.com"
   }
   ```

5. **Backend `.env`**
   ```env
   APP_URL=https://nujumalhuda.com
   SANCTUM_STATEFUL_DOMAINS=nujumalhuda.com
   ```

---

## 📋 Checklist finale

Avant mise en production :

- [ ] DNS pointent vers le VPS
- [ ] Certificat SSL Let's Encrypt configuré
- [ ] Redirection `www.nujumalhuda.com` → `nujumalhuda.com`
- [ ] Force HTTPS activé dans Nginx
- [ ] Nom complet "Institute Center" visible dans :
  - [ ] Footer du site
  - [ ] Page "À propos"
  - [ ] Métadonnées SEO
  - [ ] Certificats/diplômes
  - [ ] Signatures emails
- [ ] Domaine `nujumalhuda.com` cohérent partout :
  - [ ] Variables d'environnement
  - [ ] Configuration Nginx
  - [ ] Manifeste PWA
  - [ ] Réseaux sociaux

---

## 📚 Références

- **Convention de nommage** : `docs/NAMING-CONVENTION.md`
- **Charte graphique** : `Charte_Graphique_Nujum_Al-Huda_Institute_Center.pdf`
- **Architecture réseau** : Origine unique, routage par chemin (voir canvas)

---

**Décision confirmée le 27 septembre 2026** ✅

**Nom officiel** : Nujum Al-Huda Institute Center  
**Domaine** : `nujumalhuda.com`  
**Stratégie** : Nom complet institutionnel + domaine court mémorisable
