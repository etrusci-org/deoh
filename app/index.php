<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2.0.6/css/pico.lime.css">
    <style>
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
        <nav>
            <ul>
                <li><strong><a href="./">Discord Event Organisator Helper</a></strong></li>
            </ul>
            <ul>
                <li><a href="./api.php?calendar_file_url=<?php print($_GET['calendar_file_url'] ?? ''); ?>">api</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <?php
        if (
            !isset($_GET['calendar_file_url'])
            || empty($_GET['calendar_file_url'])
            || !filter_var($_GET['calendar_file_url'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)
        ):
        ?>
            <form action="?" method="get">
                <fieldset role="group">
                    <input type="url" name="calendar_file_url" placeholder="https://example.org/cal.ics" required>
                    <input type="submit" value="load">
                </fieldset>
            </form>

        <?php else: ?>

            <div class="calendar_output"></div>

        <?php endif; ?>
    </main>

    <script src="./lib/magic.js"></script>
</body>
</html>
