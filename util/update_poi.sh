#!/bin/sh

cd ..

DIR_PARSERS='./parsers'
FILTER='.parser.php'
if [[ "$1" != "" ]]; then FILTER=$1; fi

for fname in `find $DIR_PARSERS | grep $FILTER`
do
	php "$DIR_PARSERS/start.php" $fname
done
