import { buildTopology } from './network-topology';

const topology = document.querySelector('[data-network-topology]');
const form = document.querySelector('[data-network-form]');
if (topology && form) {
    const buttons = [...topology.querySelectorAll('[data-topology-node]')];
    const title = topology.querySelector('[data-topology-title]');
    const detail = topology.querySelector('[data-topology-detail]');
    let selected = 0;
    let diagram;

    const selectNode = (index) => {
        selected = index;
        buttons.forEach((button, i) => button.setAttribute('aria-pressed', String(i === index)));
        title.textContent = diagram.nodes[index].title;
        detail.textContent = diagram.nodes[index].detail;
    };

    const update = () => {
        diagram = buildTopology(topology.dataset.type, Object.fromEntries(new FormData(form)));
        buttons.forEach((button, i) => {
            button.querySelector('[data-node-title]').textContent = diagram.nodes[i].title;
            button.querySelector('[data-node-subtitle]').textContent = diagram.nodes[i].subtitle;
        });
        topology.querySelectorAll('[data-link-label]').forEach((label, i) => { label.textContent = diagram.links[i]; });
        selectNode(selected);
    };

    buttons.forEach((button, i) => button.addEventListener('click', () => selectNode(i)));
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    window.addEventListener('pageshow', update);
    update();
}
