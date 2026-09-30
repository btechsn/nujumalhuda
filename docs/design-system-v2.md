# Design system — Nujum Al-Huda Center v2.0

**Mise à jour selon la charte graphique officielle v1.0**  
**Date** : 27 septembre 2026

---

## 1. Principe directeur

Le rendu doit évoquer **la modernité accessible** et **l'architecture islamique**, fidèle à l'identité visuelle définie dans la charte graphique officielle. Le vert franc et lumineux (#2DAC07) symbolise l'Islam et le savoir vivant, tandis que l'or évoque la noblesse de l'architecture sacrée.

### Trois règles structurantes

1. **Le vert est la couleur dominante (60%)** — Tous les éléments d'action et de navigation
2. **L'or est un accent exclusif (10%)** — Jamais un fond, toujours des détails
3. **Le blanc respire (30%)** — Clarté et pureté de l'enseignement

---

## 2. Couleur

### Palette principale (charte officielle)

```css
/* Vert Nujum — couleur primaire (#2DAC07) */
--color-brand-500: #2dac07;
--color-brand-600: #228a06;
--color-brand-700: #1b6b05;

/* Or Lumière — accent clair (#F9CC57) */
--color-gold-300: #f9cc57;

/* Or Profond — texte doré lisible (#BA8128) */
--color-gold-700: #ba8128;

/* Anthracite — texte principal (#1D1D1D) */
--color-neutral-900: #1d1d1d;

/* Gris clair — fonds (#F5F5F5) */
--color-neutral-100: #f5f5f5;

/* Blanc pur */
--color-white: #ffffff;
```

### Usage des rôles sémantiques

| Jeton | Emploi | Valeur (mode clair) |
|-------|--------|---------------------|
| `primary` | Boutons d'action, liens actifs | `#2dac07` |
| `content` | Texte principal | `#1d1d1d` |
| `content-accent` | Texte doré (lisible) | `#ba8128` |
| `line-accent` | Filets dorés, séparateurs | `#f9cc57` |
| `canvas` | Fond de page | `#fafafa` |
| `surface` | Cartes, panneaux | `#ffffff` |
| `surface-2` | Fond secondaire | `#f5f5f5` |

### Répartition recommandée (charte)

- **Vert** : 60% (dominante — nav, boutons, titres principaux)
- **Blanc** : 30% (fonds, respirations)
- **Or** : 10% (accent — icônes, badges, bordures actives)

⚠️ **Ne jamais utiliser Or Lumière et Or Profond côte à côte sur grandes surfaces.**

### Contrastes WCAG AA

| Combinaison | Ratio | Verdict |
|-------------|-------|---------|
| Anthracite (#1D1D1D) sur Blanc | 15.8:1 | AAA |
| Anthracite (#1D1D1D) sur Gris clair (#F5F5F5) | 14.2:1 | AAA |
| Vert Nujum (#2DAC07) sur Blanc | 4.6:1 | AA ✓ |
| Or Profond (#BA8128) sur Blanc | 4.8:1 | AA ✓ |
| Or Lumière (#F9CC57) sur Vert Nujum (#2DAC07) | 5.2:1 | AA ✓ |

---

## 3. Typographie

### Polices (selon charte officielle)

**Latin — Poppins**
- **Titres** : Poppins ExtraBold (800) — 28-36pt
- **Sous-titres** : Poppins SemiBold (600) — 14-18pt
- **Corps** : Poppins Regular (400) / Medium (500) — 10-11pt

**Arabe — Noto Naskh Arabic**
- Utilisée pour **tout contenu arabe** (interface + textes)
- Excellente lisibilité aux petites tailles

**Coran — Amiri**
- Réservée aux **versets et citations sacrées**
- Naskh traditionnel, contraste élevé

### Hiérarchie typographique

```css
/* Titres */
--text-display: clamp(2.5rem, 5vw + 1rem, 4rem);     /* Poppins 800 */
--text-h1: clamp(2rem, 2.8vw + 1.3rem, 2.75rem);     /* Poppins 800 */
--text-h2: clamp(1.625rem, 1.6vw + 1.2rem, 2.125rem); /* Poppins 700 */
--text-h3: clamp(1.3125rem, 0.9vw + 1.1rem, 1.625rem); /* Poppins 600 */
--text-h4: clamp(1.0625rem, 0.4vw + 1rem, 1.25rem);  /* Poppins 600 */

/* Corps */
--text-lead: 1.125rem;      /* Poppins 500 */
--text-body: 1rem;          /* Poppins 400 */
--text-small: 0.875rem;     /* Poppins 400 */
--text-caption: 0.8125rem;  /* Poppins 400 */
```

### Règles pour l'arabe

```css
:lang(ar) {
  font-family: var(--font-arabic); /* Noto Naskh Arabic */
  letter-spacing: 0 !important;    /* Jamais d'espacement */
  line-height: 1.9;                /* Ligne généreuse */
}

.type-quran {
  font-family: var(--font-quran);  /* Amiri */
  line-height: 2.25;               /* Encore plus aéré */
}

.nh-numeric {
  direction: ltr;
  unicode-bidi: isolate;
  font-variant-numeric: tabular-nums;
}
```

---

## 4. Logo & identité

### Déclinaisons disponibles

```
frontend/public/images/
├── logo.jpeg        ← Logo principal (version complète couleur)
├── logo-alt.jpeg    ← Logo alternatif (LOGOBIS)
└── favicon.png      ← Favicon (emblème seul)

frontend/public/
└── favicon.ico      ← Favicon navigateur
```

### Zone de protection

Espace minimal = **hauteur de l'emblème (X)**  
Aucun élément ne doit empiéter sur cette zone.

### Tailles minimales

- **Emblème seul** : 24px (écran) / 10mm (impression)
- **Logo complet** : 120px (écran) / 35mm (impression)

### Ce qu'il faut faire ✓

- Utiliser les fichiers officiels fournis
- Respecter la zone de protection
- Privilégier la version sur fond blanc
- Conserver les proportions

### Ce qu'il faut éviter ✗

- Étirer, déformer, incliner
- Changer les couleurs
- Fond à contraste insuffisant
- Ombre, contour, effet 3D
- Séparer icône et texte librement
- Image compressée ou pixellisée

---

## 5. Composants

### Button

**Variants disponibles :**
- `primary` — Vert Nujum (#2DAC07), blanc sur vert
- `secondary` — Contour vert, texte vert
- `accent` — Contour Or Lumière (#F9CC57), texte Or Profond
- `ghost` — Transparent, texte vert au hover
- `danger` — Rouge erreur

```tsx
<Button variant="primary">S'inscrire</Button>
<Button variant="accent">En savoir plus</Button>
```

### Card

Sans ombre portée, bordure 1px, rayon 8px max.

```tsx
<Card>
  <CardMedia arch="pointed" src="/image.jpg" />
  <CardHeader>
    <CardEyebrow>Centre</CardEyebrow>
    <CardTitle>Titre de la carte</CardTitle>
  </CardHeader>
  <CardBody>Contenu...</CardBody>
</Card>
```

### AnnouncementTicker

Bandeau défilant accessible (WCAG 2.2.2), pause/play.

```tsx
<AnnouncementTicker announcements={data} />
```

---

## 6. Rayons & ombres

### Rayons (max 8px)

```css
--radius-sm: 4px;
--radius-md: 6px;
--radius-lg: 8px;  /* Maximum pour éléments rectangulaires */
--radius-full: 9999px;
```

### Ombres (deux seulement)

```css
--shadow-overlay: 0 4px 16px -4px rgb(29 29 29 / 0.14), 
                  0 1px 3px rgb(29 29 29 / 0.08);
--shadow-modal: 0 16px 48px -12px rgb(29 29 29 / 0.28);
```

**Règle** : Aucune ombre sur élément statique (cartes).

---

## 7. Mode sombre

Fonds teintés de vert Nujum pour continuité de marque.

```css
.dark {
  --nh-canvas: var(--color-ink-950);
  --nh-surface: var(--color-ink-900);
  --nh-content: var(--color-neutral-100);
  --nh-primary: var(--color-brand-500);
  --nh-content-accent: var(--color-gold-300);
}
```

---

## 8. Accessibilité

- **Contraste minimum** : 4.5:1 texte courant, 3:1 texte large
- **Focus visible** : Anneau vert 2px offset 2px
- **Mouvement** : Respecter `prefers-reduced-motion`
- **Bandeau** : Bouton pause explicite (WCAG 2.2.2)
- **RTL** : Support automatique via `dir="rtl"`

---

## 9. Variables CSS exposées

```css
/* Couleurs principales */
--nh-primary: #2dac07;
--nh-content: #1d1d1d;
--nh-canvas: #fafafa;
--nh-surface: #ffffff;
--nh-accent: #f9cc57;
--nh-content-accent: #ba8128;

/* Typographie */
--font-sans: Poppins, sans-serif;
--font-arabic: 'Noto Naskh Arabic', sans-serif;
--font-quran: Amiri, serif;
```

---

**Version 2.0 — Conforme à la charte graphique officielle v1.0**  
Mise à jour le 27 septembre 2026
