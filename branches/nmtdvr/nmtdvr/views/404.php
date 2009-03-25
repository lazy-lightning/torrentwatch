<?php header('HTTP/1.1 404 File Not Found'); ?>
<html><head><title>404 Error</title></head>
<body>
<h2>The requested file could not be found</h2><br>
<?php if(!IN_PRODUCTION && isset($e)): ?>
  <pre><?php echo $e; ?></pre>
<?php endif; ?>
</body></html>

