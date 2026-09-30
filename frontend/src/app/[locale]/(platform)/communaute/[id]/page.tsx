"use client";

import { useParams } from "next/navigation";

import { CommunityDiscussionDetail } from "@/components/community/community-discussion-detail";

export default function CommunityDiscussionPage() {
  const params = useParams<{ id: string }>();
  return <CommunityDiscussionDetail id={params?.id ?? ""} />;
}
