@echo off
chcp 65001 >nul
powershell -ExecutionPolicy Bypass -File "%~dp0RESTORE-DATABASE.ps1"
pause




