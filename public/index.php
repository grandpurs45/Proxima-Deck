<?php
$versionPath = dirname(__DIR__) . '/VERSION';
$version = is_file($versionPath) ? trim((string) file_get_contents($versionPath)) : 'dev';
$assetVersion = rawurlencode($version);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ProximaDeck</title>
    <meta name="description" content="Mission Control leger et auto-heberge pour homelab.">
    <link rel="stylesheet" href="/assets/styles.css?v=<?= $assetVersion ?>">
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
                <span id="versionLabel" class="version-badge">v<?= htmlspecialchars($version, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </header>

        <section class="command-row" aria-label="Recherche et etat">
            <label class="search-box">
                <span class="search-icon" aria-hidden="true">?</span>
                <input id="searchInput" type="search" placeholder="Rechercher une application" autocomplete="off">
            </label>
            <div class="metric">
                <span id="appCount">0</span>
                <small>apps visibles</small>
            </div>
        </section>

        <section class="diagnostic-panel" aria-label="Diagnostic reseau">
            <div>
                <strong>Diagnostic reseau</strong>
                <span id="diagnosticLabel">Detection automatique</span>
            </div>
            <nav class="diagnostic-actions" aria-label="Choix du contexte reseau">
                <a href="/" data-context="auto">Auto</a>
                <a href="/?context=internal" data-context="internal">LAN</a>
                <a href="/?context=external" data-context="external">Internet</a>
            </nav>
        </section>

        <section id="warningState" class="warning-state" hidden></section>
        <section id="dashboard" class="dashboard" aria-live="polite"></section>
        <section id="emptyState" class="empty-state" hidden>
            <h2>Aucune application</h2>
            <p>Aucune tuile ne correspond au filtre actuel ou au contexte reseau detecte.</p>
        </section>
    </main>

    <script src="/assets/app.js?v=<?= $assetVersion ?>" defer></script>
</body>
</html>
