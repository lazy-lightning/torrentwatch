var inspect_status = false;
var toggleInspector = function() {
  $("div#torrentlist_container,div#filterbar_container").animate(
    { width:(inspect_status?'+':'-')+"=350" },
    { queue:false , duration:600 }
  );
  $("div#inspector_container").animate( { width:"toggle" }, 600);
  inspect_status = !inspect_status;
};

$(function() { 
  // Menu Bar
  $("li#favoritesMenu a,li#config a,li#view a,li#empty a").click(function() {$(this).toggleDialog();});
  // Filter Bar - Buttons
  $("ul#filterbar li a").click(function() {
    var filter = this.hash;
    $("div#torrentlist_container").slideUp(400, function() {
      switch(filter) {
      case '#filter_all':
       $("li.torrent").removeClass('alt').show().filter(":even").addClass('alt');break;
      case '#filter_matching':
        $("li.torrent").removeClass('alt').show().filter("li.match_nomatch").hide().end()
          .filter(":not(li.match_nomatch)").filter(":even").addClass('alt');
        break;
      case '#filter_downloaded':
        $("li.torrent").removeClass('alt').hide().filter("li.match_cachehit").show()
          .filter(":even").addClass('alt');break;
      }
      $("div#torrentlist_container").slideDown(400);
    });
  });
  // Filter Bar -- By Text
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
  // Wizard
  $("div.welcome a").click(function() { $(this).toggleDialog(); });

 // Switching visible items for different clients
  $("select#client").live('change', function() {
    $("div.favorite_seedratio").css("display","none");
    switch($(this).val()) {
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

  $("div#history a,div.favinfo form a,form#config_form a,form.feedform a,div#clear_cache a:first")
    .not("a#save,a#saveConfig").live('click', function() {
    $(this).toggleDialog();
  }); 
  // Configuration dialog ajax submit
  $("a#saveConfig").live('click', function() {
    $("#progressbar").show();
    var dataString='';
    $("#configuration input,#configuration select").each(function() {
      dataString = dataString+this.name+'='+encodeURIComponent(this.value)+'&';
    }); 
    dataString = dataString.substr(0,dataString.length-1); 
    $.ajax({
      type: "GET",
      url: 'index.cgi',
      cache: false,
      data: dataString,
      dataType: 'html',
      success: function(html,textStatus) {
        $("#configuration,#feeds").remove();
//        $(data).filter("#configuration,#feeds").initConfigDialog().appendTo("body"); 
        $("body").append(html).initConfigDialog();
        $("#progressbar").hide(); 
      } 
    });
  }); 
  // Inspector
  $("li#inspector a").click(toggleInspector);
  
  // Load The Dynamic Information (feeds/favorites/history/config) 
  $.get('index.cgi', '', function(html) {
    var dynamic = $("<div id='dynamicdata'></div>");
    dynamic[0].innerHTML = html;
    //$(html).find(".favorite > ul > li").initFavorites().end().find("li.torrent").myContextMenu().end()
    dynamic.find(".favorite > ul > li").initFavorites().end().find("li.torrent").myContextMenu().end()
    .initConfigDialog().appendTo("body");
    $("#progressbar").hide();
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
  $.fn.initFavorites = function() {
    this.not(":first").tsort("a")
    .end().find("a").click(function() {
      $(this).toggleFavorite();
    });
    var selector = this.selector;
    setTimeout(function() { $(selector+":first a").toggleFavorite();}, 300);
    return this;
  }
  $.fn.toggleFavorite = function() {
    this.each(function() {
      var last = current_favorite;
      current_favorite = this.hash
      if(!last) $(current_favorite).show();
      else $(last).fadeOut(400, function() {
        $(current_favorite).fadeIn(400);
      });
    });
    return this;
  };
  $.contextMenu.defaults({
      menuStyle: {
        textAlign: "left",
        width: "160px"
      },
      itemStyle: {
        fontSize: "1.3em",
        paddingLeft: "15px"
      }
  });
  $.fn.myContextMenu = function() {
    this.contextMenu("CM1", {
      bindings: {
        'addToFavorites': function(t) {
          location.replace($(t).find("a.context_link:first").get(0).href);
        },
        'startDownloading': function(t) {
          $("#progressbar").show();
          $.get('index.cgi', $(t).find("a.context_link:last").get(0).search.substr(1),function() {
            $("#progressbar").hide();
          });
        },
        'inspect': function(t) {
          if($.torrentWatch.inspect_status == false) toggleInspector();
          $("div#inspector_container").load('inspector.cgi?title='+$(t).find("div.torrent_name").text());
        }
      }
    });
    return this;
  };
  $.fn.initConfigDialog = function() {
    setTimeout(function() {$('select#client').change();}, 500);
    return this
  };
})(jQuery);

