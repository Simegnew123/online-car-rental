@echo off
echo ==========================================
echo  Adding Firewall Rule for XAMPP Port 8080
echo  This allows phone to scan QR and connect
echo ==========================================
echo.

netsh advfirewall firewall add rule name="XAMPP Apache 8080" dir=in action=allow protocol=TCP localport=8080

echo.
echo Done! Your phone should now be able to connect.
echo Press any key to close...
pause >nul
