const LESSONS = "nh-lessons-v1";

export type CachedMaterial = {
  kind: string;
  url: string;
  name: string;
  offlineUrl?: string;
};

export type CachedLesson = {
  id: string;
  title: { fr?: string | null; en?: string | null; ar?: string | null } | string;
  content: { fr?: string | null; en?: string | null; ar?: string | null } | string | null;
  course: { fr?: string | null; en?: string | null; ar?: string | null } | string | null;
  materials: CachedMaterial[];
};

export async function readCachedLesson(): Promise<CachedLesson | null> {
  if (!("caches" in window)) return null;
  const cache = await caches.open(LESSONS);
  const hit = await cache.match("/offline-lesson.json");
  if (!hit) return null;
  return (await hit.json()) as CachedLesson;
}

export async function storeLesson(lesson: CachedLesson): Promise<CachedLesson> {
  if (!("caches" in window)) return lesson;
  const cache = await caches.open(LESSONS);
  const materials = await Promise.all(
    lesson.materials.map(async (material, index) => {
      try {
        const response = await fetch(material.url);
        if (!response.ok) return material;
        const blob = await response.blob();
        const offlineUrl = `/offline-media/${lesson.id}/${index}`;
        await cache.put(
          offlineUrl,
          new Response(blob, { headers: { "Content-Type": blob.type || "application/octet-stream" } }),
        );
        return { ...material, offlineUrl };
      } catch {
        return material;
      }
    }),
  );
  const stored = { ...lesson, materials };
  await cache.put(
    "/offline-lesson.json",
    new Response(JSON.stringify(stored), { headers: { "Content-Type": "application/json" } }),
  );
  return stored;
}

export async function materialSource(material: CachedMaterial): Promise<string> {
  if (!material.offlineUrl || !("caches" in window)) return material.url;
  const cache = await caches.open(LESSONS);
  const hit = await cache.match(material.offlineUrl);
  if (!hit) return material.url;
  const blob = await hit.blob();
  return URL.createObjectURL(blob);
}
