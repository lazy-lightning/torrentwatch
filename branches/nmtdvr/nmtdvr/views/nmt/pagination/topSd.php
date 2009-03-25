<?php $prevItem = sprintf("%02d", $previous_page ? $current_first_item-1 : $total_items); 
      if(!$previous_page && $last_page) $previous_page = $last_page;
      if(!$next_page && $first_page) $next_page = $first_page;
      ?>
<tr>
  <td height="5">
  </td>
</tr>
<tr>
  <td width="380" height="25" align="right" valign="top">
    <table border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="30">
          <?php if($previous_page): ?>
            <a href="<?php echo str_replace('{page}', $previous_page, $url) ?>" tvid="pgup">
              <img src="<?php echo $imageRoot ?>up_on.png">
            </a>
          <?php else: ?>
            <img src="<?php echo $imageRoot ?>up_off.png">
          <?php endif ?>
        </td>
        <td width="30">
          <?php if($next_page): ?>
            <a href="<?php echo str_replace('{page}', $next_page, $url) ?>" tvid="pgdn">
              <img src="<?php echo $imageRoot ?>down_on.png">
            </a>
          <?php else: ?>
            <img src="<?php echo $imageRoot ?>down_off.png">
          <?php endif ?>
        </td>
      </tr>
    </table>
  </td>
</tr>
<tr><td height="1">
  <?php if ($previous_page): ?>
    <a href="<?php echo str_replace('{page}', $prev_page, $url).'#'.$prevItem?>" onfocusload>
      <font size="1">&nbsp;</font>
    </a>
  <?php endif; ?>
</tr></td>
