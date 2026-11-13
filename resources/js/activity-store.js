export const ACTIVITY_KEY = 'netcalc-activities-v1';
export const ACTIVITY_LIMIT = 50;

function normalizeEvent(event) {
    if (!event || !['calculator', 'configuration'].includes(event.kind) || !event.input || typeof event.input !== 'object') {
        throw new TypeError('Invalid activity');
    }

    let fields;
    if (event.kind === 'calculator' && ['subnet', 'vlsm'].includes(event.input.mode)) {
        fields = ['mode', 'ip', 'cidr', ...(event.input.mode === 'vlsm' ? ['hosts'] : [])];
    } else if (event.kind === 'configuration' && event.input.type === 'switching') {
        fields = ['type', 'hostname', 'vlan_id', 'vlan_name', 'access_port', 'trunk_port'];
    } else if (event.kind === 'configuration' && event.input.type === 'routing') {
        fields = ['type', 'hostname', 'interface', 'ip', 'prefix', 'destination', 'route_prefix', 'next_hop'];
    } else {
        throw new TypeError('Invalid activity mode');
    }

    const input = {};
    for (const field of fields) {
        const value = event.input[field];
        if (!['string', 'number'].includes(typeof value) || String(value).length === 0 || String(value).length > (field === 'hosts' ? 2000 : 64)) {
            throw new TypeError('Invalid activity input');
        }
        input[field] = String(value);
    }
    return { kind: event.kind, input };
}

const signature = (event) => JSON.stringify([event.kind, event.input]);

export function readActivities(storage) {
    const raw = storage.getItem(ACTIVITY_KEY);
    if (raw === null) return [];
    if (raw.length > 1_000_000) throw new Error('Activity data is too large');
    const saved = JSON.parse(raw);
    if (saved?.version !== 1 || !Array.isArray(saved.entries)) throw new Error('Invalid activity storage');

    const entries = [];
    for (const item of saved.entries) {
        try {
            if (typeof item.id !== 'string' || !item.id || item.id.length > 128 || !Number.isFinite(item.updatedAt) || item.updatedAt <= 0 || item.updatedAt > 8.64e15) continue;
            entries.push({ ...normalizeEvent(item), id: item.id, updatedAt: item.updatedAt });
        } catch {
            // Discard incomplete records instead of rendering untrusted storage data.
        }
    }

    const seen = new Set();
    return entries.sort((a, b) => b.updatedAt - a.updatedAt).filter((entry) => {
        const key = signature(entry);
        if (seen.has(key)) return false;
        seen.add(key);
        return true;
    }).slice(0, ACTIVITY_LIMIT);
}

export function recordActivity(storage, event, now = Date.now()) {
    const normalized = normalizeEvent(event);
    const entries = readActivities(storage);
    const existing = entries.find((item) => signature(item) === signature(normalized));
    const id = existing?.id ?? globalThis.crypto?.randomUUID?.() ?? `${now.toString(36)}-${Math.random().toString(36).slice(2)}`;
    const entry = { ...normalized, id, updatedAt: now };
    const next = [entry, ...entries.filter((item) => signature(item) !== signature(normalized))].slice(0, ACTIVITY_LIMIT);
    storage.setItem(ACTIVITY_KEY, JSON.stringify({ version: 1, entries: next }));
    return next;
}

export function activitySummary(entry) {
    const input = entry.input;
    if (entry.kind === 'calculator') {
        return { title: input.mode === 'vlsm' ? 'Perencanaan VLSM' : 'Perhitungan subnet', description: `${input.ip}/${input.cidr}${input.mode === 'vlsm' ? ` · Host: ${input.hosts}` : ''}` };
    }
    return {
        title: `${input.type === 'switching' ? 'Switching' : 'Routing'} · ${input.hostname}`,
        description: input.type === 'switching' ? `VLAN ${input.vlan_id} (${input.vlan_name}) · ${input.access_port} → ${input.trunk_port}` : `${input.ip}/${input.prefix} → ${input.destination}/${input.route_prefix} via ${input.next_hop}`,
    };
}
