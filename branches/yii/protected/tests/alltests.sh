#!/bin/sh
SELENIUM="/home/guy/Projects/selenium-remote-control-1.0.1/selenium-server-1.0.1/selenium-server.jar"


if [ ! -f $SELENIUM ]; then
  SELENIUM=$(locate -r selenium-server.jar$ | tail -n 1)
fi

if [ ! -f $SELENIUM ]; then
  cat <<EOF

Please edit this script to contain the proper location of selenium-server.jar
EOF
  if [ x"$(which locate)" != x"" ]; then
    echo "Possible locations:"
    locate selenium-server.jar
  fi
  exit 1;
fi

( cd ../data/;./genOrig.sh; )
setupRamdisk.sh
echo starting selenium
java -jar $SELENIUM -browserSessionReuse 1>/dev/null 2>/dev/null &
SPID=$(ps ax | grep selenium | awk '{ print $1 }')
find ./ -iname \*.php | egrep -v 'svn|PHPUnit' | xargs -L 1 php -l
phpunit unit/ && phpunit functional/
echo killing selenium: $SPID
kill $SPID 2>/dev/null 1>/dev/null
