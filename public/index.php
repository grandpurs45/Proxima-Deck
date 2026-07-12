<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ProximaDeck</title>
    <meta name="description" content="Mission Control leger et auto-heberge pour homelab.">
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
    <main class="shell">
        <header class="topbar">
            <div>
                <p class="eyebrow">Mission Control</p>
                <h1>ProximaDeck</h1>
            </div>
            <div class="status-panel" aria-live="polite">
                <span class="pulse" aria-hidden="true"></span>
                <span id="networkLabel">Synchronisation</span>
            </div>
        </header>

        <section class="command-row" aria-label="Recherche et etat">
            <label class="search-box">
                <span class="search-icon" aria-hidden="true">⌕</span>
                <input id="searchInput" type="search" placeholder="Rechercher une application" autocomplete="off">
            </label>
            <div class="metric">
                <span id="appCount">0</span>
                <small>apps visibles</small>
            </div>
        </section>

        <section id="dashboard" class="dashboard" aria-live="polite"></section>
        <section id="emptyState" class="empty-state" hidden>
            <h2>Aucune application</h2>
            <p>Aucune tuile ne correspond au filtre actuel ou au contexte reseau detecte.</p>
        </section>
    </main>

    <script src="/assets/app.js" defer></script>
</body>
</html>
