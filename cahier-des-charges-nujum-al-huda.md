# Cahier des Charges — Plateforme Nujum Al-Huda Center

**مركز نجوم الهدى للتعليم والتربية الإسلامية — دكار**
*Nujum Al-Huda Center for Islamic Education and Upbringing — Dakar*

**Version 2.0 — Septembre 2026**

> Cette version révise la v1.0 après validation de l'architecture. Les
> écarts sont récapitulés en annexe A. Les décisions techniques arrêtées
> font foi et sont détaillées dans `docs/architecture.md`.

---

## 1. Contexte

Nujum Al-Huda Center est un établissement d'enseignement islamique à
Dakar, doté de sa propre mosquée réservée aux élèves. Il dispense :

- l'apprentissage du Coran, mémorisation et récitation ;
- l'enseignement de la langue arabe ;
- les œuvres de Cheikh Ibrahim Niasse (Baye Niasse) ;
- le corpus sunnite classique.

La plateforme doit faire rayonner le centre au-delà de ses murs :
diffusion des enseignements en direct, présence unifiée sur les réseaux
sociaux, communication avec la communauté, et à terme gestion d'un dahira
intégré.

**Devise** : Foi – Savoir – Éducation – Éthique – Excellence

### 1.1 Identité visuelle

Vert émeraude, blanc ivoire, doré, dérivés du logo existant (coupole,
minaret, Coran ouvert, étoile et croissant).

Le design system est **déjà produit et livré** : palette complète en modes
clair et sombre, système typographique trilingue, composants de base,
principes de mise en page. Il fait foi et se trouve dans
`docs/design-system.md`. Toute page s'y conforme.

### 1.2 Objectifs

1. Offrir une vitrine institutionnelle claire du centre.
2. **Alimenter les inscriptions** : permettre à une famille de découvrir
   un programme, de s'informer et de déposer une demande d'inscription en
   ligne.
3. Centraliser la diffusion des contenus en direct.
4. Permettre le suivi à distance des sessions de mémorisation.
5. Informer la communauté en temps réel.
6. Poser une architecture capable d'intégrer un module Dahira sans
   refonte.
7. Renforcer l'engagement spirituel et communautaire.

> **Ajout en v2.0** — L'objectif 2 était absent de la v1.0, alors que
> recruter des élèves est le besoin économique premier de
> l'établissement. Il devient prioritaire, au même rang que la vitrine.

### 1.3 Ce que le projet n'est pas

Délimitation explicite, pour éviter la dérive de périmètre :

- ce n'est pas un réseau social ni une messagerie ;
- ce n'est pas une plateforme de cours en ligne autonome : l'enseignement
  reste présentiel, le site le prolonge ;
- ce n'est pas un système de gestion scolaire complet (notes,
  bulletins officiels, facturation détaillée) ;
- ce n'est pas un substitut à WhatsApp, que la communauté utilise déjà et
  continuera d'utiliser.

---

## 2. Acteurs et rôles

Les rôles sont portés par la table `memberships`, donc toujours évalués
dans le périmètre d'une organisation. Un même utilisateur peut être
enseignant au centre et membre d'un dahira.

| Rôle | Droits | Volume estimé |
|---|---|---|
| Visiteur | Consultation, lives, bandeau, localisation, demande d'inscription | Public |
| Élève | Espace personnel : progression, sessions, ressources de son niveau | Dizaines à centaines |
| Parent / tuteur | Suivi de la progression de ses enfants, via `guardianships` | Dizaines à centaines |
| Enseignant | Ses cours, lancement de sessions live, évaluation des récitations, publication pédagogique | Unités à dizaines |
| Admin communication | Bandeau, réseaux sociaux, actualités, galerie | 1 à 3 |
| Admin général | Gestion complète, configuration, utilisateurs | 1 à 2 |

Un parent peut être rattaché à plusieurs élèves, et un élève à plusieurs
tuteurs : la relation est une table à part entière, pas un champ.

---

## 3. Périmètre fonctionnel

Le système compte **dix modules** partageant le socle Core. La v1.0
énumérait quinze rubriques, dont cinq étaient en réalité des
fonctionnalités ou des préoccupations transverses ; elles ont été
réaffectées (annexe A).

**Règle de dépendance** : tout module dépend de Core ; aucun module métier
n'importe le code d'un autre. La communication passe par des événements de
domaine. Seule exception déclarée : Academics lit les sessions de
récitation de Live.

### 3.1 Core — socle commun

- Utilisateurs : inscription par **email ou téléphone**, mot de passe,
  réinitialisation. Le téléphone est le premier identifiant au Sénégal.
- Organisations : le centre, sa mosquée, et plus tard les dahiras.
  Table auto-référente typée.
- Adhésions et rôles, scopés par organisation.
- **Tutelles** : relation parent ↔ élève, plusieurs à plusieurs.
- Notifications : in-app, push PWA, email, et **SMS via le wrapper Orange
  SMS Pro existant**, avec préférences par catégorie et par canal.
- Médias : dépôt, conversions, métadonnées.
- **Modération** : table polymorphe partagée. Cinq entités du projet
  réclament une modération (commentaires, témoignages, questions, sujets
  et réponses de forum) ; la mutualiser évite de l'écrire cinq fois.
- **Paiements** : registre polymorphe et interface `PaymentGateway`, en
  place dès la phase 1. Aucune passerelle implémentée avant la phase 6.
- Réglages, journal d'audit, relevés d'analytics.

### 3.2 Education

- Programmes : Coran, arabe, œuvres de Baye Niasse, corpus sunnite.
- Cours et leçons rattachés à un programme.
- **Promotions** : une promotion est une exécution datée d'un programme.
  Le cursus et la classe qui le suit sont deux objets distincts, sans quoi
  rejouer un programme obligerait à le dupliquer.
- Inscriptions, avec statut et parcours de validation.
- Séances planifiées, présences.
- Fiches enseignants : biographie, spécialité, photo, sanad éventuel.
- Fiches élèves, accès restreint selon le rôle.
- **Entonnoir d'inscription public** : formulaire de demande, pièces
  justificatives, suivi du dossier, confirmation.

### 3.3 Mosque

- Horaires des cinq prières, calculés via l'API Aladhan **et toujours
  surchargeables par l'imam**. La valeur saisie manuellement prime sur la
  valeur calculée, sans exception : des horaires publiés qui diffèrent de
  l'appel du muezzin décrédibilisent l'établissement.
- Décalages d'iqama par prière, avec période de validité.
- Annonces propres à la mosquée.
- Informations pratiques : accès, capacité, règles.
- **Mode Jumu'a** : le vendredi, la khutba et l'annonce du jour remontent
  automatiquement en page d'accueil. État calculé, aucune table.
- Calendrier hégirien, compte à rebours Ramadan et fêtes.
- **Localisation** : carte Leaflet et OpenStreetMap, adresse, itinéraire,
  accès en transport. Carte statique, sans suivi GPS.

### 3.4 Live & Médias

- **Réseaux sociaux** :
  - YouTube et Facebook : embeds officiels.
  - TikTok et Instagram : **redirection soignée vers le live natif**.
    Aucune API publique ne permet un embed fiable du direct.
  - Les identifiants de comptes sont **configurables en base**, jamais
    codés en dur : les comptes officiels restent à créer.
- **Streaming propre** :
  - Ingestion RTMP, diffusion en **WebRTC/WHEP** avec repli LL-HLS.
    Le HLS classique accuse six à vingt secondes de retard, incompatible
    avec une correction de récitation en direct.
  - Lecteur intégré, avec bascule automatique entre les deux protocoles.
  - **Variante audio seule**, indispensable au mode faible bande passante.
  - Enregistrement automatique, archive VOD rejouable.
- **Sessions de récitation, double déclenchement** :
  - planification prévisionnelle par l'enseignant selon l'avancement ;
  - déclenchement événementiel dès qu'un élève termine un palier.
  Les deux mécanismes coexistent et sont distingués en base.
- Archive classée par thème, enseignant et date.
- Chat de direct modéré, diffusé par Reverb.

### 3.5 Announcements — bandeau d'informations

- Bandeau défilant sur toutes les pages, diffusé en temps réel par
  WebSocket : une annonce apparaît sans rechargement.
- Priorisation, catégories, fenêtre de validité.
- **Ciblage d'audience** : tout public, membres, élèves d'une promotion,
  ou porteurs d'un rôle.
- Réutilisable par tout module, y compris le futur dahira.
- Le bandeau porte l'information courte et éphémère ; le blog porte le
  contenu long et archivé. La confusion des deux est le premier travers
  de ce type d'outil.

### 3.6 News — actualités et blog

- Articles rédigés par les admins et les enseignants.
- Catégories : vie du centre, enseignements, communauté, événements.
- Commentaires **authentifiés et modérés**, réponses imbriquées.
- Partage social, avec **partage WhatsApp en premier** : c'est le canal
  réel de circulation de l'information au Sénégal.
- Recherche et filtrage.

### 3.7 Resources — spiritualité et ressources

- Bibliothèque numérique : PDF et audio des œuvres de Baye Niasse et des
  textes sunnites classiques.
- Récitations audio du Coran en écoute libre.
- Verset et hadith du jour en page d'accueil, dans les trois langues.
- **Calculateur de zakat**, avec mention explicite de l'école juridique
  retenue, du nisab employé (or ou argent) et de la source savante. Un
  calcul non sourcé sur le site d'un institut islamique est un problème de
  crédibilité avant d'être un problème technique.

### 3.8 Academics — suivi pédagogique

- Progression du hifz par **paliers et badges**, sans classement
  compétitif entre élèves.
- Évaluation des récitations par l'enseignant, adossée aux sessions
  diffusées.
- **Attestations de niveau** générées automatiquement, en PDF partageable.
- **Ijaza** : objet distinct, jamais généré automatiquement. Une ijaza est
  accordée par un enseignant nommé, avec sa chaîne de transmission. Elle
  est saisie, signée et attribuée à la main. Confondre les deux serait
  inexact sur le fond et dommageable pour l'institution.
- Portail parent : niveau atteint, sessions suivies, prochaine étape.
- Quiz courts de tajwid et de vocabulaire.

### 3.9 Community — communauté et soutien

- Dons ponctuels et parrainage d'élève, mur des donateurs.
- Témoignages d'anciens élèves et de parents, modérés.
- Galerie photo et vidéo des événements.
- **« Demander à un enseignant »** : questions religieuses, réponses
  publiées, constituant une FAQ vivante.
- Inscriptions aux événements, avec confirmation de présence.
- **Digest SMS mensuel** plutôt qu'une newsletter email : la délivrabilité
  email est médiocre dans la région, et le wrapper Orange existe déjà.
- **Espaces de discussion** — voir 3.9.1.

#### 3.9.1 Espaces de discussion

La v1.0 prévoyait un forum communautaire public. Le problème n'était pas
la discussion, mais le **caractère public et anonyme** : c'est ce qui rend
la modération ingérable et expose l'institution.

La discussion est donc conservée, sous une forme radicalement différente.

| Propriété | Forum public de la v1.0 | Espace de discussion retenu |
|---|---|---|
| Accès | Ouvert à tous | **Membres authentifiés uniquement** |
| Portée | Un forum global | **Rattaché à un périmètre** : une promotion, un dahira, une organisation |
| Identité | Pseudonyme possible | Membre connu, avec son rôle |
| Modérateur | À recruter | **Déjà présent** : l'enseignant de la promotion, l'officier du dahira |
| Risque | Élevé, non maîtrisé | Faible : chacun répond de ses propos devant son groupe |

Implémentation : une table `discussions` rattachée par polymorphisme à un
`discussable` — promotion, dahira ou organisation — et adossée à la table
`moderations` de Core.

**Le point décisif est la modération.** Un forum public exige de recruter
un modérateur, ce qui n'existait pas. Un espace rattaché à une promotion
est modéré par l'enseignant de cette promotion, qui est déjà là et qui
connaît chacun de ses élèves. La modération devient une conséquence de la
structure, et non une charge supplémentaire.

Ouverture progressive, par périmètre :

1. **Phase 5** — espaces de promotion : les parents et l'enseignant d'une
   même classe. Périmètre réduit, modérateur évident, valeur immédiate.
2. **Phase 7** — espaces de dahira, avec le module Dahira, modérés par les
   officiers du dahira.
3. **Jamais par défaut** — aucun espace public global. Si le besoin s'en
   fait sentir, il se traitera comme une décision distincte, avec son
   modérateur et sa charte.

### 3.10 Dahira — phase 7

Le dahira n'est pas un module périphérique : c'est **la structure
communautaire du centre**, et le projet est conçu depuis le début pour
l'accueillir sans refonte.

- Groupes et membres, adhésions et statuts.
- Cotisations, échéanciers, relances, trésorerie.
- Réunions, convocations, registre de présence.
- **Espaces de discussion** rattachés au dahira (3.9.1).
- Annonces ciblées sur les membres du dahira.
- SMS via le wrapper Orange.

**Réutilisation intégrale de Core**, et c'est là toute la promesse
architecturale :

| Besoin du Dahira | Brique de Core, en place dès la phase 1 |
|---|---|
| Un dahira comme entité | `organizations`, table auto-référente typée |
| Membres et rôles | `memberships`, scopés par organisation |
| Cotisations | `payments`, registre polymorphe et `PaymentGateway` |
| Convocations | `notifications`, canal SMS Orange |
| Annonces internes | `announcements` avec ciblage d'audience |
| Discussions modérées | `discussions` et `moderations` |

Le module n'apporte donc que sa logique propre : échéanciers, trésorerie,
registre de présence. **Critère d'acceptation de la phase 7** : aucune
migration ne modifie une table d'un module existant. C'est le test final
de la modularité, et la raison pour laquelle `organizations`,
`memberships` et `payments` entrent dans Core dès la phase 1 alors que
rien ne les y oblige encore.

### 3.11 Écarté du périmètre, avec motif

Décisions assumées, à rediscuter si le contexte change.

| Fonctionnalité v1.0 | Décision | Motif |
|---|---|---|
| Forum communautaire **public** | **Reformulé** en espaces de discussion par périmètre (3.9.1) | Ce n'est pas la discussion qui posait problème, c'est son caractère public et anonyme. Rattachée à une promotion ou à un dahira, elle trouve son modérateur dans la structure elle-même. |
| Newsletter email | **Remplacée** par un digest SMS | Délivrabilité email faible dans la région ; le canal SMS existe déjà et est lu. |
| Navigation audio-first | **Reformulée** | Un parcours audio sur mesure sera moins bon qu'un site correctement balisé lu par un lecteur d'écran. L'engagement porte donc sur la conformité WCAG AA réelle, vérifiée avec NVDA et VoiceOver, plutôt que sur un mode bespoke. |
| Suivi GPS temps réel | **Écarté** | Aucun objet mobile à suivre. Carte statique. |

Aucun module n'est retiré du périmètre. Community et Dahira sont
maintenus en entier.

---

## 4. Exigences transverses

### 4.1 Trilinguisme — et sa politique de repli

Le site est trilingue : français, anglais, arabe. L'arabe impose le sens
d'écriture RTL sur l'ensemble de l'interface.

**Le risque principal de ce projet n'est pas technique, il est éditorial** :
trente pages multipliées par trois langues font quatre-vingt-dix contenus
à produire et à maintenir. Sans politique explicite, le site s'ouvre avec
des pages vides.

Politique retenue :

- l'**interface** est traduite intégralement dans les trois langues, dès
  la phase 1 ;
- le **contenu éditorial** a le français pour langue pivot obligatoire ;
- une page dont la traduction manque **affiche le français avec une
  mention visible**, plutôt que de disparaître du menu ou de renvoyer une
  erreur ;
- les **contenus religieux en arabe** ne sont jamais traduits
  automatiquement ;
- chaque phase de livraison intègre son budget de traduction, faute de
  quoi la fonctionnalité est livrée mais le contenu absent.

### 4.2 Accessibilité

- Conformité **WCAG 2.2 niveau AA**, vérifiée sur les trois langues.
- Contrastes : 4,5:1 sur le texte courant, 3:1 sur le texte large et les
  contours de contrôle. La palette vert et or a été auditée à cet effet :
  l'or de la marque plafonne à 2,6:1 sur blanc et ne peut donc pas porter
  de texte sur fond clair. Les nuances conformes sont fixées dans le
  design system.
- Tout contenu en mouvement de plus de cinq secondes est arrêtable. Le
  bandeau dispose d'un bouton de pause explicite.
- `prefers-reduced-motion` respecté.
- Navigation clavier complète, focus visible, lien d'évitement.
- **Fonte arabe commutable** entre tracé uthmani et tracé simplifié.
  Retrouver les formes de son mus'haf imprimé n'est pas un confort pour un
  élève en mémorisation, c'est une aide à la mémoire visuelle.
- **Agrandissement de texte** configurable, jusqu'à 125 %.

### 4.3 Performance — objectifs chiffrés

La v1.0 visait « un chargement rapide », ce qui n'est pas mesurable. Les
seuils ci-dessous sont des critères de recette, mesurés sur la page
d'accueil et sur une page de programme, dans les trois langues.

| Indicateur | Cible | Contexte de mesure |
|---|---|---|
| LCP | ≤ 2,5 s | 4G médiane |
| LCP | ≤ 4,0 s | 3G lente, mobile d'entrée de gamme |
| INP | ≤ 200 ms | — |
| CLS | ≤ 0,1 | — |
| JS initial | ≤ 180 Ko compressés | Pages publiques |
| Poids total | ≤ 800 Ko | Page d'accueil, premier chargement |
| Lighthouse performance | ≥ 90 | Mobile, trois langues |
| Lighthouse accessibilité | 100 | Trois langues |
| API, p95 | ≤ 300 ms | Endpoints de lecture publics |

**Mode faible bande passante**, activable et persistant : décor et trame
géométrique supprimés, images en qualité réduite, lecteur vidéo en audio
seul. Le contenu reste intégralement accessible ; seul l'ornement
disparaît.

### 4.4 PWA et notifications

- Installable, manifeste et icônes complets.
- Coque applicative précachée, pages consultées disponibles hors ligne.
- **Cache hors ligne des ressources de l'élève** : la leçon en cours, son
  audio et son PDF. C'est la fonctionnalité la plus utile du mode hors
  ligne pour un élève dont le forfait données est compté.
- Écritures hors ligne **hors périmètre** en phases 1 à 4 : la
  synchronisation différée impose une résolution de conflits qui est un
  chantier à part entière.
- **Gouvernance des notifications** : tout est désactivé par défaut,
  l'abonnement est explicite et par catégorie. Une notification à chaque
  prière fait cinq alertes par jour, donc une désinstallation. Les
  horaires de prière sont donc désactivés par défaut.

### 4.5 Recherche globale

Indexation des programmes, articles, VOD et ressources de la
bibliothèque. PostgreSQL ne fournit pas de dictionnaire plein texte pour
l'arabe : `pg_trgm` suffit au lancement, et le basculement vers
Meilisearch devient nécessaire dès que la bibliothèque arabe devient un
point d'entrée du site.

### 4.6 Mode sombre

Disponible sur l'ensemble du site, l'usage tôt le matin et tard le soir
étant fréquent. Décidé côté serveur, sans clignotement au chargement.
Livré avec le design system.

---

## 5. Architecture technique

Détail complet et justifications dans `docs/architecture.md`. Synthèse des
décisions arrêtées.

### 5.1 Stack

| Couche | Choix |
|---|---|
| Backend | Laravel, API REST versionnée `/api/v1` |
| Temps réel | Laravel Reverb |
| Back-office | **Filament**, servi par Laravel sur `/admin` |
| Frontend | Next.js App Router, PWA |
| Base de données | PostgreSQL |
| Cache, files, sessions | Redis |
| Streaming | MediaMTX, RTMP en entrée, WHEP et LL-HLS en sortie |
| Infrastructure | Docker Compose, VPS, reverse proxy nginx |
| i18n | next-intl, chemins traduits, RTL |

### 5.2 Décisions arrêtées

| Sujet | Décision | Conséquence |
|---|---|---|
| Domaine | `nujumalhuda.com`, **origine unique** | Routage par chemin, aucune configuration CORS, service worker pouvant cacher l'API |
| Modules | Répertoire `modules/` en PSR-4 manuel | Aucune dépendance tierce structurelle |
| Clé primaire | ULID `char(26)` | Génération possible côté client, non énumérable |
| Authentification | Sanctum, cookies httpOnly host-only | Session partagée avec Filament, isolation naturelle entre environnements |
| Latence vidéo | WHEP WebRTC, repli LL-HLS | Correction de récitation en direct possible |
| Devise et fuseau | XOF, Africa/Dakar | **Le XOF n'a pas de décimale** : montants en `amount_minor` |
| Services Docker | Neuf | nginx, app, queue, scheduler, reverb, next, db, redis, mediamtx |

### 5.3 Répartition des interfaces

Point structurant, précisé par rapport à la v1.0.

| Interface | Technologie | Contenu |
|---|---|---|
| Site public et espace membre | Next.js | Vitrine, inscription, espace élève, portail parent, espaces de discussion |
| **Console enseignant** | Next.js, espace membre | Lancement d'une session, présence en direct, évaluation de récitation. Temps réel et mobilité : Livewire y serait inadapté. |
| **Back-office** | Filament | Tout le CRUD et la configuration : programmes, élèves, articles, modération, bibliothèque, horaires, réglages, analytics |

Ce découpage réduit l'API d'environ **cent cinquante endpoints à une
quarantaine** : elle ne sert plus que le public et les membres, jamais
l'administration.

### 5.5 Le back-office porte la marque

Le back-office **n'est pas** une interface générique aux couleurs
approximatives. Il consomme le même fichier de jetons que le site public.

`design/tokens.css` est la source unique de vérité pour la couleur, la
typographie, les rayons, les ombres et l'échelle typographique. Il est
importé par les deux interfaces :

```
design/tokens.css
├── frontend/src/app/globals.css                    → site public
└── backend/resources/css/filament/admin/theme.css  → back-office
```

La marque n'est donc pas reproduite dans Filament, elle y est **importée**.
Un changement de vert se propage aux deux interfaces en une seule édition,
et la divergence devient impossible par construction.

| Aspect | Alignement |
|---|---|
| Palette | **Intégral** — les onze nuances de chaque échelle, déclarées dans le panneau Filament |
| Typographie | **Intégral** — IBM Plex Sans et Serif, IBM Plex Sans Arabic pour l'interface arabe |
| Mode sombre | **Intégral** — sur les fonds à l'encre teintée de vert du site |
| RTL | **Intégral** — Filament le prend en charge nativement, avec les règles arabes du design system |
| Rayons et ombres | **Intégral** — les rayons généreux et les ombres statiques de Filament sont ramenés aux règles du système |
| Logo, favicon, connexion | **Intégral** — la page de connexion reprend le fond vert et la trame géométrique |
| Géométrie interne des composants | **Partiel, assumé** — la structure des tableaux et des champs reste celle de Filament. Les forcer au pixel obligerait à surcharger son balisage, donc à reprendre ces surcharges à chaque montée de version, pour une interface interne de dix personnes. |

### 5.4 Chemins réservés

`/api`, `/sanctum`, `/broadcasting`, `/admin`, `/livewire`, `/ws`, `/hls`,
`/whep`, `/storage`, `/.well-known`. Ces préfixes appartiennent à nginx et
ne peuvent pas devenir des segments de route Next.js.

---

## 6. Modèle de données

Schéma détaillé, colonnes, relations et index dans
`docs/architecture.md`. Les tables sont réparties par module et migrées
dans un ordre garantissant que Core précède tout le reste.

| Module | Tables |
|---|---|
| Core | `users`, `organizations`, `memberships`, `guardianships`, `roles` et dérivées, `media`, `notifications`, `notification_preferences`, `push_subscriptions`, `moderations`, `payments`, `settings`, `audit_logs`, `analytics_snapshots` |
| Education | `programs`, `courses`, `lessons`, `cohorts`, `enrollments`, `enrollment_applications`, `class_sessions`, `attendances`, `instructor_profiles`, `lesson_completions` |
| Mosque | `mosques`, `prayer_times`, `iqama_offsets`, `mosque_events`, `khutbahs`, `hijri_events` |
| Live | `live_channels`, `live_streams`, `external_streams`, `recitation_sessions`, `stream_chat_messages`, `stream_viewer_samples` |
| Announcements | `announcements`, `announcement_audiences`, `announcement_reads` |
| News | `articles`, `article_categories`, `comments` |
| Resources | `library_resources`, `audio_recitations`, `daily_contents` |
| Academics | `hifz_milestones`, `student_progress`, `recitation_evaluations`, `certificates`, `ijazas`, `badges`, `quizzes`, `quiz_attempts` |
| Community | `donations`, `sponsorships`, `testimonials`, `gallery_items`, `questions`, `events`, `event_registrations`, `sms_digest_subscribers`, `discussions`, `discussion_messages` |
| Dahira | `dahira_groups`, `contribution_plans`, `contributions`, `contribution_schedules`, `meetings`, `meeting_attendances`, `treasury_entries` — phase 7 |

Conventions : ULID en clé primaire, `timestamptz` en UTC, textes
multilingues en `jsonb` `{fr, en, ar}`, énumérations en `varchar` sous
contrainte `CHECK`, montants en unité mineure avec code devise.

---

## 7. Exigences non fonctionnelles

| Domaine | Engagement |
|---|---|
| Responsive | Mobile-first, de 360 px au grand écran |
| Sécurité | HTTPS obligatoire, HSTS, limitation de débit sur l'API, politique de mots de passe, journal d'audit |
| Disponibilité | **99,5 %** sur un VPS unique. Promettre 99,9 % sans redondance serait mensonger |
| RPO | 24 h — sauvegarde quotidienne chiffrée |
| RTO | 4 h — procédure de restauration **testée avant la mise en production** |
| SEO | Rendu serveur, `hreflang` sur les trois langues, sitemap par locale, URL canoniques, données structurées |
| Analytics | Solution auto-hébergée sans cookie (Plausible ou Umami), cohérente avec l'infrastructure Docker et avec la protection des données |
| Rétention des enregistrements | Politique obligatoire dès l'ouverture : une heure de flux 1080p pèse environ 1,5 Go |
| Navigateurs | Deux dernières versions majeures de Chrome, Safari, Firefox et Edge ; Chrome Android et Safari iOS |

---

## 8. Données personnelles, conformité et éthique

**Section entièrement nouvelle.** La v1.0 n'abordait pas le sujet, alors
que la plateforme traite des données d'enfants et publie leur image. C'est
le point le plus sensible du projet, juridiquement et moralement.

### 8.1 Nature des données traitées

| Catégorie | Exemples | Sensibilité |
|---|---|---|
| Identité | Nom, téléphone, email, photo | Courante |
| **Données de mineurs** | Fiches élèves, progression, présences, photos | **Élevée** |
| Filiation | Tutelles parent-élève | Élevée |
| Pratique religieuse | Inscription à un institut islamique | **Données sensibles dans la plupart des régimes** |
| Financier | Dons, cotisations, références Mobile Money | Élevée |
| Voix et image | Récitations enregistrées, diffusées, archivées | **Élevée, et durable** |

### 8.2 Obligations à instruire

À confirmer avec un conseil juridique sénégalais ; le développement ne les
tranche pas.

- **Déclaration auprès de la CDP** (Commission de Protection des Données
  Personnelles), au titre de la loi sénégalaise sur la protection des
  données à caractère personnel.
- **RGPD** applicable dès qu'un membre de la diaspora réside dans l'Union
  européenne, ce qui sera le cas pour un centre à rayonnement
  international. Prévoir base légale, droit d'accès, droit d'effacement et
  registre des traitements.
- **Consentement parental écrit** pour toute publication de l'image, de la
  voix ou du nom d'un élève mineur.

### 8.3 Dispositions techniques exigées

Ces points ne sont pas négociables et conditionnent la mise en ligne.

1. **Drapeau de consentement sur la fiche élève**, distinct par usage :
   image en galerie, voix en récitation diffusée, nom en attestation
   publique. Sans consentement enregistré, la publication est
   techniquement impossible, et non simplement déconseillée.
2. **Diffusion des récitations par défaut restreinte** aux enseignants et
   aux tuteurs de l'élève. Le passage en public est une action explicite.
3. **Aucune donnée de mineur dans les analytics.**
4. **Droit à l'effacement** outillé : une procédure d'anonymisation d'un
   élève, y compris dans les archives VOD.
5. **Durée de conservation** définie par catégorie, et purge automatisée.
6. Sauvegardes chiffrées, accès journalisé.

### 8.4 Éthique éditoriale

- Les attestations de niveau et les **ijazas sont deux objets distincts**.
  Une ijaza suppose un enseignant nommé et une chaîne de transmission ;
  elle n'est jamais générée automatiquement.
- Le calculateur de zakat cite son école juridique et sa source.
- La progression du hifz s'affiche en paliers et en badges, **jamais en
  classement** entre élèves.
- Les réponses de « Demander à un enseignant » sont attribuées à un
  enseignant identifié, et non à l'institution de façon anonyme.

---

## 9. Gouvernance éditoriale et modération

**Section nouvelle.** Quatre surfaces de contribution publique existent :
commentaires, témoignages, questions aux enseignants, chat de direct.
Aucune ne peut ouvrir sans réponse aux questions suivantes.

| Question | À trancher avant l'ouverture de la surface |
|---|---|
| Qui modère ? | Nommer une personne et une suppléance |
| Sous quel délai ? | Un délai de 48 h est un engagement tenable ; l'absence de délai ne l'est pas |
| A priori ou a posteriori ? | **A priori** recommandé pour les questions religieuses, a posteriori acceptable pour les commentaires |
| Sur quelle charte ? | Charte publique, opposable, traduite dans les trois langues |
| Quel recours ? | Procédure de contestation d'un retrait |

**Règle de livraison** : une surface de contribution n'est mise en ligne
que lorsque son modérateur est nommé. Le code peut être livré et la
fonctionnalité rester désactivée.

---

## 10. Exploitation

**Section nouvelle.** Une chaîne de diffusion sans opérateur est un
investissement inerte.

| Sujet | À définir |
|---|---|
| **Opérateur du direct** | Qui installe la caméra, lance OBS, vérifie le son. Nommer la personne et prévoir sa formation |
| **Matériel** | Caméra, micro, encodeur, connexion montante du centre. À qualifier : un direct 1080p demande un débit montant stable |
| Procédure de secours | Que faire si le direct tombe pendant une khutba |
| **Qualification du VPS** | Docker disponible, port 1935 ouvrable en entrée, CPU et bande passante sortante réels. **Bloquant pour la phase 3** |
| Sauvegarde et restauration | Planification, chiffrement, copie hors site, **test de restauration documenté** |
| Supervision | Disponibilité, erreurs applicatives, espace disque, état des flux |
| Astreinte | Qui est joignable si le site tombe un vendredi matin |

---

## 11. Phasage et jalons

Sept phases. Chaque jalon comporte des **critères d'acceptation
vérifiables** : une phase n'est pas close parce que le code est écrit,
mais parce que ces critères sont satisfaits.

### Phase 1 — Fondations

Core et Announcements.

- Authentification par email et téléphone, utilisateurs, organisations,
  adhésions, tutelles.
- Back-office Filament opérationnel.
- Bandeau d'annonces diffusé en temps réel.
- Docker Compose complet, environnement local reproduisant la production.
- Interface traduite dans les trois langues.

**Acceptation** : un administrateur crée une annonce dans Filament, elle
apparaît sur une page Next.js **sans rechargement**, dans les trois
langues, et le RTL est correct en arabe. Ce seul scénario éprouve
PostgreSQL, l'API, Reverb, la session, l'i18n et le rendu.

### Phase 2 — Vitrine et inscriptions, **mise en production**

Education, Mosque, News.

- Programmes, enseignants, mosquée, horaires de prière, carte.
- **Entonnoir d'inscription en ligne**, avec suivi de dossier.
- Blog avec commentaires modérés.
- Calendrier hégirien, mode Jumu'a, mode sombre.
- PWA de base : manifeste, installabilité, coque hors ligne.

**Acceptation** : le site est **en production sur nujumalhuda.com**, les
seuils de performance du point 4.3 sont atteints, une famille peut
déposer une demande d'inscription et la recevoir confirmée.

> **Changement majeur par rapport à la v1.0**, qui plaçait la mise en
> production en phase 4, après le streaming. Une vitrine institutionnelle
> avec horaires de prière et inscriptions est déjà un produit complet.
> Retarder l'ouverture de plusieurs mois pour attendre la vidéo reporte
> toute la valeur, et prive le projet de retours réels au moment où ils
> sont les plus utiles.

### Phase 3 — Live et médias

Live. **Conditionnée à la qualification du VPS.**

- Embeds YouTube et Facebook, redirections TikTok et Instagram.
- MediaMTX, lecteur WHEP avec repli LL-HLS, variante audio.
- Console enseignant, sessions de récitation à double déclenchement.
- Archive VOD, récitations audio en écoute libre.

**Acceptation** : un enseignant lance une session depuis son téléphone,
un parent la suit avec **moins d'une seconde de retard**, l'enregistrement
est disponible en rejeu le lendemain, et la diffusion respecte le
consentement enregistré pour l'élève.

### Phase 4 — PWA avancée et performance

- Notifications push, désactivées par défaut, par catégorie.
- Cache hors ligne des ressources de l'élève.
- Mode faible bande passante.
- Recette d'accessibilité WCAG AA sur les trois langues, au lecteur
  d'écran.

**Acceptation** : audit d'accessibilité passé, budgets de performance
tenus sur 3G, un élève consulte sa leçon en cours en mode avion.

### Phase 5 — Pédagogie et communauté

Academics, Resources, Community.

- Portail parent, paliers de hifz, attestations, quiz.
- Bibliothèque numérique, zakat, verset et hadith du jour.
- Dons, témoignages, galerie, « Demander à un enseignant », événements.
- Recherche globale.

**Acceptation** : un parent suit la progression de son enfant ; chaque
surface de contribution ouverte a son modérateur nommé.

### Phase 6 — Paiement local et pilotage

- Wave et Orange Money sur le registre de paiements déjà en place.
- Digest SMS.
- Tableau de bord et analytics.

**Acceptation** : un don aboutit de bout en bout, avec réconciliation et
reçu.

### Phase 7 — Dahira

Membres, cotisations, trésorerie, SMS.

**Acceptation** : le module est livré **sans aucune migration modifiant
les tables des modules existants**. C'est le test final de la promesse
d'architecture modulaire.

---

## 12. Risques

| Risque | Gravité | Traitement |
|---|---|---|
| **VPS LWS non qualifié** | Bloquant phase 3 | Qualifier avant la fin de la phase 2. Repli SRT ou WHIP sur 443, qui change le matériel côté centre |
| **Traductions absentes** | Élevée | Budget de traduction par phase, politique de repli sur le français, mention visible |
| **Comptes sociaux à créer** | Élevée | Créer les comptes et lancer la revue Meta dès la phase 1, alors que Live n'arrive qu'en phase 3 : le délai ne dépend pas de nous |
| **Modération non staffée** | Élevée | Aucune surface ouverte sans modérateur nommé |
| **Données de mineurs** | Élevée | Consentement outillé, diffusion restreinte par défaut, conseil juridique |
| **Absence d'opérateur de direct** | Élevée | Nommer et former avant la phase 3 |
| Périmètre total | Élevée | Sept phases, jalons opposables, arbitrages du point 3.11 tenus |
| Recherche en arabe | Moyenne | `pg_trgm` au lancement, Meilisearch si la bibliothèque devient centrale |
| Croissance du stockage VOD | Moyenne | Rétention dès l'ouverture, stockage objet |

---

## Annexe A — Écarts de la v2.0 par rapport à la v1.0

### Structure

| Écart | Nature |
|---|---|
| Quinze rubriques ramenées à **dix modules** | Localisation, Engagement Pédagogique, Vie Communautaire, Accessibilité et Dashboard n'étaient pas des modules mais des fonctionnalités ou des préoccupations transverses |
| **Filament confirmé et élargi** | Tout le CRUD y passe. La console enseignant, elle, reste dans Next.js : temps réel et mobilité. L'API passe d'environ 150 à 40 endpoints |
| **Mise en production avancée en phase 2** | La v1.0 la plaçait en phase 4, après le streaming |
| **Objectif d'inscription ajouté** | Absent de la v1.0, alors que c'est le besoin économique premier |

### Ajouts

| Ajout | Motif |
|---|---|
| Point 8 — données personnelles et éthique | Absent de la v1.0, alors que le projet traite des données de mineurs et publie leur image |
| Point 9 — gouvernance de la modération | Quatre surfaces de contribution étaient prévues sans modérateur |
| Point 10 — exploitation | Aucun opérateur de direct, aucune sauvegarde, aucune astreinte n'étaient prévus |
| Seuils de performance chiffrés | « Chargement rapide » n'est pas un critère de recette |
| Critères d'acceptation par phase | Une phase se clôt sur un résultat vérifiable, pas sur du code écrit |
| Politique de repli linguistique | Le risque éditorial du trilinguisme n'était pas traité |
| Entonnoir d'inscription, tutelles, modération mutualisée | Manquants au modèle de données |
| Distinction attestation / ijaza | Une ijaza générée automatiquement serait inexacte sur le fond |

### Retraits

| Retrait | Motif |
|---|---|
| Forum public et anonyme | **Reformulé, non supprimé** : espaces de discussion rattachés à une promotion ou à un dahira, où le modérateur existe déjà dans la structure |
| Newsletter email | Remplacée par un digest SMS, canal réellement lu |
| Navigation audio-first | Remplacée par une conformité WCAG AA réelle, vérifiée au lecteur d'écran |

**Aucun module n'a été retiré.** Les dix modules de la v1.0 sont
maintenus, Community et Dahira compris.
