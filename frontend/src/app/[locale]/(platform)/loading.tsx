export default function PlatformLoading() {
  return (
    <div className="nh-container flex min-h-[40vh] items-center justify-center py-20" aria-busy="true">
      <div className="flex flex-col items-center gap-3">
        <span className="size-8 animate-spin rounded-full border-2 border-primary/25 border-t-primary" />
        <span className="text-small text-content-secondary">Chargement…</span>
      </div>
    </div>
  );
}
