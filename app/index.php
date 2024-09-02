<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.slate.min.css">
    <style>
        h1 {
            font-size: 100%;
        }
        .calendar_output {
            margin-top: 3rem;
        }
        .calendar_output .head {
            margin-bottom: 3rem;
        }
        .calendar_output .event {
            padding-bottom: 1rem;
            border-bottom: 2px dotted var(--pico-primary-underline);
            margin-bottom: 3rem;
        }
    </style>

    <title>Discord Event Organisator Helper</title>
</head>
<body>
    <header class="container">
        <h1><a href="./">Discord Event Organisator Helper</a></h1>
    </header>

    <main class="container">
        <?php if (!isset($_GET['calendar_file_url'])): ?>

            <form action="?" method="get">
                <fieldset>
                    <label>
                        Calendar file URL:
                        <input type="url" name="calendar_file_url" placeholder="https://ics.teamup.com/feed/example/calendar.ics" required>
                    </label>

                    <label>
                        Open time subtrahend:
                        <input type="number" name="dtopen_subtrahend" min="0" required>
                        <small>minutes, relative to start time</small>
                    </label>

                    <label>
                        Discord template:
                        <textarea name="discord_template" rows="6"></textarea>
                        <small>available variables: {summary} {categories} {dtopen} {dtstart} {dtend}</small>
                    </label>
                </fieldset>
                <input type="submit" value="load calendar">
                <input type="reset" value="reset form">
            </form>

            <p>
                Form values are saved into the local storage of your webbrowser for easy reuse. If you want to reset them, click "reset form".
            </p>

        <?php else: ?>

            <section>
                <p>
                    The <strong>open</strong>, <strong>start</strong> and <strong>end</strong> times on this page should be in your local time (zone: <script>document.write(Intl.DateTimeFormat().resolvedOptions().timeZone ?? 'unknown')</script>).<br>
                    Timestamps in the Discord code are in UTC ready to be displayed in the local time of the reader then.<br>
                    Events with a start <em>time < (time.now - 1 day)</em> are not displayed.<br>
                    You can also get the data in the <a href="./api.php?calendar_file_url=<?php print($_GET['calendar_file_url'] ?? ''); ?>&dtopen_subtrahend=<?php print($_GET['dtopen_subtrahend'] ?? ''); ?>">JSON</a> format.
                </p>
            </section>
            <hr>

            <div class="calendar_output"></div>

        <?php endif; ?>
    </main>

    <script src="./lib/magic.js"></script>
</body>
</html>
