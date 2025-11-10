@echo off
:: Enable Windows Location Services
:: Run as Administrator

echo ========================================
echo Enabling Windows Location Services...
echo ========================================
echo.

:: Enable Location via Registry
echo [1/3] Setting Registry values...
reg add "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\CapabilityAccessManager\ConsentStore\location" /v Value /t REG_SZ /d Allow /f >nul 2>&1
reg add "HKCU\SOFTWARE\Microsoft\Windows\CurrentVersion\CapabilityAccessManager\ConsentStore\location" /v Value /t REG_SZ /d Allow /f >nul 2>&1

:: Enable Location Service
echo [2/3] Starting Geolocation Service...
sc config lfsvc start= auto >nul 2>&1
sc start lfsvc >nul 2>&1

:: Enable Sensor Monitoring
echo [3/3] Starting Sensor Service...
sc config SensrSvc start= auto >nul 2>&1
sc start SensrSvc >nul 2>&1

echo.
echo ========================================
echo Done! Location services enabled.
echo ========================================
echo.
echo Please restart your application.
echo.
pause