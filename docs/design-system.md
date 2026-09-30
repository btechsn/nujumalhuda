# Design system — Nujum Al-Huda Center

Référence normative du rendu. Toute page du site s'y conforme ; une
exception se discute et se consigne, elle ne s'improvise pas dans un
composant.

---

## 1. Principe directeur

Le rendu doit évoquer l'imprimé institutionnel et l'architecture
islamique épurée, pas l'application grand public. Trois conséquences
concrètes, qui expliquent la plupart des règles qui suivent :

- **La hiérarchie passe par le filet et l'espace, pas par l'ombre.** Une
  carte statique n'a jamais d'ombre portée. La profondeur se lit dans un
  trait d'un pixel et dans un écart de fond entre `surface` et
  `surface-2`.
- **L'or est un accent, jamais un fond.** Un aplat doré fait basculer
  l'ensemble vers le clinquant, exactement l'inverse de l'effet
  recherché.
- **Une seule licence formelle : l'arc.** Toute l'expressivité
  architecturale est concentrée dans une forme, appliquée avec parcimonie.
  Le reste est rectangulaire et calme.

---

## 2. Couleur

### Usage des rôles sémantiques

Les composants n'utilisent **que** les jetons sémantiques. Les échelles
(`brand-700`, `gold-500`, `ivory-200`) sont réservées à `globals.css`.

| Jeton | Emploi |
| --- | --- |
| `canvas` | Fond de page |
| `surface` | Cartes, panneaux, barres |
| `surface-2` | Encadré secondaire, ligne alternée, survol discret |
| `muted` | Réserve d'image, champ désactivé |
| `inverse` | Sections vertes pleine largeur, en-tête de service, pied de page |
| `content` / `content-secondary` / `content-muted` | Trois niveaux de texte, pas quatre |
| `content-accent` | Texte doré, déjà corrigé en contraste selon le mode |
| `line` / `line-strong` / `line-accent` | Filets. `line-accent` est doré |
| `primary` | Vert d'action |
| `accent` | Or décoratif |

La palette Tailwind par défaut est effacée dans `globals.css`
(`--color-*: initial`). Écrire `bg-blue-500` ne produit aucune classe :
la contrainte est structurelle, pas déclarative.

### Règles d'emploi de l'or

Autorisé :

- filets d'un pixel, séparateurs, soulignement de surtitre (2 px) ;
- étoile à huit branches et glyphes décoratifs jusqu'à 24 px ;
- texte sur fond vert foncé, avec la nuance `gold-300` ;
- contour et texte du bouton `accent` ;
- marqueur de l'onglet de navigation actif.

Interdit :

- tout aplat de plus de 48 × 48 px ;
- tout fond de bouton ;
- tout texte doré sur fond clair en dehors de `content-accent` ;
- tout dégradé, doré ou non.

**Budget : deux éléments dorés au maximum par écran visible.** Au-delà,
l'accent n'accentue plus rien.

### Contrainte de contraste à connaître

L'or de la marque (`gold-500`, `#c8971f`) plafonne à **2,6:1 sur blanc** :
il échoue à la norme AA pour du texte. C'est une propriété de la couleur,
pas un défaut de la palette, et c'est la raison technique de la règle
« l'or n'est pas un fond ».

| Combinaison | Contraste | Verdict |
| --- | --- | --- |
| `gold-500` sur blanc | 2,6:1 | Décor uniquement |
| `gold-700` sur blanc | 5,2:1 | Texte AA — c'est `content-accent` en clair |
| `gold-300` sur `brand-800` | 9,3:1 | Texte AAA — l'or lisible vit sur le vert |
| `brand-700` sur blanc | 9,8:1 | Texte AAA |
| blanc sur `brand-700` | 9,8:1 | Bouton primaire |

### Mode sombre

Les fonds sombres sont **teintés de vert** (échelle `ink`), pas gris
neutres : un gris pur romprait la continuité avec la marque. Inversion
intéressante, l'or devient enfin lisible en texte sur ces fonds, il peut
donc être légèrement plus présent qu'en mode clair.

Le mode est décidé côté serveur, par la classe `dark` sur `<html>`. Aucun
clignotement au chargement.

---

## 3. Typographie

| Rôle | Fonte | Emploi |
| --- | --- | --- |
| `font-serif` | IBM Plex Serif | Titres latins |
| `font-sans` | IBM Plex Sans | Texte courant et interface latins |
| `font-arabic` | IBM Plex Sans Arabic | Tout l'arabe, titres et interface |
| `font-quran` | Amiri | Versets et citations sacrées **uniquement** |

Le choix d'IBM Plex est structurel : sans, serif et sans arabe sont
dessinés ensemble, aux hauteurs d'x et aux graisses alignées. Associer
Inter à Cairo, par exemple, produit une bascule visible au changement de
langue, faute d'harmonisation.

### Hiérarchie

Une page ne fixe jamais sa taille de texte à la main : elle emploie une
classe `.type-*`. Toutes sont fluides par `clamp()`.

| Classe | Taille | Emploi |
| --- | --- | --- |
| `.type-display` | 40 → 64 px | Hero d'accueil. Une occurrence par page |
| `.type-h1` | 32 → 44 px | Titre de page |
| `.type-h2` | 26 → 34 px | Titre de section |
| `.type-h3` | 21 → 26 px | Sous-section |
| `.type-h4` | 17 → 20 px | Titre de carte. En sans, pas en serif |
| `.type-lead` | 18 px | Chapeau introductif |
| `.type-eyebrow` | 12 px | Surtitre, capitales espacées, doré |
| `.type-quran` | 24 px / 2,25 | Versets, naskh, interligne très ample |

### Arabe — quatre règles non négociables

Elles sont appliquées par `globals.css` sur `:lang(ar)`, mais il faut les
connaître pour ne pas les contourner dans un composant.

1. **Interlettrage à zéro, toujours.** Espacer les lettres détruit les
   ligatures de l'écriture arabe et rend le mot illisible. La règle porte
   un `!important` : c'est l'un des rares cas où il est justifié.
2. **Interligne plus généreux.** 1,9 contre 1,65 en latin, à cause des
   hampes et des diacritiques. Les versets montent à 2,25.
3. **Taille majorée de 6 %.** À taille nominale égale, l'arabe paraît
   plus petit que le latin.
4. **Ni italique ni graisse synthétique.** `font-synthesis: none`, faute
   de quoi le navigateur fabrique des formes fautives. L'emphase se rend
   par la graisse réelle, pas par l'inclinaison.

Deux corollaires côté composants :

- **Pas de capitales en arabe.** La casse n'existe pas dans cette
  écriture ; `.type-eyebrow` reprend sa casse naturelle sur `:lang(ar)`.
- **Les nombres s'isolent.** Un horaire ou un numéro de sourate inséré
  dans un paragraphe arabe se réordonne à l'affichage. Toute valeur
  numérique porte `.nh-numeric` (`direction: ltr` + `unicode-bidi:
  isolate`). C'est **obligatoire** sur les horaires de prière.

### RTL

- Espacements et bordures en **propriétés logiques** : `ps-4`, `pe-4`,
  `ms-2`, `me-2`, `border-s`, `border-e`, `text-start`, `text-end`.
  Jamais `pl-`, `pr-`, `text-left`, `text-right`.
- Les icônes directionnelles (chevrons, flèches, lecture) portent
  `.nh-flip`, qui les retourne sous `[dir="rtl"]`.
- Le **logo ne se retourne jamais**, ni aucun pictogramme symétrique.
- Le bandeau défile **dans le sens de lecture** : l'inversion est portée
  par CSS, pas par JavaScript.

---

## 4. Mise en page

### Espacement

Base de 4 px. L'échelle utile est courte : `1 2 3 4 6 8 12 16 20 24 32`.
Les valeurs intermédiaires (`5`, `7`, `9`) ne servent qu'à un ajustement
optique justifié.

| Contexte | Valeur |
| --- | --- |
| Interstice dans un composant | `gap-2` / `gap-3` |
| Composant → composant | `gap-4` / `gap-6` |
| Padding de carte | `p-5` |
| Bloc → bloc dans une section | `gap-8` / `gap-12` |
| Rythme de section | `.nh-section` (56 → 104 px) |
| Section dense | `.nh-section-tight` |

Toute section passe par `.nh-container` : c'est ce qui garantit
l'alignement vertical d'un bout à l'autre du site. Largeur maximale
1 312 px, gouttières 20 → 40 px.

Mesure de lecture : `.measure`, soit 68 caractères en latin et 60 en
arabe, la densité de diacritiques fatiguant l'œil plus vite.

### Rayons

| Élément | Rayon |
| --- | --- |
| Bouton, champ, badge carré | `rounded-sm` (4 px) |
| Carte, panneau, menu | `rounded-md` (6 px) |
| Grand visuel, encadré | `rounded-lg` (8 px) |
| Pastille, pilule, point | `rounded-full` |

**Jamais au-delà de 8 px** pour un rectangle. Un grand rayon lit
« application grand public » et défait le registre institutionnel. Les
rayons supérieurs ne sont pas dans la config : la classe n'existe pas.

### Ombres

Le système compte **deux ombres**, et aucune ne s'applique à un élément
statique.

| Ombre | Emploi |
| --- | --- |
| `shadow-overlay` | Menu déroulant, popover, infobulle |
| `shadow-modal` | Fenêtre modale, panneau latéral |

Une carte, une section, un en-tête, une image : jamais d'ombre. Si un
élément semble manquer de détachement, la réponse est un filet ou un
changement de fond, pas une ombre.

### L'arc

La seule forme expressive. Obtenue par `clip-path` avec
`clipPathUnits="objectBoundingBox"`, donc responsive, contrairement à un
`clip-path: path()`.

- `.nh-arch` — arc brisé, registre maghrébin. Visuels de programme, de
  mosquée et d'installation, hero.
- `.nh-arch-round` — plein cintre, plus neutre. Vignettes de liste.

**Au maximum un motif d'arc par écran.** Une grille entière d'arcs cesse
d'être une référence architecturale pour devenir un effet de trame.

### La trame géométrique

Étoile à huit branches (khātam), reprise du logo, en tracé seul à 7 %
d'opacité. Réservée aux **fonds verts** et aux **hero**, jamais derrière
du texte courant. `.nh-pattern` trace en vert, `.nh-pattern-on-dark`
trace en or.

---

## 5. Accessibilité — seuils de recette

- Contraste **4,5:1** sur le texte courant, **3:1** sur le texte large et
  les contours de contrôle. Les combinaisons dorées sont tabulées au
  point 2.
- Focus visible sur tout élément actionnable : anneau de 2 px en `ring`,
  décalé de 2 px. Jamais de `outline: none` sans remplacement.
- Tout contenu en mouvement de plus de cinq secondes est **arrêtable**
  (WCAG 2.2.2). Le bandeau a un bouton de pause explicite, en plus de
  l'arrêt au survol et au focus.
- `prefers-reduced-motion` est respecté : le bandeau ne démarre pas et
  devient une liste défilable à la main.
- Lien d'évitement vers `#nh-main` en tête de document.
- Zone tactile minimale de 40 × 40 px sur mobile.

---

## 6. Préférences utilisateur

Le cahier des charges impose quatre réglages persistants. Tous se pilotent
par un attribut ou une classe sur `<html>`, décidés côté serveur pour
éviter tout clignotement au chargement. **Aucun composant n'a à connaître
l'état de ces préférences.**

| Préférence | Porteur | Mécanisme |
| --- | --- | --- |
| Mode sombre | `class="dark"` | Réaffecte les jetons `--nh-*` |
| Fonte arabe | `data-arabic-font` | Réaffecte `--nh-font-arabic` et `--nh-font-quran` |
| Échelle de texte | `data-text-scale` | Multiplie la taille racine |
| Faible bande passante | `data-bandwidth="low"` | Coupe le décor, bascule la vidéo en audio |

### Fonte arabe commutable

Le système sépare déjà la fonte d'interface de la fonte sacrée, ce qui
rend la bascule triviale : seules deux variables changent.

| Valeur | `--nh-font-arabic` | `--nh-font-quran` | Public |
| --- | --- | --- | --- |
| `simplified` (défaut) | IBM Plex Sans Arabic | Amiri | Lecteurs habitués aux formes modernes |
| `uthmani` | Noto Naskh Arabic | Amiri Quran | Lecteurs formés au tracé uthmani |

Le tracé uthmani est celui des mus'haf imprimés : pour un élève en
mémorisation, retrouver exactement les formes de son support papier n'est
pas un confort, c'est une aide à la mémoire visuelle.

### Échelle de texte

Toute l'échelle typographique est exprimée en `rem` et en `clamp()`. Un
multiplicateur sur la taille racine suffit donc, sans reprendre une seule
valeur. Aucune taille en pixels n'existe dans le système, et c'est
précisément pour cette raison.

| Valeur | Racine | Effet |
| --- | --- | --- |
| `normal` | 100 % | Référence |
| `large` | 112,5 % | 18 px de base |
| `xl` | 125 % | 20 px de base |

### Mode faible bande passante

Destiné aux connexions mobiles limitées, fréquentes à Dakar, où le coût
des données est un frein réel.

Sous `data-bandwidth="low"` :

- la trame géométrique (`.nh-pattern`) n'est pas peinte ;
- les visuels purement décoratifs sont masqués, les visuels informatifs
  conservés ;
- le lecteur vidéo démarre sur la piste audio seule ;
- les images passent en qualité réduite et en chargement différé
  systématique.

Ce mode n'est pas un thème dégradé : le contenu reste intégralement
accessible, seul le décor disparaît.

---

## 7. Ce qu'on ne fait pas

Liste courte et opposable en revue de code.

- Aucun dégradé, nulle part.
- Aucune ombre sur un élément statique.
- Aucun aplat doré, aucun bouton doré plein.
- Aucun rayon supérieur à 8 px sur un rectangle.
- Aucune couleur écrite en dur : ni hexadécimal, ni `rgb()`, ni classe
  Tailwind par défaut dans un composant.
- Aucune taille de texte arbitraire : on passe par `.type-*`.
- Aucun `pl-` / `pr-` / `text-left` / `text-right` — logique uniquement.
- Aucun interlettrage sur de l'arabe.
- Aucune capitale sur de l'arabe.
- Aucun nombre non isolé dans un contexte arabe.
- Plus d'un `.type-display` par page.
- Plus de deux éléments dorés par écran.
- Aucune lecture directe des préférences utilisateur dans un composant :
  elles vivent sur `<html>`, en CSS.

---

## 8. Portée — les deux interfaces

Ce document régit **les deux interfaces du projet**, et elles partagent la
même source de vérité.

```
design/tokens.css                                   SOURCE UNIQUE
├── frontend/src/app/globals.css                    → site public, Next.js
└── backend/resources/css/filament/admin/theme.css  → back-office, Filament
```

`design/tokens.css` contient l'intégralité de la couleur, de la
typographie, des rayons, des ombres et de l'échelle typographique. Les
deux interfaces l'importent. **La marque n'est pas reproduite dans le
back-office, elle y est importée** : un changement de vert se propage aux
deux en une seule édition, et la divergence devient impossible par
construction.

### Ce que le thème Filament aligne

| Aspect | Alignement |
| --- | --- |
| Palette | Intégral — les onze nuances de chaque échelle |
| Typographie | Intégral — IBM Plex, avec la fonte arabe pour l'interface RTL |
| Mode sombre | Intégral — sur les fonds à l'encre teintée de vert |
| RTL et règles arabes | Intégral — interlettrage nul, interligne élargi, nombres isolés |
| Rayons et ombres | Intégral — les rayons généreux et les ombres statiques de Filament sont ramenés aux règles du point 4 |
| Or en accent | Intégral — filet d'or sur l'élément de navigation actif, comme l'onglet actif du site |
| Logo, favicon, connexion | Intégral — la page de connexion reprend le fond vert et la trame géométrique |

### La seule limite, et pourquoi elle est acceptée

La **géométrie interne des composants Filament** — structure des
tableaux, disposition des champs, panneaux latéraux — reste celle de
Filament. L'aligner au pixel obligerait à surcharger son balisage, donc à
reprendre ces surcharges à chaque montée de version, sur une interface
utilisée par une dizaine de personnes en interne. Le rapport entre le
coût et le bénéfice ne le justifie pas.

La marque est donc portée intégralement ; c'est le vocabulaire de
composants qui reste celui de l'outil.

