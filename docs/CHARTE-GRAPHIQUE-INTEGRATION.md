# 🎨 Intégration de la Charte Graphique — Nujum Al-Huda Center

**Date** : 27 septembre 2026  
**Version de la charte** : 1.0  
**Statut** : ✅ Intégré dans le design system

---

## 📋 Changements effectués

### 1. **Palette de couleurs — Mise à jour complète**

#### Avant (version initiale)
- Vert principal : `#0B5A31` (vert émeraude foncé)
- Or : `#C8971F` 
- Neutres : Échelle ivoire chaude

#### Après (charte officielle v1.0)
- **Vert Nujum** : `#2DAC07` (RGB 45, 172, 7) — vert franc et lumineux
- **Or Lumière** : `#F9CC57` (RGB 249, 204, 87) — accent clair
- **Or Profond** : `#BA8128` (RGB 186, 129, 40) — texte doré lisible
- **Anthracite** : `#1D1D1D` (RGB 29, 29, 29) — texte principal
- **Gris clair** : `#F5F5F5` (RGB 245, 245, 245) — fonds neutres
- **Blanc** : `#FFFFFF`

### 2. **Typographie — Changement complet**

#### Avant
- **Titres latins** : IBM Plex Serif
- **Corps latin** : IBM Plex Sans
- **Arabe** : IBM Plex Sans Arabic

#### Après (charte officielle)
- **Latin (tout)** : **Poppins** ExtraBold (800) pour titres, Regular/Medium pour corps
- **Arabe** : **Noto Naskh Arabic** (meilleur rendu petites tailles)
- **Coran** : **Amiri** (inchangé, traditionnel)

### 3. **Logos et favicons**

**Fichiers intégrés :**
```
frontend/public/
├── favicon.ico              ← FAVICON.jpeg
├── manifest.json            ← Manifeste PWA avec theme_color #2DAC07
└── images/
    ├── logo.jpeg            ← LOGO.jpeg (logo principal)
    ├── logo-alt.jpeg        ← LOGOBIS.jpeg (logo alternatif)
    └── favicon.png          ← FAVICONPNG.jpeg
```

**Symbolique du logo (selon la charte) :**
- **Dôme doré** : Minaret et coupole de la mosquée
- **Enfants et Coran** : Transmission du savoir coranique
- **Croissant vert** : Symbole de l'Islam et de la croissance du savoir

---

## 📐 Règles d'usage extraites de la charte

### Répartition des couleurs (règle 60-30-10)
- **60%** Vert Nujum comme couleur dominante
- **30%** Blanc pour fonds et respirations
- **10%** Or en accent (titres, liens, éléments décoratifs)

⚠️ **Important** : Ne jamais utiliser Or Lumière et Or Profond côte à côte sur grandes surfaces.

### Zone de protection du logo
- Espace minimal = hauteur de l'emblème (X)
- Aucun élément ne doit empiéter sur cette zone

### Tailles minimales
- **Emblème seul** : Minimum 24px (écran) ou 10mm (impression)
- **Logo complet** : Minimum 120px (écran) ou 35mm (impression)

### À faire ✓
- Utiliser les fichiers logo officiels fournis
- Respecter la zone de protection
- Utiliser version sur fond blanc autant que possible
- Conserver les proportions lors du redimensionnement

### À éviter ✗
- Étirer, déformer ou incliner le logo
- Changer les couleurs officielles
- Placer sur fond à contraste insuffisant
- Ajouter ombre, contour ou effet 3D
- Recomposer le logo (séparer icône et texte)
- Utiliser image compressée ou pixellisée

---

## 🔧 Fichiers modifiés

### Design System
- ✅ `design/tokens.css` — Palette complète mise à jour
- ✅ `frontend/src/lib/fonts.ts` — Poppins + Noto Naskh Arabic
- ✅ `frontend/public/manifest.json` — theme_color #2DAC07

### Variables CSS impactées

**Couleurs sémantiques (mode clair) :**
```css
--nh-primary: #2dac07       /* Vert Nujum 500 */
--nh-content: #1d1d1d       /* Anthracite */
--nh-content-accent: #ba8128 /* Or Profond (lisible) */
--nh-line-accent: #f9cc57   /* Or Lumière */
--nh-canvas: #fafafa        /* Gris très clair */
--nh-surface: #ffffff       /* Blanc pur */
```

**Typographie :**
```css
--font-sans: Poppins, sans-serif
--font-arabic: 'Noto Naskh Arabic', sans-serif
--font-quran: Amiri, serif
```

---

## 📊 Correspondances PANTONE (charte)

| Couleur | HEX | PANTONE | CMJN |
|---------|-----|---------|------|
| Vert Nujum | #2DAC07 | 361 C | 74 · 0 · 96 · 33 |
| Or Lumière | #F9CC57 | 1215 C | 0 · 18 · 65 · 2 |
| Or Profond | #BA8128 | 1255 C | 0 · 31 · 78 · 27 |
| Anthracite | #1D1D1D | — | 0 · 0 · 0 · 89 |

---

## ✅ Validation de l'intégration

### Contrôles effectués
- [x] Palette complète dans `tokens.css`
- [x] Fonts Poppins + Noto Naskh Arabic chargées
- [x] Logo principal placé dans `/public/images/`
- [x] Favicon configuré (`.ico` + `.png`)
- [x] Manifeste PWA avec theme_color correct
- [x] Variables sémantiques cohérentes (light + dark)
- [x] Trame géométrique SVG mise à jour avec nouvelles couleurs

### Tests recommandés
- [ ] Vérifier rendu des titres avec Poppins ExtraBold
- [ ] Contrôler lisibilité de l'Or Profond (#BA8128) sur blanc
- [ ] Valider contraste Anthracite (#1D1D1D) sur Gris clair (#F5F5F5)
- [ ] Tester affichage du logo aux tailles minimales (24px, 120px)
- [ ] Vérifier favicon sur différents navigateurs

---

## 📚 Références

- **Charte graphique** : `Charte_Graphique_Nujum_Al-Huda_Institute_Center.pdf`
- **Design system** : `docs/design-system.md` (à mettre à jour)
- **Tokens CSS** : `design/tokens.css`

---

**Intégration réalisée par l'équipe technique le 27/09/2026**  
Conforme à la charte graphique officielle v1.0
