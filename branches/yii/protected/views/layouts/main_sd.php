<html>
	<head>
		<title><?php echo $this->pageTitle; ?></title>
   	<meta http-equiv="content-type" content="text/html; charset=utf-8">
    <style type="text/css">
      .menu {font-size:22px;color:user1;font-weight:bold;}
      .server {font-size:20px;color:user2;font-weight:bold;}
      .pagination {font-size:18px;color:user2;font-weight:bold;}
      .list {font-size:20px;color:user1;font-weight:bold;}
    </style>
	</head>
  <body bgcolor="#0A446F" focushighlight=user5 focustext=user3 background="file:///opt/sybhttpd/localhost.images/sd/bg.jpg" onloadset="$FILE_FIRST$" $MARGINHEIGHT$ $FILE_MANAGER$>
	 <table border="0" cellspacing="0" cellpadding="0">
	 	<tr>
	 		<td valign="top">
        <?php $this->widget('application.components.MainMenu', array(
              'resolution' => 'sd',
              'items' => array(
                 array('label'=>'Media Sources', 'url'=>'http://localhost:8883/start.cgi', 'name'=>'media'),
                 array('label'=>'Favorites', 'url'=>array('favorite/list'), 'name'=>'favorites'),
                 array('label'=>'Configure', 'url'=>array('dvrConfig/list'), 'name'=>'config'),
               ),
              )); ?>
	 		</td>
	 		<td height="405" width="38" background="file:///opt/sybhttpd/localhost.images/sd/divider.png"></td>
      <td width="380" valign="top">
        <?php echo $content; ?>
	 		</td>
	 	</tr>
	 </table>
  </body>
</html>

