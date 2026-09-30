"use client";

import { Suspense } from "react";

import { TeacherBoard } from "@/components/education/teacher-board";
import { useTeachers } from "@/hooks/useTeachers";

export default function TeachersPage() {
  const { data: teachers, isLoading, error } = useTeachers();

  return (
    <Suspense fallback={<TeacherBoard teachers={[]} isLoading error={null} />}>
      <TeacherBoard teachers={teachers} isLoading={isLoading} error={error} />
    </Suspense>
  );
}
