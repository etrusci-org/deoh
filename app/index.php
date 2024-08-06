<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.lime.min.css">

    <title>Discord Event Organisator Helper</title>
</head>
<body>
    <header class="container">
        <nav>
            <ul>
                <li><strong>Discord Event Organisator Helper</strong></li>
            </ul>
            <ul>
                <li><a href="./api.php">api</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">

        <table>
            <thead>
                <tr>
                    <th>start</th>
                    <th>end</th>
                    <th>summary</th>
                    <th>discord code</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

    </main>


    <script>
        const api_request = async (params = []) =>
        {
            const p = params.join('&')
            console.log('[api_request]', p)
            return fetch(`./api.php?${p}`, { method: 'GET', cache: 'no-cache' }).then((r) => r.json())
        }


        const human_timestamp = (unixtime_ms, format = '{year}-{month}-{day} {hour}:{minute} {tzoffset}') =>
        {
            const dt = new Date(unixtime_ms)
            console.log(dt)
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

            const data = await api_request(['calendar_file_url=https://ics.teamup.com/feed/ksz16ffdqpt3ojrz6f/13007322.ics'])

            data.events.forEach(v => {
                const tr = document.createElement('tr')
                const td1 = document.createElement('td')
                const td2 = document.createElement('td')
                const td3 = document.createElement('td')
                const td4 = document.createElement('td')
                const code = document.createElement('pre')

                td1.textContent = human_timestamp(v.dtstart * 1000)
                td2.textContent = human_timestamp(v.dtend * 1000)
                td3.textContent = v.summary
                code.textContent = `**${v.summary}**\n`
                code.textContent += `open: <t:${v.dtstart - 1800}:f> (<t:${v.dtstart - 1800}:R>)\n`
                code.textContent += `start: <t:${v.dtstart}:f> (<t:${v.dtstart}:R>)\n`
                code.textContent += `end: <t:${v.dtend}:f> (<t:${v.dtend}:R>)`

                td4.append(code)
                tr.append(td1, td2, td3, td4)
                output_element.append(tr)
            })

        }, false)
    </script>


</body>
</html>
