$(function() {
  // Menu Bar
  $("li#favoritesMenu a,li#config a,li#view a,li#empty a").click(function() {$(this).toggleDialog();});
  // Filter Bar
  $("ul#filterbar li a").click(function() {
    var filter = this.hash;
    $("div#torrentlist_container").slideUp(400, function() {
      switch(filter) {
      case '#filter_all':
       $("li.torrent").removeClass('alt').fadeIn().filter(":even").addClass('alt');break;
      case '#filter_matching':
        $("li.torrent").removeClass('alt').show().filter("li.match_nomatch").hide().end().
          filter(":not(li.match_nomatch)").filter(":even").addClass('alt');
        break;
      case '#filter_downloaded':
        $("li.torrent").removeClass('alt').hide().filter("li.match_cachehit").show().
          filter(":even").addClass('alt');break;
      }
      $("div#torrentlist_container").slideDown(400);
    });
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
  // Context Menu
  $("li.torrent").contextMenu("CM1", {
    menuStyle: {
      textAlign: "left",
      width: "160px"
    },
    itemStyle: {
      fontSize: "1.3em",
      paddingLeft: "15px"
    },
    bindings: {
      'addToFavorites': function(t) {
        location.replace($(t).find("a.context_link:first").get(0).href);
      },
      'startDownloading': function(t) {
        location.replace($(t).find("a.context_link:last").get(0).href);
      },
      'inspect': function(t) {
        if(inspect_status == false) toggleInspector();
        $("div#inspector_container").load('inspector.cgi?title='+$(t).find("div.torrent_name").text());
      }
    }
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
  var inspect_status = false;
  var toggleInspector = function() {
    $("div#torrentlist_container").animate(
      { width:(inspect_status?'+':'-')+"=350" },
      { queue:false , duration:600 }
    );
    $("div#filterbar_container").animate( { width:(inspect_status?'+':'-')+"=350" }, 600);
    $("div#inspector_container").animate( { width:"toggle" }, 600);
    inspect_status = !inspect_status;
  };
  $("li#inspector a").click(toggleInspector);
  // Switching visible items for different clients
  $("select#client").change(function() {
    $("div.favorite_seedratio").css("display","none");
    switch($("select#client")) {
      case 'transmission1.3x':
        $("#config_downloaddir,#config_watchdir,#config_savetorrent,#config_deepdir,#config_verifyepisodes,div.favorite_seedratio,div.favorite_savein").css("display","block");
        $("div.favinfo,div.favorite").css("height",230);
      break;
      case 'transmission1.22':
        $("#config_downloaddir,#config_deepdir,div.favorite_savein").css("display","none");
        $("#config_watchdir,#config_savetorrent,#config_verifyepisodes").css("display","block");
        $("div.favinfo,div.favorite").css("height",180);
      break;
      case 'btpd':
        $("#config_downloaddir,#config_watchdir,#config_savetorrent,#config_deepdir,#config_verifyepisodes,div.favorite_savein").css("display","block");
        $("div.favorite,div.favinfo").css("height",205);
      break;
      case 'nzbget':
        $("#config_watchdir,#config_verifyepisodes").css("display","block");
        $("#config_downloaddir,#config_savetorrent,#config_deepdir,div.favorite_savein").css("display","none");
        $("div.favorite,div.favinfo").css("height",180);
      break;
      case 'sabnzbd':
        $("#config_downloaddir,#config_watchdir,#config_savetorrent,#config_deepdir,div.favorite_savein").css("display","none");
        $("#config_verifyepisodes").css("display","block");
        $("div.favorite,div.favinfo").css("height",180);
      break;
    }
  });
  $("select#client").change();
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

function submitForm ( whichForm )
{
	document.getElementById(whichForm).submit();
}
