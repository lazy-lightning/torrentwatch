<?php
$isNMT = (false !== stristr($_SERVER['HTTP_USER_AGENT'], 'Syabas'));

Yii::import('application.components.allanMc.*');
// 10 rows and 8 columns
// height of 81 to 621 width from 176 to 1024
$grid = new Grid(10, 8, 81, 621, 176, 1024);
$blocks = array();

$length = count($tvepisodeList);
for($i = 0; $i < $length; $i++)
{
  $block = new tvEpisodeBlock($tvepisodeList[$i]);
  if(false === $grid->addBlock($block))
    break;
  $blocks[] = $block;
}
unset($block);

// buffer output so it can be stored and reused in the show view
ob_start();
?>
<!-- US TV Show Guide v1.0 -->
<!-- AllanMC Copyright © 2009, All Right Reserved -->
<html>
<head>
  <title>US TV Show Guide</title>
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta SYABAS-FULLSCREEN>
  <meta SYABAS-PLAYERMODE="msp">
  <meta SYABAS-BACKGROUND="themes/gaya_HD/images/background-new.jpg">
<script type="text/javascript">
  function popup() {
    if (document.styleSheets[1].cssRules[0].style.visibility=='hidden') {
      document.styleSheets[1].cssRules[0].style.visibility="visible";
      document.styleSheets[1].cssRules[1].style.visibility="visible";
      document.styleSheets[1].cssRules[2].style.visibility="visible";
      document.styleSheets[1].cssRules[3].style.visibility="visible";  
      document.styleSheets[1].cssRules[4].style.visibility="visible";
      document.styleSheets[1].cssRules[5].style.visibility="visible";
      document.styleSheets[1].cssRules[6].style.visibility="visible";
      document.styleSheets[1].cssRules[7].style.visibility="visible";
      document.styleSheets[1].cssRules[8].style.visibility="visible";
      document.styleSheets[1].cssRules[9].style.visibility="visible";
      document.styleSheets[1].cssRules[10].style.visibility="visible";
      document.styleSheets[1].cssRules[11].style.visibility="visible";
    }
    else {
      document.styleSheets[1].cssRules[0].style.visibility="hidden";
      document.styleSheets[1].cssRules[1].style.visibility="hidden";
      document.styleSheets[1].cssRules[2].style.visibility="hidden";
      document.styleSheets[1].cssRules[3].style.visibility="hidden";  
      document.styleSheets[1].cssRules[4].style.visibility="hidden";
      document.styleSheets[1].cssRules[5].style.visibility="hidden";
      document.styleSheets[1].cssRules[6].style.visibility="hidden";
      document.styleSheets[1].cssRules[7].style.visibility="hidden";
      document.styleSheets[1].cssRules[8].style.visibility="hidden";
      document.styleSheets[1].cssRules[9].style.visibility="hidden";
      document.styleSheets[1].cssRules[10].style.visibility="hidden";
      document.styleSheets[1].cssRules[11].style.visibility="hidden";
    }
    return true;
  }
</script>
<!-- This css sets the location of the anchor element it displays as the glowing highlight -->
<style>
  <?php echo implode("\n", $grid->getBlocksCss()); ?>
</style>
<style>
  #popupbox{visibility:visible;width:550px;height:500px;left:275px;top:78px;position:absolute;}
  #screenshot{visibility:visible;width:480px;height:200px;position:absolute;top:100px;left:310px;}
  #popuptext1{visibility:visible;font-family:arial,helvetica;font-size:18px;color:white;height:18px;position:absolute;top:367px;left:310px;}
  #popuptext2{visibility:visible;font-family:arial,helvetica;font-size:18px;color:white;height:20px;position:absolute;top:387px;left:310px;}
  #popuptext3{visibility:visible;font-family:arial,helvetica;font-size:18px;color:white;height:20px;position:absolute;top:407px;left:310px;}
  #popuptext4{visibility:visible;font-family:arial,helvetica;font-size:18px;color:white;height:20px;position:absolute;top:427px;left:310px;}
  #popuptext5{visibility:visible;font-family:arial,helvetica;font-size:18px;color:white;height:20px;position:absolute;top:447px;left:310px;}
  #popuptext6{visibility:visible;font-family:arial,helvetica;font-size:18px;color:white;height:20px;position:absolute;top:467px;left:310px;}
  #popuptext7{visibility:visible;font-family:arial,helvetica;font-size:18px;color:white;height:20px;position:absolute;top:487px;left:310px;}
  #popuptitle{visibility:visible;font-family:arial,helvetica;font-size:18px;color:white;position:absolute;top:310px;left:310px;}
  #episodeinfo{visibility:visible;font-family:arial,helvetica;font-size:16px;color:#bbbbbb;position:absolute;top:335px;left:310px;}
  #episodetitle{visibility:visible;font-family:arial,helvetica;font-size:16px;color:#bbbbbb;position:absolute;top:335px;left:447px;}
</style>
<style>
  <?php echo implode("\n", $grid->getColHeaderCss()); ?>
  <?php echo implode("\n", $grid->getRowHeaderCss()); ?>
body {
  <?php if(!$isNMT): ?>
    background-position: -90px -32px;
  <?php endif; ?>
  background-repeat: no-repeat;
  color:#ffffff;
  margin:0;
}
#status {
  height:537px;
  position:absolute;
  top:100px;
  left:951px;
  width:1px;
}
</style> 
  
<link rel="stylesheet" type="text/css" href="themes/gaya_HD/css/style.css">
<?php if($isNMT): ?>
  </head>
  <body marginwidth="0" marginheight="0" border="0" focustext="#0000CC" bgcolor="#000000" onloadset="block1" focuscolor="#66CCFF">
<?php else: ?>
  <style>
    :focus{-moz-outline-style: none;}
    img {border-style: none;}
  </style>
  <script type="text/javascript" src="http://nmt.mcdvd.dk/tv/pc.js"></script>
  </head>
  <body background="themes/gaya_HD/images/background-new.jpg" marginwidth="0" marginheight="0" border="0" focustext="#0000CC" bgcolor="#000000" onloadset="block1" focuscolor="#66CCFF">
<?php endif;?>

<table align="left" valign="top" width="1100" height="656" border="0" padding="0" cellpadding="0" cellspacing="0">
  <tr>
    <td colspan="2" height="97" align="left" valign="top">
      <table border="0" padding="0" cellpadding="0" cellspacing="0">
        <tr>
          <td>
            <img src="themes/gaya_HD/images/tv.png" width="75" height="75">
          </td>
          <td align="left" valign="middle">
             <span class="logotext"><?php echo Yii::app()->name; ?></span>
           </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td width="192" align="left" valign="bottom">&nbsp;</td>
    <td align="left" valign="top">
      <table border="0" padding="0" cellpadding="0" cellspacing="3">
        <?php $numRows = $grid->getNumRows();for($i=0;$i<$numRows;++$i): ?>
        <tr>
          <td>
            <table border="0" padding="0" cellpadding="0" cellspacing="3">
              <tr>
                <td>
                  <table border="0" padding="0" cellpadding="0" cellspacing="0">
                   <tr>
                      <?php $numColumns = $grid->getNumColumns(); for($j=0;$j<$numColumns;++$j): ?>
                      <td width="100" class="block smallblock" align="center" valign="middle"  background="themes/gaya_HD/images/bg1-notrans.png">
                        <?php echo CHtml::encode($grid->get($i, $j)->getName(25)); ?>
                      </td>
                      <td width="6" class="spacer">&nbsp;</td>
                      <?php endfor; ?>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <?php endfor; ?>
      </table>
    </td>
  </tr>
</table>
<?php
$numRows = $grid->getNumRows();
$numColumns = $grid->getNumColumns();
$columnWidth = $grid->getColumnWidth();
$rowHeight = $grid->getRowHeight();
foreach($grid->getRows() as $row => $rows)
{
  foreach($rows as $col=>$block)
  {
    $down = ($row === $numRows-1 ? '':'block'.$grid->get($row+1, $col)->getNumber());
    $up = ($row === 0 ? '':'block'.$grid->get($row-1, $col)->getNumber());
    $left = ($col === 0 ? 'prev':'block'.$grid->get($row, $col-1)->getNumber());
    $right = ($col === $numColumns-1 ? 'next':'block'.$grid->get($row, $col+1)->getNumber());
    $name = "block".$block->getNumber();
    $url = "nmtdvr.php?r=tvEpisode/show&id=".$block->tvEpisode->id;
    $focusImg = 'themes/gaya_HD/images/hl'.$block->getWidth().'.png';
    $width = $block->getWidth()*$columnWidth;
    echo <<<EOD
<a href="$url" id="$name" name="$name" onkeydownset="$down" onkeyupset="$up" onkeyleftset="$left" onkeyrightset="$right">
  <img src="themes/gaya_HD/images/trans.png" onfocussrc="$focusImg" width="144" height="89" border="0">
</a>
EOD
    ;
  }
} 

$onkey = 'block'.$grid->get(0,0)->getNumber();
$onkeydown = 'block'.$grid->get($numRows-1,0)->getNumber();
echo <<<EOD
<a href="$url" id="prev" name="prev" tvid="pgup" onkeyupset="$onkey" onkeyrightset="$onkey" onkeyleftset="prev" onkeydownset="$onkeydown">
  <img src="themes/gaya_HD/images/hc_prev1.png" onfocussrc="themes/gaya_HD/images/hc_prev_on1.png" height="67" width="37" border="0">
</a>
EOD
;
$onkey = 'block'.$grid->get(0, $numColumns-1)->getNumber();
$onkeydown = 'block'.$grid->get($numRows-1,$numColumns-1)->getNumber();
echo <<<EOD
<a href="$url" id="next" name="next" tvid="pgdn" onkeyupset="$onkey" onkeyleftset="$onkey" onkeyrightset="next" onkeydownset="$onkeydown">
  <img src="themes/gaya_HD/images/hc_next1.png" onfocussrc="themes/gaya_HD/images/hc_next_on1.png" height="67" width="37" border="0">
</a>
EOD
;
?>

<div id="day" class="info">
<?php echo date('l, M d, Y'); ?>
</div>

<div id="info1" class="info">
  Last feed update
</div>
<div id="info2" class="info">
<?php
if(!isset($lastFeedUpdate)) {
  $lastFeedUpdate = Yii::app()->getDb()->createCommand(
      'SELECT max(lastUpdated) FROM feed'
  )->queryScalar();
} 
echo date('D h:i a', $lastFeedUpdate); ?>
</div>

<div id="source1" class="info">
Source:
</div>

<div id="source2" class="info">
<b>TVDb.com</b>
</div>

<?php 
for($col=0;$col<$numColumns;++$col) {
  $block = $grid->get($numRows-1, $col);
  $date = date("h:ia", $block->getTime());
  echo "<div class='blockinfo' id='column$col'>$date</div>\n";
}
// Cache output up to here for the show view for 10 minutes
Yii::app()->getCache()->set('gaya_HD.tvEpisode.list', ob_get_contents(), 60*10);
ob_end_flush();
if(null !== ($tvEpisode = Yii::app()->user->getFlash('gaya_HD.render.show')))
  $this->renderPartial('show', array('tvepisode'=>$tvEpisode, 'disableCachedOutput'=>true));
?>
<a href="channels.php" tvid="setup" name="setuplink"></a>
</body>
</html>
