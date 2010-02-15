<div id="welcomeFeed" class="dialog_window welcome">
  <div class="content clearFix">
    <?php echo CHtml::form(array('wizardFeed'), 'post'); ?>
    <input type='hidden' name='feed[marker]' value='1'>
    <h2 class="dialog_heading">Choose a Feed</h2>
    <span class="item">
        NMTDVR requires an RSS feed from the internet that will point it to
        newly available media.  Choose a feed from the list or add your own.
    </span>
    <label class="category">Bit Torrent</label>
    <?php if($torFeed) echo CHtml::errorSummary($torFeed); ?>
    <div class="form_radio">
        <input type="radio" name="feed[torUrl]" value="http://rss.bt-chat.com/?group=3" /><label class="item">BT-Chat.com - EzTV</label>
        <label class="item">The EzTV feed from tvRSS.net</label>
    </div>
    <div class="form_radio">
        <input type="radio" name="feed[torUrl]" value="http://rss.bt-chat.com/?group=2" /><label class="item">BT-Chat.com - VTV</label>
        <label class="item">The VTV feed from tvRSS.net contains only the most popular tv shows</label>
    </div>
    <label class="category">NZB</label>
    <?php if($nzbFeed) echo CHtml::errorSummary($nzbFeed); ?>
    <div class="form_radio">
        <input type="radio" name="feed[nzbUrl]" value="http://www.tvnzb.com/tvnzb.rss" /><label class="item">TvNZB.com</label>
        <label class="item">TvNZB.com offers a feed of user submitted .nzb files</label>
    </div>
    <div class="buttonContainer clearFix">
        <a class="submitForm button" href="#">Next</a>
        <?php echo CHtml::link('Back', array('wizardClient'), array('class'=>'ajaxSubmit button')); ?>
        <a class="toggleDialog button" href="#">Close</a>
    </div>
    <?php echo CHtml::endForm(); ?>
  </div>
</div>
<?php $this->widget('actionResponseWidget', array('showDialog'=>'#welcomeFeed'));
