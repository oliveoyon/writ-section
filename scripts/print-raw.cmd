@echo off
copy /B "%~1" "%~2"
exit /B %ERRORLEVEL%
