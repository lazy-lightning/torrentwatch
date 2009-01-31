var inspect_status = false;
function toggleInspector() {
    inspect_status = !inspect_status;
    $("div#torrentlist_container,ul#filterbar_container,div#inspector_container").stop(true,true).animate(
            { right: (inspect_status? '+' : '-') + "=350" },
            { duration: 600 }
    );
}

$(function() { 
    // Menu Bar, and other buttons which show/hide a dialog
    $("a.toggleDialog").live('click', function() {
        $(this).toggleDialog();
    });
    // Filter Bar - Buttons
    $("ul#filterbar_container li:not(#filter_bytext)").click(function() {
	$("ul#filterbar_container li").removeClass("selected");
	$(this).addClass("selected");
        var filter = this.id;
        $("div#torrentlist_container").slideUp(400, function() {
            switch (filter) {
            case 'filter_all':
                $("li.torrent").removeClass('alt').show().filter(":even").addClass('alt');
                break;
            case 'filter_matching':
                $("li.torrent").removeClass('alt').show().filter("li.match_nomatch").hide().end()
                        .filter(":not(li.match_nomatch)").filter(":even").addClass('alt');
                break;
            case 'filter_downloaded':
                $("li.torrent").removeClass('alt').hide().filter("li.match_cachehit").show()
                        .filter(":even").addClass('alt');
                break;
            }
            $("div#torrentlist_container").slideDown(400);
        });
    });
    // Filter Bar -- By Text
    $("input#filter_text_input").keyup(function() {
        var filter = $(this).val().toLowerCase();
        $("li.torrent").removeClass('alt').each(function() {
            if ($(this).find("span.torrent_name").text().toLowerCase().match(filter)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        }).filter(":visible:even").addClass('alt'); 
    });
    // Switching visible items for different clients
    $("select#client").live('change', function() {
        $(".favorite_seedratio").css("display", "none");
        switch ($(this).val()) {
        case 'transmission1.3x':
            $("#config_downloaddir, #config_watchdir, #config_savetorrent, #config_deepdir, div.favorite_seedratio, div.favorite_savein").css("display", "block");
            $("form.favinfo, ul.favorite").css("height", 214);
            break;
        case 'transmission1.22':
            $("#config_downloaddir, #config_deepdir, div.favorite_savein").css("display", "none");
            $("#config_watchdir, #config_savetorrent").css("display", "block");
            $("form.favinfo, ul.favorite").css("height", 166);
            break;
        case 'btpd':
            $("#config_downloaddir, #config_watchdir, #config_savetorrent, #config_deepdir, div.favorite_savein").css("display", "block");
            $("ul.favorite, form.favinfo").css("height", 190);
            break;
        case 'nzbget':
            $("#config_watchdir").css("display", "block");
            $("#config_downloaddir, #config_savetorrent, #config_deepdir, div.favorite_savein").css("display", "none");
            $("ul.favorite, form.favinfo").css("height", 166);
            break;
        case 'sabnzbd':
            $("#config_downloaddir, #config_watchdir, #config_savetorrent, #config_deepdir, div.favorite_savein").css("display", "none");
            $("ul.favorite, form.favinfo").css("height", 166);
            break;
        }
    }); 

    // Remove old dynamic content, replace it with passed html 
    var loadDynamicData = function(html) {
        $("#dynamicdata").remove();
        var dynamic = $("<div id='dynamicdata'></div>");
        dynamic[0].innerHTML = html;
        dynamic.find("ul.favorite > li").initFavorites().end().find("li.torrent").myContextMenu().end()
                .initConfigDialog().appendTo("body");
        $("#progressbar").hide();
    }; 
    // Load The Dynamic Information (feeds/favorites/history/config) 
    $.get('index.cgi', '', loadDynamicData, 'html');
    //  Configuration dialog ajax submit
    $("a#saveConfig").live('click', function() {
        $("#progressbar").show();
        var dataString = '';
        $("#configuration input,#configuration select").each(function() {
            dataString = dataString + this.name + '=' + encodeURIComponent(this.value) + '&';
        }); 
        dataString = dataString.substr(0, dataString.length - 1); 
        $.get('index.cgi', dataString, loadDynamicData, 'html');
    }); 
    // Clear History ajax submit
    $("a#clearhistory").live('click', function() {
      $("#progressbar").show();
      $.get('index.cgi/clearHistory', '', function(html) {
          $("#progressbar").hide();
          $("div#history").html($(html).html());
      }, 'html');
      return false;
    });
    // Update/Delete Favorite ajax submit
    $("form.favinfo a.submitForm").live('click', function() {
      $("#progressbar").show();
      var form =$(this).closest("form");
	
      $.get('index.cgi/updateFavorite', {
          idx: form.find("#idx").val(),
          name: form.find(".favorite_name input").val(),
          filter: form.find(".favorite_filter input").val(),
          not: form.find(".favorite_not input").val(),
          savein: form.find(".favorite_savein input").val(),
          episodes: form.find(".favorite_episodes input").val(),
          feed: form.find(".favorite_feed select").val(),
          quality: form.find(".favorite_quality input").val(),
          seedratio: form.find(".favorite_seedratio input").val(),
          button: this.id
        }, 
        loadDynamicData, 'html'
      );
    });
      
    // Inspector
    $("li#inspector a").click(toggleInspector);
  
});

(function($) {
    var current_favorite, current_dialog;
    $.fn.toggleDialog = function() {
        this.each(function() {
            var last = current_dialog;
            current_dialog = (last === this.hash ? '' : this.hash);
            if (last) {
                $(last).fadeOut();
            }
            if (current_dialog) {
                $(current_dialog).fadeIn();
            }
        });
        return this;
    };
    $.fn.initFavorites = function() {
        var selector = this.selector;
        this.not(":first").tsort("a").end().click(function() {
            $(this).find("a").toggleFavorite();
        });
        setTimeout(function() {
            $(selector + ":first a").toggleFavorite();
        }, 300);
        return this;
    };
    $.fn.toggleFavorite = function() {
        this.each(function() {
            var last = current_favorite;
            current_favorite = this.hash;
            if (!last) {
                $(current_favorite).show();
            } else {
                $(last).fadeOut(400, function() {
                    $(current_favorite).fadeIn(400);
                });
            }
        });
        return this;
    };
    $.contextMenu.defaults({
        menuStyle: {
            textAlign: "left",
            width: "160px"
        },
        itemStyle: {
            fontSize: "1.0em",
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
                    var link = $(t).find("a.context_link:last")[0];
                    $.get(link.href, '', function() {
                        $("#progressbar").hide();
                    });
                },
                'inspect': function(t) {
                    $("#progressbar").show();
                    if (!inspect_status) {
                        toggleInspector();
                    }
                    $.get('inspector.cgi', 'title=' + encodeURIComponent($(t).find("span.torrent_name").text()), function(html) {
                        $("div#inspector_container").html(html);
			$("#progressbar").hide();
                    }, 'html');
                }
            }
        });
        return this;
    };
    $.fn.initConfigDialog = function() {
        setTimeout(function() {
            $('select#client').change();
        }, 500);
        return this;
    };
})(jQuery);

