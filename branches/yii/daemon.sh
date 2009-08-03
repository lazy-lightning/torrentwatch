#!/bin/sh

if [ x"$1" != "start" ];
  exit
fi

cd $(echo $0 | sed 's,[^/]*$,,')

# initialize the origional database if needed
if [ ! -f protected/data/source.db ]; then
  cp protected/data/source.db.orig protected/data/source.db
fi

# create the runtime directory
if [ ! -d protected/runtime ]; then
  mkdir protected/runtime
fi

# create the cache directory
if [ ! -d cache ]; then
  mkdir cache
fi

# create a faux php cli interpreter in /bin/php
if [ ! -x /bin/php ]; then
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

