import { Suspense } from "react";
import { notFound } from "next/navigation";
import { NextIntlClientProvider } from "next-intl";
import { getMessages } from "next-intl/server";

import { OrnamentDefs } from "@/components/brand/ornaments";
import { NavigationProgress } from "@/components/layout/navigation-progress";
import { fontVariables } from "@/lib/fonts";
import { LOCALE_DIRECTION, routing, type Locale } from "@/i18n/routing";

import "@/app/globals.css";

/**
 * Layout de locale.
 *
 * Trois choses se décident ici et nulle part ailleurs :
 *
 *   `lang`  — pilote toutes les règles `:lang(ar)` de globals.css, donc la
 *             fonte arabe, l'interligne élargi et la neutralisation de
 *             l'interlettrage. Un seul attribut, et la typographie arabe
 *             devient correcte sur toute la page.
 *
 *   `dir`   — pilote les propriétés logiques de Tailwind (`ps`, `me`,
 *             `text-start`) et le sens du bandeau défilant. Aucun composant
 *             n'a besoin de connaître la direction courante.
 *
 *   classe  — `dark` est posée ici, sur <html>, pour que le mode sombre
 *             soit décidé côté serveur et n'entraîne aucun clignotement.
 */
export default async function LocaleLayout({
  children,
  params,
}: {
  children: React.ReactNode;
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;

  if (!routing.locales.includes(locale as Locale)) notFound();

  const messages = await getMessages();
  const dir = LOCALE_DIRECTION[locale as Locale];

  return (
    <html lang={locale} dir={dir} className={fontVariables} suppressHydrationWarning>
      <body className="min-h-dvh bg-canvas font-sans text-content antialiased">
        {/* Les clip-paths d'arc sont définis une fois pour tout le document. */}
        <OrnamentDefs />

        <a
          href="#nh-main"
          className="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:rounded-sm focus:bg-primary focus:px-4 focus:py-2 focus:text-on-primary"
        >
          Aller au contenu
        </a>

        <NextIntlClientProvider messages={messages}>
          <Suspense fallback={null}>
            <NavigationProgress />
          </Suspense>
          {children}
        </NextIntlClientProvider>
      </body>
    </html>
  );
}

export function generateStaticParams() {
  return routing.locales.map((locale) => ({ locale }));
}
