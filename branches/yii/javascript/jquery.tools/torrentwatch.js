
$(function() {
    // Ajax load of pane content
    var onBeforeLoad = function(i) {
        var pane = this.getPanes().eq(i);
        pane.is(":empty") && pane.load(this.getTabs().eq(i).attr("href"));
    };
    // Tabbed feed items list
    $("#feedItems_container > ul").tabs('#feedItems_container > div', onBeforeLoad);

    // Various buttons that load an external page into an overlay
    // only works on anchors loaded from the main index.html
    $("a[rel]").overlay({
        expose: '#cdcdcd',
        onBeforeLoad: function() { 
          var wrap = this.getContent().find("div.wrap"); 

          if(wrap.children("ul.tabs-nav.delayLoad").length) {
            wrap.children("ul").tabs(wrap.selector+' div', onBeforeLoad);
          } else if (wrap.is(":empty")) { 
              wrap.load(this.getTrigger().attr("href")); 
          } 
        }
    });
 
    // UL's whose list items point to dynamically loaded content
    // each li must have an anchor element with href and ref attributes
    // if no ref specified content will replace parent li
    $('ul.loadContent li:not(.loaded)').live('click', function(e) {
      var a = $(this).children('a');
      var rel = a.attr('rel') || this;
      $(rel).load(a.attr('href'), function() { $(rel).addClass('loaded'); });
      e.preventDefault();
    });
});
