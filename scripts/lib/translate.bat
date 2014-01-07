
@echo off

set SYS=%2
set DIR=..\locale\%1\LC_MESSAGES

:: Dateien zusammenfueheren
%SYS%\msgmerge.exe --add-location --no-wrap --sort-by-file --update --silent %DIR%\messages.po %DIR%\messages.pot

:: In MO uebersetzen
%SYS%\msgfmt.exe %DIR%\messages.po -v -o %DIR%\messages.mo

