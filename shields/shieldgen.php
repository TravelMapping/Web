<?php
function generate($r, $force_reload = false)
{
    global $tmdb;
    $dir = $_SERVER['DOCUMENT_ROOT']."/shields";
    if(file_exists("{$dir}/cache/shield_{$r}.svg") && !$force_reload) {
        //load from cache
        return file_get_contents("{$dir}/cache/shield_{$r}.svg");
    }

    $sql_command = "SELECT * FROM routes WHERE root = '" . $r . "';";
    $res = tmdb_query($sql_command);
    $row = $res->fetch_assoc();
    $res->free();

    if (file_exists("{$dir}/template_" . $row['systemName'] . ".svg")) {
        $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . ".svg");
    } else {
        $svg = file_get_contents("{$dir}/generic.svg");
    }

    switch ($row['systemName']) {
        case 'cantch': //do nothing
            break;

        case 'usai':
        case 'usaif':
            $routeNum = explode("-", $row['route'])[1];
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usai_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'usaus':
        case 'usausb':
            $routeNum = str_replace("US", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usaus_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'usaky3':
            $routeNum = str_replace("KY", "", $row['route']);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'usamt':
            $routeNum = str_replace("MT", "", $row['route']);
            if (strlen($routeNum) > 3) {
                $svg = file_get_contents("{$dir}/template_usamt_wide4.svg");
            } elseif (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usamt_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'usansf':
            $region = explode(".", $r)[0];
            $routeNum = str_replace(strtoupper($region), "", $row['route']);
            if (strlen($routeNum) > 2) {
                if (file_exists("{$dir}/template_usa" . $region . "_wide.svg")) {
                    $svg = file_get_contents("{$dir}/template_usa" . $region . "_wide.svg");
                } else {
                    $svg = file_get_contents("{$dir}/generic_wide.svg");
                }
            } else {
                if (file_exists("template_usa" . $region . ".svg")) {
                    $svg = file_get_contents("{$dir}/template_usa" . $region . ".svg");
                } else {
                    $svg = file_get_contents("{$dir}/generic.svg");
                }
            }
            $region = strtoupper($region);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            $svg = str_replace("***SYS***", $region, $svg);
            break;

        case 'gbnm':case 'nirm':
            $routeNum = str_replace("M", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_gbnm_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'gbnam':case 'niram':
            $routeNum = str_replace("M", "", $row['route']);;
            $routeNum = str_replace("A", "", $routeNum);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_gbnam_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'usasf':case 'usanp':
            $lines = explode(',',preg_replace('/(?!^)[A-Z]{3,}(?=[A-Z][a-z])|[A-Z][a-z]/', ',$0', $row['route']));
            $index = 0;
            foreach($lines as $line) {
                if(strlen($line) > 0) {
                    $svg = str_replace("***NUMBER".($index + 1)."***", $line, $svg);
                    $index++;
                }
            }
            while($index < 3) {
                $svg = str_replace("***NUMBER".($index + 1)."***", "", $svg);
                $index++;
            }
            break;

        default:
            $region = strtoupper(explode(".", $r)[0]);
            $routeNum = str_replace($region, "", $row['route']);
            if (strlen($routeNum) > 2) {
                if (file_exists("{$dir}/template_" . $row['systemName'] . "_wide.svg")) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
                } else {
                    $svg = file_get_contents("{$dir}/generic_wide.svg");
                }
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            $svg = str_replace("***SYS***", $region, $svg);
            break;
    }
    if (!file_exists("{$dir}/cache/")) {
        mkdir("{$dir}/cache/", 0777, true);
    }
    file_put_contents("{$dir}/cache/shield_{$r}.svg", $svg);
    return $svg;
}

if(array_key_exists('shield', $_GET)) {
    echo generate($_GET['shield'], true);
}
?>
