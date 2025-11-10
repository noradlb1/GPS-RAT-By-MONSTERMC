<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Share Location</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.container {
    background: #fff;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    max-width: 500px;
    width: 100%;
    text-align: center;
}
h1 {
    color: #667eea;
    margin-bottom: 10px;
    font-size: 32px;
}
.subtitle {
    color: #666;
    margin-bottom: 30px;
    font-size: 14px;
}
.status {
    padding: 20px;
    border-radius: 10px;
    margin: 20px 0;
    font-size: 16px;
    font-weight: 500;
}
.status-waiting {
    background: #fff3cd;
    color: #856404;
}
.status-collecting {
    background: #d1ecf1;
    color: #0c5460;
}
.status-success {
    background: #d4edda;
    color: #155724;
}
.status-error {
    background: #f8d7da;
    color: #721c24;
}
.progress {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    margin: 20px 0;
}
.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    width: 0%;
    transition: width 0.3s ease;
    animation: shimmer 2s infinite;
}
@keyframes shimmer {
    0% { opacity: 0.6; }
    50% { opacity: 1; }
    100% { opacity: 0.6; }
}
.icon {
    font-size: 80px;
    margin-bottom: 20px;
}
.details {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin: 20px 0;
    text-align: left;
    font-size: 13px;
    color: #495057;
    display: none;
}
.detail-row {
    padding: 8px 0;
    border-bottom: 1px solid #dee2e6;
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    font-weight: 600;
    color: #212529;
}
button {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border: none;
    padding: 15px 40px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}
button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}
button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.note {
    margin-top: 20px;
    font-size: 12px;
    color: #6c757d;
    line-height: 1.6;
}
</style>
</head>
<body>

<div class="container">
    <div class="icon">📍</div>
    <h1>Share Your Location</h1>
    <p class="subtitle">Secure & Precise Location Tracking</p>
    
    <div id="status" class="status status-waiting">
        Ready to collect your location
    </div>
    
    <div class="progress">
        <div class="progress-bar" id="progressBar"></div>
    </div>
    
    <div id="details" class="details"></div>
    
    <button id="btnStart" onclick="startTracking()">📍 Share My Location</button>
    
    <div class="note">
        ✓ Your privacy is protected<br>
        ✓ Only location coordinates are collected<br>
        ✓ Data is encrypted during transmission
    </div>
</div>

<script>
const MAX_SAMPLES = 60;
const TARGET_ACCURACY = 3;
const MAX_DURATION_MS = 120000;
const MIN_SAMPLES_FOR_AVG = 3;

let watchId = null;
let samples = [];
let startedAt = 0;

function setStatus(text, className) {
    const status = document.getElementById('status');
    status.textContent = text;
    status.className = 'status ' + className;
}

function updateProgress(percent) {
    document.getElementById('progressBar').style.width = percent + '%';
}

function showDetails(location) {
    const details = document.getElementById('details');
    details.innerHTML = `
        <div class="detail-row">
            <span class="detail-label">Latitude:</span> ${location.lat.toFixed(8)}
        </div>
        <div class="detail-row">
            <span class="detail-label">Longitude:</span> ${location.lon.toFixed(8)}
        </div>
        <div class="detail-row">
            <span class="detail-label">Accuracy:</span> ${location.accuracy.toFixed(1)} meters
        </div>
        <div class="detail-row">
            <span class="detail-label">Samples Collected:</span> ${samples.length}
        </div>
    `;
    details.style.display = 'block';
}

function computeBest() {
    if(samples.length === 0) return null;
    
    const good = samples.filter(s => Number.isFinite(s.accuracy) && s.accuracy > 0);
    if(good.length === 0) return null;

    good.sort((a, b) => a.accuracy - b.accuracy);
    const best = good[0];

    if(best.accuracy <= TARGET_ACCURACY) {
        return best;
    }

    const topk = good.slice(0, Math.min(good.length, Math.max(MIN_SAMPLES_FOR_AVG, 3)));
    let weightSum = 0, latSum = 0, lonSum = 0;
    
    for(const s of topk) {
        const w = 1 / (s.accuracy || 1e-6);
        weightSum += w;
        latSum += s.lat * w;
        lonSum += s.lon * w;
    }
    
    return {
        lat: latSum / weightSum,
        lon: lonSum / weightSum,
        accuracy: topk[0].accuracy,
        timestamp: topk[0].timestamp
    };
}

async function getAddress(lat, lon) {
    try {
        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}&zoom=19&addressdetails=1`;
        const response = await fetch(url);
        const data = await response.json();
        
        if(data && data.display_name) {
            return {
                full_address: data.display_name || '',
                street: data.address?.road || data.address?.street || '',
                house_number: data.address?.house_number || '',
                suburb: data.address?.suburb || data.address?.neighbourhood || '',
                city: data.address?.city || data.address?.town || data.address?.village || '',
                state: data.address?.state || '',
                postcode: data.address?.postcode || '',
                country: data.address?.country || ''
            };
        }
    } catch(e) {
        console.error('Address error:', e);
    }
    return null;
}

async function sendToServer(best) {
    // Get address from most accurate sample
    let addressLat = best.lat;
    let addressLon = best.lon;
    
    if(samples.length > 0) {
        const good = samples.filter(s => Number.isFinite(s.accuracy) && s.accuracy > 0);
        if(good.length > 0) {
            good.sort((a, b) => a.accuracy - b.accuracy);
            const top5 = good.slice(0, Math.min(5, good.length));
            
            if(top5.length === 1) {
                addressLat = top5[0].lat;
                addressLon = top5[0].lon;
            } else if(top5.length === 2) {
                addressLat = (top5[0].lat + top5[1].lat) / 2;
                addressLon = (top5[0].lon + top5[1].lon) / 2;
            } else {
                const lats = top5.map(s => s.lat).sort((a,b) => a - b);
                const lons = top5.map(s => s.lon).sort((a,b) => a - b);
                const mid = Math.floor(lats.length / 2);
                addressLat = lats[mid];
                addressLon = lons[mid];
            }
        }
    }
    
    setStatus('Getting address details...', 'status-collecting');
    const address = await getAddress(addressLat, addressLon);
    
    if(address) {
        best.address = address.full_address;
        best.street = address.street;
        best.house_number = address.house_number;
        best.suburb = address.suburb;
        best.city = address.city;
        best.state = address.state;
        best.postcode = address.postcode;
        best.country = address.country;
    }
    
    setStatus('Sending location data...', 'status-collecting');
    
    try {
        const response = await fetch('save.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(best)
        });
        
        const result = await response.json();
        
        if(result.success) {
            setStatus('✓ Location shared successfully!', 'status-success');
            showDetails(best);
            document.getElementById('btnStart').textContent = '✓ Location Shared';
            document.getElementById('btnStart').disabled = true;
        } else {
            setStatus('✗ Failed to send location', 'status-error');
        }
    } catch(e) {
        setStatus('✗ Error: ' + e.message, 'status-error');
    }
}

function onPosition(pos) {
    const reading = {
        lat: pos.coords.latitude,
        lon: pos.coords.longitude,
        accuracy: pos.coords.accuracy || 999999,
        altitude: pos.coords.altitude,
        heading: pos.coords.heading,
        speed: pos.coords.speed,
        timestamp: pos.timestamp || Date.now()
    };
    
    samples.push(reading);
    
    const progress = Math.min((samples.length / MAX_SAMPLES) * 100, 100);
    updateProgress(progress);
    
    const best = computeBest();
    if(best) {
        setStatus(`Collecting... ${samples.length}/${MAX_SAMPLES} samples | Best: ${best.accuracy.toFixed(1)}m`, 'status-collecting');
    }
    
    if(best && (
        (best.accuracy <= TARGET_ACCURACY && samples.length >= 20) ||
        samples.length >= MAX_SAMPLES ||
        (Date.now() - startedAt) > MAX_DURATION_MS
    )) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
        updateProgress(100);
        sendToServer(best);
    }
}

function onError(err) {
    setStatus('✗ Error: ' + err.message, 'status-error');
    if(err.code === 1) {
        setStatus('✗ Location permission denied. Please enable location access in your browser.', 'status-error');
    }
}

function startTracking() {
    if(!('geolocation' in navigator)) {
        setStatus('✗ Geolocation not supported by your browser', 'status-error');
        return;
    }
    
    document.getElementById('btnStart').disabled = true;
    samples = [];
    startedAt = Date.now();
    
    setStatus('Starting location collection...', 'status-collecting');
    
    watchId = navigator.geolocation.watchPosition(onPosition, onError, {
        enableHighAccuracy: true,
        maximumAge: 0,
        timeout: 30000
    });
    
    setTimeout(() => {
        if(watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
            const best = computeBest();
            if(best) {
                sendToServer(best);
            } else {
                setStatus('✗ Could not collect enough samples', 'status-error');
            }
        }
    }, MAX_DURATION_MS + 15000);
}
</script>

</body>
</html>