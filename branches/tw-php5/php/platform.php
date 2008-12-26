<?php

if(file_exists("/etc/init_nmt"))
  $platform = "NMT";
else if(is_dir("/home/xbmc"))
  $platform = "XBMC";
else
  $platform = "Linux";

function platform_initialize() {
  global $platform;
  switch($platform) {
    case 'NMT':
      // PHP_SELF isn't properly set on the NMT
      $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
      break;
  }
}

function platform_install() {
  global $platform;
  switch($platform) {
    case 'NMT':
      symlink_force(platform_getInstallRoot(), platform_getWebRoot()."/torrentwatch");
      symlink_force("/mnt/syb8634/server/php5-cgi", "/usr/bin/php-cgi");
      break;
    case 'Linux':
    default:
      break;
  }
}
      
function platform_getHostName() {
  global $platform;
  if(TRUE) {
    exec('ifconfig eth0', $output, $return);
    if(preg_match('/inet addr:(\d+\.\d+\.\d+\.\d+)/', $output[1], $regs))
      return $regs[1];
  }
  switch($platform) {
    case 'NMT':
      return 'popcorn';
      break;
    case 'Linux':
    default:
      return file_get_contents('/etc/hostname');
    break;
  }
}

function platform_getConfigFile() {
  return platform_getUserRoot()."/torrentwatch.config";
}

function platform_getGunzip() {
  global $platform;
  switch($platform) {
    case 'NMT':
      if(file_exists('/bin/gunzip'))
        return "/bin/gunzip";
      else if(file_exists('/bin/busybox')) {
	exec('/bin/busybox gunzip 2>&1', $output);
	if($output[0] == 'busybox: applet not found')
          return FALSE;
        else
          return "/bin/busybox gunzip";
      }
      return FALSE;
    case 'Linux':
    default:
      if(file_exists('/bin/gunzip'))
        return "/bin/gunzip";
      return FALSE;
  }
}

function platform_getUserRoot() {
  global $platform;
  switch($platform) {
    case 'NMT':
      return "/share/.torrents";
      break;
    case 'XBMC':
      return "/home/xbmc/.torrents";
      break;
    case 'Linux':
    default:
      return "~/.torrents";  
      break;
  }
}

// this function is only called from platform_install()
function platform_getWebRoot() {
  global $platform;
  switch($platform) {
    case 'NMT':
      return "/opt/sybhttpd/default/";
      break;
    case 'Linux':
    default:
      return "/var/www";
      break;
  }
}  

function platform_getDownloadDir() {
  global $platform;
  switch($platform) {
    case 'NMT':
      return "/share/Download";
      break;
    case 'Linux':
    default:
      return "~/Download";
      break;
  }
}

function platform_getInstallRoot() {
  global $platform, $argv;
  return dirname(realpath($argv[0]));
}

function platform_getTransmissionRemote() {
  if(file_exists('/usr/bin/transmission-remote'))
    return '/usr/bin/transmission/remote';
  else if(file_exists('/mnt/syb8634/bin/transmission-remote'))
    return '/mnt/syb8634/bin/transmission-remote';
  return FALSE;
}

?>
