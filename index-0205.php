<?php
/**
 * Path: /ptwo/index.php
 */
require_once 'src/db.php';

$isLoggedIn = isset($_SESSION['user_id']);
// Real-world check: Does this user have platform-level access?
$isPlatformAdmin = (isset($_SESSION['perms']['platform_admin']) && $_SESSION['perms']['platform_admin'] == 1);

$sessionData = $isLoggedIn ? [
    'userName' => $_SESSION['user_name'],
    'firstName' => $_SESSION['first_name'],
    'lastName' => $_SESSION['last_name'],
    'company'  => $_SESSION['company'],
    'perms'    => $_SESSION['perms'],
    'isAdmin'  => $isPlatformAdmin
] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProposalWon | Human Intelligence</title>
    <link rel="stylesheet" href="css/ptwo.css">
    <script>window.userSession = <?php echo json_encode($sessionData); ?>;</script>
</head>
<body class="<?php echo $isLoggedIn ? 'app-active' : 'login-active'; ?>">

    <div id="login-page" class="container <?php echo $isLoggedIn ? 'hidden' : ''; ?>" style="margin-top: 15vh; max-width: 450px;">
        <div class="card">
            <h1 style="color: var(--primary-green); text-align: center;">WINSTRAT</h1>
            <form id="login-form" novalidate>
                <div class="form-group">
                    <label for="login-email">Email Address</label>
                    <input type="email" id="login-email" name="email" autocomplete="username" required>
                </div>
                <div class="form-group">
                    <label for="login-pass">Password</label>
                    <input type="password" id="login-pass" name="password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Sign In</button>
            </form>
        </div>
    </div>

    <div id="main-app" class="<?php echo !$isLoggedIn ? 'hidden' : ''; ?>">
        <nav>
            <div class="nav-brand">Proposal<span style="color: var(--accent-gold);">Won</span></div>
            <div class="nav-links" id="main-nav">
                <a onclick="route('capture')" id="nav-capture">Capture</a>
                <a onclick="route('intel')" id="nav-intel">Intel</a>
                <a onclick="route('library')" id="nav-library">Library</a>
                <a onclick="route('proposal')" id="nav-proposal">Proposal</a>
                <a onclick="route('debrief')" id="nav-debrief">Debrief</a>
                <a onclick="route('analytics')" id="nav-analytics">Analytics</a>
                
                <?php if ($isPlatformAdmin): ?>
                    <a onclick="route('admin')" id="nav-admin" style="color: var(--accent-gold); border-left: 1px solid #444; padding-left: 15px;">Admin</a>
                <?php endif; ?>
            </div>

            <div style="display: flex; align-items: center; gap: 20px;">
                <span onclick="toggleTheme()" style="cursor:pointer; font-size: 1.2rem;">🌗</span>
                <div style="text-align: right; line-height: 1.2;">
                    <div style="font-size: 0.8rem; font-weight: bold; color: var(--accent-gold);">
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Platform Admin'); ?>
                    </div>
                    <a href="src/logout.php" style="font-size: 0.7rem; color: #ccc; text-decoration: none;">LOGOUT</a>
                </div>
                <div class="user-icon" onclick="route('settings')">
                    <?php echo strtoupper(substr($_SESSION['first_name'] ?? 'P', 0, 1) . substr($_SESSION['last_name'] ?? 'A', 0, 1)); ?>
                </div>
            </div>
        </nav>

        <main class="container">
            <div id="view-capture" class="view">
                <div class="card" style="margin-bottom: 2rem; background: var(--bg-secondary);">
                    <h3>Search Research Lake</h3>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="lake-search-input" placeholder="Search 400k+ records by Title, Agency, or Solicitation #..." style="flex:1;">
                        <button class="btn" id="lake-search-btn">Search Lake</button>
                    </div>
                    <div id="lake-results" style="margin-top: 1rem;"></div>
                </div>

                <div class="card">
                    <div class="flex-between">
                        <h2>My Pipeline</h2>
                        <button class="btn">+ Manual Entry</button>
                    </div>
                    <div id="pipeline-table">
                        <p style="color: #888;">No opportunities in your bucket yet. Search the lake above to add some.</p>
                    </div>
                </div>
            </div>

            <div id="view-admin" class="view hidden">
                <div class="card">
                    <h2>Platform Administration</h2>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1rem;">
                        <div class="stat-card" style="padding:1rem; background: #222; border-radius: 8px;">
                            <small>Global Lake Records</small>
                            <div style="font-size: 1.5rem; color: var(--accent-gold);">~400,000</div>
                        </div>
                        <div class="stat-card" style="padding:1rem; background: #222; border-radius: 8px;">
                            <small>Active Tenants</small>
                            <div style="font-size: 1.5rem; color: var(--accent-gold);">1</div>
                        </div>
                    </div>
                    <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #333;">
                    <h3>Quick Actions</h3>
                    <button class="btn">Onboard New Client</button>
                    <button class="btn" style="background: #444;">Run SAM.gov Import</button>
                </div>
            </div>

            <div id="view-intel" class="view hidden"><div class="card"><h2>Competitive Intel</h2></div></div>
            <div id="view-library" class="view hidden"><div class="card"><h2>Knowledge Library</h2></div></div>
            <div id="view-proposal" class="view hidden"><div class="card"><h2>Proposal Manager</h2></div></div>
            <div id="view-settings" class="view hidden"><div class="card"><h2>Settings</h2></div></div>
        </main>
    </div>

    <script type="module" src="js/app.js"></script>
</body>
</html>