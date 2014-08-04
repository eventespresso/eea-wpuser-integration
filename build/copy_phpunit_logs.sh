#!/bin/bash
SOURCEDIR="$1/wordpress-develop/src/wp-content/plugins/EE4/tests/build"
TARGETDIR="$1/build"
cp -r $SOURCEDIR/logs $TARGETDIR
cp -r $SOURCEDIR/coverage $TARGETDIR
