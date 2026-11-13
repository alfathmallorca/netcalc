import test from 'node:test';
import assert from 'node:assert/strict';
import { ACTIVITY_KEY, ACTIVITY_LIMIT, readActivities, recordActivity, activitySummary } from '../../resources/js/activity-store.js';

function storage() {
    const values = new Map();
    return { getItem: (key) => values.get(key) ?? null, setItem: (key, value) => values.set(key, value) };
}
const calculator = { kind: 'calculator', input: { mode: 'subnet', ip: '192.168.1.0', cidr: 24 } };
const switching = { kind: 'configuration', input: { type: 'switching', hostname: 'SW-LAB', vlan_id: 10, vlan_name: 'STAFF', access_port: 'FastEthernet0/1', trunk_port: 'GigabitEthernet0/1' } };

test('new browser has zero activities', () => assert.deepEqual(readActivities(storage()), []));

test('stores real input without csrf tokens or unrelated fields', () => {
    const store = storage();
    recordActivity(store, { ...calculator, input: { ...calculator.input, _token: 'private-token', hosts: 'ignored-in-subnet-mode' } }, 100);
    const entries = readActivities(store);
    assert.equal(entries.length, 1);
    assert.deepEqual(entries[0].input, { mode: 'subnet', ip: '192.168.1.0', cidr: '24' });
    assert.equal(store.getItem(ACTIVITY_KEY).includes('private-token'), false);
});

test('resuming the same inputs updates time and keeps the same record', () => {
    const store = storage();
    const original = recordActivity(store, calculator, 100)[0];
    recordActivity(store, switching, 200);
    recordActivity(store, { ...calculator, input: { cidr: '24', mode: 'subnet', ip: '192.168.1.0' } }, 300);
    const entries = readActivities(store);
    assert.equal(entries.length, 2);
    assert.equal(entries[0].id, original.id);
    assert.equal(entries[0].updatedAt, 300);
    assert.equal(entries[0].kind, 'calculator');
});

test('retains the most recent 50 unique exercises', () => {
    const store = storage();
    for (let i = 1; i <= 55; i++) recordActivity(store, { ...calculator, input: { ...calculator.input, ip: `10.0.${i}.0` } }, i);
    const entries = readActivities(store);
    assert.equal(entries.length, ACTIVITY_LIMIT);
    assert.equal(entries[0].input.ip, '10.0.55.0');
    assert.equal(entries.at(-1).input.ip, '10.0.6.0');
});

test('distinct VLSM host requirements remain separate exercises', () => {
    const store = storage();
    for (const hosts of ['50, 25', '100, 10']) recordActivity(store, { ...calculator, input: { ...calculator.input, mode: 'vlsm', hosts } });
    assert.equal(readActivities(store).length, 2);
    assert.match(activitySummary(readActivities(store)[0]).description, /Host:/);
});

test('invalid data and unknown replay modes cannot be stored', () => {
    const store = storage();
    assert.throws(() => recordActivity(store, { kind: 'external-action', input: {} }), TypeError);
    assert.throws(() => recordActivity(store, { kind: 'calculator', input: { mode: 'anything' } }), TypeError);
    assert.deepEqual(readActivities(store), []);
});

test('corrupt storage is reported without overwriting existing content', () => {
    const store = storage();
    store.setItem(ACTIVITY_KEY, '{broken');
    assert.throws(() => readActivities(store));
    assert.throws(() => recordActivity(store, calculator));
    assert.equal(store.getItem(ACTIVITY_KEY), '{broken');
});

test('unreadable records are ignored while valid exercises remain available', () => {
    const store = storage();
    const [entry] = recordActivity(store, switching, 100);
    store.setItem(ACTIVITY_KEY, JSON.stringify({ version: 1, entries: [entry, { ...entry, updatedAt: 'not-a-date' }, { id: 'invalid', updatedAt: 100 }] }));
    assert.equal(readActivities(store).length, 1);
    assert.equal(activitySummary(readActivities(store)[0]).title, 'Switching · SW-LAB');
});

test('storage permission and quota failures propagate to the UI handler', () => {
    assert.throws(() => readActivities({ getItem() { throw Error('blocked'); } }), /blocked/);
    assert.throws(() => recordActivity({ getItem: () => null, setItem() { throw Error('quota'); } }, calculator), /quota/);
});
