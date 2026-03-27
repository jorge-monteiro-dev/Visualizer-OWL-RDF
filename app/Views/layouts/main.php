<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Visualizer' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@300;400;500&family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:        #0a0b0f;
            --bg2:       #11131a;
            --bg3:       #181c26;
            --surface:   #1e2330;
            --border:    #2a3045;
            --accent:    #4fffb0;
            --accent2:   #7b61ff;
            --accent3:   #ff6b6b;
            --accent4:   #ffd166;
            --text:      #e8eaf0;
            --text2:     #8b93a8;
            --text3:     #505870;
            --radius:    8px;
            --font-mono: 'DM Mono', monospace;
            --font-ui:   'DM Sans', sans-serif;
            --font-head: 'Syne', sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 15px; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--font-ui);
            min-height: 100vh;
            overflow-x: hidden;
        }
        a { color: var(--accent); text-decoration: none; }
        a:hover { text-decoration: underline; }
        ::selection { background: var(--accent2); color: #fff; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg2); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
    </style>
    <?= $extraHead ?? '' ?>
</head>
<body>
    <?= $content ?? '' ?>
    <?= $extraScripts ?? '' ?>
</body>
</html>
