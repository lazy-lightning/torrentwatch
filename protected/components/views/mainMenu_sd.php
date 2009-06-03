<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="179" height="144" valign="top" align="right">
      <table border="0" cellspacing="0" cellpadding="0">
        <tr><td height="90"></td></tr>
        <tr>
          <td width="164" valign="middle" align="right"><a href="javascript: history.go('<?php echo Yii::app()->request->baseUrl; ?>')" name="home" tvid="home"></a></td>
          <td width="15" height="25" valign="middle" align="left"></td>
        </tr>
        <tr>
          <td width="164" valign="middle" align="right"></td>
          <td width="15" height="25" valign="middle" align="left"></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td height="69" align="right" valign="bottom"><img src="<?php echo $imageRoot.$icon; ?>" width="90" height="69"></td>
  </tr>
  <tr>
    <td align="right" height="20" valign="top" class="server">// <?php echo Yii::app()->name; ?></td>
  </tr>
  <tr><td align="right"><img src="<?php echo $imageRoot; ?>line_side.png" width="86" height="29"></td></tr>
  <tr><td height="10"></td></tr>
  <tr>
    <td width="179" align="right" >
      <table border="0" cellspacing="0" cellpadding="0">
        <?php foreach($items as $item): ?>
          <tr>
            <td valign="middle" align="right" width="164">
              <?php echo CHtml::link($item['label'], $item['url'], array(
                      'class'=>'menu',
                      'name'=>$item['name'],
                      'onkeyrightset'=>$fileFirst,
                      'onkeyleftset'=>isset($last)?$last['name']:$item['name'],
                      'onkeyupset'=>isset($last)?$last['name']:$item['name'],
                      'style'=>'width:120'
                    )); ?>
            </td>
            <td valign="middle" align="right" width="15" height="30">
              <img src="<?php echo $imageRoot; ?>rect.png" width="12" height="12">
            </td>
          </tr>
        <?php $last=$item;endforeach; ?>
      </table>
    </td>
  </tr>
</table>

