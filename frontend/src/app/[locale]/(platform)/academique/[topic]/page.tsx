import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { getTranslations } from "next-intl/server";

import { PromotionBoard } from "@/components/education/promotion-board";
import { QuizBoard } from "@/components/education/quiz-board";
import { CertificateBoard } from "@/components/education/certificate-board";
import { IjazaBoard } from "@/components/education/ijaza-board";
import { EnrollmentBoard } from "@/components/education/enrollment-board";
import { FeaturePage } from "@/components/layout/feature-page";

const TOPICS = {
  inscription: "enroll",
  promotions: "promotions",
  quiz: "quiz",
  certificats: "certificates",
  ijaza: "ijaza",
} as const;

type Topic = keyof typeof TOPICS;

export function generateStaticParams() {
  return Object.keys(TOPICS).map((topic) => ({ topic }));
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string; topic: string }>;
}): Promise<Metadata> {
  const { locale, topic } = await params;
  if (!(topic in TOPICS)) return {};
  const t = await getTranslations({ locale, namespace: "pages" });
  return { title: t(`${TOPICS[topic as Topic]}.title`) };
}

export default async function AcademiqueTopicPage({
  params,
}: {
  params: Promise<{ topic: string }>;
}) {
  const { topic } = await params;
  if (!(topic in TOPICS)) notFound();

  if (topic === "promotions") {
    return <PromotionBoard />;
  }

  if (topic === "quiz") {
    return <QuizBoard />;
  }

  if (topic === "certificats") {
    return <CertificateBoard />;
  }

  if (topic === "ijaza") {
    return <IjazaBoard />;
  }

  if (topic === "inscription") {
    return <EnrollmentBoard />;
  }

  return <FeaturePage pageKey={TOPICS[topic as Topic]} />;
}
