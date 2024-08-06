<?php
declare(strict_types=1);
namespace org\etrusci\deoh;
use Exception;
use DateTime;
use DateTimeZone;




class Parser
{
    public string $raw_calendar_data = '';
    public array $events = [];


    public function __construct(
        public string $calendar_file_url,
        public int $event_age_threshold = 86400,
    ) {
        // if (($this->event_age_threshold < 0)) {
        //     $this->event_age_threshold = 0;
        // }
        $this->event_age_threshold = max(0, $this->event_age_threshold);
    }


    public function fetch_raw_calendar_data(): void
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->calendar_file_url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $data = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($http_code != 200) {
            throw new Exception("[fetch_raw_calendar_data] got bad http_code: {$http_code}");
        } # for now we just assume it's all good if we get code 200

        $this->raw_calendar_data = trim($data);
    }


    public function parse_raw_calendar_data(): void
    {
        $events = $this->extract_events($this->raw_calendar_data);

        foreach ($events as $event) {
            $summary = $this->extract_summary($event);
            $categories = $this->extract_categories($event);
            $dtstart = $this->extract_timestamp($event, key: 'dtstart');
            $dtend = $this->extract_timestamp($event, key: 'dtend');

            $this->events[] = [
                'summary' => $summary,
                'categories' => $categories,
                'dtstart' => $dtstart,
                'dtend' => $dtend,
            ];

        }

        foreach ($this->events as $k => $v) {
            if ($v['dtstart'] < time() - $this->event_age_threshold) {
                unset($this->events[$k]);
            }
        }

        usort($this->events, function($a, $b) {
            if ($a['dtstart'] == $b['dtstart']) return 0;
            return ($a['dtstart'] < $b['dtstart']) ? -1 : 1;
        });
    }


    protected static function extract_events(string $data): array
    {
        preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $data, $matches);
        return ($matches) ? $matches[1] : [];
    }


    protected static function extract_summary(string $event): string|null
    {
        preg_match('/SUMMARY:(.+)?/', $event, $match);
        return ($match) ? trim($match[1]) : null;
    }


    protected static function extract_categories(string $event): string|null
    {
        preg_match('/CATEGORIES:(.+)?/', $event, $match);
        return ($match) ? trim($match[1]) : null;
    }


    protected static function extract_timestamp(string $event, string $key): int|null
    {
        preg_match('/'.strtoupper($key).';TZID=(.+)?:(.+)?/', $event, $match);
        $dt = new DateTime($match[2], new DateTimeZone($match[1]));
        $dt->setTimezone(new DateTimeZone('UTC'));
        return $dt->getTimestamp();
    }


}
