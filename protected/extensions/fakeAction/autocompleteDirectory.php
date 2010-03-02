<?php
error_reporting(E_ALL|E_STRICT);

if(!isset($_GET['q'])) {
  echo "Bad query";
  exit;
}

$dir = $_GET['q'];
$limit = isset($_GET['limit']) ? $_GET['limit'] : 150;

if(!is_dir($dir))
  $dir = substr($dir, 0, strrpos($dir, '/')).'/';
if(!is_dir($dir)) {
  echo "&nbsp;Invalid Directory";
  exit;
}

$dh = opendir($dir);
$out = array($dir);
$n = 1;
while(false !== ($file = readdir($dh)))
{
  if($file[0] === '.')
    continue;
  $path = rtrim($dir, '/').'/'.$file;
  if(is_dir($path))
    $out[] = $path;
  if(++$n >= $limit)
    break;
}
if(count($out))
  echo implode("\n", $out);
else
  echo "No Results Found";
