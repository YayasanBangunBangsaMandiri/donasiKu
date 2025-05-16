@echo off
echo Menjalankan server PHP dengan php.ini kustom...
echo Memuat ekstensi MySQL dari C:\eRaporSMK\php\ext
php -c php.ini -S localhost:8888
pause 