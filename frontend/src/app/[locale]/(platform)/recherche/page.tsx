import { Suspense } from "react";

import { SearchBoard } from "@/components/search/search-board";

export default function SearchPage() {
  return (
    <Suspense>
      <SearchBoard />
    </Suspense>
  );
}
