
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
      container[0].innerHTML = html;
      setTimeout(function() {
        container.find('div#feedItems_container').children().each(function() {
          var dest = $('#'+this.id);
          if(!dest.is('ul'))
            dest = dest.children('ul');

          $(this)
          .find("li.torrent").myContextMenu()
          .filter(".hasDuplicates").initDuplicates();
          
          dest.find('li.loadMore').remove().end()
          .append($(this).children('ul').children('li'));
        });
      }, 0);
    };
    // Remove old dynamic content, replace it with passed html(ajax success function)
    $.loadDynamicData = function(html) {
        $("#feedItems_container").children('div')
          .each(function() {
            $(this).empty();
          }).end()
          .find('.tabs-selected')
          .removeClass('tabs-selected')
          .children("a")
          .click();
        
        $("#dynamicdata").remove();
        setTimeout(function() {
            $(current_dialog).toggleDialog();
            current_dialog = '';
            var dynamic = $("<div id='dynamicdata'/>");
            // Use innerHTML because some browsers choke with $(html) when html is many KB
            dynamic[0].innerHTML = html;
            dynamic.find("div#favorites").initFavorites().end()
                    .find("form").initForm().end()
                    .find("div#configuration").initConfigDialog().end()
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
            var hide = false, show = false;

            current_dialog = last === target ? '' : this.hash;
            if (last) {
                $(last).fadeOut();
                hide = true;
            }
            if (current_dialog && this.hash != '#') {
                show = true;
                var dialog = $(current_dialog);
                var callback = function() { 
                  dialog.fadeIn();
                  if(!dialog.find('div.close').length)
                    dialog.prepend('<div class="close"></div>');
                };
                if(!dialog.length) {
                  $.get(this.href, '', function(html) {
                    $('#dynamicdata').append(html);
                    setTimeout(callback, 0);
                  }, 'html');
                } else 
                  callback();
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
          var ul = li.children("ul");
          var callback = null;
          var onDone = function() { ul.slideToggle(400, callback); };

          if(li.hasClass('open'))
            callback = function() { li.removeClass('open'); };
          else
            li.addClass('open');

          if(ul.length == 0) {
            $.get('nmtdvr.php', 'r=feedItem/list&filter='+encodeURIComponent(li[0].id), function(html) {
              li.append(html);
              setTimeout(onDone,0);
            }, 'html');
          } else
           onDone();

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
            var onDone = function() {
              $(current_favorite).show();
            };
            if(last)
              $(last).hide();
            if($(current_favorite).length == 0) {
              var tabs = $(this).closest('.tabs-container');
              $.get('nmtdvr.php?r=ajax/loadFavorite&id='+current_favorite.substr(1), null, function(html) {
                  tabs.append(html);
                  onDone();
                  }, 'html');
            } else 
              onDone();
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
                    $.get(url, '', $.loadDynamicData, 'html');
                },
                'hideShow': function(t) {
                    url = 'nmtdvr.php?r=ajax/hideTvShow&feedItem_id='+$(t).find(".itemId").val();
                    $.get(url, '', $.loadDynamicData, 'html');
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

//
//
// Everything below here initializes the page on first load
//
//

$(function() { 
    // Menu Bar, and other buttons which show/hide a dialog
    $("a.toggleDialog").live('click', function() {
        $(this).toggleDialog();
        return false;
    });
    // X button on all dialogs
    $("div.close").live('click', function() {
        $(this).closest('.dialog_window').toggleDialog();
        return false;
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
      $('.dialog_window:visible:not(#favorites,#history)').toggleDialog();
      $(this).show();
      $('div.expose').show();
    }).ajaxStop(function() {
      $(this).hide();
      if($('.dialog_window:visible:not(#progressbar)').length == 0) 
        $('div.expose').hide();
    }).ajaxError(function(event, XMLHttpRequest, ajaxOptions, thrownError){
      var content;
      if(XMLHttpRequest.responseText === '')
        content = '<p>NMTDVR has errored in an untraceable manner</p>';
      else
        content = XMLHttpRequest.responseText;

      $(this).unbind('ajaxStop')
             .find('.content')
             .empty()
             .append(content);
    });
    // Perform the first load of the dynamic information
    $.get('nmtdvr.php?r=ajax/fullResponse', '', $.loadDynamicData, 'html');
    $("#feedItems_container").tabs({ 
        remote: true,
        onShow: function(clicked, toShow, toHide) {
          $("li.torrent:not(.initialized)", toShow)
          .addClass('initialized')
          .myContextMenu()
          .filter(".hasDuplicates")
          .initDuplicates();
        },
    });

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
