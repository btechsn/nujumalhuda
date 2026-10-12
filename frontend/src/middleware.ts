import createMiddleware from 'next-intl/middleware';
import { NextRequest } from 'next/server';
import { locales } from './i18n/request';

const handleI18n = createMiddleware({
  locales,
  defaultLocale: 'fr',
  localeDetection: true,
});

export default function middleware(request: NextRequest) {
  const headers = new Headers(request.headers);
  headers.set('x-pathname', request.nextUrl.pathname);
  return handleI18n(new NextRequest(request, { headers }));
}

export const config = {
  // Matcher qui exclut les routes d'API, assets, etc.
  matcher: ['/', '/(fr|en|ar)/:path*', '/((?!api|_next|_vercel|.*\\..*).*)'],
};
