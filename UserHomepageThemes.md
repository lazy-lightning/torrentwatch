# Introduction #

**What is a config file?**

A config file for the replacement start.cgi script is, at its most basic, a list of variables and their values.  These variables combine together to define a web page that will be displayed to the NMT Browser


**How do i link to the config files?**
  * http://127.0.0.1:8883/start.cgi?config=xxXXxx

With this link, start.cgi will look for a config file named xxXXxx.config (case sensitive) and display the webpage defined by the variables inside that config file.  This is how you will connect your multiple webpages together


**What config files are required?**

  * index.config
When the nmt first boots up, or when many of the NMT pages link back to start.cgi they dont provide a config= variable to start.cgi.  When not provided start.cgi loads index.config.

  * change.config
When the end-user wishes to change the start.cgi theme, this is the config loaded.  This config must also have ITEM\_TABLE\_FUNC="show\_theme\_table".  More about ITEM\_TABLE\_FUNC later.

**What is a variable?**

A variable is defined as described below, and accessed with ${VARIABLE}  Any variable defined in your .config will also be available when parsing your template.  More about this in the template readme(tbw)

**How is a variable defined?**

  1. VARIABLE=data
  1. VARIABLE='data with spaces and special characters'
  1. VARIABLE="${THEME}/index\_template.html"

All variables are defined by a newline with the variable name in all capitals, followed by an equals sign and the variables data.  There must not be a space anywhere before the data.  If there are spaces in the data it must be enclosed in single or double quotes.  You can use another variable in the data field but the data must be enclosed in double quotes
Throughout this file when a variable is shown its data is merely a demonstration of possible values.  In no way are you required to use the sample data provided here as your variables data

**What variables are required?**

  * PAGE\_TEMPLATE="${THEME}/mythemespage.html"

This is the only variable that is required for the most basic config file.  it must begin with "${THEME}/ and then the name of your webpage.  You may name the webpage however you want, as long as its name does not contain special characters and does not start with a -

**Why so many variables in the .config files then?**

One of the primary features of start.cgi that allows it to mimic the nmt interface, is the item table builder.  The item table builder is enabled by defining the variable BUILD\_ITEM\_TABLE=1.  The item table builder takes a list of items and outputs them in a table with a specified number of columns.  It also limits the number of items per page, and handles paging when it overflows.

**What optional variables are there outside of the item table builder?**

  * DATESTR="%h:%M %p"

Datestr uses the same formatting as the unix date command(see [garps post](http://www.networkedmediatank.com/showthread.php?tid=6874&pid=70440#pid70440) on datestr formatting, or read the [date manual](http://www.google.com/search?q=man+date) page)  Through this you can set the format of the ${DATE} variable

**What variables are required with the item table builder?**

  * BUILD\_ITEM\_TABLE=1

This variables tells start.cgi that we want to use the item builder.  It must be equal to 1 or the rest of these variables will be ignored.


---

  * ITEM\_TABLE\_FUNC="show\_item\_table"
  * PAGING\_FUNC="show\_pagination"

These two variables define what part of start.cgi will decide the contents of the table.  PAGING\_FUNC should always stay the same.  ITEM\_TABLE\_FUNC should be "show\_item\_table" for a normal table, or "show\_theme\_table" in change.config for the table of available themes

---


  * ITEM\_TEMPLATE="${THEME}/item\_template.html"

This variable defines the template to be used to fill each cell of the table.  Its format is the same as PAGE\_TEMPLATE.


---

  * TABLE\_BORDER=0
  * TABLE\_CELLSPACING=0
  * TABLE\_CELLPADDING=0

These variables are used in the <table> statement that encloses the items.<br>
<hr />

<ul><li>ITEM_HEIGHT=170<br>
</li><li>PERPAGE=12<br>
</li><li>ROWLEN=4</li></ul>

These variables describe the height of an individual item, the number of items to display per page, and the number of columns(rowlen) to be used in the table<br>
<br>
<hr />
<ul><li>PAGING_ITEM_HEIGHT=21<br>
</li><li>PAGING_ITEM_WIDTH=21<br>
</li><li>PAGING_ITEM_BORDER=0<br>
</li><li>PAGING_TOTAL_WIDTH=30</li></ul>

These variables describe the paging output.  The paging output is the two arrows that point up and down.  They also include the TVID links that let pagup and pagedown from the remote work.<br>
<hr />

<ul><li>DISPLAY_LIST="hard_disk usb network_browser network_shares upnp"</li></ul>

This variable controls which items will be put in the table. Different items must be seperated by spaces.  There is not a 1 to 1 relationship between items in this list and the output table, a single item in this list can output many items in the output table<br>
<br>
<ul><li>hard_disk        - Item for internal NMT HD<br>
</li><li>usb              - Items for attached USB drives<br>
</li><li>network_browser  - Item for the windows network browser<br>
</li><li>network_shares   - Items for saved network shares<br>
</li><li>upnp             - Items for upnp (myihome, llink) devices on the local network<br>
</li><li>msp              - Items with links to the msp homepage, and msp community<br>
</li><li>torrents         - Items for btpd and transmission1.33 torrent client status<br>
</li><li>sayatv           - Items with links to the sayatv homepage<br>
</li><li>web_services     - Items for saved web services links<br>
</li><li>poweroff         - Item for stopping active services, then powering down<br>
</li><li>reboot           - Item for stopping active services, then rebooting<br>
<hr /></li></ul>

<b>What variables are optional with the table builder</b>

<ul><li>User defined items in the item list</li></ul>

You can also put any word you want in the DISPLAY_LIST, and start.cgi will attempt to run a function of the same name.  To define a function use the following template, replacing funcname with your chosen name.  You can have multiple items from the same function by repeating the if statement.  This function should be placed at the bottom of the relevant .config file.  You can also define multiple functions with different names.<br>
<pre><code>funcname() {<br>
	if should_display_item; then<br>
		ITEM_IMAGE=<br>
		ITEM_LINK=<br>
		ITEM_NAME=<br>
		show_item<br>
	fi<br>
}<br>
<br>
funcname() {<br>
	if should_display_item; then<br>
		ITEM_IMAGE=<br>
		ITEM_LINK=<br>
		ITEM_NAME=<br>
		show_item<br>
	fi<br>
	if should_display_item; then<br>
		ITEM_IMAGE=<br>
		ITEM_LINK=<br>
		ITEM_NAME=<br>
		show_item<br>
	fi<br>
}<br>
</code></pre>
Just fill in the data after each ITEM<i>variable.<br>
<hr />
<ul><li>DISPLAY_RANDOM_HD=1</li></ul></i>

If you set this variable equal to 1, when outputting the hard_disk item 3 additional items will be included.  These 3 items will randomly play from the Video, Music, or Photo directory of the NMT HD.<br>
<br>
<br>
tbw - to be written