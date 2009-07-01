<html>
   <head>
     <title><?php echo $this->pageTitle; ?></title>
     <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <style type="text/css">
    .menu {font-size:22px;color:user1;font-weight:bold;}
    .server {font-size:20px;color:user2;font-weight:bold;}
    .pagination {font-size:18px;color:user2;font-weight:bold;}
    .list {font-size:20px;color:user1;font-weight:bold;}
    a {color: white;}
  </style>
  </head>
  <body bgcolor="#398EC4" marginwidth="0" marginheight="0" focushighlight=user5 focustext=user3 background="<?php echo $this->imageRoot; ?>bg.jpg" onloadset="$FILE_FIRST$" $FILE_MANAGER$>
   <table width="1100" border="0" cellspacing="0" cellpadding="0">
     <tr>
       <td width="270" valign="top">
        <?php $this->widget('application.components.MainMenu'); ?> 
       </td>
       <td height="656" width="100"><img src="<?php echo $this->imageRoot; ?>divider.png" width="93" height="656"></td>
       <td width="730" valign="top">
        <?php echo $content; ?>
       </td>
     </tr>
   </table>
  </body>
</html>
