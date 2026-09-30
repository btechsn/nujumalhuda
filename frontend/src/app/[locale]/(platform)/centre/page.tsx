import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { getTranslations } from "next-intl/server";

import { BannerSlider } from "@/components/layout/banner-slider";
import { CentreShortcuts } from "@/components/layout/centre-shortcuts";
import { CommuniqueBar } from "@/components/layout/communique-bar";
import { MissionsSection } from "@/components/layout/missions-section";
import { PartnersMarquee } from "@/components/layout/partners-marquee";
import { TestimonialsCarousel } from "@/components/layout/testimonials-carousel";
import { serverApiFetch } from "@/lib/server-api";

type IndicatorKey = "teachers" | "graduates" | "programs" | "khutbas" | "recitations";

const NEWS_FALLBACKS = [
  "/brand/slide-actualites.jpg",
  "/brand/slide-centre.jpg",
  "/brand/slide-academique.jpg",
] as const;

type TestimonialCard = {
  id: string;
  author: string;
  relation: string;
  content: string;
};

async function loadTestimonials(locale: string): Promise<TestimonialCard[]> {
  const body = await serverApiFetch<{
    data?: Array<{
      id: string;
      author_name?: string;
      relation?: string;
      content_i18n?: Record<string, string>;
    }>;
  }>("/community/testimonials?per_page=12");
  if (!body?.data?.length) return [];
  return body.data.map((item) => ({
    id: item.id,
    author: item.author_name || "",
    relation: item.relation || "",
    content: item.content_i18n?.[locale] || item.content_i18n?.fr || "",
  }));
}

type PartnerCard = {
  id: string;
  name: string;
  description: string;
  logoUrl: string | null;
  websiteUrl: string | null;
};

async function loadPartners(locale: string): Promise<PartnerCard[]> {
  const body = await serverApiFetch<{
    data?: Array<{
      id: string;
      name?: string;
      description?: string;
      logo_url?: string | null;
      website_url?: string | null;
    }>;
  }>("/community/partners", { locale });
  if (!body?.data?.length) return [];
  return body.data.map((item) => ({
    id: item.id,
    name: item.name || "",
    description: item.description || "",
    logoUrl: item.logo_url || null,
    websiteUrl: item.website_url || null,
  }));
}

async function loadFeaturedSlides(
  locale: string,
  labels: { eyebrow: string },
): Promise<Array<{ eyebrow: string; title: string; text: string; href: string; image: string }>> {
  const body = await serverApiFetch<{
    data?: Array<{
      slug: string;
      title?: Record<string, string>;
      excerpt?: Record<string, string>;
      cover_image_url?: string | null;
      category?: { name?: Record<string, string> } | null;
    }>;
  }>("/news/articles?featured=true&per_page=8");
  if (!body?.data?.length) return [];
  return body.data.map((article, index) => ({
    eyebrow:
      article.category?.name?.[locale] || article.category?.name?.fr || labels.eyebrow,
    title: article.title?.[locale] || article.title?.fr || "",
    text: article.excerpt?.[locale] || article.excerpt?.fr || "",
    href: `/${locale}/actualites/${article.slug}`,
    image: article.cover_image_url || NEWS_FALLBACKS[index % NEWS_FALLBACKS.length],
  }));
}

type Communique = { id: string; text: string; href?: string };

async function loadCommuniques(locale: string): Promise<Communique[]> {
  const body = await serverApiFetch<{
    data?: Array<{ id: string; message?: string; action_url?: string | null }>;
  }>("/announcements", { locale });
  if (!body?.data?.length) return [];
  return body.data.map((item) => ({
    id: item.id,
    text: item.message ?? "",
    href: `/${locale}/centre/annonces?annonce=${encodeURIComponent(item.id)}`,
  }));
}

async function loadIndicators(): Promise<Record<IndicatorKey, number> | null> {
  const body = await serverApiFetch<{ data?: Record<IndicatorKey, number> }>(
    "/education/indicators",
  );
  return body?.data ?? null;
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "doors" });
  return { title: t("centre.title") };
}

export default async function CentrePage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;

  // Traductions d’abord (local, rapide), puis API en parallèle
  // pour ne pas saturer `php artisan serve` en même temps que le compilateur.
  const [t, menu, pages, splash, common, news, notices] = await Promise.all([
    getTranslations("doors"),
    getTranslations("menu"),
    getTranslations("pages"),
    getTranslations("splash"),
    getTranslations("common"),
    getTranslations("news"),
    getTranslations("announcements"),
  ]);

  const [indicators, testimonials, partners, communiques, featuredSlidesRaw] = await Promise.all([
    loadIndicators(),
    loadTestimonials(locale),
    loadPartners(locale),
    loadCommuniques(locale),
    loadFeaturedSlides(locale, { eyebrow: news("featured") }),
  ]);

  const featuredSlides = featuredSlidesRaw;

  const fallbackSlides = [
    {
      eyebrow: t("centre.eyebrow"),
      title: t("centre.title"),
      text: t("centre.lede"),
      href: `/${locale}/mosque/prayer-times`,
      image: "/brand/slide-centre.jpg",
    },
    {
      eyebrow: menu("groups.centre"),
      title: menu("items.prayers"),
      text: pages("prayers.lede"),
      href: `/${locale}/mosque/prayer-times`,
      image: "/brand/slide-priere.jpg",
    },
    {
      eyebrow: t("academics.eyebrow"),
      title: t("academics.title"),
      text: t("academics.lede"),
      href: `/${locale}/programs`,
      image: "/brand/slide-academique.jpg",
    },
    {
      eyebrow: menu("groups.news"),
      title: splash("news.title"),
      text: splash("news.text"),
      href: `/${locale}/news`,
      image: "/brand/slide-actualites.jpg",
    },
    {
      eyebrow: splash("live.kicker"),
      title: splash("live.title"),
      text: splash("live.text"),
      href: `/${locale}/direct`,
      image: "/brand/slide-live.jpg",
    },
  ];

  return (
    <article>
      <div className="relative">
        <CommuniqueBar
          items={communiques}
          label={notices("communique")}
          listHref={`/${locale}/centre/annonces`}
        />
        <BannerSlider slides={featuredSlides.length > 0 ? featuredSlides : fallbackSlides} />

        <CentreShortcuts locale={locale} indicators={indicators} />
      </div>

      <section className="nh-container grid items-center gap-8 py-16 lg:grid-cols-2">
        <div>
          <h2 className="font-sans text-3xl font-extrabold tracking-tight text-primary sm:text-4xl">
            {t("centre.title")}
          </h2>
          <p className="mt-4 text-content-secondary">{t("centre.lede")}</p>
          <ul className="mt-6 space-y-2 text-small text-content">
            <li>{t("centre.placeText")}</li>
            <li>{t("centre.languagesText")}</li>
            <li>{t("centre.teachText")}</li>
          </ul>
          <Link
            href={`/${locale}/zawiya`}
            className="mt-6 inline-flex rounded-full bg-primary px-5 py-3 text-small font-semibold text-on-primary transition hover:bg-primary-hover"
          >
            {common("learnMore")}
          </Link>
        </div>
        <div className="relative min-h-72 overflow-hidden rounded-lg">
          <Image src="/brand/intro-lecon.jpg" alt="" fill sizes="640px" className="object-cover object-[center_40%]" />
        </div>
      </section>

      <MissionsSection />

      {testimonials.length > 0 ? (
        <section className="bg-[#f3f4f6] py-14">
          <div className="nh-container">
            <h2 className="text-center font-sans text-3xl font-extrabold text-primary">
              {t("centre.testimonialsTitle")}
            </h2>
            <p className="mx-auto mt-3 max-w-2xl text-center text-small text-content-secondary">
              {t("centre.testimonialsLede")}
            </p>
            <TestimonialsCarousel items={testimonials} />
          </div>
        </section>
      ) : null}

      {partners.filter((partner) => partner.logoUrl).length > 0 ? (
        <section className="border-y border-primary/15 bg-primary/[0.06] py-12">
          <div className="nh-container">
            <h2 className="text-center font-sans text-3xl font-extrabold text-primary">
              {t("centre.partnersTitle")}
            </h2>
            <PartnersMarquee
              items={partners
                .filter((partner): partner is typeof partner & { logoUrl: string } =>
                  Boolean(partner.logoUrl),
                )
                .map((partner) => ({
                  id: partner.id,
                  name: partner.name,
                  logoUrl: partner.logoUrl,
                  websiteUrl: partner.websiteUrl,
                }))}
            />
          </div>
        </section>
      ) : null}
    </article>
  );
}
