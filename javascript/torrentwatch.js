$(function() {
  // Menu Bar
  $("li#favoritesMenu a,li#config a,li#view a,li#empty a").click(function() {
    $(this).toggleDialog();
  });
  // Filter Bar
  $("ul#filterbar li a").click(function() {
    switch(this.hash) {
    case '#filter_all':
      $("li.torrent").removeClass('alt').show().filter(":even").addClass('alt');break;
    case '#filter_matching':
      $("li.torrent").removeClass('alt').show().filter("li.match_nomatch").hide().end().
        filter(":not(li.match_nomatch)").filter(":even").addClass('alt');
      break;
    case '#filter_downloaded':
      $("li.torrent").removeClass('alt').hide().filter("li.match_cachehit").show().filter(":even").addClass('alt');break;
    }
  });
  $("input#filter_text_input").keyup(function() {
    var filter = $(this).val().toLowerCase();
    $("div.feed ul.torrentlist li.torrent").removeClass('alt').each(function() {
      if($(this).find("div.torrent_name").text().toLowerCase().match(filter)) {
        $(this).show();
      } else {
        $(this).hide();
      }
    }).filter(":visible:even").addClass('alt');
  });
  // Dialog Buttons
  $("div#history a,div.welcome a,div.favinfo form a,form#config_form a,form.feedform a,div#clear_cache a:first").not("a#save").click(function() {
    $(this).toggleDialog();
  });

  // Favorites
  $(".favorite ul li:first a").toggleFavorite();
  $(".favorite ul li:not(:first)").tsort("a");
  $(".favorite ul li a").click(function() {
    $(this).toggleFavorite();
  });

  // Inspector
  var inspect_status = '-';
  $("li#inspector a").click(function() {
    $("div#torrentlist_container").animate(
      { width:inspect_status+"=350" },
      { queue:false , duration:600 }
    );
    $("div#filterbar_container").animate( { width:inspect_status+"=350" }, 600);
    $("div#inspector_container").animate( { width:"toggle" }, 600);
    inspect_status = (inspect_status == '+' ? '-' : '+');
  });

});

(function($) {
  var current_favorite, current_dialog;
  $.fn.toggleDialog = function() {
    this.each(function() {
      var last = current_dialog;
      current_dialog = (last == this.hash ? '' : this.hash);
      if(last) $(last).fadeOut();
      if(current_dialog) $(current_dialog).fadeIn();
    });
    return this;
  };
  $.fn.toggleFavorite = function() {
    this.each(function() {
      var last = current_favorite;
      current_favorite = this.hash
      if(!last) $(current_favorite).show();
      $(last).fadeOut(400, function() {
        $(current_favorite).fadeIn(400);
      });
    });
    return this;
  };
})(jQuery);


// Variable options based on chosen client
function updateClientOptions() {
	elem = document.getElementById('client');
	if(!elem && document.parent)
		elem = document.parent.getElementById('client');
	if(!elem)
		return;
	changecss('div.favorite_seedratio', 'display', 'none');
	switch(elem.value) {
		case 'transmission1.3x':
			showLayer('config_downloaddir');
			showLayer('config_watchdir');
			showLayer('config_savetorrent');
			showLayer('config_deepdir');
			showLayer('config_verifyepisodes');
			changecss('div.favorite_seedratio', 'display', 'block');
			changecss('div.favorite_savein', 'display', 'block');
			changecss('div.favorite', 'height', '230');
			changecss('div.favinfo', 'height', '230');
			break;
		case 'transmission1.22':
			hideLayer('config_downloaddir');
			showLayer('config_watchdir');
			showLayer('config_savetorrent');
			hideLayer('config_deepdir');
			showLayer('config_verifyepisodes');
			changecss('div.favorite_savein', 'display', 'none');
			changecss('div.favorite', 'height', '180');
			changecss('div.favinfo', 'height', '180');
			break;
		case 'btpd':
			showLayer('config_downloaddir');
			showLayer('config_watchdir');
			showLayer('config_savetorrent');
			showLayer('config_deepdir');
			showLayer('config_verifyepisodes');
			changecss('div.favorite_savein', 'display', 'block');
			changecss('div.favorite', 'height', '205');
			changecss('div.favinfo', 'height', '205');
			break;
		case 'nzbget':
			hideLayer('config_downloaddir');
			showLayer('config_watchdir');
			hideLayer('config_savetorrent');
			hideLayer('config_deepdir');
			showLayer('config_verifyepisodes');
			changecss('div.favorite_savein', 'display', 'none');
			changecss('div.favorite', 'height', '180');
			changecss('div.favinfo', 'height', '180');
			break;
		case 'sabnzbd':
			hideLayer('config_downloaddir');
			hideLayer('config_watchdir');
			hideLayer('config_savetorrent');
			hideLayer('config_deepdir');
			showLayer('config_verifyepisodes');
			changecss('div.favorite_savein', 'display', 'none');
			changecss('div.favorite', 'height', '180');
			changecss('div.favinfo', 'height', '180');
			break;
	}
}

// Buttons

function saveConfig() {
	toggleMenu('configuration');
	showLayer('progressDiv');
	setProgress('progressBar', 50);
	setText('progressBar', 'Saving Config');
	submitForm('config_form');
}

function saveWelcome() {
	toggleMenu('welcome5');
	showLayer('progressDiv');
	setProgress('progressBar', 50);
	setText('progressBar', 'Saving Config');
	submitForm('welcome_form');
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

function contextInspect()
{
	showInspector(SimpleContextMenu._attachedElement.childNodes[2].textContent);
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

// Inspiration from http://www.netlobo.com/div_hiding.html

var last_fav;
function toggleFav( whichLayer )
{
	if(last_fav)
		hideLayer(last_fav)
	showLayer(whichLayer);
	last_fav = whichLayer;
	updateClientOptions();
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
	if(elem)
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
	if(elem)
		elem.style.display = 'block';
}
