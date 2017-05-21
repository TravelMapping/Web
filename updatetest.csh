#!/bin/csh -f
scp *.php blizzard.teresco.org:/home/www/tmtest/
foreach dir (user lib devel hb css api)
  scp $dir/*.{php,js,svg,css} blizzard.teresco.org:/home/www/tmtest/$dir
end
