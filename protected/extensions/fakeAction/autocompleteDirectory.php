<?php
error_reporting(E_ALL|E_STRICT);

if(!isset($_GET['q'])) {
  echo "Bad query";
  exit;
}

$dir = $_GET['q'];

if(!is_dir($dir))
  $dir = substr($dir, 0, strrpos($dir, '/')).'/';
if(!is_dir($dir)) {
  echo "&nbsp;Invalid Directory";
  exit;
}
$dir = rtrim($dir, '/').'/';
$dh = opendir($dir);
$out = array($dir);
$n = 1;
$limit = isset($_GET['limit']) ? $_GET['limit'] : 150;
while(false !== ($file = readdir($dh)))
{
  if($file[0] === '.')
    continue;
  $path = $dir.$file;
  if(!is_dir($path))
    continue;
  $out[] = $path;
  if(++$n >= $limit)
    break;
}

echo implode("\n", $out);
