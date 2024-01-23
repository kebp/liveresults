<?php
include_once("../templates/classEmma.class.php");

$comp = Emma::GetCompetition($_GET['compid']);
if (count($comp) == 0) {
    header(Location: "./admincompetitions.php");
    exit;
}

if (isset($_POST['btnSave']))
{
	Emma::UpdateCompetition($_GET['compid'],$_POST['name'],$_POST['org'],$_POST['date'],$_POST['tenths'],$_POST['public'],$_POST['timediff']);
        isset($_POST['public']) ? $comp['public'] = 1 : $comp['public'] = 0;
        isset($_POST['tenths']) ? $comp['tenths'] = 1 : $comp['tenths'] = 0;
}
else if (isset($_POST['btnAdd']))
{
	Emma::AddRadioControl($_GET['compid'],$_POST['classname'],$_POST['controlname'],$_POST['code']);
}

if (isset($_GET['what'])) {
  switch ($_GET['what']) {
    case "delctr":
      Emma::DelRadioControl($_GET['compid'],$_GET['code'],$_GET['class']);
      break;
    case "delallctr":
      Emma::DelAllRadioControls($_GET['compid']);
      break;
    case "de":
      Emma::DelEvent($_GET['compid']);
      break;
    case "drs":
      Emma::DelRunAndRes($_GET['compid']);
      break;
  }
}


include_once("../templates/emmalang_en.php");

 $lang = "en";

 if (isset($_GET['lang']) && $_GET['lang'] != "")
   {
      $lang = $_GET['lang'];
   }

include_once("../templates/emmalang_$lang.php");

header('Content-Type: text/html; charset='.$CHARSET);

?>
<!DOCTYPE HTML>

<html>

<head><title><?=$_TITLE?></title>

<link rel="stylesheet" type="text/css" href="../css/style-eoc.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin "anonymous">
<meta name="robots" content="noindex">
<meta http-equiv="Content-Type" content="text/html;charset=<?=$CHARSET?>">

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
  if (confirm('Are you SURE you want to delete the complete event?')) {
    var x = document.getElementsByName('formdelevt');
    x[0].submit();
  }
}

function confirmDelResults() {
  if (confirm('Are you SURE you want to delete all runners and results?')) {
    var x = document.getElementsByName('formdelrun');
    x[0].submit();
  }
}

function confirmDelAllRadio() {
  if (confirm('Are you SURE you want to delete all radio controls?')) {
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
               <td><a href="../index.php"><?=$_CHOOSECMP?> to view</a></td>
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

<form name="form1" action="editComp.php?what=comp&compid=<?=$comp['tavid']?>" method="post">
<h1 class="categoriesheader">Edit competition</h1>
<b>CompetitionID</b><br/>
<input type="text" name="id" size="35" disabled="true" value="<?=$comp['tavid']?>"/><br/>
<b>Competitions Name</b><br/>
<input type="text" name="name" size="35" value="<?=$comp['compName']?>"/><br/>
<b>Organizer</b><br/>
<input type="text" name="org" size="35" value="<?=$comp['organizer']?>"/><br/>
<b>Date (format yyyy-mm-dd)</b><br/>
<input type="text" name="date" size="35" value="<?=date("Y-m-d",strtotime($comp['compDate']))?>"/> (ex. 2008-02-03)<br/>
<b>Timezonediff (hours, 0 for GMT, +1 for BST or CET and +2 for CEST)</b><br/>
<input type="text" name="timediff" size="10" value="<?=$comp['timediff']?>"/><br/>

<b>Public</b>
<input type="checkbox" name="public" <?= $comp['public'] == 1 ? "checked" : "" ?>/><br/>
<b>Show tenths second</b>
<input type="checkbox" name="tenths" <?= $comp['tenths'] == 1 ? "checked" : "" ?>/><br/><br/>
<input type="submit" name="btnSave" class="btn btn-primary" value="Save"/>
</form>

<!-- Event deletion functions -->
<hr/><h1 class="categoriesheader">Deletion</h1>
<p class="fw-bolder text-danger fs-5" >Ensure that Emma uploads are paused / halted before using the delete functions.</p>
<form name="formdelevt" action="editComp.php?compid=<?= $_GET['compid']?>&what=de"  method="post">
<input type="button" class="btn btn-danger" value="Delete Event" onclick="confirmDelEvent()"/>
</form>
<br/>
<form name="formdelrun" action="editComp.php?compid=<?= $_GET['compid']?>&what=drs" method="post">
<input type="button" class="btn btn-danger" value="Delete Runners and Results" onclick="confirmDelResults()"/>
</form>
<br/>
<form name="formdelradio" action="editComp.php?compid=<?= $_GET['compid']?>&what=delallctr" method="post">
<input type="button" class="btn btn-danger" value="Delete all radio controls" onclick="confirmDelAllRadio()"/>
</form>

<!-- Manage radio controls -->
<hr/><h1 class="categoriesheader">Radio Controls</h1>
<form name="formrdo1" action="editComp.php?what=radio&compid=<?=$comp['tavid']?>" method="post">
<table border="0">
<tr><td><b>Code</td><td><b>Name</td><td><b>Class</td><td><b>Order</td></tr>
<?php
	$rcontrols = Emma::GetRadioControls($_GET['compid']);
for ($i = 0; $i < sizeof($rcontrols); $i++)
{
	echo("<tr><td>".$rcontrols[$i]["code"]."</td><td>".$rcontrols[$i]["name"]."</td><td>".$rcontrols[$i]["classname"]."</td><td>".$rcontrols[$i]["corder"]."</td><td><a href='javascript:confirmDelete(\"Do you want to delete this radiocontrol?\",\"?compid=".$_GET['compid']."&what=delctr&compid=".$_GET['compid']."&code=".$rcontrols[$i]['code']."&class=".urlencode($rcontrols[$i]["classname"])."\");'>Delete</a></td></tr>");
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
