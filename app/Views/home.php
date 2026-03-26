<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>visualizer - OWL & RDF</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@300;400;500&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<style>

:root {
    --bg:#0a0b0f;
    --bg2:#11131a;
    --bg3:#181c26;
    --surface:#1e2330;
    --border:#2a3045;
    --accent:#4fffb0;
    --accent2:#7b61ff;
    --accent3:#ff6b6b;
    --accent4:#ffd166;
    --text:#e8eaf0;
    --text2:#8b93a8;
    --text3:#505870;
    --radius:8px;
    --mono:'DM Mono',monospace;
    --ui:'DM Sans',sans-serif;
    --head:'Syne',sans-serif;
}

*,*::before,*::after{
    box-sizing:border-box;
    margin:0;padding:0;
}

html{
    font-size:15px;
}

body{
    background:var(--bg);
    color:var(--text);
    font-family:var(--ui);
    min-height:100vh;
    display:grid;grid-template-rows:auto 1fr;
    overflow-x:hidden;
}

body::before{
    content:'';
    position:fixed;
    inset:0;
    pointer-events:none;
    z-index:0;
    background:radial-gradient(ellipse 55% 40% at 15% 25%,rgba(79,255,176,.055) 0%,transparent 60%),
               radial-gradient(ellipse 45% 50% at 85% 70%,rgba(123,97,255,.07) 0%,transparent 60%);
}

::selection{
    background:var(--accent2);
    color:#fff;
}

::-webkit-scrollbar{
    width:5px;
}

::-webkit-scrollbar-track{
    background:var(--bg2);
}

::-webkit-scrollbar-thumb{
    background:var(--border);
    border-radius:3px;
}

.topbar{
    position:relative;
    z-index:10;
    padding:.9rem 1.5rem;
    border-bottom:1px solid var(--border);
    background:rgba(10,11,15,.85);
    backdrop-filter:blur(12px);
    display:flex;
    align-items:center;
    gap:.75rem;
}

.logo{
    font-family:var(--head);
    font-size:1.25rem;
    font-weight:800;
    letter-spacing:-.02em;
    color:var(--text);
    display:flex;
    align-items:center;
    gap:.4rem;
}

.logo-accent{
    color:var(--accent);
}

.logo-badge{
    font-family:var(--mono);
    font-size:.62rem;
    background:var(--surface);
    border:1px solid var(--border);
    color:var(--text3);
    padding:.15rem .5rem;
    border-radius:4px;
    letter-spacing:.05em;
}

main{
    position:relative;
    z-index:1;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    padding:3rem 1.5rem 4rem;
    gap:2.5rem;
}

.hero{
    text-align:center;
    max-width:600px;
}

.hero-title{
    font-family:var(--head);
    font-size:clamp(1.9rem,5vw,3rem);
    font-weight:800;
    line-height:1.08;
    letter-spacing:-.03em;
    margin-bottom:.5rem;
}

.hero-title span{
    color:var(--accent);
}

.upload-card{
    width:100%;
    max-width:520px;
    background:var(--bg2);
    border:1px solid var(--border);
    border-radius:14px;
    padding:2rem;
    display:flex;
    flex-direction:column;
    gap:1.25rem;
}

.drop-zone{
    border:2px dashed var(--border);
    border-radius:10px;
    padding:2.5rem 1.5rem;
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:.85rem;
    cursor:pointer;
    position:relative;
    overflow:hidden;
    transition:border-color .2s,background .2s;
}

.drop-zone:hover,.drop-zone.drag-over{
    border-color:var(--accent);
    background:rgba(79,255,176,.035);
}

.drop-zone input[type=file]{
    position:absolute;
    inset:0;
    opacity:0;
    cursor:pointer;
    width:100%;
    height:100%;
}

.drop-icon{
    width:52px;
    height:52px;
    background:var(--surface);
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:1.6rem;
    transition:transform .2s;
}

.drop-zone:hover .drop-icon{
    transform:scale(1.08);
}

.drop-title{
    font-family:var(--head);
    font-size:.95rem;
    font-weight:700;
    color:var(--text);
    text-align:center;
}

.drop-hint{
    font-family:var(--mono);
    font-size:.68rem;
    color:var(--text3);
    letter-spacing:.05em;
    text-align:center;
}

.fmt-row{
    display:flex;
    gap:.4rem;
    flex-wrap:wrap;
    justify-content:center;
}

.fmt-badge{
    font-family:var(--mono);
    font-size:.65rem;
    padding:.15rem .55rem;
    border-radius:4px;
    letter-spacing:.04em;
    font-weight:500;
}

.fmt-owl{
    background:rgba(79,255,176,.12);
    color:var(--accent);
    border:1px solid rgba(79,255,176,.25);
}

.fmt-rdf{
    background:rgba(123,97,255,.12);
    color:var(--accent2);
    border:1px solid rgba(123,97,255,.25);
}

.fmt-xml{
    background:rgba(255,209,102,.12);
    color:var(--accent4);
    border:1px solid rgba(255,209,102,.25);
}

.fmt-json{
    background:rgba(255,107,107,.12);
    color:var(--accent3);
    border:1px solid rgba(255,107,107,.25);
}

.progress-wrap{
    height:3px;
    background:var(--surface);
    border-radius:2px;
    overflow:hidden;
    display:none;
}

.progress-fill{
    height:100%;
    width:0%;
    border-radius:2px;
    background:linear-gradient(90deg,var(--accent),var(--accent2));
    transition:width .3s ease;
}

.status{
    font-family:var(--mono);
    font-size:.75rem;
    color:var(--text2);
    min-height:1.3em;
    text-align:center;
}

.status.error{
    color:var(--accent3)}.status.success{color:var(--accent);
}

.divider{
    display:flex;
    align-items:center;
    gap:.75rem;
    font-family:var(--mono);
    font-size:.65rem;
    color:var(--text3);
    letter-spacing:.08em;
    text-transform:uppercase;
}

.divider::before,.divider::after{
    content:'';
    flex:1;
    height:1px;
    background:var(--border);
}

.demo-btn{
    width:100%;
    font-family:var(--head);
    font-size:.85rem;
    font-weight:600;
    color:var(--text2);
    background:transparent;
    border:1px solid var(--border);
    padding:.7rem 1.25rem;
    border-radius:var(--radius);
    cursor:pointer;
    transition:color .2s,border-color .2s,background .2s;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:.5rem;
    letter-spacing:.02em;
}

.demo-btn:hover{
    color:var(--accent2);
    border-color:var(--accent2);
    background:rgba(123,97,255,.06);
}

</style>
</head>


<body>

<header class="topbar">
    <div class="logo">visualizer<span class="logo-accent">OWL & RDF</span></div>
</header>

<main>
    <div class="hero">
        <h1 class="hero-title">Explorez vos<br>ontologies <span>OWL &amp; RDF</span></h1>
    </div>
    <div class="upload-card">
        <div class="drop-zone" id="dropZone">
            <input type="file" id="fileInput" accept=".owl,.rdf,.xml,.jsonld,.json">
            <div class="drop-icon">📂</div>
            <div class="drop-title">Glissez votre fichier ici<br>ou cliquez pour parcourir</div>
            <div class="fmt-row">
                <span class="fmt-badge fmt-owl">.owl</span>
                <span class="fmt-badge fmt-rdf">.rdf</span>
                <span class="fmt-badge fmt-xml">.xml</span>
                <span class="fmt-badge fmt-json">.jsonld</span>
            </div>
            <div class="drop-hint">OWL/XML · RDF/XML · JSON-LD — max 10 Mo</div>
        </div>
        <div class="progress-wrap" id="progressWrap">
            <div class="progress-fill" id="progressFill"></div>
        </div>
        <div class="status" id="statusMsg"></div>
        <div class="divider">ou</div>
        <button class="demo-btn" id="demoBtn">Essayer avec notre Pizza-Ontology 🍕</button>
    </div>
</main>


<script>
const dropZone  = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const progressW = document.getElementById('progressWrap');
const progressF = document.getElementById('progressFill');
const statusMsg = document.getElementById('statusMsg');

dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault(); dropZone.classList.remove('drag-over');
    if (e.dataTransfer.files.length > 0) uploadFile(e.dataTransfer.files[0]);
});
fileInput.addEventListener('change', () => { if (fileInput.files.length > 0) uploadFile(fileInput.files[0]); });

function setStatus(msg, type = '') { statusMsg.textContent = msg; statusMsg.className = 'status ' + type; }

async function uploadFile(file) {
    setStatus('Analyse du fichier\u2026'); progressW.style.display = 'block'; progressF.style.width = '10%';
    const fd = new FormData(); fd.append('ontology', file);
    try {
        progressF.style.width = '40%';
        const res  = await fetch('/upload', { method: 'POST', body: fd });
        progressF.style.width = '75%';
        const data = await res.json();
        if (data.error) {
            setStatus('\u274c ' + data.error, 'error');
            progressF.style.width = '0%';
            setTimeout(() => { progressW.style.display = 'none'; }, 500);
            return;
        }
        progressF.style.width = '100%';
        setStatus('\u2705 ' + data.stats.totalNodes + ' n\u0153uds, ' + data.stats.totalEdges + ' ar\u00eates \u2014 redirection\u2026', 'success');
        setTimeout(() => {
            window.location.href = '/visualize?token=' + data.token + '&filename=' + encodeURIComponent(file.name);
        }, 600);
    } catch (err) {
        setStatus('\u274c Erreur r\u00e9seau\u00a0: ' + err.message, 'error');
        progressW.style.display = 'none';
    }
}
document.getElementById('demoBtn').addEventListener('click', () => {
    setStatus('Chargement de la d\u00e9mo\u2026');
    window.location.href = '/visualize?token=demo&filename=pizza.owl';
});
</script>
</body>
</html>
