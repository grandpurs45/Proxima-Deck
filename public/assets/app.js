const state = {
  applications: [],
  network: null,
  version: 'dev',
  query: '',
  activeCategory: 'all',
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
const categoryFilters = document.querySelector('#categoryFilters');

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
  const categories = categoryList(state.applications);

  if (state.activeCategory !== 'all' && !categories.includes(state.activeCategory)) {
    state.activeCategory = 'all';
  }

  renderCategoryFilters(categories);

  const filtered = state.applications.filter((application) => {
    const haystack = [
      application.name,
      application.description,
      application.category,
      application.visibility,
    ].join(' ').toLowerCase();

    const matchesQuery = haystack.includes(state.query);
    const matchesCategory = state.activeCategory === 'all'
      || application.category === state.activeCategory;

    return matchesQuery && matchesCategory;
  });

  appCount.textContent = String(filtered.length);
  emptyState.hidden = filtered.length > 0;
  dashboard.innerHTML = '';

  for (const application of filtered) {
    dashboard.appendChild(renderCard(application));
  }
}

function renderCategoryFilters(categories) {
  categoryFilters.hidden = categories.length < 2;
  categoryFilters.innerHTML = '';

  if (categories.length < 2) {
    return;
  }

  const filters = ['all', ...categories];

  for (const category of filters) {
    const button = document.createElement('button');
    const count = category === 'all'
      ? state.applications.length
      : state.applications.filter((application) => application.category === category).length;

    button.type = 'button';
    button.className = 'category-filter';
    button.classList.toggle('is-active', category === state.activeCategory);
    button.setAttribute('aria-pressed', String(category === state.activeCategory));
    button.innerHTML = `
      <span>${escapeHtml(category === 'all' ? 'Toutes' : category)}</span>
      <small>${count}</small>
    `;
    button.addEventListener('click', () => {
      state.activeCategory = category;
      render();
    });

    categoryFilters.appendChild(button);
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

function categoryList(applications) {
  return [...new Set(applications.map((application) => application.category))]
    .sort((left, right) => left.localeCompare(right));
}

function renderCard(application) {
  const card = document.createElement(application.resolved_url ? 'a' : 'article');
  card.className = `app-card visibility-${application.visibility}`;
  const targetLabel = targetLabelFor(application.resolved_target);
  const visibilityLabel = visibilityLabelFor(application.visibility);
  const host = hostLabel(application.resolved_url);
  const icon = renderIcon(application);

  if (application.resolved_url) {
    card.href = application.resolved_url;
    card.target = '_blank';
    card.rel = 'noreferrer';
  } else {
    card.classList.add('is-disabled');
  }

  card.innerHTML = `
    <div class="card-identity">
      ${icon}
      <div class="card-title">
        <h2>${escapeHtml(application.name)}</h2>
        <p>${escapeHtml(application.category)} - ${escapeHtml(visibilityLabel)}</p>
      </div>
      <span class="chip chip-${escapeHtml(application.resolved_target)}">${escapeHtml(targetLabel)}</span>
    </div>
    <p class="card-description">${escapeHtml(application.description || 'Aucune description')}</p>
    <div class="card-endpoint">
      <strong>${escapeHtml(host)}</strong>
      <span${application.resolved_url ? ' aria-hidden="true"' : ''}>${application.resolved_url ? '&#8599;' : 'URL manquante'}</span>
    </div>
  `;

  return card;
}

function renderIcon(application) {
  const allowedTones = ['cyan', 'green', 'amber', 'rose', 'blue'];
  const tone = allowedTones.includes(application.icon_tone) ? application.icon_tone : 'cyan';
  const initials = escapeHtml(application.icon_initials || 'AP');
  const image = application.icon
    ? `<img src="/assets/icons/${encodeURIComponent(application.icon)}" alt="" loading="lazy" onerror="this.remove()">`
    : '';

  return `
    <span class="icon-frame icon-tone-${tone}" title="${escapeHtml(application.icon_label || 'Monogramme automatique')}">
      <span class="icon-monogram" aria-hidden="true">${initials}</span>
      ${image}
    </span>
  `;
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
