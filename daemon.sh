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
if [ ! -d protected/runtime -o ! -d cache/ -o ! -d assets/ -o ! -d images/TvBanners]; then
  mkdir protected/runtime cache/ assets/ images/TvBanners 2>/dev/null
fi

# only on nmt platform
if [ -d /mnt/syb8634 -o -d /nmt ]; then
  # for some reason on the nmt yiic ends up running with a cwd inside protected, these
  # symlinks allow for that
  if [ ! -h protected/cache -o ! -h protected/protected ]; then
    rm -f protected/cache protected/protected
    ln -s '../cache' protected/cache
    ln -s '.' protected/protected
  fi

  # brute force fix to let webserver write to needed files
  # might only need to allow the following:
  # assets/ cache/ protected/runtime protected/data/source.db
  chmod -R 777 .
  # switch ownership of certain things to nobody (the lighttpd and fastcgi user)
  chown -R 99.99 assets/ cache/ protected/runtime
  
  # clear the APC cache
  pidof lighttpd >/dev/null
  if [ $? = 0 ]; then
    wget -qO /dev/null 'http://localhost:9999/lighttpd_web/apc.php?SCOPE=A&SORT1=H&SORT2=D&COUNT=20&CC=1&OB=1'
  fi
  
  # create a faux php cli interpreter in /bin/php
  if [ ! -x /bin/php ]; then
    rm -f /bin/php
    PHP='/share/Apps/lighttpd/bin/php-cgi'
    if [ ! -x $PHP ]; then
      PHP='/mnt/syb8634/server/php5-cgi'
    fi
    if [ ! -x $PHP ]; then
      PHP='/nmt/apps/server/php5-cgi'
    fi
    cat >/bin/php <<EOF
#!/bin/sh
$PHP -qd register_argc_argv=1 \$*
EOF
    chmod +x /bin/php
  fi
fi

# perform db migration if neccessary
protected/yiic migrate

