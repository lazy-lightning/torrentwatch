<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="179" height="144" valign="top" align="right">
      <table border="0" cellspacing="0" cellpadding="0">
        <tr><td height="90"></td></tr>
       <tr>
         <td valign="middle" align="right" width="164">
           <a href="javascript: history.go('<?php echo $home ?>')" name="home" tvid="home"></a>
         </td>
         <td valign="middle" align="left" width="15" height="25"></td>
       </tr>
       <tr>
         <td valign="middle" align="right" width="164"></td>
         <td valign="middle" align="left" width="15" height="25"></td>
       </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td height="69" align="right" valign="bottom">
      <img src="<?php echo $imageRoot.$image; ?>.png" width="90" height="69">
    </td>
  </tr>
  <tr>
    <td align="right" height="20" valign="top">
      <font size="2" color=user2>
        <b>// <?php echo $heading; ?></b>
      </font>
    </td>
  </tr>
  <tr>
    <td align="right">
      <img src="<?php echo $imageRoot ?>line_side.png" width="86" height="29">
    </td>
  </tr>
  <tr><td height="10"></td></tr>
  <tr>
    <td width="179" align="right" >
      <table border="0" cellspacing="0" cellpadding="0">
        <?php foreach($links as $title => $link): ?>
          <?php if(!isset($onkeyup)) $onkeyup = $title; ?>
          <tr>
            <td valign="middle" align="right" width="164">
            <?php echo "<a href='$link' name='$title' onkeyrightset='$onkeyright' ".
                       "onkeyleftset='$title' onkeyupset='$onkeyup' style='width:120'>" ?>
                <font size="2" color=user1><b><?php echo $title ?></b></font>
              </a>
            </td>
            <td valign="middle" align="right" width="15" height="30">
              <img src="file:///opt/sybhttpd/localhost.images/sd/rect.png" width="12" height="12">
            </td>
          </tr>
          <?php $onkeyup = $title ?>
        <?php endforeach ?>
      </table>
    </td>
  </tr>
</table>

