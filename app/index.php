<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.lime.css">
    <style>
        th {
            vertical-align: bottom;
        }
        td {
            vertical-align: top;
        }
    </style>

    <title>Discord Event Organisator Helper</title>
</head>
<body>
    <header class="container">
        <nav>
            <ul>
                <li><strong>Discord Event Organisator Helper</strong></li>
            </ul>
            <ul>
                <li><a href="./">reset</a></li>
                <li><a href="./api.php">api</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <?php
        if (!isset($_GET['calendar_file_url']) || empty($_GET['calendar_file_url']) || !filter_var($_GET['calendar_file_url'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            // print('missing or bad param "calendar_file_url"');

            print('
            <form action="?" method="get">
                <fieldset role="group">
                    <input type="url" name="calendar_file_url" placeholder="https://example.org/cal.ics" required>
                    <input type="submit" value="load">
                </fieldset>
            </form>
            ');
        }
        else {
            print('
            <table>
                <thead>
                    <tr>
                        <th>
                            open<br>
                            start<br>
                            end
                        </th>
                        <th>
                            summary<br>
                            category
                        </td>
                        <th>discord code</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            ');
        }
        ?>
    </main>


    <script>
        // @ts-check
        const api_request = async (params = []) =>
        {
            const p = params.join('&')
            console.log('[api_request]', p)
            return fetch(`./api.php?${p}`, { method: 'GET', cache: 'no-cache' }).then((r) => r.json())
        }


        const human_timestamp = (unixtime_ms, format = '{year}-{month}-{day} {hour}:{minute} {tzoffset}') =>
        {
            const dt = new Date(unixtime_ms)
            let f = format

            f = f.replace('{year}', String(dt.getFullYear()).padStart(2, '0'))
            f = f.replace('{month}', String(dt.getMonth() + 1).padStart(2, '0'))
            f = f.replace('{day}', String(dt.getDate()).padStart(2, '0'))
            f = f.replace('{hour}', String(dt.getHours()).padStart(2, '0'))
            f = f.replace('{minute}', String(dt.getMinutes()).padStart(2, '0'))
            f = f.replace('{second}', String(dt.getSeconds()).padStart(2, '0'))
            f = f.replace('{millisecond}', String(dt.getMilliseconds()).padEnd(3, '0'))
            f = f.replace('{tzoffset}', `UTC${String(dt.getTimezoneOffset() / 60)}`)

            return f
        }


        window.addEventListener('load', async () => {
            const output_element = document.querySelector('table > tbody')

            const data = await api_request(['calendar_file_url=<?php print($_GET['calendar_file_url']); ?>'])

            data.events.forEach(v => {
                const tr = document.createElement('tr')
                const td1 = document.createElement('td')
                const td2 = document.createElement('td')
                const td3 = document.createElement('td')
                const cont_dtopen = document.createElement('div')
                const cont_dtstart = document.createElement('div')
                const cont_dtend = document.createElement('div')
                const cont_summary = document.createElement('div')
                const cont_categories = document.createElement('div')
                const cont_code = document.createElement('pre')

                cont_dtopen.textContent = human_timestamp((v.dtstart - 1800) * 1000)
                cont_dtstart.textContent = human_timestamp(v.dtstart * 1000)
                cont_dtend.textContent = human_timestamp(v.dtend * 1000)
                cont_summary.textContent = v.summary
                cont_categories.textContent = v.categories
                cont_code.textContent = `**${v.summary}**\n`
                cont_code.textContent += `open: <t:${v.dtstart - 1800}:f> (<t:${v.dtstart - 1800}:R>)\n`
                cont_code.textContent += `start: <t:${v.dtstart}:f> (<t:${v.dtstart}:R>)\n`
                cont_code.textContent += `end: <t:${v.dtend}:f> (<t:${v.dtend}:R>)`

                td1.append(cont_dtopen, cont_dtstart, cont_dtend)
                td2.append(cont_summary, cont_categories)
                td3.append(cont_code)

                tr.append(td1, td2, td3)
                output_element.append(tr)
            })

        }, false)
    </script>


</body>
</html>
