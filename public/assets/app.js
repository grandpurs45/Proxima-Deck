const state = {
  applications: [],
  network: null,
  version: 'dev',
  query: '',
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
    refreshHealth();
    window.setInterval(refreshHealth, 60000);
  } catch (error) {
    renderError(error);
  }
}

async function refreshHealth() {
  try {
    const response = await fetch(apiUrl('/api/health.php'), { headers: { Accept: 'application/json' } });

    if (!response.ok) {
      return;
    }

    const payload = await response.json();
    const statuses = payload.statuses || {};

    state.applications = state.applications.map((application) => ({
      ...application,
      health_status: statuses[application.id] || 'unknown',
    }));
    render();
  } catch {
    // Unknown remains the safe display state when health checks are unavailable.
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
  const isEnabled = state.network?.diagnostic_enabled === true;

  diagnosticLabel.textContent = !isEnabled
    ? 'Desactive en production'
    : isForced
    ? `Mode force: ${selected === 'internal' ? 'LAN' : 'Internet'}`
    : 'Detection automatique';

  for (const action of diagnosticActions) {
    const context = action.dataset.context || 'auto';
    action.classList.toggle('is-active', context === selected);
    action.classList.toggle('is-disabled', !isEnabled && context !== 'auto');
    action.setAttribute('aria-disabled', String(!isEnabled && context !== 'auto'));
  }
}

function apiUrl(path = '/api/apps.php') {
  const selected = diagnosticContext();
  const url = new URL(path, window.location.origin);

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
  const categories = groupByCategory(filtered);

  appCount.textContent = String(filtered.length);
  emptyState.hidden = filtered.length > 0;
  dashboard.innerHTML = '';

  for (const [index, [category, applications]] of categories.entries()) {
    dashboard.appendChild(renderCategory(category, applications, index));
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
    const category = application.category || 'Autres';

    if (!groups.has(category)) {
      groups.set(category, []);
    }

    groups.get(category).push(application);
  }

  return [...groups.entries()].sort(([left], [right]) => left.localeCompare(right));
}

function renderCategory(category, applications, index) {
  const section = document.createElement('section');
  const headingId = `category-${index}`;
  section.className = 'category-section';
  section.setAttribute('aria-labelledby', headingId);
  section.innerHTML = `
    <header class="category-heading">
      <h2 id="${headingId}">${escapeHtml(category)}</h2>
      <span>${applications.length}</span>
    </header>
  `;

  const grid = document.createElement('div');
  grid.className = 'category-grid';

  for (const application of applications) {
    grid.appendChild(renderCard(application));
  }

  section.appendChild(grid);

  return section;
}

function renderCard(application) {
  const card = document.createElement(application.resolved_url ? 'a' : 'article');
  card.className = 'app-card';
  const host = hostLabel(application.resolved_url);
  const health = healthPresentation(application.health_status);
  const description = application.description || host;

  if (application.resolved_url) {
    card.href = application.resolved_url;
    card.target = '_blank';
    card.rel = 'noreferrer';
    card.title = `${application.name} - ${description}`;
  } else {
    card.classList.add('is-disabled');
    card.title = 'URL manquante';
  }

  card.innerHTML = `
    <span class="health-light health-${health.status}" role="img" aria-label="${health.label}" title="${health.label}"></span>
    ${renderIcon(application)}
    <span class="app-name">${escapeHtml(application.name)}</span>
    <span class="app-launch" aria-hidden="true">${application.resolved_url ? '&#8599;' : '!'}</span>
  `;

  return card;
}

function renderIcon(application) {
  const allowedTones = ['cyan', 'green', 'amber', 'rose', 'blue'];
  const tone = allowedTones.includes(application.icon_tone) ? application.icon_tone : 'cyan';
  const initials = escapeHtml(application.icon_initials || 'AP');
  const label = escapeHtml(application.icon_label || 'Icone de l application');
  const image = application.icon_url
    ? `<img src="${escapeHtml(application.icon_url)}" alt="" loading="lazy" onerror="this.parentElement.classList.remove('has-image'); this.remove()">`
    : '';
  const imageClass = application.icon_url ? ' has-image' : '';

  return `
    <span class="app-icon icon-tone-${tone}${imageClass}" title="${label}">
      <span aria-hidden="true">${initials}</span>
      ${image}
    </span>
  `;
}

function healthPresentation(status) {
  return {
    up: { status: 'up', label: 'Service up' },
    down: { status: 'down', label: 'Service down' },
    unknown: { status: 'unknown', label: 'Etat inconnu' },
  }[status] || { status: 'unknown', label: 'Etat inconnu' };
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
