<?php

declare(strict_types=1);

date_default_timezone_set('Europe/London');
$lang = 'en';

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
}

include_once 'templates/emmalang_en.php';
include_once "templates/emmalang_{$lang}.php";
include_once 'templates/classEmma.class.php';

$RunnerStatus = [
    '1' => $_STATUSDNS,
    '2' => $_STATUSDNF,
    '11' => $_STATUSWO,
    '12' => $_STATUSMOVEDUP,
    '9' => $_STATUSNOTSTARTED,
    '0' => $_STATUSOK,
    '3' => $_STATUSMP,
    '4' => $_STATUSDSQ,
    '5' => $_STATUSOT,
    '9' => '',
    '10' => '',
];

header('content-type: application/json; charset=' . $CHARSET);
header('Access-Control-Allow-Origin: *');
header('pragma: public');
//header('cache-control: max-age=60');
//header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 10));

if (!isset($_GET['method'])) {
    $_GET['method'] = null;
}

$pretty = isset($_GET['pretty']);
$br = $pretty ? "\n" : '';
///Method returns all competitions available
switch ($_GET['method']) {
    case 'getcompetition':
        header('cache-control: max-age=60');
        $comps = Emma::GetCompetitions();
        echo "{ \"competitions\": [{$br}";
        $first = true;
        foreach ($comps as $comp) {
            if (!$first) {
                echo ',';
            }
            echo
                '{"id": '
                    . $comp['tavid']
                    . ', "name": "'
                    . $comp['compName']
                    . '", "organizer": "'
                    . $comp['organizer']
                    . '", "date": "'
                    . date('Y-m-d', strtotime($comp['compDate']))
                    . '"'
            ;

            echo ', "timediff": ' . $comp['timediff'];
            if ($comp['multidaystage'] !== '') {
                echo
                    ', "multidaystage": ' . $comp['multidaystage'] . ', "multidayfirstday": ' . $comp['multidayparent']
                ;
            }

            echo "}{$br}";
            $first = false;
        }
        echo ']';
        break;
    case 'getcompetitioninfo':
        $compid = $_GET['comp'];
        $comp = Emma::GetCompetition(intval($compid));
        set_cache_header(filter_var($comp['sprint'], FILTER_VALIDATE_BOOLEAN));
        if (isset($comp['tavid'])) {
            echo
                '{"id": '
                    . $comp['tavid']
                    . ', "name": "'
                    . $comp['compName']
                    . '", "organizer": "'
                    . $comp['organizer']
                    . '", "date": "'
                    . date('Y-m-d', strtotime($comp['compDate']))
                    . '"'
            ;

            echo ', "timediff": ' . $comp['timediff'];
            echo ', "timezone": "' . $comp['timezone'] . '"';
            echo ', "isPublic": ' . (isset($comp['public']) ? $comp['public'] : false);
            if ($comp['multidaystage'] !== '') {
                echo
                    ', "multidaystage": ' . $comp['multidaystage'] . ', "multidayfirstday": ' . $comp['multidayparent']
                ;
            }

            echo '}';
        } else {
            echo '{"id": ' . filter_input(INPUT_GET, 'compid', FILTER_SANITIZE_NUMBER_INT) . '}';
        }
        break;
    case 'getlastpassings':
        $currentComp = new Emma((int) $_GET['comp']);
        set_cache_header(filter_var($currentComp->IsSprint(), FILTER_VALIDATE_BOOLEAN));
        $lastPassings = $currentComp->getLastPassings(7);

        $first = true;
        $ret = '';
        foreach ($lastPassings as $pass) {
            if (!$first) {
                $ret .= ",{$br}";
            }
            $dt = new DateTime($pass['Changed'], new DateTimeZone('UTC'));
            $dt->setTimeZone(new DateTimeZone('Europe/London'));
            $ret .=
                '{"passtime": "'
                . $dt->format('G:i:s')
                . '",
					"runnerName": "'
                . $pass['Name']
                . '",
					"class": "'
                . $pass['class']
                . '",
					"control": '
                . $pass['Control']
                . ',
					"controlName" : "'
                . $pass['pname']
                . '",
					"time": "'
                . format_time($pass['Time'], $pass['Status'], $RunnerStatus)
                . '" }';
            $first = false;
        }

        $hash = MD5($ret);
        if (isset($_GET['last_hash']) && $_GET['last_hash'] === $hash) {
            echo '{ "status": "NOT MODIFIED"}';
        } else {
            echo "{ \"status\": \"OK\", {$br}\"passings\" : [{$br}{$ret}{$br}],{$br} \"hash\": \"{$hash}\"}";
        }
        break;
    case 'getclasses':
        $currentComp = new Emma((int) $_GET['comp']);
        set_cache_header(filter_var($currentComp->IsSprint(), FILTER_VALIDATE_BOOLEAN));
        $classes = $currentComp->Classes();
        $ret = '';
        $first = true;

        foreach ($classes as $class) {
            if (!$first) {
                $ret .= ",{$br}";
            }
            $ret .= '{"className": "' . $class['Class'] . '"}';
            $first = false;
        }

        $hash = MD5($ret);

        if (isset($_GET['last_hash']) && $_GET['last_hash'] === $hash) {
            echo '{ "status": "NOT MODIFIED"}';
        } else {
            echo "{ \"status\": \"OK\", \"classes\" : [{$br}{$ret}{$br}]";
            echo ",{$br} \"hash\": \"" . $hash . '"}';
        }
        break;
    case 'getclubresults':
        $currentComp = new Emma((int) $_GET['comp']);
        set_cache_header(filter_var($currentComp->IsSprint(), FILTER_VALIDATE_BOOLEAN));
        $club = $_GET['club'];
        $results = $currentComp->getClubResults((int) $_GET['comp'], $club);
        $ret = '';
        $unformattedTimes = false;
        $first = true;

        if (isset($_GET['unformattedTimes']) && $_GET['unformattedTimes'] === 'true') {
            $unformattedTimes = true;
        }

        foreach ($results as $res) {
            $time = $res['Time'];
            $status = intval($res['Status']);

            if ($time === '') {
                $status = 9;
            }

            $cp = $res['Place'];
            if ($status === 9 || $status === 10) {
                $cp = '';
            } elseif ($status !== 0 || $time < 0) {
                $cp = '-';
            }

            $timeplus = $res['TimePlus'];

            $age = time() - strtotime($res['Changed']);
            $modified = $age < 120 ? 1 : 0;

            if (!$unformattedTimes) {
                $time = format_time($res['Time'], $res['Status'], $RunnerStatus);
                $timeplus = '+' . format_time($timeplus, $res['Status'], $RunnerStatus);
            }

            if (!$first) {
                $ret .= ",{$br}";
            }

            $ret .=
                "{\"place\": \"{$cp}\", \"name\": \""
                . $res['Name']
                . '", "club": "'
                . $res['Club']
                . '","class": "'
                . $res['Class']
                . '", "result": "'
                . $time
                . '","status" : '
                . $status
                . ", \"timeplus\": \"{$timeplus}\"";

            if (isset($res['start'])) {
                $ret .= ",{$br} \"start\": " . $res['start'];
            } else {
                $ret .= ",{$br} \"start\": \"\"";
            }

            if ($modified) {
                $ret .= ",{$br} \"DT_RowClass\": \"new_result\"";
            }

            $ret .= "{$br}}";

            $first = false;
        }

        $hash = MD5($ret);
        if (isset($_GET['last_hash']) && $_GET['last_hash'] === $hash) {
            echo '{ "status": "NOT MODIFIED"}';
        } else {
            echo "{ \"status\": \"OK\",{$br} \"clubName\": \"" . $club . "\", {$br}\"results\": [{$br}{$ret}{$br}]";
            echo ", {$br} \"hash\": \"" . $hash . '"}';
        }
        break;
    case 'getsplitcontrols':
        $currentComp = new Emma((int) $_GET['comp']);
        set_cache_header(filter_var($currentComp->IsSprint(), FILTER_VALIDATE_BOOLEAN));
        $splits = $currentComp->getAllSplitControls();
        $splitJSON = "[{$br}";
        $first = true;
        foreach ($splits as $split) {
            if (!$first) {
                $splitJSON .= ",{$br}";
            }
            $splitJSON .=
                '{ "class": '
                . $split['className']
                . ', "code": '
                . $split['code']
                . ', "name": "'
                . $split['name']
                . '", "order": "'
                . $split['corder']
                . '"}';
            $first = false;
        }
        $splitJSON .= "{$br}]";
        $hash = MD5($splitJSON);
        if (isset($_GET['last_hash']) && $_GET['last_hash'] === $hash) {
            echo '{ "status": "NOT MODIFIED"}';
        } else {
            echo "{ \"status\": \"OK\",{$br} \"splitcontrols\": {$splitJSON}";
            echo ",{$br} \"hash\": \"" . $hash . '"}';
        }
        break;
    case 'getclassresults':
        $class = rawurldecode($_GET['class']);
        $currentComp = new Emma((int) $_GET['comp']);
        set_cache_header(filter_var($currentComp->IsSprint(), FILTER_VALIDATE_BOOLEAN));
        $results = $currentComp->getAllSplitsForClass($class);
        $splits = $currentComp->getSplitControlsForClass($class);

        $total = null;
        $retTotal = false;
        if (isset($_GET['includetotal']) && $_GET['includetotal'] === 'true') {
            $retTotal = true;
            $total = $currentComp->getTotalResultsForClass($class);

            foreach ($results as $key => $res) {
                $id = $res['DbId'];

                $results[$key]['totaltime'] = $total[$id]['Time'];
                $results[$key]['totalstatus'] = $total[$id]['Status'];
                $results[$key]['totalplace'] = $total[$id]['Place'];
                $results[$key]['totalplus'] = $total[$id]['TotalPlus'];
            }
        }

        $ret = '';
        $first = true;
        $place = 1;
        $lastplace = 0;
        $lastTime = -9999;
        $winnerTime = 0;
        $resultsAsArray = false;
        $unformattedTimes = false;
        if (isset($_GET['resultsAsArray'])) {
            $resultsAsArray = true;
        }

        if (isset($_GET['unformattedTimes']) && $_GET['unformattedTimes'] === 'true') {
            $unformattedTimes = true;
        }

        $splitJSON = "[{$br}";
        foreach ($splits as $split) {
            if (!$first) {
                $splitJSON .= ",{$br}";
            }
            $splitJSON .= '{ "code": ' . $split['code'] . ', "name": "' . $split['name'] . '"}';
            $first = false;
            usort($results, function ($a, $b) use ($split) {
                if (!isset($a[$split['code'] . '_time']) && isset($b[$split['code'] . '_time'])) {
                    return 1;
                }
                if (isset($a[$split['code'] . '_time']) && !isset($b[$split['code'] . '_time'])) {
                    return -1;
                }
                if (!isset($a[$split['code'] . '_time']) && !isset($b[$split['code'] . '_time'])) {
                    return 0;
                }
                if (isset($a[$split['code'] . '_time'], $b[$split['code'] . '_time'])) {
                    if ($b[$split['code'] . '_time'] === $a[$split['code'] . '_time']) {
                        return 0;
                    }
                    return $a[$split['code'] . '_time'] < $b[$split['code'] . '_time'] ? -1 : 1;
                }
            });

            $splitplace = 1;
            $cursplitplace = 1;
            $cursplittime = '';
            $bestsplittime = -1;
            foreach ($results as $key => $res) {
                $sp_time = '';
                $raceTime = $res['Time'];
                $raceStatus = intval($res['Status']);
                if ($raceTime === '') {
                    $raceStatus = 9;
                }

                if (isset($res[$split['code'] . '_time'])) {
                    $sp_time = $res[$split['code'] . '_time'];
                    if ($bestsplittime < 0 && ($raceStatus === 0 || $raceStatus === 9 || $raceStatus === 10)) {
                        $bestsplittime = $sp_time;
                    }
                }

                if ($sp_time !== '') {
                    $results[$key][$split['code'] . '_timeplus'] = $sp_time - $bestsplittime;
                } else {
                    $results[$key][$split['code'] . '_timeplus'] = -1;
                }

                if ($cursplittime !== $sp_time) {
                    $cursplitplace = $splitplace;
                }
                if ($raceStatus === 0 || $raceStatus === 9 || $raceStatus === 10) {
                    $results[$key][$split['code'] . '_place'] = $cursplitplace;
                    $splitplace++;
                    if (isset($res[$split['code'] . '_time'])) {
                        $cursplittime = $res[$split['code'] . '_time'];
                    }
                } else {
                    $results[$key][$split['code'] . '_place'] = '"-"';
                }
            }
        }

        usort($results, 'sort_by_result');
        $splitJSON .= "{$br}]";

        $first = true;
        $firstNonQualifierSet = false;
        foreach ($results as $res) {
            if (!$first) {
                $ret .= ',';
            }
            $time = intval($res['Time']);

            if ($first) {
                $winnerTime = $time;
            }

            $status = intval($res['Status']);
            if ($res['FinalPos'] > 0) {
                $place = intval($res['FinalPos']);
            }
            $cp = $place;
            $progress = 0;

            if ($time === '') {
                $status = 9;
            }

            if ($status === 9 || $status === 10) {
                $cp = '';

                if (count($splits) === 0) {
                    $progress = 0;
                } else {
                    $passedSplits = 0;
                    $splitCnt = 0;
                    foreach ($splits as $split) {
                        $splitCnt++;
                        if (isset($res[$split['code'] . '_time'])) {
                            $passedSplits = $splitCnt;
                        }
                    }
                    $progress = ($passedSplits * 100.0) / (count($splits) + 1);
                }
            } elseif ($status !== 0 || $time < 0) {
                $cp = '-';
                $progress = 100;
            } elseif ($place === $lastplace) {
                $cp = strval($place) . '=';
                $progress = 100;
            }

            $timeplus = '';

            if ($time > 0 && $status === 0) {
                $timeplus = $time - $winnerTime;
                $progress = 100;
            }

            $age = time() - strtotime($res['Changed']);
            $modified = $age < 120 ? 1 : 0;

            if (!$unformattedTimes) {
                $time = format_time($res['Time'], $res['Status'], $RunnerStatus);
                $timeplus = '+' . format_time($timeplus, $res['Status'], $RunnerStatus);
            }

            $tot = '';
            if ($retTotal) {
                $tot =
                    ', "totalresult": '
                    . $res['totaltime']
                    . ', "totalstatus": '
                    . $res['totalstatus']
                    . ', "totalplace": "'
                    . $res['totalplace']
                    . '", "totalplus": '
                    . $res['totalplus'];
            }

            if ($resultsAsArray) {
                $ret .=
                    "[\"{$cp}\", \""
                    . $res['Name']
                    . "\",{$br} \""
                    . str_replace('"', "'", $res['Club'])
                    . "\",{$br} "
                    . $res['Time']
                    . ",{$br} "
                    . $status
                    . ",{$br} "
                    . ($time - $winnerTime)
                    . ",{$modified}]";
            } else {
                $ret .=
                    "{\"place\": \"{$cp}\",{$br} \"name\": \""
                    . $res['Name']
                    . "\",{$br} \"club\": \""
                    . str_replace('"', "'", $res['Club'])
                    . "\",{$br} \"result\": \""
                    . $time
                    . "\",{$br} \"status\" : "
                    . $status
                    . ",{$br} \"timeplus\": \"{$timeplus}\",{$br} \"progress\": {$progress} {$tot}";

                if (count($splits) > 0) {
                    $ret .= ",{$br} \"splits\": {";
                    $firstspl = true;
                    foreach ($splits as $split) {
                        if (!$firstspl) {
                            $ret .= ",{$br}";
                        }
                        if (isset($res[$split['code'] . '_time'])) {
                            $splitStatus = $status;
                            if ($status === 9 || $status === 10) {
                                $splitStatus = 0;
                            }

                            $ret .=
                                '"'
                                . $split['code']
                                . '": '
                                . $res[$split['code'] . '_time']
                                . ',"'
                                . $split['code']
                                . '_status": '
                                . $splitStatus
                                . ',"'
                                . $split['code']
                                . '_place": '
                                . $res[$split['code'] . '_place']
                                . ',"'
                                . $split['code']
                                . '_timeplus": '
                                . $res[$split['code'] . '_timeplus'];
                            $spage = time() - strtotime($res[$split['code'] . '_changed']);
                            if ($spage < 120) {
                                $modified = true;
                            }
                        } else {
                            $ret .=
                                '"'
                                . $split['code']
                                . '": "","'
                                . $split['code']
                                . '_status": 1,"'
                                . $split['code']
                                . '_place": ""';
                        }

                        $firstspl = false;
                    }

                    $ret .= '}';
                }

                if (isset($res['start'])) {
                    $ret .= ",{$br} \"start\": " . $res['start'];
                } else {
                    $ret .= ",{$br} \"start\": \"\"";
                }

                $rowClass = '';
                if ($modified) {
                    $rowClass = 'new_result';
                    $ret .= ",{$br} \"DT_RowClass\": \"new_result\"";
                }

                if (strlen($rowClass) > 0) {
                    $ret .= ",{$br} \"DT_RowClass\": \"{$rowClass}\"";
                }

                $ret .= "{$br}}";
            }
            $first = false;
            $lastplace = $place;
            $place++;
            $lastTime = $time;
        }

        $hash = MD5($ret);
        if (isset($_GET['last_hash']) && $_GET['last_hash'] === $hash) {
            echo '{ "status": "NOT MODIFIED"}';
        } else {
            echo
                "{ \"status\": \"OK\",{$br} \"className\": \""
                    . $class
                    . "\",{$br} \"splitcontrols\": {$splitJSON},{$br} \"results\": [{$br}{$ret}{$br}]"
            ;

            echo ",{$br} \"hash\": \"" . $hash . '"}';
        }
        ;
        break;
    default:
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
        header($protocol . ' ' . (400) . ' Bad Request');

        echo '{ "status": "ERR", "message": "No method given"}';
        break;
}

function sort_by_result(array $a, array $b): int
{
    if ($a['Status'] === 0 && $b['Status'] !== 0) {
        return -1;
    }
    if ($a['Status'] !== 0 && $b['Status'] === 0) {
        return 1;
    }
    if ($a['Status'] !== $b['Status']) {
        return $a['Status'] - $b['Status'];
    }
    if ($a['FinalPos'] > 0 || $b['FinalPos'] > 0) {
        return $a['FinalPos'] - $b['FinalPos'];
    }
    return (int) $a['Time'] - (int) $b['Time'];
}

function format_time(int $time, string $status, array &$RunnerStatus): string
{
    global $lang;

    if ($status !== '0') {
        return (string) $RunnerStatus[$status]; //$status;
    }

    if ($lang === 'fi') {
        $hours = floor($time / 360000);

        $minutes = floor(($time - ($hours * 360000)) / 6000);

        $seconds = floor(($time - ($hours * 360000) - ($minutes * 6000)) / 100);

        if ($hours > 0) {
            return (
                strval($hours)
                . ':'
                . str_pad(strval($minutes), 2, '0', STR_PAD_LEFT)
                . ':'
                . str_pad(strval($seconds), 2, '0', STR_PAD_LEFT)
            );
        }
        // Zero hours
        return strval($minutes) . ':' . str_pad(strval($seconds), 2, '0', STR_PAD_LEFT);
    }

    // not Finnish language
    $minutes = floor($time / 6000);

    $seconds = floor(($time - ($minutes * 6000)) / 100);

    return str_pad(strval($minutes), 2, '0', STR_PAD_LEFT) . ':' . str_pad(strval($seconds), 2, '0', STR_PAD_LEFT);
}

function url_raw_decode(string $raw_url_encoded)
{
    $res = [];
    // Hex conversion table
    $hex_table = [
        0 => 0x00,
        1 => 0x01,
        2 => 0x02,
        3 => 0x03,
        4 => 0x04,
        5 => 0x05,
        6 => 0x06,
        7 => 0x07,
        8 => 0x08,
        9 => 0x09,
        'A' => 0x0a,
        'B' => 0x0b,
        'C' => 0x0c,
        'D' => 0x0d,
        'E' => 0x0e,
        'F' => 0x0f,
    ];

    // Fixin' latin character problem
    if (preg_match_all("/\%C3\%([A-Z0-9]{2})/i", $raw_url_encoded, $res)) {
        $res = array_unique($res = $res[1]);
        $arr_unicoded = [];
        foreach ($res as $key => $value) {
            $arr_unicoded[] = chr(
                0xc0 | ($hex_table[substr($value, 0, 1)] << 4) | (0x03 & $hex_table[substr($value, 1, 1)]),
            );
            $res[$key] = '%C3%' . $value;
        }

        $raw_url_encoded = str_replace($res, $arr_unicoded, $raw_url_encoded);
    }

    // Return decoded  raw url encoded data
    return rawurldecode($raw_url_encoded);
}

function set_cache_header(bool $isSprint)
{
    if ($isSprint) {
        header('cache-control: max-age=10');
    } else {
        header('cache-control: max-age=60');
    }
}
