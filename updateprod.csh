#!/bin/csh -f
scp *.php blizzard.teresco.org:/home/www/tm/
foreach dir (user lib devel hb shields)
  scp $dir/*.{php,js} blizzard.teresco.org:/home/www/tm/$dir
end
