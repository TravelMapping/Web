#!/usr/bin/env bash
#
# Copy files from working directory of Web repository to the server
#
set -e
shopt -s nullglob
server=noreaster.teresco.org
basedir=/home/www/
rootdir=tmdevel
shieldsdir=
wpteditdir=
fontsdir=
otherdirs="user lib devel devel/manual hb css graphs"
while (( "$#" )); do

    if [ "$1" == "--prod" ]; then
	rootdir=tm
    fi

    if [ "$1" == "--stage" ]; then
	rootdir=tmstage
    fi

    if [ "$1" == "--test" ]; then
	rootdir=tmtest
    fi

    if [ "$1" == "--rail" ]; then
	rootdir=tmrail
    fi

    if [ "$1" == "--ski" ]; then
	rootdir=tmski
    fi

    if [ "$1" == "--shields" ]; then
	shieldsdir=shields
    fi
    
    if [ "$1" == "--wptedit" ]; then
	wpteditdir=wptedit
    fi
    
    if [ "$1" == "--fonts" ]; then
	fontsdir=fonts
    fi
    
    shift
done

echo "Updating to $server:$basedir$rootdir, directories . $otherdirs $shieldsdir $wpteditdir $fontsdir"
scp *.php favicon.* $server:$basedir$rootdir
for dir in $otherdirs $shieldsdir $wpteditdir $fontsdir; do
    ssh $server mkdir -p $basedir$rootdir/$dir
    scp $dir/*.{php,js,svg,css,png,gif} $server:$basedir$rootdir/$dir
done
