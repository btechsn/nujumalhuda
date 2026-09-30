import { cn } from "@/lib/utils";

/**
 * Ornements géométriques du système.
 *
 * Tout est en tracé, jamais en aplat, et l'or n'y dépasse jamais un filet
 * ou un petit glyphe. C'est ce qui distingue une référence à
 * l'architecture islamique d'une décoration appliquée par-dessus.
 */

/**
 * Définitions SVG globales. À monter une seule fois, dans le layout racine,
 * juste après l'ouverture du body.
 *
 * clipPathUnits="objectBoundingBox" exprime la forme en coordonnées 0→1 :
 * l'arc s'adapte donc à n'importe quelle taille d'élément, ce qu'un
 * clip-path: path() ne sait pas faire.
 */
export function OrnamentDefs() {
  return (
    <svg aria-hidden="true" focusable="false" className="absolute size-0 overflow-hidden">
      <defs>
        {/* Arc brisé — sommet légèrement pointu, registre maghrébin. */}
        <clipPath id="nh-arch-pointed" clipPathUnits="objectBoundingBox">
          <path d="M0,1 L0,0.42 C0,0.2 0.24,0.055 0.5,0 C0.76,0.055 1,0.2 1,0.42 L1,1 Z" />
        </clipPath>

        {/* Arc en plein cintre — plus neutre, pour les vignettes de liste. */}
        <clipPath id="nh-arch-round" clipPathUnits="objectBoundingBox">
          <path d="M0,1 L0,0.38 C0,0.17 0.22,0 0.5,0 C0.78,0 1,0.17 1,0.38 L1,1 Z" />
        </clipPath>
      </defs>
    </svg>
  );
}

/**
 * Étoile à huit branches (khātam), reprise de celle du logo.
 * Deux carrés superposés à 45°, en tracé seul.
 */
export function EightPointStar({
  className,
  size = 16,
}: {
  className?: string;
  size?: number;
}) {
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.25"
      aria-hidden="true"
      focusable="false"
      className={cn("shrink-0", className)}
    >
      <rect x="4.5" y="4.5" width="15" height="15" />
      <rect x="4.5" y="4.5" width="15" height="15" transform="rotate(45 12 12)" />
    </svg>
  );
}

/**
 * Séparateur de section : filet d'or, étoile centrale, filet d'or.
 * Remplace avantageusement un titre surdimensionné pour marquer une
 * rupture entre deux blocs.
 */
export function StarDivider({ className }: { className?: string }) {
  return (
    <div className={cn("flex items-center gap-4", className)} role="presentation">
      <span className="h-px flex-1 bg-line-accent/60" />
      <EightPointStar className="text-accent" size={14} />
      <span className="h-px flex-1 bg-line-accent/60" />
    </div>
  );
}

/**
 * Trame de fond. `tone` suit le fond sur lequel la trame est posée :
 * `light` trace en vert, `dark` trace en or.
 */
export function GeometricPattern({
  tone = "light",
  className,
}: {
  tone?: "light" | "dark";
  className?: string;
}) {
  return (
    <div
      aria-hidden="true"
      className={cn(
        "pointer-events-none absolute inset-0",
        tone === "light" ? "nh-pattern" : "nh-pattern-on-dark",
        className,
      )}
    />
  );
}

/**
 * Encadrement en arc pour un visuel. L'enfant est un <Image> ou un bloc
 * de couleur ; le filet d'or reprend le contour de l'arc par-dessus.
 */
export function ArchFrame({
  children,
  variant = "pointed",
  className,
}: {
  children: React.ReactNode;
  variant?: "pointed" | "round";
  className?: string;
}) {
  return (
    <div className={cn("relative", className)}>
      <div className={cn("overflow-hidden", variant === "pointed" ? "nh-arch" : "nh-arch-round")}>
        {children}
      </div>
      <svg
        aria-hidden="true"
        focusable="false"
        viewBox="0 0 100 100"
        preserveAspectRatio="none"
        className="pointer-events-none absolute inset-0 size-full text-line-accent"
      >
        <path
          d={
            variant === "pointed"
              ? "M0,100 L0,42 C0,20 24,5.5 50,0 C76,5.5 100,20 100,42 L100,100"
              : "M0,100 L0,38 C0,17 22,0 50,0 C78,0 100,17 100,38 L100,100"
          }
          fill="none"
          stroke="currentColor"
          strokeWidth="1"
          vectorEffect="non-scaling-stroke"
        />
      </svg>
    </div>
  );
}
