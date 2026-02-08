/**
 * Path: /ptwo/js/app.js
 * Finalized Core Bootstrapper
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Theme from storage
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.setAttribute('data-theme', savedTheme);

    // 2. Global Event Delegation
    document.addEventListener('click', (e) => {
        // Handle Routing (Look for data-route attribute)
        const routeTarget = e.target.closest('[data-route]');
        if (routeTarget) {
            route(routeTarget.getAttribute('data-route'));
            return;
        }

        // Handle Theme Toggle
        if (e.target.id === 'theme-toggle') {
            toggleTheme();
        }
    });

    // 3. Setup Login Listener using FormData
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await handleLogin(loginForm);
        });
    }

    // 4. App Initialization
    if (window.userSession) {
        window.userPerms = window.userSession.perms;
        window.isAdmin = window.userSession.isAdmin;
        
        const initialView = window.location.hash.replace('#', '') || 'capture';
        route(initialView);
    }
});

/**
 * Global Router with Lazy-Loading
 */
export async function route(viewId) {
    if (viewId === 'admin' && !window.isAdmin) {
        alert("Access Denied.");
        return;
    }

    // Update UI active states
    document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
    const link = document.getElementById('nav-' + viewId);
    if(link) link.classList.add('active');

    // Toggle View Visibility
    document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
    const target = document.getElementById('view-' + viewId);
    if(target) target.classList.remove('hidden');

    window.location.hash = viewId;

    // Lazy Loading Modules
    try {
        if (viewId === 'capture') {
            const { initCapture } = await import('./modules/capture.js');
            initCapture();
            const { initPipeline } = await import('./modules/pipeline.js');
            initPipeline();
        } else if (viewId === 'proposal') {
             // Future module for Proposal Automation
        }
    } catch (err) {
        console.error(`Failed to load module: ${viewId}`, err);
    }
}

/**
 * Modern Login Handler using FormData
 */
async function handleLogin(formElement) {
    const formData = new FormData(formElement);

    try {
        const response = await fetch('src/auth.php', {
            method: 'POST',
            body: formData // No manual JSON.stringify or headers required
        });

        const result = await response.json();
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message);
        }
    } catch (err) {
        alert("Server connection failed.");
    }
}

function toggleTheme() {
    const theme = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    document.body.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
}
