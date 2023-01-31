<?php

namespace TiagoF2\Helpers;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Str;

class DiffTime
{
    protected ?Carbon $start = null;
    protected ?Carbon $end = null;
    protected ?string $id = null;
    protected static ?bool $quiet = false;

    public function __construct(
        null|Carbon|DateTime|string $start = 'now',
        null|Carbon|DateTime|string $end = null
    ) {
        $this->setStart($start);
        $this->setEnd($end);
        $this->createId();

        $this->outputStarted();

        return $this;
    }

    /**
     * @param ?bool $quiet
     * @return void
     */
    public static function quiet(?bool $quiet = true): void
    {
        static::$quiet = $quiet;
    }

    /**
     * @return string
     */
    protected function outputStarted()
    {
        if (static::$quiet) {
            return;
        }

        echo static::line() . sprintf(
            'Started at %s | ID: %s',
            $this->start(),
            $this->getId(),
        ) . static::line();
    }

    /**
     * @return string
     */
    protected function outputFinished()
    {
        if (!$this->end() || static::$quiet) {
            return;
        }

        echo static::line() . sprintf(
            'Finished at %s | ID: %s | Total time in seconds: %s',
            $this->end(),
            $this->getId(),
            $this->getFinalDiffIn('seconds')
        ) . static::line();
    }

    /**
     * @param string $in minutes|seconds|days|...
     * @return int
     */
    public static function diffIn(
        Carbon|DateTime|string $startDate,
        Carbon|DateTime|string $endDate,
        string $in = 'seconds'
    ): int {
        [$in, $diffMethod] = static::diffMethodInfo($in);

        $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
        return $startDate->{$diffMethod}($endDate);
    }

    /**
     * @param string $in minutes|seconds|days|...
     * @return int
     */
    public function diffFromNowIn(string $in = 'seconds'): int
    {
        [$in, $diffMethod] = static::diffMethodInfo($in);

        return $this->start()->{$diffMethod}($this->now());
    }

    /**
     * @param string $in minutes|seconds|days|...
     * @param null|array|string $reference
     * @param ?bool $hideBacktrace
     * @return string
     */
    public function log(
        string $in = 'seconds',
        null|array|string $reference = null,
        ?bool $hideBacktrace = false
    ): string {
        static::getValidIn($in);

        $backtrace = $hideBacktrace ? '' : collect(debug_backtrace()[0] ?? [])->only([
            "file",
            "line",
            "function",
            "class",
        ])->toJson(JSON_UNESCAPED_SLASHES) . \PHP_EOL;

        $reference = $reference && is_array($reference) ? json_encode(
            $reference,
            JSON_UNESCAPED_SLASHES
        ) : $reference;

        return static::line() . sprintf(
            '%s %s %s',
            $this->getStartEndDiff($in),
            $reference ? "\nREF: \n{$reference}\n" : '',
            $hideBacktrace ? '' : "\nBacktrace: \n{$backtrace}"
        ) . static::line();
    }

    /**
     * @param string $in minutes|seconds|days|...
     * @return int
     */
    public function getFinalDiffIn(string $in = 'seconds'): int
    {
        static::getValidIn($in);

        if (!$this->end()) {
            return $this->diffFromNowIn($in);
        }

        return static::diffIn($this->start(), $this->end(), $in);
    }

    /**
     * @param $end null|Carbon|DateTime|string
     * @return void
     */
    protected function setEnd(null|Carbon|DateTime|string $end = null): void
    {
        if ($this->end || !$end) {
            return;
        }

        $this->end = $end instanceof Carbon ? $end : Carbon::parse($end);
    }
    /**
     * @return void
     */
    protected function createId(): void
    {
        if ($this->id) {
            return;
        }

        $this->id = Str::uuid()->toString();
    }

    /**
     * @param $start null|Carbon|DateTime|string
     * @return void
     */
    public function setStart(null|Carbon|DateTime|string $start = 'now'): void
    {
        if ($this->start) {
            return;
        }

        $this->start = $start instanceof Carbon ? $start : Carbon::parse($start);
    }

    /**
     * @return void
     */
    public function endNow(): void
    {
        $this->setEnd(now());
        $this->outputFinished();
    }

    /**
     * @param ?string $format
     * @return Carbon|string
     */
    public function now(?string $format = null): Carbon|string
    {
        return $format ? Carbon::now($format) : Carbon::now();
    }

    /**
     * @return ?Carbon
     */
    public function end(): ?Carbon
    {
        if (!$this->end) {
            return null;
        }

        return $this->end;
    }

    /**
     * @return ?string
     */
    public function getId(): ?string
    {
        if (!$this->id) {
            return null;
        }

        return $this->id;
    }

    /**
     * function start
     *
     * @return ?Carbon
     */
    public function start(): ?Carbon
    {
        if (!$this->start) {
            return null;
        }

        return $this->start;
    }

    /**
     * @param string $in minutes|seconds|days|...
     * @return string
     */
    public function getStartEndDiff(string $in = 'seconds'): string
    {
        static::getValidIn($in);

        $endTime = $this->end() ? "{$this->end('c')}" : '[Not finished yet.]';

        return implode(
            ' | ',
            [
                "Start: {$this->start('c')}",
                "End: {$endTime}",
                "Now: {$this->now('c')}",
                sprintf("Started %s %s ago", $this->getFinalDiffIn($in), $in),
                "ID: {$this->getId()}",
            ]
        );
    }

    /**
     * @param string $in minutes|seconds|days|...
     * @return
     */
    public function getStartNowDiff(string $in = 'seconds')
    {
        static::getValidIn($in);

        return implode(
            ' | ',
            [
                "Start: {$this->start('c')}",
                "Now: {$this->now('c')}",
                sprintf("Started %s %s ago", $this->diffFromNowIn($in), $in),
                "ID: {$this->getId()}",
            ]
        );
    }

    /**
     * @param string $in
     * @return string
     */
    protected static function getValidIn(string &$in): string
    {
        [$in, $method] = static::diffMethodInfo($in);

        return $in;
    }

    /**
     * @param string $in
     * @return array
     */
    protected static function diffMethodInfo(string $in): array
    {
        $validValues = [
            'minute',
            'minutes',
            'second',
            'seconds',
            'hour',
            'hours',
            'day',
            'days',
            'year',
            'years',
        ];

        $in = in_array($in, $validValues) ? $in : 'seconds';
        $diffMethod = 'diffIn' . Str::studly($in);

        return [
            $in,
            $diffMethod,
        ];
    }

    /**
     * @param string $char
     * @param int $repeat
     * @return string
     */
    public static function line(string $char = '-', int $repeat = 100)
    {
        return PHP_EOL . str_repeat($char, $repeat) . PHP_EOL;
    }
}
