<?php
declare(strict_types=1);
namespace org\etrusci\deoh;
use Exception;




require __DIR__.'/lib/parser.php';


// print_r($_GET);

$output = [
    'info' => [
        'all event timestamps = UTC',
        'events with a dtstart time < (time.now - event_age_threshold) will not be displayed',
        'required_url_params' => [
            'calendar_file_url = <URL_TO_CALENDAR_FILE>',
            'dtopen_subtrahend = <MINUTES> (will be converted to seconds in this JSON output)',
        ],
    ],
    'error' => [],
    'dtopen_subtrahend' => -1,
    'event_age_threshold' => -1,
    'events_count' => -1,
    'events' => [],
];


try {
    if (!isset($_GET['calendar_file_url']) || empty($_GET['calendar_file_url'])) {
        throw new Exception('missing or empty param "calendar_file_url"');
    }

    if (!filter_var($_GET['calendar_file_url'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
        throw new Exception('bad "calendar_file_url"');
    }

    if (!isset($_GET['dtopen_subtrahend']) || !is_numeric($_GET['dtopen_subtrahend'])) {
        throw new Exception('missing or empty param "dtopen_subtrahend"');
    }

    $calendar_file_url = trim($_GET['calendar_file_url']);
    $dtopen_subtrahend = intval($_GET['dtopen_subtrahend']);

    $Parser = new Parser(calendar_file_url: $calendar_file_url, dtopen_subtrahend: $dtopen_subtrahend);

    $Parser->fetch_raw_calendar_data();
    $Parser->parse_raw_calendar_data();

    $output['event_age_threshold'] = $Parser->event_age_threshold;
    $output['events_count'] = count($Parser->events);
    $output['dtopen_subtrahend'] = $Parser->dtopen_subtrahend;
    $output['events'] = $Parser->events;
}
catch (Exception $boo) {
    $output['error'][] = $boo->getMessage();
}


$output = json_encode($output, flags: JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

header('Content-type: application/json; charset=utf-8');
print($output);
