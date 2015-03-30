**What is a template?**

A template is just a regular html page, which has the option of using variables that will be replaced with text and html code each time it is displayed.

**How are page templates and item templates different?**

Item templates have all the same variables as the page templates, plus a few more.  Also some variables have a slightly different meaning in the item template

**How do i use a variable?**
  * ${VARIABLE}
The variables name must be enclosed with ${} and it must be in all capital letters.

**What are some usefull variables?**
  * ${DATE} - The date, formating of which is set by DATESTR in the .config
  * ${JS\_FUNC\_PLAYMEDIA} - javascript function to mount the share a media file is on, and then play it
  * ${JS\_FUNC\_PLAYRANDOM} - javascript function to mount the share a directory is on, and then build and play a playlist in random order
  * ${HD\_LINK} - link to either autoload index.htm from HARD\_DISK, or display the filebrowser filter chooser
    * Also available as
    * http://localhost.drives:8883/HARD_DISK/?home=0
    * ${USB\_A\_LINK}
    * ${USB\_A1\_LINK}
    * ${USB\_B\_LINK}
    * ${USB\_B1\_LINK}
  * ${HD\_LINK\_ALL} - Direct link to filebrowser displaying all media types(movies, photos, music, web pages) at the same time
    * Same available options as previous, just append `_ALL`
    * http://localhost.drives:8883/HARD_DISK/?filter=0

**What variables does the item table builder add to the page template?**
  * ${HTML\_ITEMS\_TABLE} - This is the table that you told start.cgi to build in your .config
  * ${HTML\_PAGING} - The paging table, which is the up and down page links
  * ${COUNT} - The number of items across all pages
  * ${STARTITEM} - The number of the first item on this page
  * ${ENDITEM} - The number of the last item on this page

**What variables does the item table builder add to the item template?**
  * ${ITEM\_IMAGE} - Link to the image for this item
  * ${ITEM\_NAME} - Name of this item
  * ${ITEM\_LINK} - The destination URL of this item
  * ${ITEM\_ALT} - info to be placed in the anchor tags alt=, this will be displayed when info is pushed
  * ${COUNT} - In the item template, COUNT is the current item number, with 1 being at the begining of the item list
  * ${LINKNUM} - The current items number, with 1 being at the begining of the current page
  * ${FIRSTLINK} - If this item is the begining of a row, this will be set to the contents of FIRST\_LINK\_DATA from the .config file.  This usually is set to onkeyleftset='linkname' to direct the link focus from the table to a specific link

**What other variables can i use?**

> For a full list of variables, see the VARIABLES file in the theme-installer directory of the most recent release.  You can also set variables in your .config file, and use them in your template.

**What happens if i use a variable name that doesnt exist?**

> It will be replaced with nothing, and it will be as if the variable was never there.