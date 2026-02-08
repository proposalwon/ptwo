/**
 * Path: /ptwo/js/modules/pipeline.js
 */
export async function initPipeline() {
    const container = document.querySelector('#pipeline-table');
    if (!container || container.getAttribute('data-bound')) return;

    // Centralized Event Delegation
    container.addEventListener('change', (e) => {
        if (e.target.matches('.status-select')) handleStatusChange(e);
    });

    container.addEventListener('click', (e) => {
        const deleteBtn = e.target.closest('[data-action="delete"]');
        if (deleteBtn) handleDelete(deleteBtn.dataset.id);
        
        // Proposal App Trigger: Handled by app.js router via data-route
    });

    container.setAttribute('data-bound', 'true');
    await loadPipeline();
}

async function loadPipeline() {
    const container = document.querySelector('#pipeline-table');
    try {
        const response = await fetch('api/pipeline/get_opportunities.php');
        const result = await response.json();
        if (result.success) renderPipeline(result.data);
    } catch (err) {
        container.innerHTML = '<p class="error">Failed to load pipeline.</p>';
    }
}

function renderPipeline(items) {
    const container = document.querySelector('#pipeline-table');
    if (items.length === 0) {
        container.innerHTML = '<p style="color: #888;">Your pipeline is empty.</p>';
        return;
    }

    container.innerHTML = items.map(item => `
        <div class="pipeline-card" data-id="${item.id}">
            <div class="flex-between">
                <div>
                    <small style="color: var(--accent-gold);">${item.solicitation_number}</small>
                    <h4>${item.title}</h4>
                </div>
                <select class="status-select">
                    ${['Qualified', 'Proposal', 'Won', 'Lost'].map(s => 
                        `<option value="${s}" ${item.status === s ? 'selected' : ''}>${s}</option>`
                    ).join('')}
                </select>
            </div>
            <div style="margin-top:10px; display:flex; gap:10px;">
                <button class="btn-sm" data-route="proposal" data-id="${item.id}">Open Proposal</button>
                <button class="btn-sm btn-outline" data-action="delete" data-id="${item.id}">Remove</button>
            </div>
        </div>
    `).join('');
}

async function handleStatusChange(e) {
    const id = e.target.closest('.pipeline-card').dataset.id;
    const payload = new FormData(); // Transport Standard
    payload.append('id', id);
    payload.append('status', e.target.value);

    const response = await fetch('api/pipeline/update_status.php', { method: 'POST', body: payload });
    const result = await response.json();
    if (!result.success) alert(result.message);
}

async function handleDelete(id) {
    if (!confirm("Remove this from your pipeline?")) return;
    const payload = new FormData();
    payload.append('id', id);

    const response = await fetch('api/pipeline/delete_opportunity.php', { method: 'POST', body: payload });
    const result = await response.json();
    if (result.success) loadPipeline();
}