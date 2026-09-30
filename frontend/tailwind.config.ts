import type { Config } from 'tailwindcss';

const config: Config = {
  content: [
    './src/pages/**/*.{js,ts,jsx,tsx,mdx}',
    './src/components/**/*.{js,ts,jsx,tsx,mdx}',
    './src/app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  darkMode: 'class',
  theme: {
    // La configuration complète est importée depuis design/tokens.css
    // Les tokens CSS custom properties sont déjà définis
    extend: {},
  },
  plugins: [],
};

export default config;
