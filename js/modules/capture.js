/**
 * Path: /ptwo/js/modules/capture.js
 */
export function initCapture() {
    const searchBtn = document.querySelector('#lake-search-btn');
    const searchInput = document.querySelector('#lake-search-input');
    const resultsContainer = document.querySelector('#lake-results');

    if (searchBtn && !searchBtn.getAttribute('data-bound')) {
        searchBtn.addEventListener('click', () => searchLake(searchInput.value));
        searchBtn.setAttribute('data-bound', 'true');
    }

    // Event Delegation for "Promote" and "History" buttons
    resultsContainer?.addEventListener('click', (e) => {
        const promoteBtn = e.target.closest('[data-action="promote"]');
        const historyBtn = e.target.closest('[data-action="history"]');

        if (promoteBtn) promoteRecord(promoteBtn.dataset.id);
        if (historyBtn) viewHistory(historyBtn.dataset.id);
    });
}

async function searchLake(query) {
    const container = document.querySelector('#lake-results');
    if (!query || query.length < 3) return;

    container.innerHTML = '<div class="loader">Scanning Research Lake...</div>';

    try {
        const response = await fetch(`api/capture/search_lake.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        renderLakeResults(data);
    } catch (err) {
        container.innerHTML = '<div class="error">Search failed.</div>';
    }
}

function renderLakeResults(results) {
    const container = document.querySelector('#lake-results');
    const isEnterprise = window.userSession.plan === 'enterprise';

    if (!results || results.length === 0) {
        container.innerHTML = '<p>No records found.</p>';
        return;
    }

    container.innerHTML = results.map(row => `
        <div class="lake-card">
            <div class="lake-card-header">
                <strong>${row.solicitation_number || 'N/A'}</strong>
                <span class="agency-tag">${row.agency_name}</span>
            </div>
            <h4>${row.title}</h4>
            <div class="lake-card-actions">
                <button class="btn btn-sm" data-action="promote" data-id="${row.id}">
                    Promote to Pipeline
                </button>
                <button class="btn btn-sm ${isEnterprise ? 'btn-outline' : 'btn-disabled'}" 
                        data-action="history" data-id="${row.id}">
                    View History ${isEnterprise ? '' : '🔒'}
                </button>
            </div>
        </div>
    `).join('');
}

async function promoteRecord(lakeId) {
    if (!confirm("Promote this record to your private pipeline?")) return;

    const payload = new FormData(); // Transport Standard
    payload.append('lake_id', lakeId);

    try {
        const response = await fetch('api/capture/promote.php', { method: 'POST', body: payload });
        const result = await response.json();
        alert(result.message);
        if (result.success) {
            const { initPipeline } = await import('./pipeline.js');
            initPipeline(); // Refresh the pipeline list immediately
        }
    } catch (err) {
        console.error("Promotion Error:", err);
    }
}

function viewHistory(lakeId) {
    if (window.userSession.plan !== 'enterprise') {
        alert('Upgrade to Enterprise to see history!');
        return;
    }
    console.log("Fetching history for:", lakeId);
}