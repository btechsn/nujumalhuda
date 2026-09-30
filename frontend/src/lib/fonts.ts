import { Amiri, Noto_Naskh_Arabic, Poppins } from "next/font/google";

/**
 * Système typographique trilingue selon la charte graphique v1.0.
 *
 * POLICES SELON LA CHARTE OFFICIELLE :
 *   Poppins      → titres et texte latin (ExtraBold pour titres, Regular/Medium pour corps)
 *   Noto Naskh   → contenus arabes et interface (remplace IBM Plex Sans Arabic)
 *   Amiri        → textes coraniques uniquement (traditionnel, contraste élevé)
 *
 * La charte privilégie Poppins pour sa lisibilité et son caractère moderne,
 * tout en conservant Noto Naskh Arabic pour l'arabe (excellent rendu aux
 * petites tailles) et Amiri pour les versets sacrés.
 */

export const fontSans = Poppins({
  subsets: ["latin", "latin-ext"],
  weight: ["400", "500", "600", "700", "800"],
  variable: "--nh-font-sans",
  display: "swap",
});

export const fontArabic = Noto_Naskh_Arabic({
  subsets: ["arabic"],
  weight: ["400", "500", "600", "700"],
  variable: "--nh-font-arabic",
  display: "swap",
});

/**
 * Amiri n'est pas préchargé : il ne sert qu'aux versets, absents de la
 * plupart des pages. Le navigateur ne téléchargera le fichier que si un
 * glyphe le réclame.
 */
export const fontQuran = Amiri({
  subsets: ["arabic"],
  weight: ["400", "700"],
  variable: "--nh-font-quran",
  display: "swap",
  preload: false,
});

export const fontVariables = [
  fontSans.variable,
  fontArabic.variable,
  fontQuran.variable,
].join(" ");
