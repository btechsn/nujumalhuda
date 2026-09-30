import { cva, type VariantProps } from "class-variance-authority";
import { Slot } from "@radix-ui/react-slot";
import * as React from "react";

import { cn } from "@/lib/utils";

/**
 * Bouton.
 *
 * Six variantes seulement, et chacune existe pour un contexte précis.
 * À noter : `accent` est la seule variante dorée, et elle n'a pas de fond.
 * L'or reste un filet et un texte, jamais un aplat — c'est la règle du
 * système, et un bouton doré plein la briserait sur l'élément le plus
 * visible de la page.
 */
const buttonVariants = cva(
  [
    "inline-flex items-center justify-center gap-2 whitespace-nowrap",
    "font-sans font-medium",
    "rounded-sm border",
    "transition-colors duration-150",
    "focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring",
    "disabled:pointer-events-none disabled:opacity-45",
    "[&_svg]:size-4 [&_svg]:shrink-0",
    // L'interlettrage casse les ligatures arabes : neutralisé sur :lang(ar)
    // dans la couche de base, donc rien à faire ici.
  ],
  {
    variants: {
      variant: {
        /** Action principale d'une page ou d'un formulaire. Une seule par vue. */
        primary:
          "border-transparent bg-primary text-on-primary hover:bg-primary-hover active:bg-primary-active",
        /** Action secondaire de même importance fonctionnelle, moindre en poids visuel. */
        secondary:
          "border-primary bg-transparent text-primary hover:bg-primary-subtle active:bg-primary-subtle",
        /** Action tertiaire : filtres, liens d'action, barres d'outils. */
        ghost:
          "border-transparent bg-transparent text-content-secondary hover:bg-surface-2 hover:text-content",
        /** Accent doré, sans fond. Réservé à l'appel au don et au soutien. */
        accent:
          "border-line-accent bg-transparent text-content-accent hover:bg-accent-subtle",
        /** À poser sur un fond vert : sections inversées, en-tête, pied de page. */
        inverse:
          "border-transparent bg-ivory-50 text-brand-800 hover:bg-ivory-200 active:bg-ivory-300",
        /** Suppression et actions irréversibles, côté administration. */
        danger:
          "border-transparent bg-danger text-white hover:opacity-90 active:opacity-80",
      },
      size: {
        sm: "h-8 px-3 text-small",
        md: "h-10 px-4 text-small",
        lg: "h-12 px-6 text-body",
        icon: "size-10 p-0",
      },
    },
    defaultVariants: {
      variant: "primary",
      size: "md",
    },
  },
);

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  /** Rend l'enfant à la place du <button> — pour un <Link> stylé en bouton. */
  asChild?: boolean;
  /** Affiche un indicateur d'attente et neutralise l'interaction. */
  loading?: boolean;
}

export const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(function Button(
  { className, variant, size, asChild = false, loading = false, disabled, children, ...props },
  ref,
) {
  const Component = asChild ? Slot : "button";

  return (
    <Component
      ref={ref}
      className={cn(buttonVariants({ variant, size }), className)}
      disabled={disabled || loading}
      aria-busy={loading || undefined}
      {...props}
    >
      {loading ? <Spinner /> : null}
      {children}
    </Component>
  );
});

function Spinner() {
  return (
    <svg
      viewBox="0 0 24 24"
      fill="none"
      aria-hidden="true"
      className="motion-safe:animate-spin"
    >
      <circle cx="12" cy="12" r="9" stroke="currentColor" strokeWidth="2.5" opacity="0.25" />
      <path
        d="M21 12a9 9 0 0 0-9-9"
        stroke="currentColor"
        strokeWidth="2.5"
        strokeLinecap="round"
      />
    </svg>
  );
}

export { buttonVariants };
