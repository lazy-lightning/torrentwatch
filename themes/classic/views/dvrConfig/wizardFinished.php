<div id='welcomeFinished' class='dialog_window welcome'>
  <div class='content clearFix'>
    <h2 class='dialog_heading'>Initial configuration is complete</h2>
    <p>
      Upon closing this dialog you will be presented with the media that was
      found from the feeds you have chosen.  The feed items will be grouped by
      type(ex: TV.Show.S04E02.HDTV and TV.Show.S04E02.720P will display as TV 
      Show S04E02).  Click on the item to see all the feed items it contains.
    </p>
    <p>
      Click the heart shaped button to make any item a favorite.  Once favorited it will be downloaded every
      an undownloaded episode matching the filters is seen in the feed.  Click the triangle next to the heart
      to start downloading without favoriting.  On the right side of Tv Episodes and Movies there is also a
      blue button which will display information detected from TheTvDB.com, TV.com and IMDb.com about this media item.
    </p>
    <div class='buttonContainer clearFix'>
      <?php echo CHtml::link('Back', array('wizardSettings'), array('class'=>'ajaxSubmit button')); ?>
      <a class='button toggleDialog' href='#'>Close</a>
    </div>
  </div>
</div>
<?php $this->widget('actionResponseWidget', array('showDialog'=>'#welcomeFinished'));
