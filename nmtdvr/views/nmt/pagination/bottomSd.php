<tr><td height=15  align=right><font size=1 color=user2>
  <b>
    <?php if($total_items != 1) 
      echo $current_first_item." - ".$current_last_item." / ".$total_items." ".$item_type ?>
  </b>
</td></tr>
<tr><td>
  <?php if($total_items != 1): ?>
    <?php if(!$next_page && $first_page) $next_page = $first_page;
    if($next_page): ?>
      <a href="<?php echo str_replace('{page}', $next_page, $url) ?>" onfocusload tvid="pgdn"><font size="1">&nbsp;</font></a>
    <?php endif ?>
  <?php endif ?>
</td></tr>

