<?php
$tokenJs   = json_encode($token ?? '');
$isDemo    = ($token ?? '') === 'demo';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $title ?? 'visualizer OWL & RDF' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@300;400;500&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>

:root {
    --bg:       #0a0b0f;
    --bg2:      #11131a;
    --bg3:      #181c26;
    --surface:  #1e2330;
    --border:   #2a3045;
    --accent:   #4fffb0;
    --accent2:  #7b61ff;
    --accent3:  #ff6b6b;
    --accent4:  #ffd166;
    --accent5:  #06b6d4;
    --text:     #e8eaf0;
    --text2:    #8b93a8;
    --text3:    #505870;
    --radius:   8px;
    --mono:     'DM Mono', monospace;
    --ui:       'DM Sans', sans-serif;
    --head:     'Syne', sans-serif;
}

*,*::before,*::after{
    box-sizing:border-box;
    margin:0;
    padding:0;
}

html{
    font-size:14px;
}

body{
    background:var(--bg);
    color:var(--text);
    font-family:var(--ui);
    height:100vh;
    display:grid;
    grid-template-rows:auto 1fr;
    overflow:hidden
}

::selection{
    background:var(--accent2);
    color:#fff;
}

::-webkit-scrollbar{
    width:5px;
    height:5px;
}

::-webkit-scrollbar-track{
    background:var(--bg2);
}

::-webkit-scrollbar-thumb{
    background:var(--border);
    border-radius:3px;
}

.topbar{
    display:flex;
    align-items:center;
    gap:1rem;
    padding:.75rem 1.25rem;
    border-bottom:1px solid var(--border);
    background:rgba(10,11,15,.9);
    backdrop-filter:blur(12px);
    z-index:100;
    flex-wrap:wrap;
}

.logo{
    font-family:var(--head);
    font-size:1.15rem;
    font-weight:800;
    letter-spacing:-.02em;
    white-space:nowrap;
}

.logo span{
    color:var(--accent)
}

.file-badge{
    font-family:var(--mono);
    font-size:.7rem;
    background:var(--surface);
    border:1px solid var(--border);
    color:var(--text2);
    padding:.2rem .65rem;
    border-radius:4px;
    letter-spacing:.04em;
    white-space:nowrap;
    max-width:220px;
    overflow:hidden;
    text-overflow:ellipsis;
}

.spacer{
    flex:1;
}

.stats-row{
    display:flex;
    gap:.5rem;
    flex-wrap:wrap;
}

.stat-chip{
    font-family:var(--mono);
    font-size:.68rem;
    padding:.2rem .65rem;
    border-radius:4px;
    background:var(--surface);
    border:1px solid var(--border);
    color:var(--text2);
    white-space:nowrap;
}

.stat-chip b{
    color:var(--accent);
    font-weight:500;
}

.back-btn{
    font-family:var(--head);
    font-size:.78rem;
    font-weight:600;
    background:transparent;
    border:1px solid var(--border);
    color:var(--text2);
    padding:.4rem .9rem;
    border-radius:var(--radius);
    cursor:pointer;
    transition:all .2s;
    text-decoration:none;
    display:flex;
    align-items:center;
    gap:.35rem;
    white-space:nowrap;
}

.back-btn:hover{
    color:var(--text);
    border-color:var(--text2);
}

.main-layout{
    display:grid;
    grid-template-columns:200px 1fr 260px;
    overflow:hidden;
    height:100%;
}

.left-nav{
    border-right:1px solid var(--border);
    background:var(--bg2);
    display:flex;
    flex-direction:column;
    padding:1rem 0;
    gap:.25rem;
    overflow-y:auto;
}

.nav-label{
    font-family:var(--mono);
    font-size:.65rem;
    color:var(--text3);
    letter-spacing:.12em;
    text-transform:uppercase;
    padding:.5rem 1.1rem .25rem;
}

.view-btn{
    display:flex;
    align-items:center;
    gap:.65rem;
    padding:.65rem 1.1rem;
    margin:0 .5rem;
    border-radius:var(--radius);
    background:transparent;
    border:none;
    cursor:pointer;
    color:var(--text2);
    font-family:var(--ui);
    font-size:.82rem;
    transition:all .15s;
    text-align:left;
    width:calc(100% - 1rem);
}

.view-btn:hover{
    background:var(--surface);
    color:var(--text)
}

.view-btn.active{
    background:rgba(79,255,176,.1);
    color:var(--accent);
    border:1px solid rgba(79,255,176,.2);
}

.view-btn .vicon{
    font-size:1.05rem;
    flex-shrink:0;
}

.nav-divider{
    height:1px;
    background:var(--border);
    margin:.5rem .75rem;
}

.filter-label{
    font-family:var(--mono);
    font-size:.65rem;
    color:var(--text3);
    letter-spacing:.1em;
    text-transform:uppercase;
    padding:.75rem 1.1rem .35rem;
}

.type-filter{
    display:flex;
    align-items:center;
    gap:.5rem;
    padding:.4rem 1.1rem;
    cursor:pointer;
}

.type-filter label{
    display:flex;
    align-items:center;
    gap:.5rem;
    cursor:pointer;
    font-size:.78rem;
    color:var(--text2);
    flex:1;
}

.type-filter input{
    accent-color:var(--accent);
    cursor:pointer;
}

.type-dot{
    width:8px;
    height:8px;
    border-radius:50%;
    flex-shrink:0;
}

.canvas-wrap{
    position:relative;
    overflow:hidden;
    background:var(--bg);
}

#viz-canvas{
    width:100%;
    height:100%;
}

.canvas-overlay{
    position:absolute;
    inset:0;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-direction:column;
    gap:1rem;
    pointer-events:none;
}

.loading-ring{
    width:48px;
    height:48px;
    border:3px solid var(--border);
    border-top-color:var(--accent);
    border-radius:50%;
    animation:spin .8s linear infinite;
}

@keyframes spin{to{transform:rotate(360deg)}}

.loading-text{
    font-family:var(--mono);
    font-size:.78rem;
    color:var(--text2);
    letter-spacing:.06em;
}

.canvas-controls{
    position:absolute;
    bottom:1rem;
    left:50%;
    transform:translateX(-50%);
    display:flex;
    gap:.4rem;
    background:var(--bg2);
    border:1px solid var(--border);
    border-radius:8px;
    padding:.35rem .5rem;
    backdrop-filter:blur(8px);
}

.ctrl-btn{
    background:transparent;
    border:none;
    color:var(--text2);
    cursor:pointer;
    width:30px;
    height:30px;
    border-radius:5px;
    font-size:1rem;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:all .15s;
}

.ctrl-btn:hover{
    background:var(--surface);
    color:var(--text);
}

.search-wrap{
    position:absolute;
    top:1rem;
    left:50%;
    transform:translateX(-50%);
    display:flex;
    background:var(--bg2);
    border:1px solid var(--border);
    border-radius:8px;
    overflow:hidden;
    backdrop-filter:blur(8px);
    transition:box-shadow .2s;
    width:260px;
}

.search-wrap:focus-within{
    border-color:var(--accent);
    box-shadow:0 0 0 3px rgba(79,255,176,.1);
}

.search-input{
    background:transparent;
    border:none;
    outline:none;
    padding:.55rem .75rem;
    font-family:var(--mono);
    font-size:.76rem;
    color:var(--text);
    flex:1;
    letter-spacing:.02em;
}

.search-input::placeholder{
    color:var(--text3);
}

.search-icon{
    padding:.55rem .7rem;
    color:var(--text3);
    font-size:.85rem;
}

.right-panel{
    border-left:1px solid var(--border);
    background:var(--bg2);
    display:flex;
    flex-direction:column;
    overflow:hidden;
}

.panel-header{
    padding:.85rem 1rem;
    border-bottom:1px solid var(--border);
    font-family:var(--head);
    font-size:.85rem;
    font-weight:700;
    color:var(--text);
    display:flex;
    align-items:center;
    gap:.5rem;
}

.panel-body{
    flex:1;
    overflow-y:auto;
    padding:.75rem;
}

.node-empty{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    height:100%;
    gap:.75rem;
    color:var(--text3);
    font-size:.82rem;
    font-family:var(--mono);
    text-align:center;
    padding:2rem;
}

.node-empty .empty-icon{
    font-size:2.5rem;
    opacity:.4;
}

.detail-type-badge{
    display:inline-block;
    font-family:var(--mono);
    font-size:.68rem;
    letter-spacing:.06em;
    padding:.2rem .6rem;
    border-radius:4px;
    margin-bottom:.75rem;
    font-weight:500;
}

.detail-label{
    font-family:var(--head);
    font-size:1.1rem;
    font-weight:700;
    margin-bottom:.35rem;
    word-break:break-all;
}

.detail-uri{
    font-family:var(--mono);
    font-size:.68rem;
    color:var(--text3);
    word-break:break-all;
    margin-bottom:.85rem;
    line-height:1.5;
}

.detail-comment{
    font-size:.8rem;
    color:var(--text2);
    line-height:1.6;
    margin-bottom:1rem;
    padding:.65rem .8rem;
    background:var(--surface);
    border-radius:var(--radius);
    border-left:3px solid var(--border);
}

.detail-section{
    margin-bottom:.85rem;
}

.detail-section-title{
    font-family:var(--mono);
    font-size:.65rem;
    color:var(--text3);
    letter-spacing:.1em;
    text-transform:uppercase;
    margin-bottom:.5rem;
}

.detail-prop{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:.5rem;
    padding:.35rem 0;
    border-bottom:1px solid rgba(42,48,69,.6);
    font-size:.76rem;
}

.detail-prop:last-child{
    border-bottom:none;
}

.prop-key{
    color:var(--text3);
    font-family:var(--mono);
    flex-shrink:0;
    max-width:50%;
}

.prop-val{
    color:var(--text2);
    text-align:right;
    word-break:break-all;
}

.neighbor-item{
    display:flex;
    align-items:center;
    gap:.5rem;
    padding:.3rem 0;
    font-size:.76rem;
    border-bottom:1px solid rgba(42,48,69,.4);
    cursor:pointer;
    transition:color .15s;
}

.neighbor-item:hover{
    color:var(--accent);
}

.neighbor-item:last-child{
    border-bottom:none;
}

.neighbor-arrow{
    color:var(--text3);
    font-family:var(--mono);
    font-size:.7rem;
}

.legend-section{
    border-top:1px solid var(--border);
    padding:.75rem 1rem;
}

.legend-title{
    font-family:var(--mono);
    font-size:.65rem;
    color:var(--text3);
    letter-spacing:.1em;
    text-transform:uppercase;
    margin-bottom:.6rem;
}

.legend-items{
    display:flex;
    flex-direction:column;
    gap:.3rem;
}

.legend-item{
    display:flex;
    align-items:center;
    gap:.5rem;
    font-size:.75rem;
    color:var(--text2);
}

.legend-dot{
    width:10px;
    height:10px;
    border-radius:50%;
    flex-shrink:0;
}

.legend-line{
    width:22px;
    height:2px;
    flex-shrink:0;
    border-radius:1px;
}

.toast{
    position:fixed;
    bottom:1.5rem;
    right:1.5rem;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:.7rem 1.1rem;
    font-family:var(--mono);
    font-size:.76rem;
    color:var(--text);
    box-shadow:0 8px 32px rgba(0,0,0,.5);
    z-index:9999;
    transform:translateY(20px);
    opacity:0;
    transition:all .3s ease;
    pointer-events:none;
    max-width:320px;
}

.toast.show{
    transform:translateY(0);
    opacity:1;

}

</style>
</head>

<body>

<header class="topbar">
    <div class="logo">visua<span>lizer</span></div>
    <div class="file-badge" id="fileBadge">📄 <?= htmlspecialchars($filename ?? 'ontologie') ?></div>
    <div class="spacer"></div>
    <div class="stats-row" id="statsRow">
        <div class="stat-chip">Chargement…</div>
    </div>
    <a href="/" class="back-btn">← Accueil</a>
</header>


<div class="main-layout">

    <nav class="left-nav">
        <div class="nav-label">Vues</div>
        <button class="view-btn active" data-view="radial" onclick="switchView('radial')">
            Force
        </button>
        <button class="view-btn" data-view="sunburst" onclick="switchView('sunburst')">
            Radiale
        </button>
        <button class="view-btn" data-view="tree" onclick="switchView('tree')">
            Hiérarchie
        </button>

        <div class="nav-divider"></div>
        <div class="filter-label">Filtres</div>
        <div id="typeFilters"></div>

        <div class="nav-divider"></div>
        <div class="filter-label">Options</div>
        <div class="type-filter">
            <label>
                <input type="checkbox" id="showLabels" checked onchange="toggleLabels()">
                Labels
            </label>
        </div>
        <div class="type-filter">
            <label>
                <input type="checkbox" id="showEdgeLabels" onchange="toggleEdgeLabels()">
                Noms arêtes
            </label>
        </div>
    </nav>

    <div class="canvas-wrap">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input class="search-input" type="text" id="searchInput" placeholder="Rechercher un nœud…" oninput="searchNodes(this.value)">
        </div>

        <svg id="viz-canvas"></svg>

        <div class="canvas-overlay" id="canvasOverlay">
            <div class="loading-ring"></div>
            <div class="loading-text">Chargement du graphe…</div>
        </div>

        <div class="canvas-controls">
            <button class="ctrl-btn" title="Zoom +" onclick="zoomIn()">+</button>
            <button class="ctrl-btn" title="Zoom -" onclick="zoomOut()">−</button>
            <button class="ctrl-btn" title="Reset" onclick="resetZoom()">⟳</button>
            <button class="ctrl-btn" title="Plein écran" onclick="fitGraph()">⤢</button>
            <button class="ctrl-btn" title="Exporter SVG" onclick="exportSVG()">↓</button>
        </div>
    </div>

    <aside class="right-panel">
        <div class="panel-header">🔎 Détail du nœud</div>
        <div class="panel-body" id="nodeDetail">
            <div class="node-empty">
                <div class="empty-icon">◎</div>
                <div>Cliquez sur un nœud<br>pour voir ses détails</div>
            </div>
        </div>

        <div class="legend-section">
            <div class="legend-title">Légende</div>
            <div class="legend-items" id="legendItems"></div>
        </div>
    </aside>
</div>

<div class="toast" id="toast"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.9.0/d3.min.js"></script>

<script>

const TOKEN = <?= $tokenJs ?>;
const IS_DEMO = <?= $isDemo ? 'true' : 'false' ?>;

const NODE_COLORS = {
    class:              '#4fffb0',
    objectProperty:     '#7b61ff',
    datatypeProperty:   '#ffd166',
    annotationProperty: '#06b6d4',
    individual:         '#ff6b6b',
    restriction:        '#f97316',
    ontology:           '#ec4899',
    resource:           '#64748b',
    blank:              '#374151',
};
const EDGE_COLORS = {
    subClassOf:      '#4fffb0',
    subPropertyOf:   '#7b61ff',
    type:            '#ffd166',
    domain:          '#06b6d4',
    range:           '#ff6b6b',
    equivalentClass: '#ec4899',
    disjointWith:    '#f97316',
    onProperty:      '#84cc16',
    relation:        '#4a5568',
};
const TYPE_LABELS = {
    class:              'Classe',
    objectProperty:     'Propriété objet',
    datatypeProperty:   'Propriété donnée',
    annotationProperty: 'Propriété annotation',
    individual:         'Individu',
    restriction:        'Restriction',
    ontology:           'Ontologie',
    resource:           'Ressource',
};

let graphData = null;
let currentView = 'radial';
let svg, g, zoom;
let simulation;
let activeFilters = new Set(Object.keys(NODE_COLORS));
let showLabels = true;
let showEdgeLabels = false;
let selectedNode = null;


window.addEventListener('DOMContentLoaded', async () => {
    buildLegend();
    const data = await loadGraph();
    if (!data) return;
    graphData = data;
    updateStats(data.stats);
    buildFilters(data);
    renderView('radial');
    hideOverlay();
});

async function loadGraph() {
    try {
        const url = IS_DEMO ? '/api/demo' : `/api/graph?token=${TOKEN}`;
        const res = await fetch(url);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return await res.json();
    } catch (e) {
        showError('Erreur de chargement : ' + e.message);
        return null;
    }
}

function hideOverlay() {
    document.getElementById('canvasOverlay').style.display = 'none';
}
function showError(msg) {
    const ov = document.getElementById('canvasOverlay');
    ov.innerHTML = `<div style="color:var(--accent3);font-family:var(--mono);font-size:.82rem;padding:2rem;text-align:center">❌ ${msg}</div>`;
}


function updateStats(stats) {
    if (!stats) return;
    document.getElementById('statsRow').innerHTML = `
        <div class="stat-chip"><b>${stats.totalNodes}</b> nœuds</div>
        <div class="stat-chip"><b>${stats.totalEdges}</b> arêtes</div>
        ${Object.entries(stats.nodeTypes || {}).map(([t,c]) =>
            `<div class="stat-chip" style="color:${NODE_COLORS[t] || '#64748b'}">${c} ${TYPE_LABELS[t] || t}</div>`
        ).join('')}
    `;
}

function buildFilters(data) {
    const types = [...new Set(data.nodes.map(n => n.type))];
    const container = document.getElementById('typeFilters');
    container.innerHTML = types.map(t => `
        <div class="type-filter">
            <label>
                <input type="checkbox" checked onchange="toggleFilter('${t}', this.checked)">
                <span class="type-dot" style="background:${NODE_COLORS[t] || '#64748b'}"></span>
                ${TYPE_LABELS[t] || t}
            </label>
        </div>
    `).join('');
}

function toggleFilter(type, enabled) {
    if (enabled) activeFilters.add(type);
    else activeFilters.delete(type);
    renderView(currentView, true);
}
function toggleLabels() {
    showLabels = document.getElementById('showLabels').checked;
    renderView(currentView, true);
}
function toggleEdgeLabels() {
    showEdgeLabels = document.getElementById('showEdgeLabels').checked;
    renderView(currentView, true);
}


function switchView(view) {
    if (view === currentView) return;
    currentView = view;
    document.querySelectorAll('.view-btn').forEach(b => b.classList.toggle('active', b.dataset.view === view));
    if (simulation) { simulation.stop(); simulation = null; }
    d3.select('#viz-canvas').selectAll('*').remove();
    renderView(view);
}

function renderView(view, refresh = false) {
    if (!graphData) return;

    if (simulation) { simulation.stop(); simulation = null; }

    const nodes = graphData.nodes.filter(n => activeFilters.has(n.type));
    const nodeIds = new Set(nodes.map(n => n.id));
    const edges = graphData.edges.filter(e => nodeIds.has(e.source) && nodeIds.has(e.target));
    const svgEl = document.getElementById('viz-canvas');
    const W = svgEl.clientWidth || 900;
    const H = svgEl.clientHeight || 600;

    d3.select('#viz-canvas').selectAll('*').remove();

    if (view === 'radial') renderRadial(nodes, edges, W, H);
    else if (view === 'sunburst') renderSunburst(nodes, edges, W, H);
    else if (view === 'tree') renderTree(nodes, edges, W, H);
}


function renderRadial(nodes, edges, W, H) {
    const svgSel = d3.select('#viz-canvas');
    if (!svgSel.select('g.root').empty()) svgSel.select('g.root').remove();

    const simNodes = nodes.map(n => ({ ...n, x: W/2 + (Math.random()-.5)*200, y: H/2 + (Math.random()-.5)*200 }));
    const idxMap = {};
    simNodes.forEach((n,i) => idxMap[n.id] = i);

    const simEdges = edges.map(e => ({
        ...e,
        source: idxMap[e.source] !== undefined ? idxMap[e.source] : 0,
        target: idxMap[e.target] !== undefined ? idxMap[e.target] : 0,
    })).filter(e => e.source !== e.target);

    zoom = d3.zoom().scaleExtent([.05, 8]).on('zoom', e => g.attr('transform', e.transform));
    svgSel.call(zoom);

    g = svgSel.append('g').attr('class', 'root');

    const defs = svgSel.append('defs');
    Object.entries(EDGE_COLORS).forEach(([type, color]) => {
        defs.append('marker')
            .attr('id', `arrow-${type}`)
            .attr('viewBox', '0 -4 10 8')
            .attr('refX', 18).attr('refY', 0)
            .attr('markerWidth', 6).attr('markerHeight', 6)
            .attr('orient', 'auto')
            .append('path')
            .attr('d', 'M0,-4L10,0L0,4')
            .attr('fill', color).attr('opacity', .7);
    });

    const edgeSel = g.append('g').attr('class', 'edges')
        .selectAll('line').data(simEdges).join('line')
        .attr('stroke', d => EDGE_COLORS[d.type] || EDGE_COLORS.relation)
        .attr('stroke-opacity', .5)
        .attr('stroke-width', d => d.type === 'subClassOf' ? 1.5 : 1)
        .attr('stroke-dasharray', d => d.type === 'disjointWith' ? '4,3' : null)
        .attr('marker-end', d => `url(#arrow-${d.type})`);

    const edgeLabelSel = g.append('g').attr('class', 'edge-labels')
        .selectAll('text').data(simEdges).join('text')
        .attr('font-family', "'DM Mono', monospace")
        .attr('font-size', 9)
        .attr('fill', d => EDGE_COLORS[d.type] || '#64748b')
        .attr('opacity', showEdgeLabels ? .7 : 0)
        .attr('text-anchor', 'middle')
        .text(d => d.label);

    const nodeSel = g.append('g').attr('class', 'nodes')
        .selectAll('g').data(simNodes).join('g')
        .attr('class', 'node')
        .style('cursor', 'pointer')
        .call(d3.drag()
            .on('start', (e, d) => { if (!e.active) simulation.alphaTarget(.3).restart(); d.fx=d.x; d.fy=d.y; })
            .on('drag',  (e, d) => { d.fx=e.x; d.fy=e.y; })
            .on('end',   (e, d) => { if (!e.active) simulation.alphaTarget(0); d.fx=null; d.fy=null; })
        )
        .on('click', (e, d) => { e.stopPropagation(); selectNode(d, simEdges, simNodes); });

    nodeSel.append('circle')
        .attr('r', d => nodeRadius(d))
        .attr('fill', d => NODE_COLORS[d.type] || '#64748b')
        .attr('fill-opacity', .85)
        .attr('stroke', d => NODE_COLORS[d.type] || '#64748b')
        .attr('stroke-width', 1.5)
        .attr('stroke-opacity', .4);

    nodeSel.append('text')
        .attr('font-family', "'DM Sans', sans-serif")
        .attr('font-size', d => d.type === 'class' ? 11 : 9)
        .attr('font-weight', d => d.type === 'class' ? '600' : '400')
        .attr('fill', '#e8eaf0')
        .attr('opacity', showLabels ? 1 : 0)
        .attr('dy', d => nodeRadius(d) + 12)
        .attr('text-anchor', 'middle')
        .text(d => truncate(d.label, 18));

    svgSel.on('click', () => deselectNode());

    nodeSel.append('title').text(d => `${d.label}\n${d.uri}\nType: ${TYPE_LABELS[d.type] || d.type}`);

    simulation = d3.forceSimulation(simNodes)
        .force('link', d3.forceLink(simEdges).id((d,i) => i).distance(d => d.type === 'subClassOf' ? 80 : 120).strength(.6))
        .force('charge', d3.forceManyBody().strength(-180))
        .force('center', d3.forceCenter(W/2, H/2))
        .force('collision', d3.forceCollide(d => nodeRadius(d) + 12))
        .force('radial', d3.forceRadial(d => d.type === 'class' ? 0 : 220, W/2, H/2).strength(d => d.type === 'class' ? .15 : 0))
        .on('tick', () => {
            edgeSel
                .attr('x1', d => d.source.x).attr('y1', d => d.source.y)
                .attr('x2', d => d.target.x).attr('y2', d => d.target.y);
            edgeLabelSel
                .attr('x', d => (d.source.x + d.target.x)/2)
                .attr('y', d => (d.source.y + d.target.y)/2)
                .attr('opacity', showEdgeLabels ? .7 : 0);
            nodeSel.attr('transform', d => `translate(${d.x},${d.y})`);
        });
}

function nodeRadius(n) {
    return n.type === 'class' ? 9 : n.type === 'objectProperty' ? 7 : n.type === 'individual' ? 8 : 6;
}

function renderSunburst(nodes, edges, W, H) {
    const svgSel = d3.select('#viz-canvas');

    if (nodes.length === 0) {
        svgSel.append('text').attr('x', W/2).attr('y', H/2)
            .attr('text-anchor', 'middle').attr('fill', '#505870')
            .attr('font-family', "'DM Mono', monospace").attr('font-size', 13)
            .text('Aucun nœud à afficher avec les filtres actuels.');
        return;
    }

    const hierarchyData = buildHierarchy(nodes, edges);
    const root = d3.hierarchy(hierarchyData);

    root.sum(d => (!d.children || d.children.length === 0) ? 1 : 0)
        .sort((a, b) => b.value - a.value);

    if (root.value === 0) {
        root.each(d => { if (!d.children || d.children.length === 0) d.value = 1; });
    }

    const R = Math.min(W, H) / 2 - 30;
    zoom = d3.zoom().scaleExtent([.2, 8]).on('zoom', e => g.attr('transform', e.transform));
    svgSel.call(zoom);
    g = svgSel.append('g').attr('class', 'root').attr('transform', `translate(${W/2},${H/2})`);

    const partition = d3.partition().size([2 * Math.PI, R]);
    partition(root);

    const arc = d3.arc()
        .startAngle(d => d.x0)
        .endAngle(d => d.x1)
        .padAngle(d => Math.min((d.x1 - d.x0) / 2, 0.005))
        .padRadius(R / 3)
        .innerRadius(d => d.y0 + 2)
        .outerRadius(d => Math.max(d.y0 + 2, d.y1 - 1));

    const paths = g.append('g').selectAll('path')
        .data(root.descendants().filter(d => d.depth > 0 && d.x1 > d.x0))
        .join('path')
        .attr('d', arc)
        .attr('fill', d => {
            let anc = d;
            while (anc.depth > 1) anc = anc.parent;
            return NODE_COLORS[anc.data.type] || NODE_COLORS[d.data.type] || '#64748b';
        })
        .attr('fill-opacity', d => Math.max(0.2, 0.9 - d.depth * 0.15))
        .attr('stroke', 'rgba(10,11,15,.6)')
        .attr('stroke-width', .8)
        .style('cursor', 'pointer')
        .on('click', (e, d) => {
            e.stopPropagation();
            if (d.data.id !== undefined && d.data.id !== -1) {
                selectNodeById(d.data.id, nodes, edges);
            }
        })
        .on('mouseover', function(e, d) {
            d3.select(this).attr('fill-opacity', 1);
            showToast(`${d.data.label} — ${TYPE_LABELS[d.data.type] || d.data.type || ''}${d.children ? ' · ' + d.children.length + ' enfants' : ''}`);
        })
        .on('mouseout', function(e, d) {
            d3.select(this).attr('fill-opacity', Math.max(0.2, 0.9 - d.depth * 0.15));
        });

    paths.append('title').text(d => d.ancestors().map(a => a.data.label).reverse().join(' › '));
    if (showLabels) {
        g.append('g').attr('pointer-events', 'none')
            .selectAll('text')
            .data(root.descendants().filter(d => {
                const angle = d.x1 - d.x0;
                const radius = (d.y0 + d.y1) / 2;
                return d.depth > 0 && angle > 0.06 && angle * radius > 14;
            }))
            .join('text')
            .attr('transform', d => {
                const midAngle = (d.x0 + d.x1) / 2;
                const midR     = (d.y0 + d.y1) / 2;
                const deg      = midAngle * 180 / Math.PI - 90;
                const flip     = midAngle > Math.PI ? 180 : 0;
                return `rotate(${deg}) translate(${midR},0) rotate(${flip})`;
            })
            .attr('dy', '.35em')
            .attr('text-anchor', 'middle')
            .attr('fill', '#e8eaf0')
            .attr('font-size', d => d.depth === 1 ? 10 : 9)
            .attr('font-family', "'DM Sans', sans-serif")
            .text(d => {
                const angle  = d.x1 - d.x0;
                const radius = (d.y0 + d.y1) / 2;
                const maxChars = Math.floor(angle * radius / 6);
                return truncate(d.data.label, Math.max(4, maxChars));
            });
    }

    const centerG = g.append('g').attr('pointer-events', 'none');
    centerG.append('text')
        .attr('text-anchor', 'middle').attr('dy', '-0.4em')
        .attr('font-family', "'Syne', sans-serif").attr('font-size', 14).attr('font-weight', 700)
        .attr('fill', '#e8eaf0')
        .text(truncate(hierarchyData.label || 'Ontologie', 20));
    centerG.append('text')
        .attr('text-anchor', 'middle').attr('dy', '1em')
        .attr('font-family', "'DM Mono', monospace").attr('font-size', 10)
        .attr('fill', '#505870')
        .text(`${nodes.length} nœuds`);

    svgSel.on('click', () => deselectNode());
}

function renderTree(nodes, edges, W, H) {
    const svgSel = d3.select('#viz-canvas');

    if (nodes.length === 0) {
        svgSel.append('text').attr('x', W/2).attr('y', H/2)
            .attr('text-anchor', 'middle').attr('fill', '#505870')
            .attr('font-family', "'DM Mono', monospace").attr('font-size', 13)
            .text('Aucun nœud à afficher avec les filtres actuels.');
        return;
    }

    const hierarchyData = buildHierarchy(nodes, edges);

    const root = d3.hierarchy(hierarchyData);

    root.descendants().forEach(d => {
        if (d.depth >= 3 && d.children) {
            d._children = d.children;
            d.children = null;
        }
    });

    zoom = d3.zoom().scaleExtent([.05, 6]).on('zoom', e => g.attr('transform', e.transform));
    svgSel.call(zoom);
    g = svgSel.append('g').attr('class', 'root');

    const treeLayout = d3.tree().nodeSize([20, 200]);
    let firstDraw = true;

    function update() {
        treeLayout(root);
        const allNodes = root.descendants();
        const allLinks = root.links();

        const linkSel = g.selectAll('.tree-link')
            .data(allLinks, d => d.target.data.uri + '_' + d.target.depth);

        const linkEnter = linkSel.enter().append('path')
            .attr('class', 'tree-link')
            .attr('fill', 'none')
            .attr('stroke', d => NODE_COLORS[d.target.data.type] || '#64748b')
            .attr('stroke-opacity', .3)
            .attr('stroke-width', 1)
            .attr('d', d3.linkHorizontal().x(d => d.y).y(d => d.x));

        linkSel.merge(linkEnter)
            .transition().duration(220)
            .attr('stroke', d => NODE_COLORS[d.target.data.type] || '#64748b')
            .attr('d', d3.linkHorizontal().x(d => d.y).y(d => d.x));

        linkSel.exit().remove();

        const nodeSel = g.selectAll('.tree-node')
            .data(allNodes, d => d.data.uri + '_' + d.depth);

        const nodeEnter = nodeSel.enter().append('g')
            .attr('class', 'tree-node')
            .attr('transform', d => `translate(${d.y},${d.x})`)
            .style('cursor', 'pointer')
            .on('click', (e, d) => {
                e.stopPropagation();
                if (d.children) {
                    d._children = d.children;
                    d.children = null;
                } else if (d._children) {
                    d.children = d._children;
                    d._children = null;
                } else {
                    selectNodeById(d.data.id, nodes, edges);
                    return;
                }
                update();
            });

        nodeEnter.append('circle')
            .attr('r', 0) 
            .attr('fill', d => NODE_COLORS[d.data.type] || '#64748b')
            .attr('fill-opacity', .85)
            .attr('stroke', d => NODE_COLORS[d.data.type] || '#64748b')
            .attr('stroke-opacity', .4)
            .attr('stroke-width', 1.5);

        nodeEnter.append('text')
            .attr('font-family', "'DM Sans', sans-serif")
            .attr('font-size', 11)
            .attr('fill', '#e8eaf0')
            .attr('opacity', showLabels ? 1 : 0)
            .attr('dy', '.35em')
            .attr('x', d => (d.children || d._children) ? -10 : 10)
            .attr('text-anchor', d => (d.children || d._children) ? 'end' : 'start')
            .text(d => truncate(d.data.label, 24));

        nodeEnter.append('title').text(d => d.data.uri || d.data.label);

        const nodeAll = nodeSel.merge(nodeEnter);

        nodeAll.transition().duration(220)
            .attr('transform', d => `translate(${d.y},${d.x})`);

        nodeAll.select('circle')
            .transition().duration(220)
            .attr('r', d => (d.children || d._children) ? 7 : 5)
            .attr('stroke-dasharray', d => d._children ? '3,2' : null);

        nodeAll.select('text')
            .attr('opacity', showLabels ? 1 : 0)
            .attr('x', d => (d.children || d._children) ? -10 : 10)
            .attr('text-anchor', d => (d.children || d._children) ? 'end' : 'start')
            .text(d => truncate(d.data.label, 24));

        nodeSel.exit().remove();

        if (firstDraw) {
            firstDraw = false;
            requestAnimationFrame(() => {
                const bounds = g.node().getBBox();
                if (!bounds.width || !bounds.height) return;
                const pad = 60;
                const scale = Math.min(
                    (W - pad * 2) / bounds.width,
                    (H - pad * 2) / bounds.height,
                    2.5
                );
                const tx = (W - bounds.width * scale) / 2 - bounds.x * scale;
                const ty = (H - bounds.height * scale) / 2 - bounds.y * scale;
                svgSel.call(zoom.transform, d3.zoomIdentity.translate(tx, ty).scale(scale));
            });
        }
    }

    update();
}

function buildHierarchy(nodes, edges) {
    const nodeMap = {};
    nodes.forEach(n => { nodeMap[n.id] = { ...n, children: [], _uid: n.uri }; });

    const children   = {};
    const hasParent  = new Set();

    edges.forEach(e => {
        if (e.type !== 'subClassOf') return;
        const src = typeof e.source === 'object' ? e.source.id : e.source;
        const tgt = typeof e.target === 'object' ? e.target.id : e.target;
        if (!nodeMap[src] || !nodeMap[tgt] || src === tgt) return;
        if (!children[tgt]) children[tgt] = [];
        if (!children[tgt].includes(src)) {
            children[tgt].push(src);
            hasParent.add(src);
        }
    });

    if (hasParent.size === 0) {
        edges.forEach(e => {
            if (!['type', 'domain', 'range', 'relation'].includes(e.type)) return;
            const src = typeof e.source === 'object' ? e.source.id : e.source;
            const tgt = typeof e.target === 'object' ? e.target.id : e.target;
            if (!nodeMap[src] || !nodeMap[tgt] || src === tgt) return;
            if (!children[tgt]) children[tgt] = [];
            if (!children[tgt].includes(src)) {
                children[tgt].push(src);
                hasParent.add(src);
            }
        });
    }

    const rootCandidates = nodes.filter(n => !hasParent.has(n.id));

    const rootChildren = rootCandidates.map(n =>
        buildSubTree(n.id, children, nodeMap, new Set())
    );

    return {
        id: -1, label: 'Ontologie', uri: 'root', type: 'ontology',
        _uid: 'root',
        children: rootChildren.length > 0 ? rootChildren : nodes.map(n => ({ ...nodeMap[n.id], children: [] }))
    };
}

function buildSubTree(id, children, nodeMap, visited) {
    if (visited.has(id)) return { ...nodeMap[id], children: [] };
    visited.add(id);
    const node = { ...nodeMap[id], children: [] };
    if (children[id] && children[id].length > 0) {
        node.children = children[id]
            .filter(cid => !visited.has(cid))
            .map(cid => buildSubTree(cid, children, nodeMap, new Set([...visited])));
    }
    return node;
}

function selectNode(d, simEdges, simNodes) {
    selectedNode = d;
    highlightNode(d.id);
    showNodeDetail(d, simEdges.filter(e =>
        (e.source.id ?? e.source) === d.id || (e.target.id ?? e.target) === d.id
    ), simNodes);
}

function selectNodeById(id, nodes, edges) {
    const node = nodes.find(n => n.id === id);
    if (!node) return;
    selectedNode = node;
    const relEdges = edges.filter(e => {
        const s = e.source?.id ?? e.source;
        const t = e.target?.id ?? e.target;
        return s === id || t === id;
    });
    showNodeDetail(node, relEdges, nodes);
}

function deselectNode() {
    selectedNode = null;
    d3.select('#viz-canvas').selectAll('.node circle').attr('stroke-width', 1.5).attr('stroke-opacity', .4);
    document.getElementById('nodeDetail').innerHTML = `
        <div class="node-empty"><div class="empty-icon">◎</div><div>Cliquez sur un nœud<br>pour voir ses détails</div></div>`;
}

function highlightNode(id) {
    d3.select('#viz-canvas').selectAll('.node circle')
        .attr('stroke-width', (d,i) => d.id === id ? 3 : 1.5)
        .attr('stroke-opacity', (d,i) => d.id === id ? 1 : .25)
        .attr('fill-opacity', (d,i) => d.id === id ? 1 : .4);
}

function showNodeDetail(node, relEdges, allNodes) {
    const color = NODE_COLORS[node.type] || '#64748b';
    const label = TYPE_LABELS[node.type] || node.type;
    const props = node.properties || {};

    const nodeMap = {};
    allNodes.forEach(n => nodeMap[n.id] = n);

    const neighbors = relEdges.map(e => {
        const s = e.source?.id ?? e.source;
        const t = e.target?.id ?? e.target;
        const otherId = s === node.id ? t : s;
        const dir = s === node.id ? '→' : '←';
        const other = nodeMap[otherId];
        return other ? { node: other, dir, label: e.label } : null;
    }).filter(Boolean);

    let propsHtml = '';
    if (Object.keys(props).length > 0) {
        propsHtml = `
        <div class="detail-section">
            <div class="detail-section-title">Propriétés</div>
            ${Object.entries(props).map(([k,v]) => `
                <div class="detail-prop">
                    <span class="prop-key">${escHtml(k)}</span>
                    <span class="prop-val">${escHtml(String(v).slice(0,80))}</span>
                </div>`).join('')}
        </div>`;
    }

    let neighborsHtml = '';
    if (neighbors.length > 0) {
        neighborsHtml = `
        <div class="detail-section">
            <div class="detail-section-title">Relations (${neighbors.length})</div>
            ${neighbors.slice(0, 12).map(nb => `
                <div class="neighbor-item" onclick="selectNodeById(${nb.node.id}, graphData.nodes, graphData.edges)">
                    <span class="neighbor-arrow">${nb.dir}</span>
                    <span class="type-dot" style="background:${NODE_COLORS[nb.node.type]||'#64748b'}"></span>
                    <span style="flex:1">${escHtml(nb.node.label)}</span>
                    <span style="color:var(--text3);font-size:.68rem;font-family:var(--mono)">${escHtml(nb.label)}</span>
                </div>`).join('')}
            ${neighbors.length > 12 ? `<div style="font-size:.72rem;color:var(--text3);padding:.25rem 0">+ ${neighbors.length - 12} autres…</div>` : ''}
        </div>`;
    }

    document.getElementById('nodeDetail').innerHTML = `
        <div class="detail-type-badge" style="background:${color}22;color:${color};border:1px solid ${color}44">
            ${escHtml(label)}
        </div>
        <div class="detail-label">${escHtml(node.label)}</div>
        <div class="detail-uri">${escHtml(node.uri)}</div>
        ${node.comment ? `<div class="detail-comment">${escHtml(node.comment)}</div>` : ''}
        ${propsHtml}
        ${neighborsHtml}
    `;
}

function searchNodes(q) {
    if (!graphData || !q.trim()) {
        d3.select('#viz-canvas').selectAll('.node circle, .node text').attr('opacity', 1);
        return;
    }
    const ql = q.toLowerCase();
    d3.select('#viz-canvas').selectAll('.node')
        .each(function(d) {
            const match = d.label.toLowerCase().includes(ql) || d.uri.toLowerCase().includes(ql);
            d3.select(this).selectAll('circle').attr('opacity', match ? 1 : .12);
            d3.select(this).selectAll('text').attr('opacity', match ? 1 : .08);
        });
}

function zoomIn()    { d3.select('#viz-canvas').transition().call(zoom.scaleBy, 1.4); }
function zoomOut()   { d3.select('#viz-canvas').transition().call(zoom.scaleBy, 0.7); }
function resetZoom() { d3.select('#viz-canvas').transition().call(zoom.transform, d3.zoomIdentity); }
function fitGraph()  {
    const bounds = d3.select('#viz-canvas g.root').node()?.getBBox();
    if (!bounds) return;
    const svg = document.getElementById('viz-canvas');
    const W = svg.clientWidth, H = svg.clientHeight;
    const pad = 40;
    const scale = Math.min((W - pad*2) / bounds.width, (H - pad*2) / bounds.height, 4);
    const tx = (W - bounds.width * scale) / 2 - bounds.x * scale;
    const ty = (H - bounds.height * scale) / 2 - bounds.y * scale;
    d3.select('#viz-canvas').transition().duration(500)
        .call(zoom.transform, d3.zoomIdentity.translate(tx, ty).scale(scale));
}

function exportSVG() {
    const svg = document.getElementById('viz-canvas');
    const data = new XMLSerializer().serializeToString(svg);
    const blob = new Blob([data], { type: 'image/svg+xml' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'ontoviz_export.svg';
    a.click();
    showToast('SVG exporté ✓');
}

function buildLegend() {
    const items = Object.entries(NODE_COLORS).map(([t, c]) => `
        <div class="legend-item">
            <div class="legend-dot" style="background:${c}"></div>
            ${TYPE_LABELS[t] || t}
        </div>`).join('');

    const edgeItems = [
        ['subClassOf', 'Sous-classe'],
        ['domain',     'Domaine'],
        ['range',      'Portée'],
        ['type',       'Type'],
        ['relation',   'Relation'],
    ].map(([t, l]) => `
        <div class="legend-item">
            <div class="legend-line" style="background:${EDGE_COLORS[t]}"></div>
            ${l}
        </div>`).join('');

    document.getElementById('legendItems').innerHTML = items + edgeItems;
}

function truncate(s, n) { return s && s.length > n ? s.slice(0, n) + '…' : (s || ''); }
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
let toastTimer;
function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('show'), 2200);
}

new ResizeObserver(() => { if (graphData) renderView(currentView, false); })
    .observe(document.getElementById('viz-canvas'));
</script>
</body>
</html>
