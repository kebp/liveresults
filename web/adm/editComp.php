<?php
include_once '../templates/classEmma.class.php';

$comp = Emma::GetCompetition($_GET['compid']);
if (count($comp) == 0) {
    header(Location: './admincompetitions.php');
    exit();
}

$hdr = Emma::GetHeader($_GET['compid']);
$rcontrols = Emma::GetRadioControls($_GET['compid']);
$stats = Emma::GetCompetitionStats($_GET['compid']);

if (isset($_POST['btnSave'])) {
    Emma::UpdateCompetition(
        $_GET['compid'],
        $_POST['name'],
        $_POST['org'],
        $_POST['date'],
        $_POST['tenths'],
        $_POST['public'],
        $_POST['timediff'],
    );
    isset($_POST['public']) ? ($comp['public'] = 1) : ($comp['public'] = 0);
    isset($_POST['tenths']) ? ($comp['tenths'] = 1) : ($comp['tenths'] = 0);
} else if (isset($_POST['btnAdd'])) {
    Emma::AddRadioControl($_GET['compid'], $_POST['classname'], $_POST['controlname'], $_POST['code']);
}

if (isset($_GET['what'])) {
    switch ($_GET['what']) {
        case 'delctr':
            Emma::DelRadioControl($_GET['compid'], $_GET['code'], $_GET['class']);
            break;
        case 'delallctr':
            Emma::DelAllRadioControls($_GET['compid']);
            break;
        case 'de':
            Emma::DelEvent($_GET['compid']);
            break;
        case 'drs':
            Emma::DelRunAndRes($_GET['compid']);
            break;
        case 'hdr':
            Emma::SetHeader($_GET['compid'], $_POST['foreground'], $_POST['background'], $_POST['url'], $_POST['url2']);
            break;
        case 'logo':
            Emma::UploadLogo($_GET['compid'], $_FILES['fileToUpload']);
            break;
    }
}

include_once '../templates/emmalang_en.php';

$lang = 'en';

if (isset($_GET['lang']) && $_GET['lang'] != '') {
    $lang = $_GET['lang'];
}

include_once "../templates/emmalang_$lang.php";

header('Content-Type: text/html; charset=' . $CHARSET);

?>
<!DOCTYPE HTML>

<html>

<head><title><?= $_TITLE ?></title>

<link rel="stylesheet" type="text/css" href="../css/style-eoc.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin "anonymous">
<meta name="robots" content="noindex">
<meta http-equiv="Content-Type" content="text/html;charset=<?= $CHARSET ?>">

<script language="javascript">

function colorRow(row)

{

var el = document.getElementById(row);

if (el == null)

  return;

el.style.backgroundColor = "#C0D6FF";

}

function resetRow(row)

{

var el = document.getElementById(row);

if (el == null)

  return;

el.style.backgroundColor = "";

}

function confirmDelete(msg,url)
{
  if (confirm(msg))
	{
		window.location = "editComp.php" + url;
	}
}

function confirmDelEvent() {
  var e = <?php echo $_GET['compid'] ?>;
  var n = <?php echo $stats['Names'] ?>;
  var t = <?php echo '"' . $comp['compName'] . '"' ?>;
  if (confirm('Are you SURE you want to delete the entire event ' + t + ' (' + e + ') with ' + n + ' runners?')) {
    var x = document.getElementsByName('formdelevt');
    x[0].submit();
  }
}

function confirmDelResults() {
  var c = <?php echo $stats['Classes'] ?>;
  var n = <?php echo $stats['Names'] ?>;
  var t = <?php echo '"' . $comp['compName'] . '"' ?>;
  if (confirm('Are you SURE you want to delete all ' + n + ' runners in ' + c + ' classes and all their results from ' + t + '?')) {
    var x = document.getElementsByName('formdelrun');
    x[0].submit();
  }
}

function confirmDelAllRadio() {
  var rc = <?php echo sizeof($rcontrols) ?>;
  var t = <?php echo '"' . $comp['compName'] . '"' ?>;
  if (confirm('Are you SURE you want to delete all ' + rc + ' radio controls from ' + t + '?')) {
    var x = document.getElementsByName('formdelradio');
    x[0].submit();
  }
}


</script>

</head>

<body topmargin="0" leftmargin="0">

<!-- MAIN DIV -->

<div class="maindiv">

<table width="759" cellpadding="0" cellspacing="0" border="0" ID="Table6">
	<tr>
		<TR>
			<TD>

			</TD>

		</TR>
        </tr>
</table>



<table border="0" cellpadding="0" cellspacing="0" width="759">
  <tr>
     <td valign="bottom">

<!-- MAIN MENU FLAPS - Two rows, note that left and right styles differs from middle ones -->
     <table border="0" cellpadding="0" cellspacing="0">
          <!-- Top row with rounded corners -->
          <tr>
               <td colspan="4"><span class="mttop"></td>
          </tr>
     </table>
     </td>
     <td align="right" valign="bottom">
     </td>
  </tr>
  <tr>
    <td class="submenu" colspan="2">
       <table border="0" cellpadding="0" cellspacing="0">
             <tr>
             <td><a href="admincompetitions.php">Adminpage Competitionindex</a> | </td>
               <td><a href="../index.php"><?= $_CHOOSECMP ?> to view</a></td>
             </tr>
       </table>
     </td>
  </tr>
<!-- End SUB MENU -->
  <tr>

    <td class="searchmenu" colspan="2" style="padding: 5px;">

       <table border="0" cellpadding="0" cellspacing="0" width="400">

             <tr>

               <td>

<form name="form1" action="editComp.php?what=comp&compid=<?= $comp['tavid'] ?>" method="post">
<h1 class="categoriesheader">Edit competition</h1>
<b>CompetitionID</b><br/>
<input type="text" name="id" size="35" disabled="true" value="<?= $comp['tavid'] ?>"/><br/>
<b>Competitions Name</b><br/>
<input type="text" name="name" size="35" value="<?= $comp['compName'] ?>"/><br/>
<b>Organizer</b><br/>
<input type="text" name="org" size="35" value="<?= $comp['organizer'] ?>"/><br/>
<b>Date (format yyyy-mm-dd)</b><br/>
<input type="text" name="date" size="35" value="<?= date('Y-m-d', strtotime($comp['compDate'])) ?>"/> (ex. 2008-02-03)<br/>
<b>Timezonediff (hours: 0 for GMT / BST, 1 for  CET / CEST)</b><br/>
<input type="text" name="timediff" size="10" value="<?= $comp['timediff'] ?>"/><br/>

<b>Public</b>
<input type="checkbox" name="public" <?= $comp['public'] == 1 ? 'checked' : '' ?>/><br/>
<b>Show tenths second</b>
<input type="checkbox" name="tenths" <?= $comp['tenths'] == 1 ? 'checked' : '' ?>/><br/><br/>
<input type="submit" name="btnSave" class="btn btn-primary" value="Save"/>
</form>

<br><h1 class="categoriesheader">Header markup</h1>
<p>Header settings are all otional, but useful if you have majore event branding. Defaults are all sensible</p>
<form name="header" action="editComp.php?what=hdr&compid=<?= $comp['tavid'] ?>" method="post">
<div>
  <b><label for="foreground">Foreground color</label></b>
  <select id="foreground" name="foreground">
    <option <?php if ($hdr['fg'] == 'navbar-dark')
        echo 'selected = "selected"'; ?> value="navbar-dark">Light text</option>
    <option <?php if ($hdr['fg'] == 'navbar-light')
        echo 'selected = "selected"'; ?> value="navbar-light">Dark text</option>
  </select>
</div>
<div>
  <b><label for="background">Background color</label></b>
  <input type="color" id="background" name="background" value="<?= $hdr['bg'] ?>" />
</div>
<div>
  <b>Logo: </b><?= $hdr['logoname'] ?><br/>
  <b><label for="url">URL for logo</label></b>
  <input type="url" name="url" size="35" value="<?= $hdr['url'] ?>" /><br/>
  <b><label for="url">URL for menu Home</label></b>
  <input type="url2" name="url2" size="35" value="<?= $hdr['url2'] ?>" /><br/>
  <input type="submit" name="btnHdr" class="btn btn-primary" value="Save"/>
</div>
</form>
</br>
<p>Optionally upload an event or club logo for the event.
Should be in SVG format and &lt; 50k in size.</p>
<form action="editComp.php?what=logo&compid=<?= $comp['tavid'] ?>" method="post" enctype="multipart/form-data">
  <b><label for="fileToUpload">Select image to upload</label></b>
  <input type="file" name="fileToUpload" id="fileToUpload"></br>
  <input type="submit" value="Upload Logo" name="btnLogo"  class="btn btn-primary">
</form>

<!-- Event deletion functions -->
<hr/><h1 class="categoriesheader">Deletion</h1>
<p class="fw-bolder text-danger fs-5" >Ensure that Emma uploads are paused / halted before using the delete functions.</p>
<form name="formdelevt" action="editComp.php?compid=<?= $_GET['compid'] ?>&what=de"  method="post">
<input type="button" class="btn btn-danger" value="Delete Event" onclick="confirmDelEvent()"/>
</form>
<br/>
<form name="formdelrun" action="editComp.php?compid=<?= $_GET['compid'] ?>&what=drs" method="post">
<input type="button" class="btn btn-danger" value="Delete Runners and Results" onclick="confirmDelResults()"/>
</form>
<br/>
<form name="formdelradio" action="editComp.php?compid=<?= $_GET['compid'] ?>&what=delallctr" method="post">
<input type="button" class="btn btn-danger" value="Delete all radio controls" onclick="confirmDelAllRadio()"/>
</form>

<!-- Manage radio controls -->
<hr/><h1 class="categoriesheader">Radio Controls</h1>
<form name="formrdo1" action="editComp.php?what=radio&compid=<?= $comp['tavid'] ?>" method="post">
<table border="0">
<tr><td><b>Code</td><td><b>Name</td><td><b>Class</td><td><b>Order</td></tr>
<?php
$rcontrols = Emma::GetRadioControls($_GET['compid']);
for ($i = 0; $i < sizeof($rcontrols); $i++) {
    echo
        '<tr><td>'
        . $rcontrols[$i]['code']
            . '</td><td>'
            . $rcontrols[$i]['name']
            . '</td><td>'
            . $rcontrols[$i]['classname']
            . '</td><td>'
            . $rcontrols[$i]['corder']
            . "</td><td><a href='javascript:confirmDelete(\"Do you want to delete this radiocontrol?\",\"?compid="
            . $_GET['compid']
            . '&what=delctr&compid='
            . $_GET['compid']
            . '&code='
            . $rcontrols[$i]['code']
            . '&class='
            . urlencode($rcontrols[$i]['classname'])
            . "\");'>Delete</a></td></tr>"
    ;
}

?>
</table>

<br/><b>Add Radio Control</b><br/>
Code = 1000*passingcnt + controlCode, <br/>
ex. first pass at control 53 => Code = 1053, second pass => Code = 2053<br/>
Code: <input type="text" name="code"/><br/>
Control-Name: <input type="text" name="controlname"/><br/>
ClassName: <input type="text" name="classname"/><br/>
<input type="submit" name="btnAdd" value="Add Control"/>
</form>

		</td>

	     </tr>

	</table>
     </td>
  </tr>
</table>
</div>
</body>
</html>
