import { ProgramDetail } from "@/components/education/program-detail";

export default async function ProgramDetailPage({
  params,
}: {
  params: Promise<{ id: string }> | { id: string };
}) {
  const resolved = await Promise.resolve(params);

  return <ProgramDetail id={resolved.id} />;
}
