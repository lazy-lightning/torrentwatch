<?php header('HTTP/1.1 500 Internal Server Error'); ?>
<html><head><title>500 Internal Server Error</title></head>
<body>
<h2>The application has experienced an Internal Server Error.</h2><br>
<?php if(!IN_PRODUCTION && isset($e)): ?>
  <pre><?php echo $e; ?></pre>
<?php endif; ?>
</body></html>

