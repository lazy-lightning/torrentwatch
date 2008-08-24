#!/bin/sh 

# Get the header in early, incase we have any errors
cat <<EOF
Content-Type: text/html

<html>
<head>
<META HTTP-EQUIV="expires" CONTENT="0">
<title>
telnet-installer
</title>
</head>
<body>

<h2>
EOF

# Clean up PATH for security reasons.
PATH=/bin:/usr/bin

# Work out where we are run from.
pwd=$0;
# "/opt/sybhttpd/localhost.drives/HARD_DISK/llink-installer.cgi"
urlpwd=`echo $pwd | sed 's#/opt/sybhttpd/localhost.drives/\(.*\)/.*$#\1#g'`
# "HARD_DISK"
exename=`echo $pwd | sed 's#.*/\([^/]*\)$#\1#g'`
# "torrentwatch-installer.cgi"


# Defines used for this version
INSTALL="/opt/sybhttpd/localhost.drives/$urlpwd/tw.scripts.tar"
DEST="/opt/sybhttpd/localhost.drives/HARD_DISK/.torrents"
USBDIR="/opt/sybhttpd/localhost.drives/$urlpwd"
STARTER="/opt/sybhttpd/localhost.drives/HARD_DISK/start_app.sh"
# This is where our php interface script will be linked to
# cannot be in a hidden directory like .torrents
IFACE_DEST="/share/"
IMAGE_DEST="/share/images/"

# Script to install into the autostarter
START_SCRIPT='rss_dl.php'
SCRIPT_OPTS='-i'


###############################################################
# Define the functions we will call.
# No changes should be needed from here on
###############################################################
MARKER="#M_A_R_K_E_R_do_not_remove_me"
FTPSERVER="/mnt/syb8634/etc/ftpserver.sh"

# Send the closing HTML
end_html() 
{ 
  cat <<EOF
</h2>
</body>
</html>
EOF
  
}


# Do some minor sanity checks that the HDD is mounted.
check_has_hdd()
{

  /bin/mount | /bin/grep -q /opt/sybhttpd/localhost.drives/HARD_DISK
  if [ $? != 0 ]; then
    echo "Sorry, but you do not appear to have HARD_DISK mounted<br>" 
    end_html
    exit 0
  fi

}

autostart_add()
{   
    /bin/cat "$STARTER" | /bin/grep -q "$START_SCRIPT"
    if [ $? == 0 ]; then
		echo "$START_SCRIPT already set to start on boot, skipping <br>"
    else
		echo "Adding $START_SCRIPT to community agreed startup script.<br>"
		
		rm -f /tmp/.starter.tmp
		IFS=""
		cat "$STARTER" | while read line 
		do
		  echo "$line" >> /tmp/.starter.tmp
		  if [ x"$line" == x"$MARKER" ]; then
			  # echo "cd ${DEST}/ && ./telnetd -l /bin/sh &" >> /tmp/.starter
			  echo "${DEST}/${START_SCRIPT} ${SCRIPT_OPTS}" >> /tmp/.starter.tmp
		  fi
		done
		cat < /tmp/.starter.tmp > "$STARTER"
		chmod 755 "$STARTER"
		rm -f /tmp/.starter.tmp
    fi

}

autostart_setup()
{

  # Insert code here to make it auto-start
	if [ -f "$STARTER" ]; then
		echo "Found user community agreed on startup file...<br>"
	else
		# Create the file. Then do the scary part of making it be
		# executed at boot time.
		echo "Created user community startup file...<br>"
		cat > "$STARTER" <<EOF
#!/bin/sh
#

$MARKER

exit 0
EOF
		chmod 755 "$STARTER"
     fi

		# Check if it is already called from ftpserver.sh
	grep -q "$STARTER" "$FTPSERVER"
	if [ $? != 0 ]; then
		echo "Installer starter<br>"

		cp "$FTPSERVER" "$FTPSERVER.backup"
		
		rm -f /tmp/.ftpserver.tmp /tmp/.found
		IFS=""
		cat "$FTPSERVER" | while read line
		  do
		  echo "$line" >> /tmp/.ftpserver.tmp
		  if [ x"$line" == x"start() {" ]; then
			  echo "      $STARTER &" >> /tmp/.ftpserver.tmp
			  touch /tmp/.found
		  fi
		done
		
		if [ -f /tmp/.found ]; then
			cat < /tmp/.ftpserver.tmp > "$FTPSERVER"
			chmod 774 "$FTPSERVER"
		fi
		rm -f /tmp/.ftpserver.tmp /tmp/.found
	fi
	
}

# Install program on HDD
install_harddisk()
{ 
	check_has_hdd

	# find a name
	set -- $INSTALL
	INSTA="$1"

 # We expect to find tarball next to this script.
	if [ ! -f "$INSTA" ]; then
		echo "Unable to read<br>"
		echo "$INSTALL<br>"
		echo "Please place binary next to<br>"
		echo "$exename<br>"
		echo "script, and try again.<br>"
		exit 0;
	fi

	echo "Installing to HARD_DISK...<br>"
  # We make sure that HARD_DISK/.torrents
	mkdir -p "$DEST"
	chmod 777 "$DEST"
	
	# Actually copy the data now.
	(cd "$DEST" && tar -xf "$INSTA")
	#cp -f "$INSTA" "$DEST/"

	if [ $? != 0 ]; then
		echo "Sorry, it appears that tar returned an error.<br>"
		exit 0;
	fi

	echo "Configuring installed files.<br>"
	# Verify our new program in executable
	chmod a+x $DEST/$START_SCRIPT
	chmod a+x $DEST/torrentwatch.php
	chmod a+x $DEST/tw-iface.php

	# Link our hidden directory to a non-hidden directory.  This is so the NMT media
  # browser doesn't show an extra folder
	if [ -h /share/torrentwatch ]; then
		rm /share/torrentwatch
		ln -s $DEST /share/torrentwatch
	fi

	# Anti-clobber routine for the config script
	if [ ! -f $DEST/rss_dl.config ];then
		cp $DEST/rss_dl.config.orig $DEST/rss_dl.config
	fi

	# Check auto starter
	autostart_setup
	
	# Add our script to the auto starter
	echo "<p>Installing ${START_SCRIPT} into ${STARTER}</p>"
	autostart_add
	# Run the script, since the autostart wont be running until reboot
	date >> /var/rss_dl.log
	echo "Installed cron hook from configuration script" >> /var/rss_dl.log
	${DEST}/${START_SCRIPT} ${SCRIPT_OPTS}

	echo "Success..<br>"

	echo 'Please procede to the';
	echo '<a href="http://localhost.drives:8883/HARD_DISK/torrentwatch/tw-iface.cgi">Configuration Interface</a>.<br>'
  # Remove the tarball
	rm -f "$INSTA"

	exit 0;
}





#########################################################################
#
# Main function begins here
#
#########################################################################

#start_html


# check if we were run without arguments, if so display "menu"
case "$1" in
  install)
      install_harddisk;;
esac


cat <<EOF
Welcome to the Torrent Watch Folder installer.<br>
We are currently running from:<br>
$urlpwd/$exename<br>
<br>

<a href="http://localhost.drives:8883/$urlpwd/$exename?install">
Install to HARD_DISK
</a>

<br>


EOF

end_html

exit 0


