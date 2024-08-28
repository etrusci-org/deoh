interface APIData {
    info: string[]
    error: string[]
    event_age_threshold: number
    events_count: number
    events: APIDataEvent[]
}


interface APIDataEvent {
    summary: string
    categories: string
    dtopen: number
    dtstart: number
    dtend: number
}




const load_calendar = async (): Promise<void> =>
{
    const output_element: HTMLTableSectionElement | null = document.querySelector('.calendar_output')

    if (!output_element) return

    const params: URLSearchParams = new URLSearchParams(document.location.search)
    const calendar_file_url: string | null = params.get('calendar_file_url')
    const dtopen_subtrahend: string | null = params.get('dtopen_subtrahend')
    const data: APIData = await api_request([`calendar_file_url=${calendar_file_url}`, `dtopen_subtrahend=${dtopen_subtrahend}`])

    output_element.innerHTML = `
        <div class="head">
            <small>displaying ${data.events_count} events</small>
        </div>
    `

    data.events.forEach(v => {
        const container: HTMLDivElement = document.createElement('div')
        container.classList.add('event')

        container.innerHTML = `
            <strong>${v.summary}</strong><br>
            <em>${v.categories}</em><br>
            <strong>open</strong>: ${human_timestamp((v.dtopen) * 1000)}
            &middot; <strong>start</strong>: ${human_timestamp(v.dtstart * 1000)}
            &middot; <strong>end</strong>: ${human_timestamp(v.dtend * 1000)}<br>
            <br>
            <code>
                **${v.summary}**<br>
                *${v.categories}*<br>
                open:  &lt;t:${v.dtopen}:f&gt;  (&lt;t:${v.dtopen}:R&gt;)<br>
                start: &lt;t:${v.dtstart}:f&gt; (&lt;t:${v.dtstart}:R&gt;)<br>
                end:   &lt;t:${v.dtend}:f&gt;   (&lt;t:${v.dtend}:R&gt;)
            </code>
        `

        output_element.append(container)
    })
}


const api_request = async (params: string[] = []): Promise<APIData> =>
{
    const p: string = params.join('&')

    return fetch(`./api.php?${p}`, { method: 'GET', cache: 'no-cache' }).then((r) => r.json())
}


const human_timestamp = (unixtime_ms: number, format: string = '{year}-{month}-{day} {hour}:{minute}'): string =>
{
    const dt: Date = new Date(unixtime_ms)
    let f: string = format

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




window.addEventListener('load', load_calendar, false)
