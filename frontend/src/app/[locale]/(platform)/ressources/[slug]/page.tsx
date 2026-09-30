"use client";

import { useParams } from "next/navigation";

import { LibraryDetail } from "@/components/resources/library-board";

export default function LibraryItemPage() {
  const params = useParams<{ slug: string }>();
  return <LibraryDetail slug={params?.slug ?? ""} />;
}
