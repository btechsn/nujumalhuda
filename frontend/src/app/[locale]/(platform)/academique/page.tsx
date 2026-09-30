import { redirect } from "next/navigation";

/** L’entrée Académique ouvre directement les programmes. */
export default async function AcademiquePage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  redirect(`/${locale}/programs`);
}
