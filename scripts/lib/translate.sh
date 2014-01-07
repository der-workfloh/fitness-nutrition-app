#!/bin/sh

export LC_ALL="C"
DIR="../locale/$1/LC_MESSAGES"

if test -f $DIR/messages.po
then
    # Dateien zusammenfueheren
	msgmerge \
		--add-location \
		--no-wrap \
		--sort-by-file \
		--update \
		--silent \
		$DIR/messages.po \
		$DIR/messages.pot
else
    # In MO uebersetzen
	msginit \
		--no-wrap \
		--input=$DIR/messages.pot \
		--output=$DIR/messages.po

fi

msgfmt $DIR/messages.po -v -o $DIR/messages.mo
