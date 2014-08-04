#!/bin/bash
TARGETDIR="$1/wordpress-develop/src/wp-content/plugins/ee4-wpusers"
mkdir $TARGETDIR
for file in *
do test "$file" != "wordpress-develop" && cp -r "$file" "$TARGETDIR/"
done
