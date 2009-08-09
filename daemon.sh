#!/bin/sh

if [ x"$1" != x"start" ]; then
  exit
fi

# change into the directory this script is located in
cd $(echo $0 | sed 's,[^/]*$,,')

# initialize the origional database
if [ ! -f protected/data/source.db ]; then
  cp protected/data/source.db.orig protected/data/source.db
fi

# create the runtime directorys
if [ ! -d protected/runtime -o ! -d cache/ -o ! -d assets/ ]; then
  mkdir protected/runtime cache/ assets/ 2>/dev/null
fi

# for some reason on the nmt yiic ends up running with a cwd inside protected, these
# symlinks allow for that
if [ ! -h protected/cache -o ! -h protected/protected ]; then
  rm -f protected/cache protected/protected
  ln -s '../cache' protected/cache
  ln -s '.' protected/protected
fi

# create a faux php cli interpreter in /bin/php
if [ ! -x /bin/php ]; then
  rm /bin/php
  cat >/bin/php <<EOF
#!/bin/sh
/mnt/syb8634/server/php5-cgi -qd register_argc_argv=1 \$*
EOF
  chmod +x /bin/php
fi

# brute force fix to let webserver write to needed files
# might only need to allow the following:
# assets/ cache/ protected/runtime protected/data/source.db
chmod -R 777 .

# clear the APC cache
pidof lighttpd >/dev/null
if [ $? = 0 ]; then
  wget -qO /dev/null 'http://localhost:9990/lighttpd_web/apc.php?SCOPE=A&SORT1=H&SORT2=D&COUNT=20&CC=1&OB=1'
fi
