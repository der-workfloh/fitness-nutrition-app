#!/bin/sh

export LC_ALL="C"

# Finde alle Dateien die Uebersetzungstext enthalten
find ../lib -iregex .*\.php > ./tmpfilelist
DIR="../locale/$1/LC_MESSAGES"

# Hole Texte aus den Files

# Verschiebe neue POT-Datei wieder an den richtigen Platz
xgettext --files-from=tmpfilelist  \
    --default-domain='messages' \
    --output=$DIR/messages.pot  \
    --language="php" \
    --from-code="UTF-8" \
    --add-comments \
    --add-location \
    --no-wrap \
    --sort-by-file



# Loesche Fileliste
rm ./tmpfilelist
