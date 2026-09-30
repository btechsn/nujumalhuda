<?php

return [
    /*
    | École retenue par l'institut : malikite.
    | Le nisab et la source affichés au public viennent de la ligne
    | zakat_rates courante, pas de ces constantes. Elles servent de
    | valeurs de référence si aucune ligne n'a encore été saisie.
    */
    'madhhab' => 'maliki',

    'gold_nisab_grams' => 85,
    'silver_nisab_grams' => 595,

    'rate_numerator' => 1,
    'rate_denominator' => 40,

    'default_nisab_basis' => 'silver',

    'source' => [
        'fr' => 'École malikite. Nisab de l\'or : 20 dinars, soit 85 grammes. Nisab de l\'argent : 200 dirhams, soit 595 grammes. Les bijoux portés à titre personnel ne sont pas zakatable. Pour la monnaie, l\'institut retient le nisab de l\'argent, seuil le plus bas, par précaution. Référence : al-Mudawwana.',
        'en' => 'Maliki school. Gold nisab: 20 dinars, 85 grams. Silver nisab: 200 dirhams, 595 grams. Personal jewelry is not zakatable. For cash, the institute uses the silver nisab, the lower threshold, as a precaution. Reference: al-Mudawwana.',
        'ar' => 'المذهب المالكي. نصاب الذهب: عشرون دينارا، أي 85 غراما. نصاب الفضة: مئتا درهم، أي 595 غراما. حلي الاستعمال الشخصي غير زكوي. في النقود يعتمد المعهد نصاب الفضة، وهو الأدنى، احتياطا. المرجع: المدونة.',
    ],
];
