var inspect_status = false;
function toggleInspector() {
    inspect_status = !inspect_status;
    $("div#torrentlist_container,ul#filterbar_container,div#inspector_container").stop(true,true).animate(
            { right: (inspect_status? '+' : '-') + "=350" },
            { duration: 600 }
    );
}
// Remove old dynamic content, replace it with passed html(ajax success function)
var loadDynamicData = function(html) {
    $("#dynamicdata").remove();
    var dynamic = $("<div id='dynamicdata'></div>");
    // Use innerHTML because some browsers choke with $(html) when html is many KB
    dynamic[0].innerHTML = html;
    dynamic.find("ul.favorite > li").initFavorites().end().find("li.torrent").myContextMenu().end()
            .initConfigDialog().appendTo("body");
    $("#progressbar").hide();
}; 

$(function() { 
    // Menu Bar, and other buttons which show/hide a dialog
    $("a.toggleDialog").live('click', function() {
        $(this).toggleDialog();
    });
    // Filter Bar - Buttons
    $("ul#filterbar_container li:not(#filter_bytext)").click(function() {
	if($(this).is('.selected'))
            return;
	$(this).addClass('selected').siblings().removeClass("selected");
        var filter = this.id;
        $("div#torrentlist_container").slideUp(400, function() {
            var tor = $("li.torrent").removeClass('hidden');
            switch (filter) {
            case 'filter_matching':
                tor.filter(".match_nomatch").addClass('hidden');
                break;
            case 'filter_downloaded':
                tor.not('.match_cachehit, .match_match').addClass('hidden');
                break;
            }
            tor.markAlt().closest("#torrentlist_container").slideDown(400);
        });
    });
    // Filter Bar -- By Text
    $("input#filter_text_input").keyup(function() {
        var filter = $(this).val().toLowerCase();
        $("li.torrent").addClass('hidden_bytext').each(function() {
            if ($(this).find("span.torrent_name").text().toLowerCase().match(filter)) {
                $(this).removeClass('hidden_bytext');
            }
        }).markAlt(); 
    });
    // Switching visible items for different clients
    $("select#client").live('change', function() {
        $(".favorite_seedratio").css("display", "none");
        $("#torrent_settings").css("display", "block");
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
            $("#config_downloaddir, #config_watchdir, #config_savetorrent, #config_deepdir, div.favorite_savein,#torrent_settings").css("display", "none");
            $("ul.favorite, form.favinfo").css("height", 166);
            break;
        }
    }); 
    // Perform the first load of the dynamic information
    $.get('index.cgi', '', loadDynamicData, 'html');

    //  Configuration, wizard, and update/delete favorite ajax submit
    $("a.submitForm").live('click', function() {
        $("#progressbar").show();
        var form = $(this).closest("form");
        $.get(form.get(0).action, form.buildDataString(this), loadDynamicData, 'html');
    }); 
    // Clear History ajax submit
    $("a#clearhistory").live('click', function() {
      $("#progressbar").show();
      $.get(this.href, '', function(html) {
          $("#progressbar").hide();
          $("div#history").html($(html).html());
      }, 'html');
      return false;
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
                    $("#progressbar").show();
                    $.get($(t).find("a.context_link:first").get(0).href, '', loadDynamicData, 'html')
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
    $.fn.buildDataString = function(buttonElement) {
        var dataString = '';
        this.find('input,select').each(function() {
            dataString += (dataString.length == 0 ? '' : '&' ) + this.name + '=';
            if(this.type == 'checkbox')
                dataString += (this.checked ? '1' : '0');
            else
                dataString += encodeURIComponent(this.value);
        }); 
        if(buttonElement) {
            dataString += (dataString.length == 0 ? '' : '&' ) + 'button=' + buttonElement.id;
        }
        return dataString;
    };
    $.fn.markAlt = function() {
      return this.removeClass('alt').filter(":visible:even").addClass('alt');
    };
})(jQuery);

