#!/bin/csh -f
scp *.php blizzard.teresco.org:/home/www/tm/
foreach dir (user lib devel hb shields css api)
  scp $dir/*.{php,js,svg,css} blizzard.teresco.org:/home/www/tm/$dir
end
