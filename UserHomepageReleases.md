# [0.10](http://rapidshare.com/files/149609701/start.cgi.0.10.zip.html) #
  * new suspend.cgi and related item in table, stops all nmt-apps
  * play.cgi updated for proper slideshow support
  * play.cgi updated with exclude filters(space seperated list of regexp's to match to dir names)
  * added end-user preferences page http://127.0.0.1:8883/themeprefs.cgi
  * new variables set by end user for links to movies, photos, and music
  * new variable set by end user for the random play exclude filter
  * msp theme has its own (shoddy) pictures now, only a few borrowed from MSP
  * Added Grand theme by Dinauktion
  * Added Classic\_German theme by FLaSH
  * Updated Classic theme
  * Updated Sleek theme
    * visual updates from mcmilly
    * updating clock from erik bernhardson
  * new script openshared.cgi, like play.cgi but for html files
    * Intended to mount a shared drive before loading the jukebox
    * http://127.0.0.1:8883/openshared.cgi?share=mysharename&destpath=/path/on/share/file.html
  * added multiple language support for the item table builder
    * if your PCH uses a different language, please see the readme in the .user\_homepage/lang directory.
  * when changing theme, will now also show theme-`*`.zip files in the root of mounted drives, and install them when chosen