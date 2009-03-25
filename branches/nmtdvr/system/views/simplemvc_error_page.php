<?php defined('SYSPATH') or die('No direct access allowed'); ?>
<html>
  <head>
   <title>Error</title>
  </head>
  <body>
    <div id="framework_error">
      <h3>Oops.  Seems to be a problem here.</h3>
      <?php if(!empty($line) AND !empty($file)): ?>
      <p><?php echo "Error Occured in $file on line $line" ?></p>
      <?php endif ?>
      <p>Error of type: <?php echo $type ?></p>
      <p><code class="block"><?php echo $code ?></code></p>
    </div>
  </body>
</html>
 
