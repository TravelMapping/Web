#!/usr/bin/env bash
#
# Copy files from working directory of Web repository to the server
#
set -e
shopt -s nullglob
server=noreaster.teresco.org
basedir=/home/www/
rootdir=tmtest
shieldsdir=
wpteditdir=
otherdirs="user lib devel hb css graphs"
while (( "$#" )); do

    if [ "$1" == "--prod" ]; then
	rootdir=tm
    fi

    if [ "$1" == "--test2" ]; then
	rootdir=tmtest2
    fi

    if [ "$1" == "--shields" ]; then
	shieldsdir=shields
    fi
    
    if [ "$1" == "--wptedit" ]; then
	wpteditdir=wptedit
    fi
    
    shift
done

echo "Updating to $server:$basedir$rootdir, directories . $otherdirs $shieldsdir $wpteditdir"
scp *.php favicon.* $server:$basedir$rootdir
for dir in $otherdirs $shieldsdir $wpteditdir; do
    ssh $server mkdir -p $basedir$rootdir/$dir
    scp $dir/*.{php,js,svg,css,png,gif} $server:$basedir$rootdir/$dir
done
