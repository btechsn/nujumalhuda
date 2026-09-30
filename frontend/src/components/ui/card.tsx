import { cva, type VariantProps } from "class-variance-authority";
import * as React from "react";

import { cn } from "@/lib/utils";

/**
 * Carte.
 *
 * Aucune ombre, à aucune variante. La hiérarchie repose sur un filet d'un
 * pixel et sur un écart de fond entre `surface` et `surface-2`. Une ombre
 * portée sur une carte statique est précisément ce qui fait basculer une
 * interface institutionnelle vers le template générique — les deux ombres
 * du système sont réservées aux calques flottants, menus et fenêtres
 * modales.
 *
 * Au survol d'une carte interactive, c'est le filet qui passe à l'or.
 * L'accent se déplace, il ne s'ajoute pas.
 */
const cardVariants = cva(
  ["relative rounded-md border transition-colors duration-150"],
  {
    variants: {
      variant: {
        /** Cas courant : contenu posé sur le fond de page. */
        default: "border-line bg-surface",
        /** Encadré secondaire, ou ligne d'un tableau de bord. */
        muted: "border-line bg-surface-2",
        /** Mise en avant forte. Sur fond vert, l'or devient enfin lisible. */
        inverse: "border-brand-700 bg-inverse text-on-inverse",
        /** Bloc sans contour, pour une grille aérée sans effet de damier. */
        plain: "border-transparent bg-transparent",
      },
      interactive: {
        true: "hover:border-line-accent focus-within:border-line-accent",
        false: "",
      },
    },
    defaultVariants: {
      variant: "default",
      interactive: false,
    },
  },
);

export interface CardProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof cardVariants> {
  asChild?: boolean;
}

export function Card({ className, variant, interactive, ...props }: CardProps) {
  return <div className={cn(cardVariants({ variant, interactive }), className)} {...props} />;
}

/**
 * Visuel de carte. `arch` applique l'arc brisé du système — à réserver aux
 * cartes de programme et d'installation, où la référence architecturale a
 * du sens. Une grille entière d'arcs deviendrait un motif, donc un bruit.
 */
export function CardMedia({
  className,
  arch = false,
  children,
  ...props
}: React.HTMLAttributes<HTMLDivElement> & { arch?: boolean }) {
  return (
    <div
      className={cn(
        "relative overflow-hidden bg-muted",
        arch ? "nh-arch" : "rounded-t-[5px]",
        className,
      )}
      {...props}
    >
      {children}
    </div>
  );
}

export function CardHeader({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return <div className={cn("flex flex-col gap-2 p-5 pb-3", className)} {...props} />;
}

/**
 * Surtitre. En latin il est en capitales espacées ; en arabe la couche de
 * base retire la casse et l'interlettrage, puisque ni l'une ni l'autre
 * n'existent dans cette écriture.
 */
export function CardEyebrow({ className, ...props }: React.HTMLAttributes<HTMLSpanElement>) {
  return <span className={cn("type-eyebrow", className)} {...props} />;
}

export function CardTitle({
  className,
  as: Component = "h3",
  ...props
}: React.HTMLAttributes<HTMLHeadingElement> & { as?: "h2" | "h3" | "h4" }) {
  return <Component className={cn("type-h4 text-balance", className)} {...props} />;
}

export function CardBody({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn("px-5 pb-5 text-small text-content-secondary [&>*+*]:mt-3", className)}
      {...props}
    />
  );
}

/**
 * Pied de carte, séparé par un filet. `border-t` suit l'axe de bloc et
 * reste donc correct en RTL sans traitement particulier.
 */
export function CardFooter({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn("flex items-center gap-3 border-t border-line px-5 py-3.5", className)}
      {...props}
    />
  );
}

export { cardVariants };
