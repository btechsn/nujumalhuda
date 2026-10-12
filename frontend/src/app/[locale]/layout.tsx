import { Suspense } from "react";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { headers } from "next/headers";
import Script from "next/script";
import { NextIntlClientProvider } from "next-intl";
import { getMessages, getTranslations } from "next-intl/server";

import { OrnamentDefs } from "@/components/brand/ornaments";
import { ComfortControls } from "@/components/layout/comfort-controls";
import { InstallPrompt } from "@/components/pwa/install-prompt";
import { NavigationProgress } from "@/components/layout/navigation-progress";
import { AuthProvider } from "@/hooks/useAuth";
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
 *   classe  — `dark`, la taille du texte et la graphie arabe sont posées
 *             sur <html> par un script avant le premier affichage, à partir
 *             du choix enregistré sur cet appareil.
 */
export async function generateMetadata(): Promise<Metadata> {
  const path = (await headers()).get("x-pathname") ?? "/fr";
  const rest = path.replace(/^\/(fr|en|ar)(?=\/|$)/, "");
  const languages = {
    fr: `/fr${rest}`,
    en: `/en${rest}`,
    ar: `/ar${rest}`,
    "x-default": `/fr${rest}`,
  };

  return {
    manifest: "/manifest.json",
    alternates: { languages },
  };
}

const organizationJsonLd = {
  "@context": "https://schema.org",
  "@type": ["EducationalOrganization", "Mosque"],
  name: "Nujum Al-Huda Institute",
  url: "https://nujumalhuda.com",
  address: {
    "@type": "PostalAddress",
    streetAddress: "28M Cité des Magistrats, Sud Foire",
    addressLocality: "Dakar",
    addressCountry: "SN",
  },
};
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
  const t = await getTranslations({ locale, namespace: "comfort" });
  const dir = LOCALE_DIRECTION[locale as Locale];

  return (
    <html lang={locale} dir={dir} className={fontVariables} suppressHydrationWarning>
      <body className="min-h-dvh bg-canvas font-sans text-content antialiased">
        {/* Le serveur de développement et la feuille de style ne donnent pas
            le même identifiant à Poppins. Sans ce raccord, le navigateur
            retombe sur la police du système, plus grasse que la production. */}
        <script
          dangerouslySetInnerHTML={{
            __html: `(function(){try{var names=[];function walk(rules){if(!rules)return;for(var i=0;i<rules.length;i++){var rule=rules[i];if(rule.cssRules){walk(rule.cssRules);continue;}var text=rule.cssText||"";if(text.indexOf("@font-face")!==0)continue;var at=text.indexOf("font-family:");if(at<0)continue;var rest=text.slice(at+12);var end=rest.indexOf(";");var name=(end<0?rest:rest.slice(0,end)).trim();if(name.charAt(0)==="'"||name.charAt(0)==='"')name=name.slice(1,-1);if(names.indexOf(name)===-1)names.push(name);}}for(var s=0;s<document.styleSheets.length;s++){try{walk(document.styleSheets[s].cssRules);}catch(e){}}function value(test){var main="";var fallback="";for(var i=0;i<names.length;i++){var name=names[i];if(!test(name))continue;if(name.indexOf("Fallback")!==-1)fallback=name;else main=name;}if(!main)return "";return "'"+main+"'"+(fallback?", '"+fallback+"'":"");}var root=document.documentElement;var sans=value(function(name){return name.indexOf("__Poppins_")===0;});var arabic=value(function(name){return name.indexOf("__Noto_Naskh_Arabic_")===0;});var uthmani=value(function(name){return name.indexOf("__Amiri_Quran_")===0;});var quran=value(function(name){return name.indexOf("__Amiri_")===0&&name.indexOf("__Amiri_Quran_")!==0;});if(sans)root.style.setProperty("--nh-font-sans",sans);if(arabic)root.style.setProperty("--nh-font-arabic",arabic);if(quran)root.style.setProperty("--nh-font-quran",quran);if(uthmani)root.style.setProperty("--nh-font-uthmani",uthmani);}catch(e){}})();`,
          }}
        />
        <Script id="nh-comfort" strategy="beforeInteractive">
          {`(function(){try{var raw=localStorage.getItem("nh-comfort");var p=raw?JSON.parse(raw):{};var root=document.documentElement;var theme=p.theme==="dark"||p.theme==="system"?p.theme:"light";var dark=theme==="dark"||(theme==="system"&&window.matchMedia("(prefers-color-scheme: dark)").matches);root.classList.toggle("dark",dark);if(p.text==="125")root.setAttribute("data-text","125");if(p.arabic==="uthmani")root.setAttribute("data-arabic","uthmani");var save=navigator.connection&&navigator.connection.saveData;if(p.bandwidth==="low"||(p.bandwidth!=="full"&&save))root.setAttribute("data-bandwidth","low");}catch(e){}})();`}
        </Script>
        {/* Les clip-paths d'arc sont définis une fois pour tout le document. */}
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(organizationJsonLd) }}
        />
        <OrnamentDefs />

        <a
          href="#nh-main"
          className="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:rounded-sm focus:bg-primary focus:px-4 focus:py-2 focus:text-on-primary"
        >
          {t("skip")}
        </a>

        <NextIntlClientProvider messages={messages}>
          <AuthProvider>
            <Suspense fallback={null}>
              <NavigationProgress />
            </Suspense>
            {children}
            <ComfortControls />
            <InstallPrompt />
          </AuthProvider>
        </NextIntlClientProvider>
      </body>
    </html>
  );
}

export function generateStaticParams() {
  return routing.locales.map((locale) => ({ locale }));
}
