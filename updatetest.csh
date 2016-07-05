#!/bin/csh -f
scp *.php blizzard.teresco.org:/home/www/tmtest/
foreach dir (user lib devel hb shields)
  scp $dir/*.{php,js,svg} blizzard.teresco.org:/home/www/tmtest/$dir
end
