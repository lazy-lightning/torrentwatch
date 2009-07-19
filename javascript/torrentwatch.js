$(function() { 
    // Menu Bar, and other buttons which show/hide a dialog
    $("a.toggleDialog").live('click', function() {
        $(this).toggleDialog();
    });
    // Vary the font-size
    $("div#config_webui select").live('change', function() {
        var f = $(this).val();
        $.cookie('twFontSize', f);
        switch (f) {
        case 'Small':
            $("body").css('font-size', '75%');
            break;
        case 'Medium':
            $("body").css('font-size', '85%');
            break;
        case 'Large':
            $("body").css('font-size', '100%');
            break;
        }
    });
    // Filter Bar - Buttons
    $("ul#filterbar_container li:not(#filter_bytext)").click(function() {
        if($(this).is('.selected'))
            return;
        $(this).addClass('selected').siblings().removeClass("selected");
        var filter = this.id;
        var container = $("div#feedItems_container > div:visible");
        container.slideUp(400, function() {
            var tor = $("li.torrent:not(.duplicate)").removeClass('hidden');
            switch (filter) {
            case 'filter_matching':
                tor.filter(".match_New, .match_Unmatched, .match_Auto").addClass('hidden');
                break;
            case 'filter_downloaded':
                tor.not('.match_Automatic, .match_Manual, .match_Failed').addClass('hidden');
                break;
            }
            tor.markAlt();
            container.slideDown(400);
        });
    });
    // Filter Bar -- By Text
    $("input#filter_text_input").keyup(function() {
        var filter = $(this).val().toLowerCase();
        $("li.torrent:not(.duplicate)").addClass('hidden_bytext').each(function() {
            var item = $(this).find("span.torrent_name");
            if (item.text().toLowerCase().match(filter)) {
                $(this).removeClass('hidden_bytext');
            }
        }).markAlt(); 
    });
    // Switching visible items for different clients
    $("select#client").live('change', function() {
        $(".favorite_seedratio, #config_folderclient").css("display", "none");
        $("#torrent_settings").css("display", "block");
        var target = 'http://'+location.hostname;
        switch ($(this).val()) {
        case 'folder':
            $("#config_watchdir, #config_savetorrent, #config_deepdir, #torrent_settings, div.favorite_savein").css("display", "none");
            $("#config_folderclient, #config_downloaddir").css("display", "block");
            $("form.favinfo, ul.favorite").css("height", 166);
            target = '#';
            break;
        case 'transmission1.3x':
            $("#config_downloaddir, #config_watchdir, #config_savetorrent, #config_deepdir, div.favorite_seedratio, div.favorite_savein").css("display", "block");
            $("form.favinfo, ul.favorite").css("height", 214);
            target += ':9091/transmission/web/';
            break;
        case 'transmission1.22':
            $("#config_downloaddir, #config_deepdir, div.favorite_savein").css("display", "none");
            $("#config_watchdir, #config_savetorrent").css("display", "block");
            $("form.favinfo, ul.favorite").css("height", 166);
            target += ':8077/';
            break;
        case 'btpd':
            $("#config_downloaddir, #config_watchdir, #config_savetorrent, #config_deepdir, div.favorite_savein").css("display", "block");
            $("ul.favorite, form.favinfo").css("height", 190);
            target += ':8883/torrent/bt.cgi';
            break;
        case 'nzbget':
            $("#config_watchdir").css("display", "block");
            $("#config_downloaddir, #config_savetorrent, #config_deepdir, div.favorite_savein").css("display", "none");
            $("ul.favorite, form.favinfo").css("height", 166);
            target += ':8066/';
            break;
        case 'sabnzbd':
            $("#config_downloaddir, #config_watchdir, #config_savetorrent, #config_deepdir, div.favorite_savein,#torrent_settings").css("display", "none");
            $("ul.favorite, form.favinfo").css("height", 166);
            target += ':8080/sabnzbd/';
            break;
        }
        $("#webui a").text($(this).val())[0].href = target;
    });
    // Ajax progress bar
    $("#progressbar").ajaxStart(function() {
      $('.dialog_window:visible').toggleDialog();
      $(this).show();
      $('div.expose').show();
    }).ajaxStop(function() {
      $(this).hide();
      $('div.expose').hide();
    });
    // Perform the first load of the dynamic information
    $.get('nmtdvr.php?r=ajax/fullResponce', '', $.loadDynamicData, 'html');

    // Configuration, wizard, and update/delete favorite ajax submit
    $("a.submitForm,input.submitForm").live('click', function(e) {
        e.stopImmediatePropagation();
        $.submitForm(this);
    });
    // History details hide/reveal
    $("div#history li").live('click', function() {
        $(this).find(".hItemDetails").slideToggle(600);
        return false;
    });

    // Clear History ajax submit
    $("a.historySubmit").live('click', function() {
      $.get(this.href, '', function(html) {
          // $(html).html() is used to strip the outer tag(<div#history></div>) and get the children
          $("div#history").html($(html).html());
      }, 'html');
      return false;
    });
    // Standard ajax submit with reload
    $("a.ajaxSubmit").live('click', function() {
      $.get(this.href, '', $.loadDynamicData, 'html');
      return false;
    });
    // Inspector
    $("li#inspector a").click($.toggleInspector);
    // Feed Item Load More
    $("li.loadMore a").live('click', function() {
        $.get(this.href, '', $.loadMoreFeedItems, 'html');
        return false;
    });
  
});

(function($) {
    var current_favorite, current_dialog, inspect_status;
    $.toggleInspector = function() {
        inspect_status = !inspect_status;
        $("div#feedItems_container,div#feedItems_container > div,ul#filterbar_container,div#inspector_container").stop(true,true).animate(
                { right: (inspect_status? '+' : '-') + "=350" },
                { duration: 600 }
        );
    };
    $.loadMoreFeedItems = function(html) {
      var container = $("<div />");
      var dest = '';
      container[0].innerHTML = html;
      container.find('div#feedItems_container').children().each(function() {
        var dest = $('#'+this.id);
        $(this).find("li.torrent").myContextMenu()
               .filter(".hasDuplicates").initDuplicates();
        dest.find('li.loadMore').remove().end()
            .append($(this).children('ul'));
      });
    };
    // Remove old dynamic content, replace it with passed html(ajax success function)
    $.loadDynamicData = function(html) {
        $("#dynamicdata").remove();
        setTimeout(function() {
            $(current_dialog).toggleDialog();
            current_dialog = '';
            var dynamic = $("<div id='dynamicdata'/>");
            // Use innerHTML because some browsers choke with $(html) when html is many KB
            dynamic[0].innerHTML = html;
            dynamic.find("div#favorites").initFavorites().end()
                    .find("li.torrent").myContextMenu()
                      .filter(".hasDuplicates").initDuplicates().end().end()
                    .find("form").initForm().end()
                    .find("div#configuration").initConfigDialog().end()
                    .find("#feedItems_container").tabs().end()
                    .appendTo("body");
            setTimeout(function() {
                var container = $("#feedItems_container > div:first");
                if(container.children('ul').children().length == 1 && $(".login_form").length == 0) {
                    current_dialog = '#welcome1';
                    $(current_dialog).show();
                } else {
                    container.slideDown(400, function() {
                        if(inspect_status)
                            container.css('right', 350);
                    });
                }
            }, 50);
        }, 100);
    };
    $.submitForm = function(button) {
        var form;
        if($(button).is('form')) { // User pressed enter
            form = $(button);
            button = form.find('a');
            if(button.length == 0)
              button = form.find('input[type=submit]');
            button = button[0];
        } else
            form = $(button).closest("form");
        // close any open dialog
        if(current_dialog)
          $(current_dialog).toggleDialog();

        $.post(form.get(0).action, form.buildDataString(button), $.loadDynamicData, 'html');
    }; 
    $.fn.toggleDialog = function() {
        this.each(function() {
            var last = current_dialog === '#' ? '' : current_dialog;
            var target = this.hash === '#' ? '#'+$(this).closest('.dialog_window').id : this.hash;
            var hide = false;
            var show = false;
            current_dialog = last === target ? '' : this.hash;
            if (last) {
                $(last).fadeOut();
                hide = true;
            }
            if (current_dialog && this.hash != '#') {
                $(current_dialog).fadeIn();
                show = true;
            }
            if(hide && !show)
              $('div.expose').hide();
            if(!hide && show)
              $('div.expose').show();
        });
        return this;
    };
    $.fn.initDuplicates = function() {
      this.click(function() {
          var li = $(this);
          var hide = false;
          if(li.hasClass('open'))
            hide = true;
          else
            li.addClass('open');
          li.children("ul").slideToggle(400, function() {
            if(hide)
              li.removeClass('open');
          });
      });
      return this;
    };

    $.fn.initFavorites = function() {
      this.find("ul.favorite > li").not(":first").tsort("a").end().click(function() {
          $(this).find("a").toggleFavorite();
        });

      return this.children('.content').tabs({ fxAutoHeight: true }).find("input#favoriteMovies_rating").spin({ interval: 0.1, min: 0, max: 10 }).end().end();
    };

    $.fn.initForm = function() {
        this.submit(function(e) {
            $.submitForm(this);
            return false;
        });
        var f = $.cookie('twFontSize');
        if(f)
            this.find("#config_webui").val(f).change();
        return this;
    };
    $.fn.toggleFavorite = function() {
        this.each(function() {
            var last = current_favorite;
            current_favorite = this.hash;
            if (!last) {
                $(current_favorite).show();
            } else {
                $(last).fadeOut(600, function() {
                    $(current_favorite).fadeIn(600);
                });
            }
        });
        return this;
    };

    $.showFavorite = function(hash) {
      $('<a href="'+hash+'"/>').toggleFavorite();
    };

    $.showTab = function(hash) {
      var dialog = $(hash).closest('.dialog_window');
      $.showDialog('#'+dialog.get(0).id)
      dialog.find('ul.tabs-nav').find("a[href="+hash+"]").click();
    }

    $.showDialog = function(hash) {
      $('<a href="'+hash+'"/>').toggleDialog();
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
                    url = 'nmtdvr.php?r=ajax/addFavorite&feedItem_id='+$(t).find(".itemId").val();
                    $.get(url, '', $.loadDynamicData, 'html')
                },
                'startDownloading': function(t) {
                    url = 'nmtdvr.php?r=ajax/dlFeedItem&feedItem_id='+$(t).find(".itemId").val();
                    $.get(url); // Should make a responce and do something with it
                },
                'inspect': function(t) {
                    $.get('nmtdvr.php', 'r=ajax/inspect&feedItem_id='+$(t).find(".itemId").val()+'&title='+
                          encodeURIComponent($(t).find("span.torrent_name").text()), function(html) {
                        $("div#inspector_container").html(html);
                        if (!inspect_status) {
                            $.toggleInspector();
                        }
                    }, 'html');
                }
            }
        });
        return this;
    };
    $.fn.initConfigDialog = function() {
        this.children('.content').tabs({fxAutoHeight: true });
        this.find('.client_config select').change(function() {
            $(this).closest('.client_config').find('.config').hide().end()
              .find('#'+$(this).val()).show();
        }).change();
        setTimeout(function() {
            $('select#client').change();
        }, 500);
        return this;
    };
    $.fn.buildDataString = function(buttonElement) {
        var dataString = $(this).filter('form').serialize();
        if(buttonElement) {
            dataString += (dataString.length == 0 ? '' : '&' ) + 'button=' + buttonElement.id;
        }
        return dataString;
    };
    $.fn.markAlt = function() {
      return this.removeClass('alt').filter(":visible:even").addClass('alt');
    };
})(jQuery);

