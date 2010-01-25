
(function($) {
    var current_favorite, inspect_status;
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

          $(this).find("li.torrent.hasDuplicates").initDuplicates();
          
          dest.find('li.loadMore').remove().end()
          .append($(this).children('ul').children('li'));
        });
      }, 0);
    };
    // Remove old dynamic content, replace it with passed html(ajax success function)
    $.loadDynamicData = function(html) {
      // Reset the feed items container and click the selected link to trigger reload
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
            // Close any open dialog
            $(window.current_dialog).toggleDialog();
            window.current_dialog = '';
            var dynamic = $("<div id='dynamicdata'/>");
            // Use innerHTML because some browsers choke with $(html) when html is many KB
            dynamic[0].innerHTML = html;
            dynamic .find("form").initForm().end()
                    .find("div#configuration").initConfigDialog().end()
                    .appendTo("body");
            setTimeout(function() {
                var container = $("#feedItems_container > div:first");
                if(container.children('ul').children().length == 1 && $(".login_form").length == 0 &&
                   window.showWelcomeScreen) {
                    window.current_dialog = '#welcome1';
                    $(window.current_dialog).show();
                    window.showWelcomeScreen = false;
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
        if(window.current_dialog)
          $(window.current_dialog).toggleDialog();

        $.post(form.get(0).action, form.buildDataString(button), $.loadFormUpdate, 'html');
    }; 
    $.loadFormUpdate = function(html) {
      var data = $(html);
      var id = '#'+data.filter('form')[0].id;
      $(id).replaceWith(data.filter('form'));
      setTimeout(function() {
          $(id).initForm().show();
//        $('body').append(data.filter('script')); 
          $.showDialog('#favorites');
      }, 50);
    };
    $.fn.toggleDialog = function() {
        this.each(function() {
            var last = window.current_dialog === '#' ? '' : window.current_dialog;
            var target = this.hash === '#' ? '#'+$(this).closest('.dialog_window').id : this.hash;
            var hide = false, show = false;

            window.current_dialog = last === target ? '' : this.hash;
            if (last) {
                $(last).fadeOut();
                hide = true;
            }
            if (window.current_dialog && this.hash != '#') {
                show = true;
                var dialog = $(window.current_dialog);
                var callback = function() { 
                  dialog.fadeIn();
                  if(!dialog.find('div.close').length)
                    dialog.prepend('<div class="close"></div>');
                  var tabs = dialog.find('.tabs-container');
                  if(tabs.length != 0 && tabs.filter('.tabs-hide').children().length == 0)
                    dialog.find('.tabs-nav .tabs-selected').removeClass('tabs-selected').find('a').click();
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
        $.get('nmtdvr.php', 'r=feedItem/list&filter='+encodeURIComponent(li[0].id), function(html) {
          li.replaceWith(html);
          $('li.torrent').markAlt();
        }, 'html');
      });
      return this;
    };

    $.fn.initFavorites = function() {
      return this.children('.content').tabs({ 
          fxAutoHeight: true , 
          remote: true, 
          onShow: function(clicked, toShow, toHide) {
            $(toShow).find('form').initForm().end()
                     .addClass('clearfix').find("ul.favorite > li").not(":first").tsort("a");
          },
      }).end();
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
              $.get(this.href, null, function(html) {
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
    $.fn.initConfigDialog = function() {
        // initialize the tabs
        this.children('.content').tabs({fxAutoHeight: true });
        // setup the auto switch of form information for client tabs
        this.find('.client_config select').change(function() {
            $(this).closest('.client_config').find('.config').hide().end()
              .find('#'+$(this).val()).show();
        });
        // First trigger of change() will hide the unselected client forms
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
      return this.removeClass('alt').removeClass('notalt').filter(":visible")
        .filter(":even").addClass('alt').end()
        .filter(":odd").addClass('notalt');
    };
})(jQuery);

//
//
// Everything below here initializes the page on first load
//
//

$(function() { 
    // Handle button click events
    $("body").live('click', function(e) {
      if(e.button != 0)
        return;
      var target = $(e.target);
      // Menu Bar, and other buttons which show/hide a dialog
      if(target.is('a.toggleDialog')) {
        target.toggleDialog();
        return false;
      }
      // X button on all dialogs
      if(target.is('div.close')) {
        target.closest('.dialog_window').toggleDialog();
        return false;
      }
      // Configuration, wizard, and update/delete favorite ajax submit
      if(target.is('a.submitForm,input.submitForm')) {
        $.submitForm(target[0]);
        return false;
      }
      // History details hide/reveal
      if(target.is("div#history li")) {
        target.find(".hItemDetails").slideToggle(600);
        return false;
      }
      // Clear History ajax submit
      if(target.is("a.historySubmit")) {
        $.get(this.href, '', function(html) {
          // $(html).html() is used to strip the outer tag(<div#history></div>) and get the children
          $("div#history").html($(html).html());
        }, 'html');
        return false;
      }
      // toggle visible favorite
      if(target.is('ul.favorite > li'))
        target = target.find("a");
      if(target.is('ul.favorite > li a')) {
        target.toggleFavorite();
        return false;
      }
      // Standard ajax submit with reload
      if(target.is("img") && target.parent().is("a.ajaxSubmit"))
        target = target.parent();
      if(target.is("a.ajaxSubmit")) {
        $.get(target[0].href, '', $.loadDynamicData, 'html');
        return false;
      }
      // Inspector
      if(target.is("li#inspector a")) {
        $.toggleInspector();
        return false;
      }
      // Feed Item Load More
      if(target.is("li.loadMore a")) {
        $.get(target[0].href, '', $.loadMoreFeedItems, 'html');
        return false;
      }
    });

    // Vary the font-size
    $("#config_webui select").live('change', function() {
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
        $("li.torrent").addClass('hidden_bytext').each(function() {
            var item = $(this).find("span:first");
            if (item.text().toLowerCase().match(filter)) {
                $(this).removeClass('hidden_bytext');
            }
        }).markAlt(); 
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
    
    window.showWelcomeScreen = true;
    // Perform the first load of the dynamic information
    $.get($('#fullResponseLink')[0].href, '', $.loadDynamicData, 'html');

    // Initialize the tabs which will also load dynamic information
    $("#feedItems_container").tabs({ 
        remote: true,
        onShow: function(clicked, toShow, toHide) {
          $("li.torrent:not(.initialized)", toShow)
          .addClass('initialized')
          .filter(".hasDuplicates")
          .initDuplicates();
        },
    });

    $("#favorites").initFavorites();
});
