<?php
declare(strict_types=1);

date_default_timezone_set('Europe/London');

include_once './templates/emmalang_en.php';
include_once './templates/classEmma.class.php';
$lang = 'en';
if (isset($_GET['lang']) && $_GET['lang'] !== '') {
    $lang = $_GET['lang'];
}
include_once "./templates/emmalang_{$lang}.php";

header('Content-Type: text/html; charset=' . $CHARSET);
?>

<!DOCTYPE html>
<html lang="en-GB">
<head>
<title><?= $_TITLE ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=<?= $CHARSET ?>">
<meta name="viewport" content="width=1200,initial-scale=1.0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#555556">
<meta name="description" content="Follow British and international orienteering live online with the help of liveoresults">

<link rel="stylesheet" type="text/css" href="css/style-eoc.css">
<link rel="stylesheet" type="text/css" href="css/ui-darkness/jquery-ui-1.8.19.custom.css">
<link rel="stylesheet" type="text/css" href="css/jquery.dataTables_themeroller-eoc.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
<script
  src="https://code.jquery.com/jquery-1.7.2.min.js"
  integrity="sha256-R7aNzoy2gFrVs+pNJ6+SokH04ppcEqJ0yFLkNGoFALQ="
  crossorigin="anonymous"></script>
<script src="js/jquery.dataTables.min.js"></script>

<script>
function colorRow(row)
{
	var el = document.getElementById(row);
	if (el === null)
	  return;
	el.style.backgroundColor = "#C0D6FF";
}
function resetRow(row)
{
var el = document.getElementById(row);
if (el === null)
  return;
el.style.backgroundColor = "";
}

</script>
</head>
<body>
<!-- MAIN DIV -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #0f2170;">
  <div class="container-fluid">
  <a class="navbar-brand" href="https://liveoresults.org.uk/index.php">
  <img src="/logos/liveoresults.svg" alt="LiveResults logo" width="200" height="60" class="d-inline-block align-text-top">
  </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
           <?php switch ($lang) {
        case 'en':
            echo "<img src='images/en.png' alt='English'>";
            break;
        case 'sv':
            echo "<img src='images/se.png' alt='Svenska'>";
            break;
        case 'fi':
            echo "<img src='images/fi.png' alt='Suomeksi'>";
            break;
        case 'ru':
            echo "<img src='images/ru.png' alt='Русский'>";
            break;
        case 'cz':
            echo "<img src='images/cz.png' alt='Česky'>";
            break;
        case 'de':
            echo "<img src='images/de.png' alt='Deutsch'>";
            break;
        case 'bg':
            echo "<img src='images/bg.png' alt='български'>";
            break;
        case 'fr':
            echo "<img src='images/fr.png' alt='Français'>";
            break;
        case 'it':
            echo "<img src='images/it.png' alt='Italiano'>";
            break;
        case 'hu':
            echo "<img src='images/hu.png' alt='Magyar'>";
            break;
        case 'es':
            echo "<img src='images/es.png' alt='Español'>";
            break;
        case 'pl':
            echo "<img src='images/pl.png' alt='Polska'>";
            break;
        default:
            echo "<img src='images/pt.png?a' alt='Português'>";
            break;
    }
    ?>
          </a>
        <ul class="dropdown-menu">
          <li><?php echo
              $lang === 'en'
                  ? "<img src='images/en.png' alt='English'> English <i class='fa fa-check'></i>"
                  : "<a href=\"?lang=en&amp;comp="
                  . strval(filter_input(INPUT_GET, 'comp', FILTER_SANITIZE_NUMBER_INT))
                  . "\" style='text-decoration: none'><img src='images/en.png' alt='English'> English</a>"
          ?>
          </li>
          <li><?php echo
              $lang === 'sv'
                  ? "<img src='images/se.png' alt='Svenska'> Svenska <i class='fa fa-check' style='color:green;'></i>"
                  : "<a href=\"?lang=sv&amp;comp="
                  . strval(filter_input(INPUT_GET, 'comp', FILTER_SANITIZE_NUMBER_INT))
                  . "\" style='text-decoration: none'><img src='images/se.png' alt='Svenska'> Svenska</a>"
          ?>
          </li>
          <li><?php echo
              $lang === 'fi'
                  ? "<img src='images/fi.png' alt='Suomeksi'> Suomeksi <i class='fa fa-check' style='color:green;'></i>"
                  : "<a href=\"?lang=fi&amp;comp="
                  . strval(filter_input(INPUT_GET, 'comp', FILTER_SANITIZE_NUMBER_INT))
                  . "\" style='text-decoration: none'><img src='images/fi.png'  alt='Suomeksi'> Suomeksi</a>"
          ?>
          </li>
          <li> <?php echo
              $lang === 'ru'
                  ? "<img src='images/ru.png' alt='Русский'> Русский <i class='fa fa-check' style='color:green;'></i>"
                  : "<a href=\"?lang=ru&amp;comp="
                  . strval(filter_input(INPUT_GET, 'comp', FILTER_SANITIZE_NUMBER_INT))
                  . "\" style='text-decoration: none'><img src='images/ru.png' alt='Русский'> Русский</a>"
          ?>
          </li>
          <li><?php echo
              $lang === 'cz'
                  ? "<img src='images/cz.png' alt='Česky'> Česky <i class='fa fa-check' style='color:green;'></i>"
                  : "<a href=\"?lang=cz&amp;comp="
                  . strval(filter_input(INPUT_GET, 'comp', FILTER_SANITIZE_NUMBER_INT))
                  . "\" style='text-decoration: none'><img src='images/cz.png' alt='Česky'> Česky</a>"
          ?>
          </li>
          <li> <?php echo
              $lang === 'de'
                  ? "<img src='images/de.png' alt='Deutsch'> Deutsch <i class='fa fa-check' style='color:green;'></i>"
                  : "<a href=\"?lang=de&amp;comp="
                  . strval(filter_input(INPUT_GET, 'comp', FILTER_SANITIZE_NUMBER_INT))
                  . "\" style='text-decoration: none'><img src='images/de.png' alt='Deutsch'> Deutsch</a>"
          ?>
          </li>
          <li><?php echo
              $lang === 'bg'
                  ? "<img src='images/bg.png' alt='български'> български  <i class='fa fa-check' style='color:green;'></i>"
                  : "<a href=\"?lang=bg&amp;comp="
                  . strval(filter_input(INPUT_GET, 'comp', FILTER_SANITIZE_NUMBER_INT))
                  . "\" style='text-decoration: none'><img src='images/bg.png' alt='български'> български</a>"
          ?>
          </li>
          <li><?php echo
              $lang === 'fr'
                  ? "<img src='images/fr.png' alt='Français'> Français <i class='fa fa-check' style='color:green;'></i>"
                  : "<a href=\"?lang=fr&amp;comp="
                  . strval(filter_input(INPUT_GET, 'comp', FILTER_SANITIZE_NUMBER_INT))
                  . "\" style='text-decoration: none'><img src='images/fr.png' alt='Français'> Français</a>"
          ?>
          </li>
          <li><?php echo
              $lang === 'it'
                  ? "<img src='images/it.png' alt='Italiano'> Italiano <i class='fa fa-check' style='color:green;'></i>"
                  : "<a href=\"?lang=it&amp;comp="
                  . strval(filter_input(INPUT_GET, 'comp', FILTER_SANITIZE_NUMBER_INT))
                  . "\" style='text-decoration: none'><img src='images/it.png' alt='Italiano'> Italiano</a>"
          ?>
          </li>
          <li><?php echo
              $lang === 'hu'
                  ? "<img src='images/hu.png' alt='Magyar'> Magyar <i class='fa fa-check' style='color:green;'></i>"
                  : "<a href=\"?lang=hu&amp;comp="
                  . strval(filter_input(INPUT_GET, 'comp', FILTER_SANITIZE_NUMBER_INT))
                  . "\" style='text-decoration: none'><img src='images/hu.png' alt='Magyar'> Magyar</a>"
          ?>
          </li>
          <li><?php echo
              $lang === 'es'
                  ? "<img src='images/es.png' alt='Español'> Español <i class='fa fa-check' style='color:green;'></i>"
                  : "<a href=\"?lang=es&amp;comp="
                  . strval(filter_input(INPUT_GET, 'comp', FILTER_SANITIZE_NUMBER_INT))
                  . "\" style='text-decoration: none'><img src='images/es.png' alt='Español'> Español</a>"
          ?>
          </li>
          <li><?php echo
              $lang === 'pl'
                  ? "<img src='images/pl.png' alt='Polska'> Polska <i class='fa fa-check' style='color:green;'></i>"
                  : "<a href=\"?lang=pl&amp;comp="
                  . strval(filter_input(INPUT_GET, 'comp', FILTER_SANITIZE_NUMBER_INT))
                  . "\" style='text-decoration: none'><img src='images/pl.png' alt='Polska'> Polska</a>"
          ?>
          </li>
          <li><?php echo
              $lang === 'pt'
                  ? "<img src='images/pt.png?a' alt='Português'> Português <i class='fa fa-check' style='color:green;'></i>"
                  : "<a href=\"?lang=pt&amp;comp="
                  . strval(filter_input(INPUT_GET, 'comp', FILTER_SANITIZE_NUMBER_INT))
                  . "\" style='text-decoration: none'><img src='images/pt.png?a' alt='Português'> Português</a>"
          ?>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</div>
</nav>

<div class="maindiv" style="padding-left:2em">
<br>
<h1 class="categoriesheader">LIVE TODAY!</h1>
			<table style="width:100%" id="tblLiveComps">
			<tr><th style="width:25%; text-align:left"><?= $_DATE ?></th><th style="width:50%; text-align:left"><?= $_EVENTNAME ?></th><th style="text-align:left"><?= $_ORGANIZER ?></th></tr>
<?php

$comps = Emma::GetCompetitionsToday();

foreach ($comps as $comp) { ?>
                <tr id="rowi-tody<?= $comp['tavid'] ?>" style="font-weight:bold;"><td><?=
        date('Y-m-d', strtotime($comp['compDate']))
    ?></td>
                <td><a onmouseover="colorRow('row-today<?= $comp['tavid'] ?>')" onmouseout="resetRow('row<?= $comp['tavid'] ?>')" href="followfull.php?comp=<?=
        $comp['tavid']
    ?>&amp;lang=<?= $lang ?>"><?= $comp['compName'] ?></a></td>
                <td style="font-weight:normal"><?= $comp['organizer'] ?></td>
                </tr>
        <?php } ?>
                        </table>
<br>

<h1 class="categoriesheader"><?= $_CHOOSECMP ?></h1>
			<table style="width:100%" id="tblComps">
			<tr><th style="width:25%; text-align:left;"><?= $_DATE ?></th><th style="width:50%; text-align:left"><?= $_EVENTNAME ?></th><th style="text-align:left"><?= $_ORGANIZER ?></th></tr>
<?php

$comps = Emma::GetCompetitions();

foreach ($comps as $comp) { ?>
		<tr id="row<?= $comp['tavid'] ?>"><td><?= date('Y-m-d', strtotime($comp['compDate'])) ?></td>
		<td><a onmouseover="colorRow('row<?= $comp['tavid'] ?>')" onmouseout="resetRow('row<?= $comp['tavid'] ?>')" href="followfull.php?comp=<?=
        $comp['tavid']
    ?>&amp;lang=<?= $lang ?>"><?= $comp['compName'] ?></a></td>
		<td><?= $comp['organizer'] ?></td>
		</tr>
	<?php }

?>
</table>

<br><br>
</div>
</body>
</html>
