import { ACTIVITY_KEY, activitySummary, readActivities, recordActivity } from './activity-store';

const eventElement = document.getElementById('netcalc-activity');
if (eventElement) {
    try {
        recordActivity(window.localStorage, JSON.parse(eventElement.textContent));
    } catch {
        document.querySelector('[data-activity-save-status]').textContent = 'Hasil berhasil dibuat, tetapi aktivitas tidak dapat disimpan. Periksa izin dan ruang penyimpanan browser.';
    }
}

const dashboard = document.querySelector('[data-activity-dashboard]');
if (dashboard) {
    const status = dashboard.querySelector('[data-activity-dashboard-status]');
    const latestPanel = dashboard.querySelector('[data-latest-activity]');
    const empty = dashboard.querySelector('[data-activity-empty]');
    const list = dashboard.querySelector('[data-activity-list]');
    const resumeLatest = dashboard.querySelector('[data-resume-latest]');
    let latest = null;

    const resume = (entry) => {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!token) {
            status.textContent = 'Muat ulang halaman sebelum melanjutkan latihan.';
            return;
        }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = entry.kind === 'calculator' ? dashboard.dataset.calculatorAction : dashboard.dataset.configurationAction;
        for (const [name, value] of Object.entries({ ...entry.input, _token: token })) {
            const field = document.createElement('input');
            field.type = 'hidden';
            field.name = name;
            field.value = value;
            form.append(field);
        }
        form.hidden = true;
        document.body.append(form);
        form.submit();
    };

    const render = () => {
        let entries = [];
        status.textContent = '';
        try {
            entries = readActivities(window.localStorage);
        } catch {
            status.textContent = 'Aktivitas tidak dapat dibaca. Penyimpanan browser mungkin dibatasi atau datanya rusak. Fitur kalkulator dan konfigurasi tetap dapat digunakan.';
        }

        for (const node of dashboard.querySelectorAll('[data-activity-count]')) {
            const kind = node.dataset.activityCount;
            node.textContent = String(kind === 'total' ? entries.length : entries.filter((entry) => entry.kind === kind).length);
        }

        latest = entries[0] ?? null;
        latestPanel.hidden = !latest;
        empty.hidden = entries.length > 0;
        if (latest) {
            const summary = activitySummary(latest);
            dashboard.querySelector('[data-latest-title]').textContent = summary.title;
            dashboard.querySelector('[data-latest-description]').textContent = summary.description;
        }

        list.replaceChildren();
        for (const entry of entries.slice(0, 5)) {
            const summary = activitySummary(entry);
            const item = document.createElement('li');
            item.className = 'flex flex-col items-start gap-3 py-4 sm:flex-row sm:items-center sm:justify-between';
            const description = document.createElement('div');
            description.className = 'w-full min-w-0 sm:w-auto sm:flex-1';
            const title = document.createElement('p');
            title.className = 'break-words text-sm font-semibold text-slate-200';
            title.textContent = summary.title;
            const detail = document.createElement('p');
            detail.className = 'mt-1 break-all text-xs leading-relaxed text-slate-400';
            detail.textContent = summary.description;
            const time = document.createElement('time');
            time.className = 'mt-2 block text-xs text-slate-500';
            time.dateTime = new Date(entry.updatedAt).toISOString();
            time.textContent = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(entry.updatedAt);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'action-button rounded-lg border border-slate-700 px-3 py-2 text-xs font-semibold text-cyan-300 hover:border-cyan-400';
            button.textContent = 'Buka latihan';
            button.setAttribute('aria-label', `Buka latihan ${summary.title}`);
            button.addEventListener('click', () => resume(entry));
            description.append(title, detail, time);
            item.append(description, button);
            list.append(item);
        }
    };

    resumeLatest.addEventListener('click', () => { if (latest) resume(latest); });
    window.addEventListener('pageshow', render);
    window.addEventListener('storage', (event) => { if (event.key === ACTIVITY_KEY || event.key === null) render(); });
    render();
}
