<?php

declare(strict_types=1);

namespace Modules\Mosque\Support;

final class HijriDate
{
    private const MONTHS = [
        1 => ['en' => 'Muharram', 'fr' => 'Mouharram', 'ar' => 'محرم'],
        2 => ['en' => 'Safar', 'fr' => 'Safar', 'ar' => 'صفر'],
        3 => ['en' => 'Rabi al-Awwal', 'fr' => 'Rabi al-awwal', 'ar' => 'ربيع الأول'],
        4 => ['en' => 'Rabi al-Thani', 'fr' => 'Rabi al-thani', 'ar' => 'ربيع الثاني'],
        5 => ['en' => 'Jumada al-Awwal', 'fr' => 'Joumada al-oula', 'ar' => 'جمادى الأولى'],
        6 => ['en' => 'Jumada al-Thani', 'fr' => 'Joumada al-thania', 'ar' => 'جمادى الآخرة'],
        7 => ['en' => 'Rajab', 'fr' => 'Rajab', 'ar' => 'رجب'],
        8 => ['en' => 'Shaban', 'fr' => 'Chaabane', 'ar' => 'شعبان'],
        9 => ['en' => 'Ramadan', 'fr' => 'Ramadan', 'ar' => 'رمضان'],
        10 => ['en' => 'Shawwal', 'fr' => 'Chawwal', 'ar' => 'شوال'],
        11 => ['en' => 'Dhu al-Qadah', 'fr' => 'Dhou al-qi`da', 'ar' => 'ذو القعدة'],
        12 => ['en' => 'Dhu al-Hijjah', 'fr' => 'Dhou al-hijja', 'ar' => 'ذو الحجة'],
    ];

    /**
     * Calendrier hégirien tabulaire (arithmétique). L'observation locale peut décaler d'un jour.
     *
     * @return array{year: int, month: int, day: int}
     */
    public static function fromGregorian(int $year, int $month, int $day): array
    {
        $jd = self::julianDay($year, $month, $day);
        $l = $jd - 1948440 + 10632;
        $n = intdiv($l - 1, 10631);
        $l = $l - 10631 * $n + 354;
        $j = (intdiv(10985 - $l, 5316) * intdiv(50 * $l, 17719))
            + (intdiv($l, 5670) * intdiv(43 * $l, 15238));
        $l = $l - (intdiv(30 - $j, 15) * intdiv(17719 * $j, 50))
            - (intdiv($j, 16) * intdiv(15238 * $j, 43)) + 29;
        $hijriMonth = intdiv(24 * $l, 709);
        $hijriDay = $l - intdiv(709 * $hijriMonth, 24);
        $hijriYear = 30 * $n + $j - 30;

        return [
            'year' => $hijriYear,
            'month' => $hijriMonth,
            'day' => $hijriDay,
        ];
    }

    /**
     * @return array{year: int, month: int, day: int}
     */
    public static function toGregorian(int $year, int $month, int $day): array
    {
        $jd = intdiv(11 * $year + 3, 30) + 354 * $year + 30 * $month
            - intdiv($month - 1, 2) + $day + 1948440 - 385;

        return self::julianToGregorian($jd);
    }

    public static function monthName(int $month, string $locale): string
    {
        return self::MONTHS[$month][$locale] ?? self::MONTHS[$month]['fr'];
    }

    private static function julianDay(int $year, int $month, int $day): int
    {
        $a = intdiv(14 - $month, 12);
        $y = $year + 4800 - $a;
        $m = $month + 12 * $a - 3;

        return $day + intdiv(153 * $m + 2, 5) + 365 * $y + intdiv($y, 4) - intdiv($y, 100) + intdiv($y, 400) - 32045;
    }

    /**
     * @return array{year: int, month: int, day: int}
     */
    private static function julianToGregorian(int $jd): array
    {
        $a = $jd + 32044;
        $b = intdiv(4 * $a + 3, 146097);
        $c = $a - intdiv(146097 * $b, 4);
        $d = intdiv(4 * $c + 3, 1461);
        $e = $c - intdiv(1461 * $d, 4);
        $m = intdiv(5 * $e + 2, 153);

        $day = $e - intdiv(153 * $m + 2, 5) + 1;
        $month = $m + 3 - 12 * intdiv($m, 10);
        $year = 100 * $b + $d - 4800 + intdiv($m, 10);

        return ['year' => $year, 'month' => $month, 'day' => $day];
    }
}
