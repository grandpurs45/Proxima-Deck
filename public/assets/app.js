const state = {
  applications: [],
  network: null,
  version: 'dev',
  query: '',
  collapsedCategories: new Set(),
};

const dashboard = document.querySelector('#dashboard');
const emptyState = document.querySelector('#emptyState');
const warningState = document.querySelector('#warningState');
const searchInput = document.querySelector('#searchInput');
const networkLabel = document.querySelector('#networkLabel');
const versionLabel = document.querySelector('#versionLabel');
const appCount = document.querySelector('#appCount');
const diagnosticLabel = document.querySelector('#diagnosticLabel');
const diagnosticActions = document.querySelectorAll('[data-context]');

searchInput.addEventListener('input', (event) => {
  state.query = event.target.value.trim().toLowerCase();
  render();
});

async function boot() {
  try {
    const response = await fetch(apiUrl(), { headers: { Accept: 'application/json' } });
    const payload = await response.json();

    if (!response.ok) {
      throw payload;
    }

    state.applications = payload.applications;
    state.network = payload.network;
    state.version = payload.version || 'dev';
    renderNetwork();
    renderDiagnostic();
    renderWarnings(payload.validation?.warnings || []);
    render();
  } catch (error) {
    renderError(error);
  }
}

function renderNetwork() {
  const scope = state.network?.scope === 'internal' ? 'LAN' : 'Internet';
  const method = methodLabel(state.network?.method || 'unknown');

  networkLabel.textContent = `${scope} - ${method}`;
  versionLabel.textContent = `v${state.version}`;
}

function renderDiagnostic() {
  const selected = diagnosticContext();
  const isForced = state.network?.method === 'diagnostic_query';

  diagnosticLabel.textContent = isForced
    ? `Mode force: ${selected === 'internal' ? 'LAN' : 'Internet'}`
    : 'Detection automatique';

  for (const action of diagnosticActions) {
    const context = action.dataset.context || 'auto';
    action.classList.toggle('is-active', context === selected);
  }
}

function apiUrl() {
  const selected = diagnosticContext();
  const url = new URL('/api/apps.php', window.location.origin);

  if (selected !== 'auto') {
    url.searchParams.set('context', selected);
  }

  return url.toString();
}

function diagnosticContext() {
  const context = new URLSearchParams(window.location.search).get('context');

  return ['internal', 'external'].includes(context) ? context : 'auto';
}

function methodLabel(method) {
  return {
    diagnostic_query: 'diagnostic',
    environment: 'env',
    reverse_proxy: 'proxy',
    private_ip: 'auto IP',
  }[method] || method;
}

function render() {
  const filtered = state.applications.filter((application) => {
    const haystack = [
      application.name,
      application.description,
      application.category,
      application.visibility,
    ].join(' ').toLowerCase();

    return haystack.includes(state.query);
  });

  const groups = groupByCategory(filtered);

  appCount.textContent = String(filtered.length);
  emptyState.hidden = filtered.length > 0;
  dashboard.innerHTML = '';

  for (const [category, applications] of groups) {
    dashboard.appendChild(renderCategory(category, applications));
  }
}

function renderWarnings(issues) {
  const warnings = issues.filter((issue) => issue.level === 'warning');

  if (warnings.length === 0) {
    warningState.hidden = true;
    warningState.innerHTML = '';
    return;
  }

  warningState.hidden = false;
  warningState.innerHTML = `
    <h2>Configuration a verifier</h2>
    <ul>
      ${warnings.map(renderIssue).join('')}
    </ul>
  `;
}

function renderError(error) {
  const message = error?.message || 'Configuration indisponible';
  const issues = Array.isArray(error?.issues) ? error.issues : [];

  emptyState.hidden = true;
  warningState.hidden = true;
  dashboard.innerHTML = `
    <article class="notice notice-error">
      <h2>Configuration invalide</h2>
      <p>${escapeHtml(message)}</p>
      ${issues.length > 0 ? `<ul>${issues.map(renderIssue).join('')}</ul>` : ''}
    </article>
  `;
}

function renderIssue(issue) {
  const app = issue.application_id ? ` - ${issue.application_id}` : '';
  const field = issue.field ? ` - ${issue.field}` : '';

  return `
    <li>
      <strong>${escapeHtml(issue.code || issue.level || 'issue')}</strong>
      <span>${escapeHtml(`${app}${field}`)}</span>
      <p>${escapeHtml(issue.message || '')}</p>
    </li>
  `;
}

function groupByCategory(applications) {
  const groups = new Map();

  for (const application of applications) {
    if (!groups.has(application.category)) {
      groups.set(application.category, []);
    }

    groups.get(application.category).push(application);
  }

  return [...groups.entries()].sort(([left], [right]) => left.localeCompare(right));
}

function renderCategory(category, applications) {
  const section = document.createElement('section');
  const isCollapsed = state.collapsedCategories.has(category);
  section.className = 'category';

  const header = document.createElement('button');
  header.className = 'category-header';
  header.type = 'button';
  header.setAttribute('aria-expanded', String(!isCollapsed));
  header.innerHTML = `
    <span>${escapeHtml(category)}</span>
    <small>${applications.length}</small>
  `;
  header.addEventListener('click', () => {
    if (state.collapsedCategories.has(category)) {
      state.collapsedCategories.delete(category);
    } else {
      state.collapsedCategories.add(category);
    }

    render();
  });

  const grid = document.createElement('div');
  grid.className = 'app-grid';
  grid.hidden = isCollapsed;

  for (const application of applications) {
    grid.appendChild(renderCard(application));
  }

  section.append(header, grid);

  return section;
}

function renderCard(application) {
  const card = document.createElement(application.resolved_url ? 'a' : 'article');
  card.className = `app-card visibility-${application.visibility}`;
  const targetLabel = targetLabelFor(application.resolved_target);
  const visibilityLabel = visibilityLabelFor(application.visibility);
  const host = hostLabel(application.resolved_url);

  if (application.resolved_url) {
    card.href = application.resolved_url;
    card.target = '_blank';
    card.rel = 'noreferrer';
  } else {
    card.classList.add('is-disabled');
  }

  card.innerHTML = `
    <div class="card-topline">
      <span class="icon-frame" title="${escapeHtml(application.icon_label || 'Icone')}">
        <img src="/assets/icons/${encodeURIComponent(application.icon)}" alt="" loading="lazy" onerror="this.src='/assets/icons/default.svg'">
      </span>
      <span class="chip chip-${escapeHtml(application.resolved_target)}">${escapeHtml(targetLabel)}</span>
    </div>
    <h2>${escapeHtml(application.name)}</h2>
    <p>${escapeHtml(application.description || 'Aucune description')}</p>
    <div class="endpoint">
      <span>Endpoint</span>
      <strong>${escapeHtml(host)}</strong>
    </div>
    <div class="card-footer">
      <span>${escapeHtml(visibilityLabel)}</span>
      <span>${application.resolved_url ? 'Ouvrir ->' : 'URL manquante'}</span>
    </div>
  `;

  return card;
}

function targetLabelFor(target) {
  return {
    internal: 'LAN',
    external: 'WEB',
    fallback: 'Fallback',
  }[target] || 'N/A';
}

function visibilityLabelFor(visibility) {
  return {
    internal: 'LAN uniquement',
    external: 'Web uniquement',
    both: 'LAN + Web',
  }[visibility] || visibility;
}

function hostLabel(url) {
  if (!url) {
    return 'Non configure';
  }

  try {
    const parsed = new URL(url);

    return parsed.host;
  } catch {
    return url;
  }
}

function escapeHtml(value) {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

boot();
