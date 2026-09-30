import { getTranslations } from "next-intl/server";

import { PageHeading } from "@/components/layout/page-heading";

/** Page d'une fonction : ce qu'elle est, et ce que le visiteur y trouve. */
export async function FeaturePage({
  pageKey,
  heading = true,
}: {
  pageKey: string;
  heading?: boolean;
}) {
  const t = await getTranslations("pages");
  const points = t.raw(`${pageKey}.points`) as string[];

  return (
    <article>
      {heading ? (
        <PageHeading
          eyebrow={t(`${pageKey}.eyebrow`)}
          title={t(`${pageKey}.title`)}
          lede={t(`${pageKey}.lede`)}
        />
      ) : null}
      <div className="nh-container nh-section-tight">
        <ul className="grid gap-4 md:grid-cols-3">
          {points.map((point) => (
            <li key={point} className="rounded-lg border border-line bg-surface p-5 text-small text-content">
              {point}
            </li>
          ))}
        </ul>
      </div>
    </article>
  );
}
