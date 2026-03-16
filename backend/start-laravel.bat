@echo off
echo Starting Laravel server with Laragon PHP...
cd /d "C:\Users\ream.khorn\Desktop\Achar-Event-Booking\backend"
"C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan serve --host=127.0.0.1 --port=8000
pause
