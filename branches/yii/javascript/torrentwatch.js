(function($) {
    var inspect_status;
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
    $.fn.tabsResetAjax = function () {
      // Reset the feed items container and click the selected link to trigger reload
      this.children('div')
        .each(function() {
          $(this).empty();
        }).end()
        .find('.tabs-selected')
        .removeClass('tabs-selected')
        .children("a")
        .click();
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

        $.post(form.get(0).action, form.buildDataString(button), $.loadFormUpdate, 'html');
    }; 
    $.loadFormUpdate = function(html) {
      var data = $(html);
      var form = data.filter('form');
      if(form.length) {
        var id = '#'+form.attr('id');
        // special handling for receiving a brand new item
        // origional form had id like: favoriteTvShow-
        // new form has: favoriteTvShow-23
        var oldForm = $(id);
        if(oldForm.length == 0) {
          oldForm = $(id.replace(/\d+$/,'')).hide();
          oldForm[0].reset()
          oldForm.parent().append(form);
        } else
          oldForm.replaceWith(form);
        setTimeout(function() {
          $(id).initForm().show();
          data.filter('script').each(function() {
            $.globalEval( this.text || this.textContent || this.innerHTML || "" );
          });
        }, 50);
      } else
        $.loadAjaxUpdate(data);
    };
    $.loadAjaxUpdate = function(html) {
      var data = $(html);
      data.each(function() { 
        if(!this.id) return;
        var target = $('#'+this.id);
        if(target.length) {
          target.after(this).remove();
          data = data.not(this);
          // trigger any onshow events
          // FIXME: feels like a bad hack
          var onShow = $('#'+this.id).closest('.tabs-container').parent().data('onShow')
          if(typeof onShow == 'function')
            onShow(null, this.parentNode, null);
        }
      });
      data.not('script').addClass('dynamic-load').appendTo('body');
      setTimeout(function() {
        data.filter('script').each(function() {
          $.globalEval( this.text || this.textContent || this.innerHTML || "" );
        });
      },0);
    };
    // toggleDialog is a click handler for anchors 
    window.current_dialog = '';
    $.fn.toggleDialog = function() {
        this.each(function() {
            var $this = $(this), dialog;
            if($this.is('a') && this.hash)
                dialog = $(this.hash);
            else if($this.is('.dialog_window'))
                dialog = $this
            else
                dialog = $this.closest('.dialog_window');

            var toHide = $('.dialog_window:visible');
            var visible = dialog.is(':visible');

            var callback = function() { 
                $('div.expose').show();
                dialog = $($this[0].hash);
                dialog.fadeIn();
                if(dialog.find('div.close').length == 0)
                    dialog.prepend('<div class="close"></div>');
                // if tabs are initialized but the active one is empty trigger the ajax load
                var tabs = dialog.find('.tabs-container');
                if(tabs.length != 0 && tabs.filter('.tabs-hide').children().length == 0)
                    dialog.find('.tabs-nav .tabs-selected').removeClass('tabs-selected').find('a').click();
            };
         
            if(dialog && !visible) {
                if(dialog.length == 0) {
                    $.get(this.href, '', function(html) {
                        $.loadAjaxUpdate(html);
                        setTimeout(callback, 100);
                    }, 'html');
                } else 
                    callback();
            } else if(visible)
                $('div.expose').hide();
            toHide.fadeOut();
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
    // click handler for anchors
    // will make the element found by rel visible
    // or if non-existant the page referenced by the anchor will
    // be loaded and appended to the closest tabs container
    $.fn.toggleFavorite = function() {
        var current_favorite = $(this).attr('rel'),
            onDone = function() {
          $(current_favorite).show();
        };
        $(this).closest('.tabs-container').children('.favinfo:visible').hide();
        if($(current_favorite).length == 0) {
          var tabs = $(this).closest('.tabs-container');
          $.get(this[0].href, null, function(html) {
              tabs.append(html);
              onDone();
              }, 'html');
        } else 
          onDone();
        return this;
    };

    $.showTab = function(hash) {
      var dialog = $(hash).closest('.dialog_window');
      $.showDialog('#'+dialog.get(0).id)
      dialog.find('ul.tabs-nav').find("a[href="+hash+"]").click();
    }

    $.showDialog = function(hash) {
      $('<a href="'+hash+'"/>').toggleDialog();
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
        $.get(target[0].href, '', $.loadAjaxUpdate, 'html');
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
    window.ajaxCount = 0;
    $("#progressbar").ajaxStart(function() {
      window.ajaxCount++;
      $(this).show();
      $('div.expose').show();
    }).ajaxStop(function() {
      window.ajaxCount--;
      var progress = $(this);
      setTimeout(function() {
        if(window.ajaxCount > 0) return;
        progress.hide();
        if($('.dialog_window:visible').not(progress[0]).length == 0) 
          $('div.expose').hide();
      },100);
    }).ajaxError(function(event, XMLHttpRequest, ajaxOptions, thrownError){
      var content;
      if(XMLHttpRequest.responseText === '')
        content = '<p>NMTDVR has errored in an untraceable manner</p>';
      else
        content = XMLHttpRequest.responseText;

      $(this).unbind('ajaxStop')
             .find('.content')
             .replaceWith(content);
    });
    
    window.showWelcomeScreen = true;
    // Initialize the feed items and open wizard if empty on first load
    $("#feedItems_container").tabs({ 
        remote: true,
        onShow: function(clicked, toShow, toHide) {
          $("li.torrent:not(.initialized)")
          .addClass('initialized')
          .filter(".hasDuplicates")
          .initDuplicates();
          if(window.showWelcomeScreen && clicked && !clicked.jquery) {
            if($(toShow).children('ul').children().length <= 1 && $(".login_form").length == 0) {
              $.get($('#wizardLink')[0].href, '', $.loadAjaxUpdate, 'html');
            }
            window.showWelcomeScreen = false;
          }
        },
    }).tabsResetAjax();

    $("#configuration .content").tabs({
      remote: true,
      onShow: function(clicked, toShow, toHide) {
        var show = $(toShow);
        show.find('form').initForm();
        if(show.children('.client_config').length) {
          // setup the auto switch of form information for client tabs
          show.find('.client_config select').change(function() {
            $(this).closest('.client_config').find('form').hide().end()
                .find('#'+$(this).val()).show();
          });
          // First trigger of change() will hide the unselected client forms
          setTimeout(function() { show.find('.client_config select').change(); }, 0);
        }
      },
    });
    // Initialize the 
    $("#favorites > .content").tabs({ 
      fxAutoHeight: true , 
      remote: true, 
      onShow: function(clicked, toShow, toHide) {
        $(toShow).addClass('clearfix');
      },
    });
});
