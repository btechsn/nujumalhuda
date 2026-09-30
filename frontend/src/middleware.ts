import createMiddleware from 'next-intl/middleware';
import { locales } from './i18n/request';

export default createMiddleware({
  // Liste des locales supportées
  locales,

  // Locale par défaut
  defaultLocale: 'fr',

  // Détection automatique de la locale depuis le header Accept-Language
  localeDetection: true,
});

export const config = {
  // Matcher qui exclut les routes d'API, assets, etc.
  matcher: ['/', '/(fr|en|ar)/:path*', '/((?!api|_next|_vercel|.*\\..*).*)'],
};
