<?php

declare(strict_types=1);

namespace Chronos\EventCore\Parsers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use RRule\RRule;

/**
 * Class RRuleParser
 *
 * Трансформирует iCalendar RRULE строки в коллекции Carbon-дат.
 * Использует rlanvin/php-rrule для полной поддержки RFC 5545:
 * FREQ, INTERVAL, BYDAY, BYMONTHDAY, COUNT, UNTIL, EXDATE.
 *
 * @package Chronos\EventCore\Parsers
 */
class RRuleParser
{
    /**
     * Развертывает правило повторения в набор дат внутри указанного окна.
     *
     * @param string       $rrule      RFC 5545 RRULE строка (напр. "FREQ=WEEKLY;BYDAY=MO,WE;COUNT=10").
     * @param Carbon       $startDate  Дата фактического начала первого вхождения.
     * @param Carbon       $rangeStart Начало периода выборки (включительно).
     * @param Carbon       $rangeEnd   Конец периода выборки (включительно).
     * @param string[]     $exdates    Исключённые даты в формате ISO 8601 (EXDATE из iCal).
     * @return Collection<int, Carbon>
     */
    public static function getOccurrences(
        string $rrule,
        Carbon $startDate,
        Carbon $rangeStart,
        Carbon $rangeEnd,
        array $exdates = []
    ): Collection {
        $maxOccurrences = config('chronos.max_occurrences', 365);

        $rules = self::parseString($rrule);

        // Защита от бесконечных серий: если нет COUNT и UNTIL — добавляем COUNT
        if (!isset($rules['COUNT']) && !isset($rules['UNTIL'])) {
            $rules['COUNT'] = $maxOccurrences;
        }

        $rules['DTSTART'] = $startDate->toDateTimeString();

        $rruleObj = new RRule($rules);

        // Нормализуем EXDATE для быстрого поиска по ключу
        $excludedKeys = [];
        foreach ($exdates as $exdate) {
            $excludedKeys[Carbon::parse($exdate)->toDateTimeString()] = true;
        }

        $occurrences = collect();

        foreach ($rruleObj as $date) {
            $carbonDate = Carbon::instance($date);

            // Прерываем если вышли за пределы диапазона
            if ($carbonDate->gt($rangeEnd)) {
                break;
            }

            // Пропускаем даты до начала диапазона
            if ($carbonDate->lt($rangeStart)) {
                continue;
            }

            // Пропускаем явно исключённые даты (EXDATE)
            if (isset($excludedKeys[$carbonDate->toDateTimeString()])) {
                continue;
            }

            $occurrences->push($carbonDate);
        }

        return $occurrences;
    }

    /**
     * Преобразует строку RRULE в ассоциативный массив параметров.
     * Поддерживает как "FREQ=WEEKLY;BYDAY=MO" так и "RRULE:FREQ=WEEKLY;BYDAY=MO".
     *
     * @param string $rrule
     * @return array<string, string>
     */
    public static function parseString(string $rrule): array
    {
        // Убираем префикс "RRULE:" если присутствует
        $rrule = preg_replace('/^RRULE:/i', '', trim($rrule));

        $parts = explode(';', $rrule);
        $result = [];

        foreach ($parts as $part) {
            if (str_contains($part, '=')) {
                [$key, $value] = explode('=', $part, 2);
                $result[strtoupper(trim($key))] = trim($value);
            }
        }

        return $result;
    }
}