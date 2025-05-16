@echo off
echo Mengimpor database DonasiKu...
cd %~dp0
"C:\xampp\mysql\bin\mysql" -u root -e "CREATE DATABASE IF NOT EXISTS donatehub;"
"C:\xampp\mysql\bin\mysql" -u root donatehub < schema.sql
echo Database berhasil diimpor!
pause 