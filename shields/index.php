<?php
function generate($r)
{
    $dir = $_SERVER['DOCUMENT_ROOT']."/shields";
    $db = new mysqli("localhost", "travmap", "clinch", "TravelMapping") or die("Failed to connect to database");
    $sql_command = "SELECT * FROM routes WHERE root = '" . $r . "';";
    $res = $db->query($sql_command);
    $row = $res->fetch_assoc();

    if (file_exists("{$dir}/template_" . $row['systemName'] . ".svg")) {
        $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . ".svg");
    } else {
        $svg = file_get_contents("{$dir}/generic.svg");
    }

    switch ($row['systemName']) {
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

    $insert = strpos($svg, ".svg\">") + strlen(".svg\">");
    $svgdefs = <<<SVGDEFS
    <defs>
        <style type="text/css">@import url('/fonts/roadgeek.css');</style>
    </defs>
SVGDEFS;
    return substr($svg, 0, $insert).$svgdefs.substr($svg, $insert);
}

if(array_key_exists('shield', $_GET)) {
    echo generate($_GET['shield']);
}
?>
