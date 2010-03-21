<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="40">
      <a href="$FILE_PGUP$" $TVID_PGUP$></a>
      <a href="$FILE_PREV$" onfocusload><font size="1">&nbsp;</font></a>
    </td>
  </tr>
  <tr>
    <td width="730" height="30" align="right" valign="top">
      <table border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="30">&nbsp;</td>
          <td width="30">&nbsp;</td>
        </tr>
      </table>
    </td>
  </tr>
  <?php foreach($items as $item): ?>
    <tr>
      <td>
        <table border="0" cellspacing="0" cellpadding="0" background="<?php echo $this->imageRoot; ?>list_bar.png">
          <tr>
            <td width="70" height="46" align="right" class="list"><?php echo $item['index']; ?></td>
            <td width="80" align="right">
              <img src="<?php echo $item['icon']; ?>" width="45" height="35"></td>
            <td width="570">
              <?php echo CHtml::link($item['label'], $item['url'], array(
                      'class'=>'list',
                      'name'=>$item['name'],
                      'onkeyleftset'=>$this->mainMenuItems[0]['name'],
                      'onkeyrightset'=> $item['index'],
                      'tvid' => $item['tvid'],
                      'alt'=>isset($item['alt']) ? $item['alt'] : '',
                    )); ?>
            </td>
            <td width="10"></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr><td height="3"></td></tr>
  <?php endforeach; ?>
  <tr>
    <td height="35" align="right">
      <table border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td height="35" class="pagination">&nbsp;</td>
          <td width="10"></td>
        </tr>
        <tr>
          <td>
            <a href="$FILE_NEXT$" onfocusload $TVID_PGDN$><font size="1">&nbsp;</font></a>
            <a href="$FILE_FAST$" tvid=#$FILE_TOTAL$ onclick=history.go(-1)></a>
          </td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
