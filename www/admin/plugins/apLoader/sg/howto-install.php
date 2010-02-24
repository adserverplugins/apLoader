<?php
/*---------------------------------------------------------------------
/   This script will tell you how to install SourceGuardian loaders   /
/                  http://www.sourceguardian.com/                     /
---------------------------------------------------------------------*/

function ondebug(){
  echo "Your PHP engine is compiled in DEBUG mode.<BR>";
  echo "This mode was designed for debug PHP engine but not for production servers.<BR>";
  echo "You should install normal PHP engine which is available from ";
  echo '<A HREF="http://www.php.net/">http://www.php.net/</A>.';
}
function sws($pack,$ext_dir,$php_ini,$loader){
  echo '<ol style="margin-left: 2em">';
  echo '<li>Download the appropriate package:<ul><li>'.join('</li><li>', $pack).'</li></ul></li>';
  echo '<li>Extract it to folder <b>'.$ext_dir.'</b></li>';
  echo '<li>Edit PHP.INI file (located at <b>'.$php_ini.'</b>) and add following line:<br />';
  echo '<b>extension='.$loader.'</b></li>';
  echo '<li>Restart the webserver</li>';
  echo '</ol>';
}
function nosws($reason){
  echo 'Server-wide setup is not possible because of <b>'.$reason.'</b>.<br />';
}
function unknown_os(){
  echo 'Probably this operating system is not yet supported by SourceGuardian.<br />';
  echo 'Please visit support section at <a href="http://www.sourceguardian.com/">SourceGuardian home</a>.';
}
function get_pack_link($os_alias, $ext, $text = ''){
  return '<a href="http://www.sourceguardian.com/ixeds/ixed4.'.$os_alias.$ext.'">ixed4.'.$os_alias.$ext.' '.$text.'</a> ';
}

  $nl='<br />';

  $uname=php_uname();
  $os=substr($uname,0,strpos($uname,' '));
  $os_alias=strtolower(substr($uname,0,3));

  $zts=0;
  $php_ini='';
  $safe_mode=ini_get('safe_mode');
  $disable_functions=explode(',',ini_get('disable_functions'));
  $dl_disabled=(in_array('dl',$disable_functions) ||
                in_array('phpversion',$disable_functions) ||
                in_array('php_uname',$disable_functions) ||
                in_array('ini_get',$disable_functions) ||
                in_array('dirname',$disable_functions));
  $cur_dir=dirname(__FILE__);
  $debug=0;
  $php_ver = phpversion();
  $php_ver = substr($php_ver,0,strrpos($php_ver,'.'));

  $sapi=php_sapi_name();

  ob_start();
  phpinfo(INFO_GENERAL);
  $php_info=ob_get_contents();
  ob_end_clean();

  $info=explode("\n",$php_info);
  while (list($key,$val)=each($info)){
    if (preg_match('/command/i',$val)) continue;
    if (preg_match('/thread safety.*(enabled|yes)/i',$val)) $zts=1;
    if (preg_match('/debug.*(enabled|yes)/i',$val)) $debug=1;
    if (preg_match('/configuration file.*path.*(<\/B><\/td><TD ALIGN="left">| => |v">)([^ <]*)(.*<\/td.*)?/i', $val,$match)) {$php_ini=$match[2];}
  }

  if ($debug) {
    ondebug();
    exit();
  }

  $dl=1;
  $dl_reason='unknown';
  if ($zts){
    if ((substr($sapi,0,3)!='cgi') and ($sapi!="cli") and (substr($sapi,0,5)!='embed')) {
      $dl=0;
      $dl_reason='thread safety';
    }
  }

  if ($safe_mode) {
    $dl=0;
    $dl_reason='safe mode';
  }
  if ($dl_disabled) {
    $dl=0;
    $dl_reason='following functions should NOT be disabled - dl(), phpversion(), php_uname(), ini_get(), dirname()';
  }

  $ext=(($os_alias=='win') || ($os_alias=='dar'))?'.zip':'.tar.gz';
  $loader='ixed.'.$php_ver.(($zts)?'ts':'').'.'.$os_alias;
  $ext_dir=realpath(ini_get('extension_dir'));

  if (version_compare(PHP_VERSION, '5.2.4', '>')) {
    $dl=0;
    $dl_reason="Automatic loading is not possible on your machine.
To install the SourceGuardian loader on this machine you need to copy the $loader file to the $ext_dir directory (extension_dir).
No need for changing the php.ini PHP configuration file as dynamic loading is allowed and the loader will be used from extension_dir.";
  }

  if(!$ext_dir) {
    $dl = 0;
    $dl_reason = 'extension_dir does not exists';
    nosws($dl_reason);
  } else {
    switch($os_alias) {
      case 'win':
        $pack=array(
            get_pack_link($os_alias.'.vc6', $ext, '(for Intel x86 32-bit processor and PHP compiled with VC6)'),
            get_pack_link($os_alias.'.vc9', $ext, '(for Intel x86 32-bit processor and PHP compiled with VC9)'),
            get_pack_link($os_alias.'.vc9.x86-64', $ext, '(for Intel x86 64-bit processor and PHP compiled with VC9)')
        );
        sws($pack,$ext_dir,$php_ini,$loader);
        break;

      case 'lin':
      case 'fre':
      case 'sun':
      case 'net':
      case 'ope':
        $pack=array(
            get_pack_link($os_alias.'.x86-32', $ext, '(for Intel x86 32-bit processor)'),
            get_pack_link($os_alias.'.x86-64', $ext, '(for Intel x86 64-bit processor)')
        );
        sws($pack,$ext_dir,$php_ini,$loader);
        break;

      case 'dar':
        $pack=array(get_pack_link($os_alias, $ext));
        sws($pack,$ext_dir,$php_ini,$loader);
        break;

      default:
        unknown_os();
    }
  }
