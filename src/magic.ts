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


interface Params {
    calendar_file_url: string
    dtopen_subtrahend: number
    discord_template: string
}




const DEFAULT_PARAMS: Params = {
    calendar_file_url: '',
    dtopen_subtrahend: 30,
    discord_template: `
**{summary}**
*{categories}*
open:  <t:{dtopen}:f> (<t:{dtopen}:R>)
start: <t:{dtstart}:f> (<t:{dtstart}:R>)
end:   <t:{dtend}:f> (<t:{dtend}:R>)
    `,
}




const init_params_form = (): void =>
{
    const params_form = document.querySelector('form') as HTMLFormElement

    if (!params_form) return

    const params_form_calendar_file_url = document.querySelector('form [name=calendar_file_url]') as HTMLInputElement
    const params_form_dtopen_subtrahend = document.querySelector('form [name=dtopen_subtrahend]') as HTMLInputElement
    const params_form_discord_template = document.querySelector('form [name=discord_template]') as HTMLInputElement
    const params_form_submit = document.querySelector('form [type=submit]') as HTMLInputElement
    const params_form_reset = document.querySelector('form [type=reset]') as HTMLInputElement

    let storage_data: string | null = window.localStorage.getItem('deoh_params')
    let params: Params

    if (!storage_data) {
        params = DEFAULT_PARAMS
        window.localStorage.setItem('deoh_params', JSON.stringify(params))
    }
    else {
        params = JSON.parse(storage_data)
    }

    params_form_calendar_file_url.value = params.calendar_file_url
    params_form_dtopen_subtrahend.value = String(params.dtopen_subtrahend)
    params_form_discord_template.value = params.discord_template.trim()

    params_form_submit.addEventListener('click', (event) => {
        event.preventDefault()

        if (
            !params_form_calendar_file_url.value
            || !params_form_dtopen_subtrahend.value
            || !params_form_discord_template.value
        ) {
            alert('fill out all fields')
            return
        }

        params = {
            calendar_file_url: params_form_calendar_file_url.value,
            dtopen_subtrahend: Number(params_form_dtopen_subtrahend.value),
            discord_template: params_form_discord_template.value,
        }
        window.localStorage.setItem('deoh_params', JSON.stringify(params))

        params_form_discord_template.value = btoa(params_form_discord_template.value)

        params_form.submit()
    }, false)


    params_form_reset.addEventListener('click', (event) => {
        event.preventDefault()

        params_form_calendar_file_url.value = DEFAULT_PARAMS.calendar_file_url
        params_form_dtopen_subtrahend.value = String(DEFAULT_PARAMS.dtopen_subtrahend)
        params_form_discord_template.value = DEFAULT_PARAMS.discord_template.trim()

        window.localStorage.setItem('deoh_params', JSON.stringify(DEFAULT_PARAMS))

    }, false)
}


const load_calendar = async (): Promise<void> =>
{
    const output_element: HTMLTableSectionElement | null = document.querySelector('.calendar_output')

    if (!output_element) return

    const params: URLSearchParams = new URLSearchParams(document.location.search)
    const calendar_file_url: string | null = params.get('calendar_file_url')
    const dtopen_subtrahend: string | null = params.get('dtopen_subtrahend')
    const discord_template: string = atob(params.get('discord_template') ?? '')
    const data: APIData = await api_request([`calendar_file_url=${calendar_file_url}`, `dtopen_subtrahend=${dtopen_subtrahend}`])

    output_element.innerHTML = `
        <div class="head">
            <small>displaying ${data.events_count} events</small>
        </div>
    `

    data.events.forEach(v => {
        const container: HTMLDivElement = document.createElement('div')
        container.classList.add('event')

        let discord_code_content = discord_template
        discord_code_content = discord_code_content.replaceAll('{summary}', v.summary)
        discord_code_content = discord_code_content.replaceAll('{categories}', v.categories)
        discord_code_content = discord_code_content.replaceAll('{dtopen}', v.dtopen.toString())
        discord_code_content = discord_code_content.replaceAll('{dtstart}', v.dtstart.toString())
        discord_code_content = discord_code_content.replaceAll('{dtend}', v.dtend.toString())

        const discord_code = document.createElement('pre')
        discord_code.textContent = discord_code_content

        container.innerHTML = `
            <strong>${v.summary}</strong><br>
            <em>${v.categories}</em><br>
            <strong>open</strong>: ${human_timestamp((v.dtopen) * 1000)}
            &middot; <strong>start</strong>: ${human_timestamp(v.dtstart * 1000)}
            &middot; <strong>end</strong>: ${human_timestamp(v.dtend * 1000)}<br>
            <br>
        `

        container.append(discord_code)

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




window.addEventListener('load', () => {
    init_params_form()
    load_calendar()
}, false)
