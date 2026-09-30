# Module News — 100 %

Le module Actualités couvre maintenant la publication, la modération, les notifications et le flux RSS.

## Ce qui est en place

- Articles, catégories et commentaires en fils, avec modération avant affichage public.
- Badge Filament pour les commentaires en attente, approbation individuelle et en lot.
- E-mail à l’auteur de l’article dès qu’un commentaire n’est pas classé spam.
- E-mail au lecteur quand son commentaire est approuvé ou refusé, avec le motif en cas de refus.
- Anti-spam : champ leurre `website`, liens, expressions interdites, doublons, limite de débit par compte et par adresse IP. Le spam est enregistré avec le statut `spam` et un score, sans prévenir l’auteur de l’article.
- Signalement : trois signalements distincts passent le commentaire en `flagged` et le retirent du compteur public.
- Flux RSS : `GET /api/v1/news/feed`.
- Le détail d’un article renvoie le contenu et les commentaires approuvés. La liste paginée expose `last_page`.

## Fichiers ajoutés

- Migration `2024_01_03_100003_add_spam_fields_to_article_comments_table.php`
- `Services/CommentSpamService.php`
- Événements `CommentApproved` et `CommentRejected`
- Notifications `NewCommentNotification`, `CommentApprovedNotification`, `CommentRejectedNotification`
- Listeners et `Providers/EventServiceProvider.php`
- `Http/Controllers/RssFeedController.php`

## Routes

```
GET  /api/v1/news/feed
GET  /api/v1/news/articles
GET  /api/v1/news/articles/{slug}
GET  /api/v1/news/articles/{id}/comments
POST /api/v1/news/comments                  (auth)
POST /api/v1/news/comments/{id}/report      (auth)
```

Le corps d’un commentaire accepte un champ `website`. Il doit rester vide : s’il est rempli, le message est traité comme du spam.

## E-mails

Les notifications partent par la file si `QUEUE_CONNECTION` n’est pas `sync`. Le worker se lance avec :

```bash
php artisan queue:work
```

L’approbation et le refus dans Filament déclenchent les e-mails, y compris pour l’approbation en lot.

## Configuration

Le lien public des articles dans le RSS et dans l’e-mail d’approbation utilise `FRONTEND_URL`, sinon `APP_URL`.

```env
FRONTEND_URL=https://nujumalhuda.com
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS=noreply@nujumalhuda.com
MAIL_FROM_NAME="Nujum Al-Huda Institute"
```

Ajoutez `frontend_url` dans `config/app.php` une fois le fichier de configuration Laravel publié :

```php
'frontend_url' => env('FRONTEND_URL', env('APP_URL')),
```
