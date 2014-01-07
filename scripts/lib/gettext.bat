
@echo off

:: Finde alle Dateien die Uebersetzungstext enthalten
dir "..\lib\*.php" /A:-H-D /S /B > .\tmpfilelist

set SYS=%2
set DIR=..\locale\%1\LC_MESSAGES

:: Verschiebe neue POT-Datei wieder an den richtigen Platz
%SYS%\xgettext.exe --files-from=tmpfilelist  --default-domain='messages' --output=%DIR%\messages.pot  --language="php" --from-code="UTF-8" --add-comments --add-location --no-wrap --sort-by-file


:: Loesche Fileliste
del .\tmpfilelist
