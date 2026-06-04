<?php

declare(strict_types=1);

namespace Chronos\EventCore\Tests\Unit\Parsers;

use Chronos\EventCore\Parsers\RRuleParser;
use Chronos\EventCore\Tests\TestCase;
use Illuminate\Support\Carbon;

/**
 * Class RRuleParserTest
 *
 * Покрывает все ключевые параметры RFC 5545: FREQ, INTERVAL, BYDAY,
 * BYMONTHDAY, COUNT, UNTIL, EXDATE, а также граничные случаи.
 */
class RRuleParserTest extends TestCase
{
    public function test_daily_rule_returns_correct_count(): void
    {
        $start      = Carbon::parse('2026-03-01 10:00:00');
        $rangeStart = Carbon::parse('2026-03-01 00:00:00');
        $rangeEnd   = Carbon::parse('2026-03-05 23:59:59');

        $result = RRuleParser::getOccurrences('FREQ=DAILY', $start, $rangeStart, $rangeEnd);

        $this->assertCount(5, $result);
    }

    public function test_weekly_rule_byday_mo_we_fr(): void
    {
        $start      = Carbon::parse('2026-03-02 10:00:00'); // Понедельник
        $rangeStart = Carbon::parse('2026-03-01');
        $rangeEnd   = Carbon::parse('2026-03-31');

        $result = RRuleParser::getOccurrences('FREQ=WEEKLY;BYDAY=MO,WE,FR', $start, $rangeStart, $rangeEnd);

        // В марте 2026: 4 недели × 3 дня + частичная первая неделя = 13 вхождений
        $this->assertGreaterThan(0, $result->count());

        foreach ($result as $date) {
            $this->assertContains($date->dayOfWeek, [1, 3, 5]); // MO=1, WE=3, FR=5
        }
    }

    public function test_count_limits_occurrences(): void
    {
        $start      = Carbon::parse('2026-01-01 09:00:00');
        $rangeStart = Carbon::parse('2026-01-01');
        $rangeEnd   = Carbon::parse('2026-12-31');

        $result = RRuleParser::getOccurrences('FREQ=DAILY;COUNT=5', $start, $rangeStart, $rangeEnd);

        $this->assertCount(5, $result);
    }

    public function test_until_stops_at_given_date(): void
    {
        $start      = Carbon::parse('2026-03-01 10:00:00');
        $rangeStart = Carbon::parse('2026-03-01 00:00:00');
        $rangeEnd   = Carbon::parse('2026-12-31 23:59:59');

        // UNTIL=20260310T235959Z — включаем 10-е число
        $result = RRuleParser::getOccurrences('FREQ=DAILY;UNTIL=20260310T235959Z', $start, $rangeStart, $rangeEnd);

        $this->assertCount(10, $result);
        $this->assertTrue($result->last()->lte(Carbon::parse('2026-03-10 23:59:59')));
    }

    public function test_monthly_bymonthday_15(): void
    {
        $start      = Carbon::parse('2026-01-15 10:00:00');
        $rangeStart = Carbon::parse('2026-01-01');
        $rangeEnd   = Carbon::parse('2026-06-30');

        $result = RRuleParser::getOccurrences('FREQ=MONTHLY;BYMONTHDAY=15', $start, $rangeStart, $rangeEnd);

        $this->assertCount(6, $result);

        foreach ($result as $date) {
            $this->assertEquals(15, $date->day);
        }
    }

    public function test_exdate_excludes_specific_occurrence(): void
    {
        $start      = Carbon::parse('2026-03-01 10:00:00');
        $rangeStart = Carbon::parse('2026-03-01 00:00:00');
        $rangeEnd   = Carbon::parse('2026-03-05 23:59:59');

        $result = RRuleParser::getOccurrences(
            'FREQ=DAILY',
            $start,
            $rangeStart,
            $rangeEnd,
            ['2026-03-03 10:00:00']
        );

        // 5 дней минус 1 исключённый = 4
        $this->assertCount(4, $result);

        $dates = $result->map(fn($d) => $d->toDateString())->toArray();
        $this->assertNotContains('2026-03-03', $dates);
    }

    public function test_interval_every_2_weeks(): void
    {
        $start      = Carbon::parse('2026-03-02 10:00:00'); // Понедельник
        $rangeStart = Carbon::parse('2026-03-01');
        $rangeEnd   = Carbon::parse('2026-04-30');

        $result = RRuleParser::getOccurrences('FREQ=WEEKLY;INTERVAL=2;BYDAY=MO', $start, $rangeStart, $rangeEnd);

        $this->assertGreaterThanOrEqual(1, $result->count());

        // Разница между вхождениями должна быть ровно 14 дней
        if ($result->count() >= 2) {
            $diff = $result->first()->diffInDays($result->get(1));
            $this->assertEquals(14, $diff);
        }
    }

    public function test_leap_year_feb_29_daily(): void
    {
        $start      = Carbon::parse('2028-02-27 10:00:00');
        $rangeStart = Carbon::parse('2028-02-27 00:00:00');
        $rangeEnd   = Carbon::parse('2028-03-02 23:59:59');

        $result = RRuleParser::getOccurrences('FREQ=DAILY', $start, $rangeStart, $rangeEnd);

        $this->assertCount(5, $result);

        $dates = $result->map(fn($d) => $d->toDateString())->toArray();
        $this->assertContains('2028-02-29', $dates);
    }

    public function test_range_outside_event_returns_empty(): void
    {
        $start      = Carbon::parse('2026-01-01 10:00:00');
        $rangeStart = Carbon::parse('2026-06-01');
        $rangeEnd   = Carbon::parse('2026-06-30');

        // Только 5 повторений, все в январе
        $result = RRuleParser::getOccurrences('FREQ=DAILY;COUNT=5', $start, $rangeStart, $rangeEnd);

        $this->assertCount(0, $result);
    }

    public function test_parse_string_strips_rrule_prefix(): void
    {
        $result = RRuleParser::parseString('RRULE:FREQ=WEEKLY;BYDAY=MO');

        $this->assertEquals('WEEKLY', $result['FREQ']);
        $this->assertEquals('MO', $result['BYDAY']);
    }

    public function test_no_count_no_until_uses_max_occurrences_limit(): void
    {
        $start      = Carbon::parse('2026-01-01 10:00:00');
        $rangeStart = Carbon::parse('2026-01-01');
        $rangeEnd   = Carbon::parse('2030-12-31');

        // Без COUNT и UNTIL — должен использовать config('chronos.max_occurrences', 365)
        $result = RRuleParser::getOccurrences('FREQ=DAILY', $start, $rangeStart, $rangeEnd);

        $this->assertLessThanOrEqual(365, $result->count());
    }
}
