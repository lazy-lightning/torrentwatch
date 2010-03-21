(function ($) {
    // Wait for a selector to be present before triggering
    // given callback.  selector can also be a function whih
    // must return not false when the callback is ready to run
    $.waitFor = function (selector, callback) {
        var test = typeof selector === 'function' ? selector :
              function() { return $(selector).length; }, 
        wait = function () { test() ? callback() : setTimeout(wait, 200); };
        wait();
    };
    // append child to parent once parent exists in the dom
    // optionally delete an item from the dom first
    // needs wait for because sometimes the dialog that contains
    // the parent needs to be loaded first
    $.ajaxAppend = function (child, parent, deleteSelector, sortSelector) {
        $.waitFor(parent, function () {
            if (deleteSelector) {
                $(deleteSelector).remove();
            }
            $(child).remove().appendTo(parent);
            if(sortSelector) {
              $(sortSelector).not('li:first').tsort();
            }
        });
    };
    // open/close the inspector pane
    var inspect_status = false;
    $.showInspector = function() {
      if(!inspect_status) $.toggleInspector();
    };
    $.toggleInspector = function () {
        inspect_status = !inspect_status;
        $("#feedItems_container,#feedItems_container > div,#filterbar_container,#inspector_container").stop(true, true).animate(
                { right: (inspect_status ? '+' : '-') + "=350" },
                { duration: 600 }
        );
    };
    // reset all forms in the container
    // 
    $.fn.reset = function () {
        this.filter('form').find('errorSummary').remove().end().each(function () {
            this.reset(); 
        });
        return this;
    };
    // Reset the container and click the selected link to trigger reload
    // Used to reset a tabs container that has dynamic content
    $.fn.tabsResetAjax = function () {
        return this.children('div').empty().end()
          .find('.tabs-selected').removeClass('tabs-selected')
          .children("a").click().end().end();
    };
    // generic submit form handler
    $.submitForm = function (button) {
        var form = $(button).closest('form');
        form.find('input.gray').attr('value', '');
        $.post(form.get(0).action, form.serialize(), $.loadAjaxUpdate, 'html');
    }; 
    // handles almost all ajax responses(exceptions: ajax tabs, toggleFavorite).
    // If an element of same id as that in response exists it will be replaced
    // remaining elements will be attached to the document body.
    // Special handling occurs for handling forms to initialize them and reset
    // possible parent forms 
    $.loadAjaxUpdate = function (html) {
        var data = $(html), login = $('#login_form');
        // hack for login replacement
        if(login.length)
          login.replaceWith('<div id="tv_container" />')
        data.each(function () { 
            if (!this.id) { 
                return; 
            }
            var id = this.id, target = $('#' + id), oldForm, onShow;
            if ($(this).is('form')) {
              setTimeout(function () { 
                $('#' + id).initForm().show(); 
              }, 100);
              if (target.length === 0) {
                // could this be done in the view instead?
                oldForm = $('#' + id.replace(/\d+$/, '')).reset().hide();
              }
            }
            if (target.length) {
                // if replacing visible dialog, make the replacement visible
                if(target.is(".dialog_window:visible"))
                  $(this).show();
                target.replaceWith(this);
                data = data.not(this);
                // trigger any onshow events
                // FIXME: feels like a bad hack
                onShow = $('#' + this.id).closest('.tabs-container').parent().data('onShow');
                if (typeof onShow === 'function') {
                    onShow(null, this.parentNode, null);
                }
            }
        });
        data.not('script').addClass('dynamic-load').end().appendTo('body');
    };
    // Auto-hiding expose.  Elements acted upon will be hidden when
    // no dialog windows are visible.  Upon hide the feed items container
    // is checked to see if a reset is required.
    // FIXME: this was lazy
    $.fn.autoHideExpose = function () {
        var $this = $(this), wait = function () {
            if ($this.is(':visible') && $('.dialog_window:visible').length === 0) {
                var feedItems = $("#feedItems_container");
                if (feedItems.hasClass('needsReset')) {
                    feedItems.removeClass('needsReset').tabsResetAjax();
                } else {
                    $this.fadeOut();
                }
            }
        };
        setInterval(wait, 300);
    };
    // toggleDialog can be called on either an anchor or an element in
    // a dialog.  When called on an element in a dialog the dialog will be 
    // closed
    // When called on an anchor the element indicated as an id by the anchors hash
    // will be displayed.  If not available the href attribute of the anchor will
    // be queried and loaded with $.loadAjaxUpdate
    // Special handling is done when a dialog with tabs is loaded and the active
    // tab has no content.  In this case the tab is unselected and a click event
    // is raised.
    // Also adds a close button to each dialog
    $.fn.toggleDialog = function () {
        var dialog = (this.is('a') && this[0].hash) ? $(this[0].hash) :
          this.closest('.dialog_window'),
        dialogSelector = this[0].hash || ('#' + dialog[0].id),
        toHide = $('.dialog_window:visible:not(.progressbar)'),
        callback = function () {
            $('div.expose').not(':animated').fadeIn();
            dialog = $(dialogSelector).fadeIn();
            // all dialogs must have a close button
            if (dialog.find('div.close').length === 0) {
                dialog.prepend('<div class="close" />');
            }
            // if tabs are initialized but the active one is empty trigger the ajax load
            var tabs = dialog.find('.tabs-container');
            if (tabs.length !== 0 && tabs.filter('.tabs-hide').children().length === 0) {
                dialog.find('.tabs-selected').removeClass('tabs-selected').find('a').click();
            }
        };

        dialog.is(':visible') ? dialog.find('.saved').remove() :
          dialog.length !== 0 ? callback() : 
            $.get(this.attr('href'), '', function (html) {
              $.loadAjaxUpdate(html);
              callback();
            }, 'html');
        toHide.fadeOut();
        return this;
    };
    // Sets up forms to submit via $.submitForm instead of standard browser method
    $.fn.initForm = function () {
        return this.submit(function (e) {
            $.submitForm(this);
            return false;
        });
    };
    // click handler for anchors
    // will make the element found by rel visible
    // or if non-existant the page referenced by the anchor will
    // be loaded and appended to the closest tabs container child div
    $.fn.toggleFavorite = function () {
      var $this = $(this), toShow = $this.attr('rel'), tabs,
      onDone = function () {
        setTimeout(function() {
            $(toShow).initForm().show()
            .find('.favorite_saveIn')
            .children('input:not(.init)').addClass('init')
            .autocomplete('nmtdvr.php', {
                matchCase: true,
                extraParams: { f: 'autocompleteDirectory' } 
            });
          });
        };
        $this.closest('.tabs-container > div').children('.favinfo').hide()
          .find('.saved').remove();
        $(toShow).length > 0 ? onDone() :
          $.get(this[0].href, null, function (html) {
              $this.closest('.tabs-container div').append(html);
              onDone();
          }, 'html');
        return this;
    };

    // Utility function called from ajax response javascript
    // to make a tab and its parent dialog visible
    $.showTab = function (linkSelector) {
        $.showDialog('#' + $(linkSelector).click().closest('.dialog_window').attr('id'));
    };
    // Utility function called from ajax response javascript
    // to make a dialog visible.
    $.showDialog = function (hash) {
      setTimeout(function() {
        if($(hash).is(':not(:visible)'))
          $('<a href="' + hash + '"/>').toggleDialog();
      }, 100);
    };
    // Utility function called from ajax response javascript
    // to change the currently displayed favorite
    $.showFavorite = function (hash) {
        var selector = "a[rel='" + hash + "']";
        $.waitFor(function() {
            var $hash = $(hash);
            // wait for link, and hash to exist.  Also wait for hash to be in a dialog_window
            return !($(selector).length === 0 || $hash.length === 0 || $hash.parents('.dialog_window').length === 0);
        }, function() {
          $(selector).toggleFavorite();
        });
    }
    // marks the group of elements as alt/notalt
    $.fn.markAlt = function () {
        return this.removeClass('alt').removeClass('notalt').filter(":visible")
            .filter(":even").addClass('alt').end()
            .filter(":odd").addClass('notalt');
    };

//
//
// Everything below here initializes the page on first load
//
//

    $(function () { 
        var ajaxCount = 0, showWelcomeScreen = true;
        // auto-hiding expose
        $('div.expose').autoHideExpose();
        // Handle button click events
        $("body").live('click', function (e) {
            if (e.button !== 0) {
                return true;
            }
            var target = $(e.target);
            // Menu Bar, and other buttons which show/hide a dialog
            if (target.is('a.toggleDialog')) {
                target.toggleDialog();
                e.returnValue = false;
                return false;
            }
            // X button on all dialogs
            if (target.is('div.close')) {
                target.closest('.dialog_window').toggleDialog();
                e.returnValue = false;
                return false;
            }
            // Configuration, wizard, and update/delete favorite ajax submit
            if (target.is('a.submitForm,input.submitForm')) {
                $.submitForm(target[0]);
                e.returnValue = false;
                return false;
            }
            // toggle visible favorite
            if (target.is('ul.favorite > li')) {
                target = target.find("a");
            }
            if (target.is('ul.favorite > li a')) {
                target.toggleFavorite();
                e.returnValue = false;
                return false;
            }
            // History details hide/reveal
            if (target.closest("#history li").length) {
                target.closest("#history li").children(".hItemDetails").slideToggle(300);
                e.returnValue = false;
                return false;
            }
            // Clear History ajax submit
            if (target.is("a.historySubmit")) {
                $.get(this.href, '', $.loadAjaxUpdate, 'html');
                e.returnValue = false;
                return false;
            }
            // display update feed form
            if(target.is(".activeFeed"))
              target=target.find("a:not(.button)");
            // Standard ajax submit with reload
            if (target.is("img") && target.parent().is("a.ajaxSubmit")) {
                target = target.parent();
            }
            if (target.is("a.ajaxSubmit")) {
                $.post(target[0].href, '', $.loadAjaxUpdate, 'html');
                e.returnValue = false;
                return false;
            }
            // Loading related feedItems from owner types (tvepisode/movie/other)
            // needs to be near end to not override clickable items in the li
            if (target.closest('li.torrent.hasDuplicates').length) {
                target = target.closest('li.torrent.hasDuplicates');
            }
            if (target.is('li.torrent.hasDuplicates')) {
                $.get(target.find('a.loadDuplicates').attr('href'), '', function (html) {
                    target.replaceWith($(html).children('li'));
                    $('li.torrent').markAlt();
                }, 'html');
                return false;
            }
            // Inspector
            if (target.is("li#inspector a")) {
                $.toggleInspector();
                e.returnValue = false;
                return false;
            }
            return true;
        });
   
        // Auto-empty text fields with gray'd text
        $("input.gray").live('focusin', function() {
          $(this).filter('.gray').each(function() {
            $(this).data('gray', $(this).attr('value'))
              .attr('value', '');
          }).removeClass('gray').addClass('notgray');
        });
        $("input.notgray").live('focusout', function() {
          $(this).filter('.notgray').each(function() {
            var $t = $(this);
            if($t.attr('value') == '') {
              $t.removeClass('notgray').addClass('gray').attr('value', $t.data('gray'))
            }
          });
        });
        // Filter Bar - Buttons
        $("ul#filterbar_container li:not(#filter_bytext)").click(function () {
            // If filter already selected do nothing
            if ($(this).is('.selected')) { 
                return; 
            }
            // mark this filter selected, siblings as not seleceted
            $(this).addClass('selected').siblings().removeClass("selected");
            // Find out the type of filter
            var filter = this.id,
            // Find the active feed item container
                container = $("#feedItems_container > div:visible");
            // Hide the container while filtering
            container.slideUp(400, function () {
                // Get all the currently known about feed items
                var tor = $("li.torrent").removeClass('hidden');
                if (filter === 'filter_matching') {
                    // Hide the following classes
                    tor.filter(".match_New, .match_Unmatched, .match_Auto").addClass('hidden');
                } else if (filter === 'filter_downloaded') { 
                    // Hide all except the following classes
                    tor.not('.match_Automatic, .match_Manual, .match_Failed').addClass('hidden');
                }
                // re-mark the torrents as alt/notalt
                tor.markAlt();
                // Display the container again
                container.slideDown(400);
            });
        });
    
        // Filter Bar -- By Text
        $("input#filter_text_input").keyup(function () {
            var filter = $(this).val().toLowerCase();
            $("li.torrent").addClass('hidden_bytext').each(function () {
                if ($(this).find("span:first").text().toLowerCase().match(filter)) {
                    $(this).removeClass('hidden_bytext');
                }
            }).markAlt(); 
        });
   
        // prevent non-numeric input in certain spots
        $("input.numeric").live('keypress', function(e) {
            // KEY_0 = 48 KEY_9 = 57
            // BACK  = 8  DEL   = 0
            var key = e.which;
            if((key < 48 || key > 57) && key != 0 && key != 8) {
              // not numeric or delete
              e.preventDefault();
              e.returnValue = false;
              return false;
            }
        });
        // Ajax progress bar
        $("#progressbar").ajaxStart(function () {
            ajaxCount = ajaxCount + 1;
            $(this).show();
            $('div.expose').not(':animated').fadeIn();
        }).ajaxStop(function () {
            ajaxCount = ajaxCount - 1;
            var progress = $(this);
            setTimeout(function () {
                if (ajaxCount === 0) {
                    progress.hide();
                }
            }, 300);
        }).ajaxError(function (event, XMLHttpRequest, ajaxOptions, thrownError) {
            var content, dialog;
            if (XMLHttpRequest.responseText === '') {
                content = '<p>NMTDVR has errored in an untraceable manner</p>';
            } else {
                content = XMLHttpRequest.responseText;
            }
            $('<div id="ajaxError" class="dialog_window"><div class="content"></div></div>')
                .find('.content').html(content).end()
                .appendTo('body');
            $('#ajaxError').toggleDialog();
        });
        
        // Initialize the feed items and open wizard if empty on first load
        var 
          onShow = function (clicked, toShow, toHide) {
            if (showWelcomeScreen && clicked && !clicked.jquery) {
                if ($(toShow).children('ul').children().length <= 1 && $(".login_form").length === 0) {
                    $.get($('#wizardLink')[0].href, '', $.loadAjaxUpdate, 'html');
                }
                showWelcomeScreen = false;
            }
          },
          onHide = function(clicked, toShow, toHide) {
              $(toHide).find('.saved').remove();
          };
        $("#feedItems_container").tabs({ 
            remote: true,
            onHide: onHide,
            onShow: onShow
        });
        // grab initial value of feedItems_container
        var setPreloaded = function() {
          window.PRELOADED ?
            onShow(true, $("#remote-tab-1").append(window.PRELOADED), null) :
            setTimeout(setPreloaded,50);
        };
        setPreloaded();
        $("#configuration .content").tabs({
            remote: true,
            onHide: onHide,
            onShow: function (clicked, toShow, toHide) {
                var show = $(toShow);
                show.find('form').initForm();
                // Special handling for client configuration tabs
                // Could be handled from the view instead?
                if (show.children('.client_config').length) {
                    // setup the auto switch of form information for client tabs
                    show.find('.client_config select').change(function () {
                        $(this).closest('.client_config')
                          .find('form').hide().end()
                          .find('#' + $(this).val()).show();
                    }).change();
                }
            }
        });
        // Initialize the favorites tab, perhaps clearfix should be done directly in the view?
        $("#favorites > .content").tabs({ 
            fxAutoHeight: true,
            remote: true,
            onHide: onHide,
            onShow: function (clicked, toShow, toHide) {
                $(toShow).addClass('clearfix');
            }
        });
    });
}(jQuery));