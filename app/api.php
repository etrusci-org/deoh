<?php
declare(strict_types=1);
namespace org\etrusci\deoh;
use Exception;




require __DIR__.'/lib/parser.php';


// print_r($_GET);

$output = [
    'info' => [
        'work in progress',
        'all timestamps = UTC',
    ],
    'error' => [],
    'events_age_threshold' => 0,
    'events_count' => 0,
    'dtopen_subtrahend' => 0,
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

    $output['events_age_threshold'] = $Parser->event_age_threshold;
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
