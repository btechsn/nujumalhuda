"use client";

import { ProgramBoard } from "@/components/education/program-board";
import { usePrograms } from "@/hooks/usePrograms";

export default function ProgramsPage() {
  const { data: programs, isLoading, error } = usePrograms({ active: true });

  return <ProgramBoard programs={programs} isLoading={isLoading} error={error} />;
}
