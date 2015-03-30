## To install via FTP ##
  1. decompress the zip
  1. ftp to your popcorn, and upload these 3 files from the zip to your PCH
    * tw.scripts.tar
    * torrentwatch-installer.html
    * torrentwatch-installer.cgi
  1. send the command "site chmod 755 torrentwatch-installer.cgi" through ftp
  1. Go to http://ip-of-pch:8088/stream/file=/opt/sybhttpd/localhost.drives/HARD_DISK/torrentwatch-installer.cgi?install **OR**
  1. On your popcorn remote push the source button
  1. Select HARD\_DISK
  1. Select the 4th icon, which is files
  1. Select torrentwatch-installer.html
  1. Select Run from HARD\_DISK
  1. Select Install to HARD\_DISK

If you get an error of "Request cannot be processed"
This means the torrentwatch-installer.cgi is not executable. Redo step 3.

## To Install via USB ##
  1. decompress the zip to the base dir of the USB stick
  1. Push the source button on your PCH remote
  1. Remember which USB\_DRIVE is being offered(A, A-1, B, B-1)
    * I'm using A-1 for this example
  1. Select USB\_DRIVE\_A-1
  1. Select the 4th icon, which is files
  1. Select torrentwatch-installer.html
  1. Select Run from USB\_DRIVE\_A-1
  1. Select Install to HARD\_DISK

## Post Install ##
After installing torrentwatch to your NMT you will want to configure it, this is done from a normal PC browser like firefox, opera, or safari.
  * http://ip-of-pch:8883/torrentwatch/index.cgi
  * Be sure to bookmark the configuration page after you first use it.