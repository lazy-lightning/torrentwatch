<pre><?php var_dump($fav); ?></pre>
<tr height=400><td>
  <form action="../updateFavorite/<?php echo $fav->id;?>" type="GET">
    <table>
      <?php echo form::displayTextInput($fav, 'name'); ?>
      <?php echo form::displayTextInput($fav, 'filter'); ?>
      <?php echo form::displayTextInput($fav, 'not'); ?>
      <?php echo form::displayTextInput($fav, 'quality'); ?>
      <?php echo form::displayTextInput($fav, 'feed'); ?>
      <?php echo form::displayTextInput($fav, 'episodes'); ?>
      <?php echo form::displayTextInput($fav, 'saveIn'); ?>
      <?php echo form::displayTextInput($fav, 'seedRatio'); ?>
    </table>
  </form>
</td></tr>
