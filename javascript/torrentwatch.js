(function($) {
    var inspect_status;
    $.ajaxAppend = function(child, parent) {
      var wait = function() {
        if($(parent).length == 0)
          setTimeout(wait, 100);
        else {
          var old = $(parent).find(child);
          if(old.length)
            old.remove();
          $(child).remove().appendTo(parent);
        }
      }
      wait();
    }
    $.toggleInspector = function() {
        inspect_status = !inspect_status;
        $("div#feedItems_container,div#feedItems_container > div,ul#filterbar_container,div#inspector_container").stop(true,true).animate(
                { right: (inspect_status? '+' : '-') + "=350" },
                { duration: 600 }
        );
    };
    $.fn.tabsResetAjax = function () {
      // Reset the container and click the selected link to trigger reload
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
          oldForm = $(id.replace(/\d+$/,'')).hide().parent().append(form).end()[0].reset();
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
      data.not('script').addClass('dynamic-load').end().appendTo('body');
/*      setTimeout(function() {
        data.filter('script').each(function() {
          $.globalEval( this.text || this.textContent || this.innerHTML || "" );
        });
      },0); */
    };
    $.fn.hideExpose = function() {
      var $this = $(this);
      var wait = function() {
        if($this.is(':visible') && $('.dialog_window:visible').length == 0) {
          var feedItems = $("#feedItems_container");
          if(feedItems.hasClass('needsReset'))
            feedItems.removeClass('needsReset').tabsResetAjax();
          else
            $this.hide();
        }
        setTimeout(wait, 300);
      }
      wait();
    };
    // toggleDialog is a click handler for anchors 
    $.fn.toggleDialog = function() {
        var dialog;
        if(this.is('a') && this[0].hash)
            dialog = $(this[0].hash);
        else if(this.is('.dialog_window'))
            dialog = this
        else
            dialog = this.closest('.dialog_window');
        var dialogSelector = '#'+dialog[0].id;

        var toHide = $('.dialog_window:visible').not('.progressbar');
        var visible = dialog.is(':visible');

        var callback = function() { 
            $('div.expose').show();
            dialog = $(dialogSelector);
            dialog.fadeIn();
            // all dialogs must have a close button
            if(dialog.find('div.close').length == 0)
                dialog.prepend('<div class="close"></div>');
            // if tabs are initialized but the active one is empty trigger the ajax load
            var tabs = dialog.find('.tabs-container');
            if(tabs.length != 0 && tabs.filter('.tabs-hide').children().length == 0)
                dialog.find('.tabs-nav .tabs-selected').removeClass('tabs-selected').find('a').click();
        };
     
        if(dialog && !visible) {
            if(dialog.length == 0) {
                $.post(this.href, '', function(html) {
                    $.loadAjaxUpdate(html);
                    setTimeout(callback, 100);
                }, 'html');
            } else 
                callback();
        }
        toHide.fadeOut();
        return this;
    };
    $.fn.initDuplicates = function() {
      this.click(function() {
        var li = $(this);
        $.get('nmtdvr.php', 'r=feedItem/list&filter='+encodeURIComponent(li[0].id), function(html) {
          li.replaceWith($(html).children('li'));
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

    // Utility function called from ajax response javascript
    $.showTab = function(linkSelector) {
      var link = $(linkSelector);
      var dialog = link.closest('.dialog_window')[0];
      // click before show to prevent loading current tab
      // and newly selected tab via ajax
      link.click();
      $.showDialog('#'+dialog.id)
    }
    // Utility function called from ajax response javascript
    $.showDialog = function(hash) {
//      $('#mainoptions a[rel='+hash+']').toggleDialog();
      $('<a href="'+hash+'"/>').toggleDialog();
    };
    $.showFavorite = function(hash) {
      var selector = "a[rel='"+hash+"']";
      var wait = function() {
        var link = $(selector);
        var $hash = $(hash);
        // wait for link, and hash to exist.  Also wait for hash to be in a dialog_window
        if(link.length == 0 || $hash.length == 0 || $hash.parents('.dialog_window').length == 0)
          setTimeout(wait, 300);
        else {
          link.toggleFavorite();
        }
      }
      wait();
    };

    // Used to build the string to submit a form
    // Will add buttonElement as a form option
    $.fn.buildDataString = function(buttonElement) {
        var dataString = $(this).filter('form').serialize();
        if(buttonElement) {
            dataString += (dataString.length == 0 ? '' : '&' ) + 'button=' + buttonElement.id;
        }
        return dataString;
    };
    // marks the group of elements as alt/notalt
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
    // auto-hiding expose
    $('div.expose').hideExpose();
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
      if(target.closest("#history li").length)
        target = target.closest("#history li");
      if(target.is("#history li")) {
        target.children(".hItemDetails").slideToggle(300);
        return false;
      }
      // Clear History ajax submit
      if(target.is("a.historySubmit")) {
        $.get(this.href, '', function(html) {
          // $(html).html() is used to strip the outer tag(<div#history></div>) and get the children
          $("div#history").html($(html).html());
        }, 'html');
        e.returnValue = false;
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
        $.post(target[0].href, '', $.loadAjaxUpdate, 'html');
        return false;
      }
      // Inspector
      if(target.is("li#inspector a")) {
        $.toggleInspector();
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
        // If filter already selected do nothing
        if($(this).is('.selected'))
            return;
        // mark this filter selected, siblings as not seleceted
        $(this).addClass('selected').siblings().removeClass("selected");
        // Find out the type of filter
        var filter = this.id;
        // Find the active feed item container
        var container = $("div#feedItems_container > div:visible");
        // Hide the container while filtering
        container.slideUp(400, function() {
          // Get all the currently known about feed items
            var tor = $("li.torrent:not(.duplicate)").removeClass('hidden');
            switch (filter) {
            case 'filter_matching':
                // Hide
                tor.filter(".match_New, .match_Unmatched, .match_Auto").addClass('hidden');
                break;
            case 'filter_downloaded':
                // Hide all except the following classes
                tor.not('.match_Automatic, .match_Manual, .match_Failed').addClass('hidden');
                break;
            }
            // re-mark the torrents as alt/notalt
            tor.markAlt();
            // Display the container again
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
      },300);
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
        }
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
      }
    });
    // Initialize the 
    $("#favorites > .content").tabs({ 
      fxAutoHeight: true , 
      remote: true, 
      onShow: function(clicked, toShow, toHide) {
        $(toShow).addClass('clearfix');
      }
    });
});
