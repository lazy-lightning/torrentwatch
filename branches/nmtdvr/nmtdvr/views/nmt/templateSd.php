<html>
  <head>
    <title><?php echo $title ?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
  </head>
  <body bgcolor="#0A446F" focushighlight=user5 focustext=user3 background="<?php echo $background ?>" 
        onloadset="<?php echo $onloadset ?>" >
    <table border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td valign="top">
          <?php echo $sidebar ?>
        </td>
        <td height="405" width="38" background="<?php echo $imageRoot ?>divider.png"></td>
        <td width="380" valign="top">
          <table border="0" cellspacing="0" cellpadding="0">
            <?php echo $paginationTop ?>
            <?php echo $content ?>
            <?php echo $paginationBottom ?>
            <tr><td>
              <a href="<?php echo $tvidRed; ?>" tvid="red"></a></td>
              <a href="<?php echo $tvidGreen; ?>" tvid="green"></a></td>
              <a href="<?php echo $tvidYellow; ?>" tvid="yellow"></a></td>
              <a href="<?php echo $tvidBlue; ?>" tvid="blue"></a></td>
            </td></tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>

