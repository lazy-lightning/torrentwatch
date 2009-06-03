<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="5">
      <font size="1">&nbsp;</font>
    </td>
  </tr>
  <tr>
    <td width="380" height="25" align="right" valign="top">
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
            <td width="40" height="35" align="right"><font size="2" color=user1><b><?php echo $item['index']; ?></b></font></td>
            <td width="40" align="right">
              <img src="<?php echo $item['icon']; ?>" width="35" height="25">
            </td>
            <td width="290"> <!-- orrigionally a marquee, but the marquee has to be inside the label -->
              <?php echo CHtml::link($item['label'], $item['url'], array(
                      'class'=>'list',
                      'name'=>$item['name'],
                      'onkeyleftset'=>$sideBarFirstLink,
                      'onkeyrightset'=> $item['index'],
                      'tvid' => $item['tvid'],
                      'alt'=>$item['alt'],
                    )); ?>
            </td>
            <td width="10"></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr><td height="2"></td></tr>
  <?php endforeach; ?>
  <tr>
    <td height=15  align=right><font size=1 color=user2><b>&nbsp;</b></font></td>
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
