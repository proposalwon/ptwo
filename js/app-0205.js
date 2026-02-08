/**
 * Path: /ptwo/js/app.js
 * Core Bootstrapper - ES Module version
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.setAttribute('data-theme', savedTheme);

    // 2. Setup Login Listener
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await handleLogin();
        });
    }

    // 3. App Initialization
    if (window.userSession) {
        window.userPerms = window.userSession.perms;
        window.isAdmin = window.userSession.isAdmin;
        
        // Load the initial view
        const initialView = window.location.hash.replace('#', '') || 'capture';
        route(initialView);
    }
});

/**
 * Global Router with Lazy-Loading
 */
export async function route(viewId) {
    // Admin Guard
    if (viewId === 'admin' && !window.isAdmin) {
        alert("Access Denied.");
        return;
    }

    // Update UI Links
    document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
    const link = document.getElementById('nav-' + viewId);
    if(link) link.classList.add('active');

    // Toggle View Visibility
    document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
    const target = document.getElementById('view-' + viewId);
    if(target) target.classList.remove('hidden');

    // Update URL Hash
    window.location.hash = viewId;

    // --- LAZY LOADING MODULES ---
    try {
        if (viewId === 'capture') {
            const { initCapture } = await import('./modules/capture.js');
            initCapture();
        } else if (viewId === 'admin') {
            const { initAdmin } = await import('./modules/admin.js');
            initAdmin();
        }
    } catch (err) {
        console.error(`Failed to load module: ${viewId}`, err);
    }
}

/**
 * Make route available globally for the onclick attributes in index.php
 */
window.route = route;

async function handleLogin() {
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-pass').value;

    try {
        const response = await fetch('src/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
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

// Global UI helpers
window.toggleTheme = () => {
    const theme = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    document.body.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
};