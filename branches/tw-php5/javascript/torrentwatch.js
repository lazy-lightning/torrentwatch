// Buttons

function saveConfig() {
	toggleMenu('configuration');
	showLayer('progressDiv');
	setProgress('progressBar', 50);
	setText('progressBar', 'Saving Config');
	submitForm('config_form');
}

// Update Frame Utils
function updateFrameLoad( url, progressText) {
	setProgress('progressBar', 50);
	setText('progressBar', progressText);
	showLayer('progressDiv');
	document.getElementById('update_frame').src = url;
}

function updateFrameCopyDiv( whichDiv ) {
	parent.document.getElementById( whichDiv ).innerHTML = document.getElementById( whichDiv ).innerHTML;
}

function updateFrameFinished() {
	parent.setText('progressBar', "Finished");
	parent.hideLayer('progressDiv');
}

// Context Menu links 
function contextAddToFav()
{
	location.assign(SimpleContextMenu._attachedElement.childNodes[0].href);
}

function contextDLNow()
{
	setProgress('progressBar', 20);
	setText('progressBar', 'Starting Torrent');
	showLayer('progressDiv');	
	document.getElementById('update_frame').src = SimpleContextMenu._attachedElement.childNodes[1].href;
}


function submitForm ( whichForm )
{
	document.getElementById(whichForm).submit();
}

// Function by Shawn Olsen
function changecss(theClass,element,value) {
	//Last Updated on May 21, 2008
	//documentation for this script at
	//http://www.shawnolson.net/a/503/altering-css-class-attributes-with-javascript.html
	//
	//Logic moved to getClassBySelector by Erik Bernhardson
	var myClass = getClassBySelector(theClass);
	if(myClass)
		myClass.style[element] = value;
}


// Logic origionally in changecss by Shawn Olsen
function getClassBySelector( whichClass ) {
	var cssRules;
	if (document.all) {
		cssRules = 'rules';
	} else if (document.getElementById) {
		cssRules = 'cssRules';
	}
	for (var S = 0; S < document.styleSheets.length; S++) {
		for (var R = 0; R < document.styleSheets[S][cssRules].length; R++) {
			if(document.styleSheets[S][cssRules][R].selectorText == whichClass) {
				return document.styleSheets[S][cssRules][R];
			}
		}
	}
}

function changeFilter( filterType , data) {
	changecss('UL.torrentlist LI.match_'+filterType, 'display', data); // IE
	changecss('ul.torrentlist li.torrent.match_'+filterType, 'display', data); // FF
}

function markTorrentAlt()
{
	var alt = 0;
	var elem = document.getElementById('torrentlist_container');
	for( var F in elem.childNodes ) {
		if ( elem.childNodes[F].className == 'feed' ) {
			for ( var T in elem.childNodes[F].firstChild.childNodes ) {
				var torrent = elem.childNodes[F].firstChild.childNodes[T];
				if(torrent.className && torrent.className.substring(0,7) == 'torrent') {
					var match_class = torrent.className.split(" ")[1];
					var class_item = getClassBySelector('ul.torrentlist li.torrent.'+match_class);
					if(!class_item) {
						class_item = getClassBySelector('UL.torrentlist LI.'+match_class);
					}
					if(!class_item)
						return;
					if(class_item.style.display == "block") {
						if(alt) {
							torrent.className = "torrent "+match_class;
							alt = 0;
						} else {
							torrent.className = "torrent "+match_class+" alt";
							alt = 1;
						}
					}
				}
			}
		}
	}
}

function filterFeeds( filterType )
{
	var elem;
	elem = document.getElementById('filter_'+filterType);
	for ( var i in elem.parentNode.childNodes )
	{
		elem.parentNode.childNodes[i].className = ''
	}
	elem.className = 'selected';

	switch(filterType) {
		case 'all':
			changeFilter('nomatch', 'block');
			changeFilter('match', 'block');
			changeFilter('cachehit', 'block');
			changeFilter('duplicate', 'block');
			break;
		case 'matching':
			changeFilter('nomatch', 'none');
			changeFilter('match', 'block');
			changeFilter('cachehit', 'block');
			changeFilter('duplicate', 'none');
			break;
		case 'downloaded':
			changeFilter('nomatch', 'none');
			changeFilter('match', 'none');
			changeFilter('cachehit', 'block');
			changeFilter('duplicate', 'none');
			break;
	}
	markTorrentAlt();
}

		
			
// Inspiration from http://www.netlobo.com/div_hiding.html

var last_fav;
function toggleFav( whichLayer )
{
	if(last_fav)
		hideLayer(last_fav)
	showLayer(whichLayer);
	last_fav = whichLayer;
}

var last_menu;
function toggleMenu( whichLayer )
{
	if(last_menu && last_menu != whichLayer) {
		hideLayer(last_menu);
	}
	toggleLayer(whichLayer);
	last_menu = whichLayer;
}

function toggleLayer( whichLayer )
{
	var elem, vis;
	if( document.getElementById ) // this is the way the standards work
		elem = document.getElementById( whichLayer );
	else if( document.all ) // this is the way old msie versions work
			elem = document.all[whichLayer];
	else if( document.layers ) // this is the way nn4 works
		elem = document.layers[whichLayer];
	vis = elem.style;
	// if the style.display value is blank we try to figure it out here
	if(vis.display==''&&elem.offsetWidth!=undefined&&elem.offsetHeight!=undefined)
		vis.display = (elem.offsetWidth!=0&&elem.offsetHeight!=0)?'block':'none';
	vis.display = (vis.display==''||vis.display=='block')?'none':'block';
	if(whichLayer=='favorites') // Also display the first fav 
		toggleFav(elem.childNodes[1].id);
}

function hideLayer( whichLayer ) {
	var elem, vis;
	if( document.getElementById ) // this is the way the standards work
		elem = document.getElementById( whichLayer );
	else if( document.all ) // this is the way old msie versions work
			elem = document.all[whichLayer];
	else if( document.layers ) // this is the way nn4 works
		elem = document.layers[whichLayer];
	elem.style.display = 'none';
}
function showLayer( whichLayer ) {
	var elem, vis;
	if( document.getElementById ) // this is the way the standards work
		elem = document.getElementById( whichLayer );
	else if( document.all ) // this is the way old msie versions work
			elem = document.all[whichLayer];
	else if( document.layers ) // this is the way nn4 works
		elem = document.layers[whichLayer];
	elem.style.display = 'block';
}
