<?php
date_default_timezone_set("Europe/London");
$lang = "en";

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
}

include_once("templates/emmalang_en.php");
include_once("templates/emmalang_$lang.php");
include_once("templates/classEmma.class.php");

header('Content-Type: text/html; charset='.$CHARSET);

$currentComp = new Emma($_GET['comp']);

$isSingleClass = isset($_GET['class']);
$isSingleClub = isset($_GET['club']);
$showPath = true;

if (isset($_GET['showpath']) && $_GET['showpath'] == "false") {
    $showPath = false;
}

$singleClass = "";
$singleClub = "";
if ($isSingleClass) {
    $singleClass = $_GET['class'];
}
if ($isSingleClub) {
    $singleClub = utf8_decode(rawurldecode($_GET['club']));
}

$showLastPassings = !($isSingleClass || $isSingleClub) || (isset($_GET['showLastPassings']) && $_GET['showLastPassings'] == "true");
$RunnerStatus = array("1" =>  $_STATUSDNS, "2" => $_STATUSDNF, "11" =>  $_STATUSWO, "12" => $_STATUSMOVEDUP, "9" => $_STATUSNOTSTARTED,"0" => $_STATUSOK, "3" => $_STATUSMP, "4" => $_STATUSDSQ, "5" => $_STATUSOT, "9" => "", "10" => "");

$showTimePrediction = true;

echo("<?xml version=\"1.0\" encoding=\"$CHARSET\" ?>\n");
?>

<!doctype html>
<html lang="en">
  <head>
    <title><?=$_TITLE?> :: <?=$currentComp->CompName()?> [<?=$currentComp->CompDate()?>]</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

<META HTTP-EQUIV="expires" CONTENT="-1">
<meta http-equiv="Content-Type" content="text/html;charset=<?=$CHARSET?>">
<meta name="theme-color" content="#555556">
<link rel="stylesheet" type="text/css" href="css/style-eoc.css">
<link rel="stylesheet" type="text/css" href="css/ui-darkness/jquery-ui-1.8.19.custom.css">
<link rel="stylesheet" type="text/css" href="css/jquery.dataTables_themeroller-eoc.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script type="text/javascript">
window.mobilecheck = function() {
  var check = false;
  (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
  return check;
}
</script>

<?php
$debug = isset($_GET['debug']) && $_GET["debug"] == "true";
if ($debug) {
    ?>
<!-- DEBUG -->
<script language="javascript" type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.ba-hashchange.min.js"></script>
<script language="javascript" type="text/javascript" src="js/LiveResults.debug.js?rnd=<?=time()?>"></script>
<?php } else {?>
<!-- RELEASE-->
<script language="javascript" type="text/javascript" src="js/liveresults.min.js"></script>
<?php }?>
<script
  src="https://code.jquery.com/jquery-1.7.2.min.js"
  integrity="sha256-R7aNzoy2gFrVs+pNJ6+SokH04ppcEqJ0yFLkNGoFALQ="
  crossorigin="anonymous"></script>
<script language="javascript" type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.ba-hashchange.min.js"></script>
<script language="javascript" type="text/javascript" src="js/NoSleep.min.js"></script>
<script language="javascript" type="text/javascript">

var noSleep = new NoSleep();

function enableNoSleep() {
  noSleep.enable();
  document.removeEventListener('click', enableNoSleep, false);
}

document.addEventListener('click', enableNoSleep, false);


var res = null;

var Resources = {
	_TITLE: "<?= $_TITLE?>",
	_CHOOSECMP: "<?=$_CHOOSECMP?>",
	_AUTOUPDATE: "<?=$_AUTOUPDATE?>",
	_LASTPASSINGS: "<?=$_LASTPASSINGS?>",
	_LASTPASSFINISHED: "<?=$_LASTPASSFINISHED?>",
	_LASTPASSPASSED: "<?=$_LASTPASSPASSED?>",
	_LASTPASSWITHTIME: "<?=$_LASTPASSWITHTIME?>",
	_CHOOSECLASS: "<?=$_CHOOSECLASS?>",
	_NOCLASSESYET: "<?=$_NOCLASSESYET?>",
	_CONTROLFINISH: "<?=$_CONTROLFINISH?>",
	_NAME: "<?=$_NAME?>",
	_CLUB: "<?=$_CLUB?>",
	_TIME: "<?=$_TIME?>",
	_NOCLASSCHOSEN: "<?=$_NOCLASSCHOSEN?>",
	_HELPREDRESULTS: "<?=$_HELPREDRESULTS?>",
	_NOTICE: "<?=$_NOTICE?>",
	_STATUSDNS: "<?=$_STATUSDNS ?>",
	_STATUSDNF: "<?=$_STATUSDNF?>",
	_STATUSWO: "<?=$_STATUSWO?>",
	_STATUSMOVEDUP: "<?=$_STATUSMOVEDUP?>",
	_STATUSNOTSTARTED: "<?=$_STATUSNOTSTARTED?>",
	_STATUSOK: "<?=$_STATUSOK ?>",
	_STATUSMP: "<?=$_STATUSMP ?>",
	_STATUSDSQ: "<?=$_STATUSDSQ?>",
	_STATUSOT: "<?=$_STATUSOT?>",
	_FIRSTPAGECHOOSE: "<?=$_FIRSTPAGECHOOSE ?>",
	_FIRSTPAGEARCHIVE: "<?=$_FIRSTPAGEARCHIVE?>",
	_LOADINGRESULTS: "<?=$_LOADINGRESULTS ?>",
	_ON: "<?=$_ON ?>",
	_OFF: "<?=$_OFF?>",
	_TEXTSIZE: "<?=$_TEXTSIZE ?>",
	_LARGER: "<?=$_LARGER?>",
	_SMALLER: "<?=$_SMALLER?>",
	_OPENINNEW: "<?=$_OPENINNEW?>",
	_FORORGANIZERS: "<?=$_FORORGANIZERS ?>",
	_FORDEVELOPERS: "<?=$_FORDEVELOPERS ?>",
	_RESETTODEFAULT: "<?=$_RESETTODEFAULT?>",
	_OPENINNEWWINDOW: "<?=$_OPENINNEWWINDOW?>",
	_INSTRUCTIONSHELP: "<?=$_INSTRUCTIONSHELP?>",
	_LOADINGCLASSES: "<?=$_LOADINGCLASSES ?>",
	_START: "<?=$_START?>",
	_TOTAL: "<?=$_TOTAL?>",
	_CLASS: "<?=$_CLASS?>"
};

var runnerStatus = Array();
runnerStatus[0]= "<?=$_STATUSOK?>";
runnerStatus[1]= "<?=$_STATUSDNS?>";
runnerStatus[2]= "<?=$_STATUSDNF?>";
runnerStatus[11] =  "<?=$_STATUSWO?>";
runnerStatus[12] = "<?=$_STATUSMOVEDUP?>";
runnerStatus[9] = "";
runnerStatus[3] = "<?=$_STATUSMP?>";
runnerStatus[4] = "<?=$_STATUSDSQ?>";
runnerStatus[5] = "<?=$_STATUSOT?>";
runnerStatus[9] = "";
runnerStatus[10] = "";


$(document).ready(function()
{
	res = new LiveResults.AjaxViewer(<?= $_GET['comp']?>,"<?= $lang?>","divClasses","divLastPassings","resultsHeader","resultsControls","divResults","txtResetSorting",Resources,<?= ($currentComp->IsMultiDayEvent() ? "true" : "false")?>,<?= (($isSingleClass || $isSingleClub) ? "true" : "false")?>,"setAutomaticUpdateText", runnerStatus);
        res.setShowTenth(<?= $currentComp->IsTenths() ? "true" : "false" ?>);
	<?php if ($isSingleClass) {?>
		res.chooseClass('<?=$singleClass?>');
	<?php } elseif ($isSingleClub) {?>
		res.viewClubResults('<?=$singleClub?>');
	<?php } else {?>
		$("#divClasses").html("<?=$_LOADINGCLASSES?>...");
		res.updateClassList();
	<?php }?>

<?php if ($showLastPassings) {?>
	res.updateLastPassings();
	<?php }?>

	<?php if ($showTimePrediction) { ?>
		res.eventTimeZoneDiff = <?=$currentComp->TimeZoneDiff();?>;
		res.startPredictionUpdate();
				
	<?php }?>
});


</script>
</head>
<body>

<!-- MAIN DIV -->

<?php if (!$isSingleClass && !$isSingleClub && $showPath) {?>
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #0f2170;">
  <div class="container-fluid">
  <a class="navbar-brand" href="https://www.woc2024.org">
    <img src="images/logo.svg" alt="WOC2024 logo" width="200px" height="60px" class="d-inline-block align-text-top">
  </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <span id="setAutomaticUpdateText">
            <a class="nav-link active" aria-current="page" href="javascript:LiveResults.Instance.setAutomaticUpdate(false);"><?=$_AUTOUPDATE?>&nbsp<i class="fa fa-check-square-o" aria-hidden="true"></i></a>
          </span>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if ($lang == "en") {echo "<img src='images/en.png' alt='English'>";}
            elseif ($lang == "sv") {echo "<img src='images/se.png' alt='Svenska'>";}
            elseif ($lang == "fi") {echo "<img src='images/fi.png' alt='Suomeksi'>";}
            elseif ($lang == "ru") {echo "<img src='images/ru.png' alt='Русский'>";}
            elseif ($lang == "cz") {echo "<img src='images/cz.png' alt='Česky'>";}
            elseif ($lang == "de") {echo "<img src='images/de.png' alt='Deutsch'>";}
            elseif ($lang == "bg") {echo "<img src='images/bg.png' alt='български'>";}
            elseif ($lang == "fr") {echo "<img src='images/fr.png' alt='Français'>";}
            elseif ($lang == "it") {echo "<img src='images/it.png' border='0' alt='Italiano'>";}
            elseif ($lang == "hu") {echo "<img src='images/hu.png' border='0' alt='Magyar'>";}
            elseif ($lang == "es") {echo "<img src='images/es.png' border='0' alt='Español'>";}
            elseif ($lang == "pl") {echo "<img src='images/pl.png' border='0' alt='Polska'>";}
            else {echo "<img src='images/pt.png?a' border='0' alt='Português'>";}
            ?>
          </a>
        <ul class="dropdown-menu">
          <li><?php echo($lang == "en" ? "<img src='images/en.png' alt='English'> English <i class='fa fa-check'></i>" :
"<a href=\"?lang=en&amp;comp=".$_GET['comp']."\" style='text-decoration: none'><img src='images/en.png' alt='English'> English</a>")?>
          </li>
          <li><?php echo($lang == "sv" ? "<img src='images/se.png' alt='Svenska'> Svenska <i class='fa fa-check' style='color:green;'></i>" :
"<a href=\"?lang=sv&amp;comp=".$_GET['comp']."\" style='text-decoration: none'><img src='images/se.png' alt='Svenska'> Svenska</a>")?>
          </li>
          <li><?php echo($lang == "fi" ? "<img src='images/fi.png' alt='Suomeksi'> Suomeksi <i class='fa fa-check' style='color:green;'></i>" :
"<a href=\"?lang=fi&amp;comp=".$_GET['comp']."\" style='text-decoration: none'><img src='images/fi.png'  alt='Suomeksi'> Suomeksi</a>")?>
          </li>
          <li> <?php echo($lang == "ru" ? "<img src='images/ru.png' alt='Русский'> Русский <i class='fa fa-check' style='color:green;'></i>" :
"<a href=\"?lang=ru&amp;comp=".$_GET['comp']."\" style='text-decoration: none'><img src='images/ru.png' alt='Русский'> Русский</a>")?>
          </li>
          <li><?php echo($lang == "cz" ? "<img src='images/cz.png' alt='Česky'> Česky <i class='fa fa-check' style='color:green;'></i>" :
"<a href=\"?lang=cz&amp;comp=".$_GET['comp']."\" style='text-decoration: none'><img src='images/cz.png' alt='Česky'> Česky</a>")?>
          </li>
          <li> <?php echo($lang == "de" ? "<img src='images/de.png' alt='Deutsch'> Deutsch <i class='fa fa-check' style='color:green;'></i>" :
"<a href=\"?lang=de&amp;comp=".$_GET['comp']."\" style='text-decoration: none'><img src='images/de.png' alt='Deutsch'> Deutsch</a>")?>
          </li>
          <li><?php echo($lang == "bg" ? "<img src='images/bg.png' alt='български'> български  <i class='fa fa-check' style='color:green;'></i>" :
"<a href=\"?lang=bg&amp;comp=".$_GET['comp']."\" style='text-decoration: none'><img src='images/bg.png' alt='български'> български</a>")?>
          </li>
          <li><?php echo($lang == "fr" ? "<img src='images/fr.png' alt='Français'> Français <i class='fa fa-check' style='color:green;'></i>" :
"<a href=\"?lang=fr&amp;comp=".$_GET['comp']."\" style='text-decoration: none'><img src='images/fr.png' alt='Français'> Français</a>")?>
          </li>
          <li><?php echo($lang == "it" ? "<img src='images/it.png' border='0' alt='Italiano'> Italiano <i class='fa fa-check' style='color:green;'></i>" :
"<a href=\"?lang=it&amp;comp=".$_GET['comp']."\" style='text-decoration: none'><img src='images/it.png' border='0' alt='Italiano'> Italiano</a>")?>
          </li>
          <li><?php echo($lang == "hu" ? "<img src='images/hu.png' border='0' alt='Magyar'> Magyar <i class='fa fa-check' style='color:green;'></i>" :
"<a href=\"?lang=hu&amp;comp=".$_GET['comp']."\" style='text-decoration: none'><img src='images/hu.png' border='0' alt='Magyar'> Magyar</a>")?>
          </li>
          <li><?php echo($lang == "es" ? "<img src='images/es.png' border='0' alt='Español'> Español <i class='fa fa-check' style='color:green;'></i>" :
"<a href=\"?lang=es&amp;comp=".$_GET['comp']."\" style='text-decoration: none'><img src='images/es.png' border='0' alt='Español'> Español</a>")?>
          </li>
          <li><?php echo($lang == "pl" ? "<img src='images/pl.png' border='0' alt='Polska'> Polska <i class='fa fa-check' style='color:green;'></i>" :
"<a href=\"?lang=pl&amp;comp=".$_GET['comp']."\" style='text-decoration: none'><img src='images/pl.png' border='0' alt='Polska'> Polska</a>")?>
          </li>
          <li><?php echo($lang == "pt" ? "<img src='images/pt.png?a' border='0' alt='Português'> Português <i class='fa fa-check' style='color:green;'></i>" :
"<a href=\"?lang=pt&amp;comp=".$_GET['comp']."\" style='text-decoration: none'><img src='images/pt.png?a' border='0' alt='Português'> Português</a>")?>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</div>
</nav>

<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #0f2170;">
  <div class="container-fluid">
    <span class="navbar-brand mb-0 h1"><?=$currentComp->CompName()?> &#8210; <?=$currentComp->CompDate()?></span>

    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
    <?php if (!$isSingleClass && !$isSingleClub) {?>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <span id="resultsHeader"><?=$_CHOOSECLASS?></span>
        </a>
        <ul class="dropdown-menu" id=divClasses>
          <li><hr class="dropdown-divider"></li>
        </ul>
      </li>
    <?php }?>
  </ul>
  </div>
</nav>
<?php }?>

<div class="maindiv">

<?php if($showLastPassings) {?>
<div class="accordion" id="accordionExample">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingOne">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        <?=$_LASTPASSINGS?>
      </button>
    </h2>
    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
      <div id="divLastPassings" class="accordion-body">
      </div>
    </div>
  </div>
</div>
<?php }?>

<div>&nbsp<span id="txtResetSorting"></span></div>
<table id="divResults" width="100%">
</table>

<div id=divFooter> 
  <font color="AAAAAA">* <?=$_HELPREDRESULTS?></font>
  <p align="left">&copy;2012-2023, <?=$_NOTICE?></p>
</div>

</div>

</body>
</html>
