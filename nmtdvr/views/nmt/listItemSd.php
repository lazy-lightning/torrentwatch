<?php foreach($links as $title => $link): ?>
  <tr><td>
    <table border="0" cellspacing="0" cellpadding="0"
           background="<?php echo $imageRoot ?>/list_bar.png">
      <tr>
        <td width="40" height="35" align="right">
          <font size="2" color=user1>
            <b><?php echo $link['index']; ?></b>
          </font>
        </td>
        <td width="40" align="right">
          <img src="<?php echo $imageRoot.$link['icon'] ?>.png" width="35" height="25">
        </td>
        <td width="290">
          <?php echo "<a href='{$link['href']}' name='{$link['index']}' onkeyleftset='$onkeyleft' ".
                     "   onkeyrightset={$link['index']} tvid='{$link['index']}' >" ?>
            <font size="2" color=user1>
              <b><marquee behavior=focus width=290>&nbsp;&nbsp;<?php echo $title ?></marquee></b>
            </font>
          </a>
        </td>
        <td width="10"></td>
        </tr>
      </table>
    </td></tr>
  <tr><td height="2"></td></tr>
<?php endforeach ?>

