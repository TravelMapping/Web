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
        case 'canab': case 'canqca': //uses different shields for 1, 2, 3 digits
            $routeNum = $row['route'];
            $routeNum = str_replace("AB", "", $routeNum);
            $routeNum = str_replace("A-", "", $routeNum);
            if (strlen($routeNum) > 1) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            if (strlen($routeNum) > 2) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide3.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
        case 'cansph':
            $region = explode(".", $r)[0];
            $routeNum = str_replace(strtoupper($region), "", $row['route']);
            if (strlen($routeNum) > 2) {
                if (file_exists("{$dir}/template_can" . $region . "_wide.svg")) {
                    $svg = file_get_contents("{$dir}/template_can" . $region . "_wide.svg");
                } else {
                    $svg = file_get_contents("{$dir}/generic_wide.svg");
                }
            } else {
                if (file_exists("{$dir}/template_can" . $region . ".svg")) {
                    $svg = file_get_contents("{$dir}/template_can" . $region . ".svg");
                } else {
                    $svg = file_get_contents("{$dir}/generic.svg");
                }
            }
            $region = strtoupper($region);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            $svg = str_replace("***SYS***", $region, $svg);
            break;
            
        case 'cantch': //do nothing
            break;
            
        case 'canyt':
            $routeNum = str_replace("YT", "", $row['route']); //gets rid of the extra YT on BC ones
            if (file_exists("{$dir}/template_canyt" . $routeNum . ".svg")) {
                $svg = file_get_contents("{$dir}/template_canyt" . $routeNum . ".svg");
            } 
            else {
                $svg = file_get_contents("{$dir}/generic_wide.svg");
            }
            break;
            
        case 'mexd':
            $routeNum = str_replace("MEX", "", $row['route']);
            $routeNum = str_replace("D", "", $routeNum); //handled by template
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_mexd_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
            
        case 'mexed':
            $region = explode(".", $r)[0];
            $region = strtoupper($region);
            if ($region == "MEXEMEX")
                 $region = "EMEX";
            else
                 $region = str_replace("MEX","", $region);
            $routeNum = str_replace($region, "", $row['route']);
            $routeNum = str_replace("D", "", $routeNum); //handled by template
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_mexed_wide.svg");
            }
            $svg = str_replace("***REGION***", $region, $svg);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'usai':
        case 'usaif':
            $routeNum = explode("-", $row['route'])[1];
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usai_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'usaib':
            //FIXME: route type text will not render small enough in clinched shield viewer
            $routeNum = explode("-", $row['route'])[1];
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usaib_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            $type = "LOOP";
            if ($row['banner'] == 'BS') $type = 'SPUR';
            $svg = str_replace("***TYPE***", $type, $svg);
            break;

        case 'usaus':
            $routeNum = str_replace("US", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usaus_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
            
        /*
        case 'usaush':
            $routeNum = str_replace("US", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usaush_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
        */

        case 'usausb':
            $routeNum = str_replace("US", "", $row['route']);
            $routeNum .= $row['banner'][0];
            if (strlen($routeNum) == 3) {
                $svg = file_get_contents("{$dir}/template_usausb_wide.svg");
            }
            if (strlen($routeNum) > 3) {
                $svg = file_get_contents("{$dir}/template_usausb_wide4.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'usaky3': case 'usaky4': case 'usaky5': case 'usaky6': case 'usaky7': case 'usaky8': case 'usaky9':
            $routeNum = str_replace("KY", "", $row['route']);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
            
        case 'usamts':
            $routeNum = str_replace("SR", "", $row['route']);
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
                if (file_exists("{$dir}/template_usa" . $region . ".svg")) {
                    $svg = file_get_contents("{$dir}/template_usa" . $region . ".svg");
                } else {
                    $svg = file_get_contents("{$dir}/generic.svg");
                }
            }
            $region = strtoupper($region);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            $svg = str_replace("***SYS***", $region, $svg);
            $svg = str_replace("***LETTER***", "", $svg); //hack to kill off the suffix for usaar
            break;

        case 'belb': case 'hunm': case 'lvaa': case 'lvap': case 'nldp': case 'nldr': case 'pola': case 'pols': case 'svkd': case 'svkr':
            // replace placeholder
            $svg = str_replace("***NUMBER***", $row['route'], $svg);
            break;

        case 'andcg':
            // replace placeholder, use wide svg file for 4-digit numbers
            if (strlen($row['route']) > 3) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $row['route'], $svg);
            break;

        case 'itaa':
            // replace placeholder, use wide svg file for 5-/7-digit numbers
            if (strlen($row['route']) > 4) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide5.svg");
            }
            if (strlen($row['route']) > 6) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide7.svg");
            }
            $svg = str_replace("***NUMBER***", $row['route'], $svg);
            break;

        case 'alakt': case 'alavt': case 'canmbw': case 'chea': case 'czed': case 'czei': case 'czeii': case 'deua': case 'dnksr': case 'deub': case 'estp': case 'estt': case 'finkt': case 'hunf': case 'islth': case 'ltuk': case 'poldk': case 'poldw': case 'svki': case 'swel':
            // replace placeholder, remove prefix
            $routeNum = str_replace("A", "", $row['route']);
            $routeNum = str_replace("B", "", $routeNum);
            $routeNum = str_replace("DK", "", $routeNum);
            $routeNum = str_replace("DW", "", $routeNum);
            $routeNum = str_replace("D", "", $routeNum);
            $routeNum = str_replace("F", "", $routeNum);
            $routeNum = str_replace("I", "", $routeNum);
            $routeNum = str_replace("Kt", "", $routeNum);
            $routeNum = str_replace("K", "", $routeNum);
            $routeNum = str_replace("L", "", $routeNum);
            $routeNum = str_replace("Rte", "", $routeNum);
            $routeNum = str_replace("SR", "", $routeNum);
            $routeNum = str_replace("TH", "", $routeNum);
            $routeNum = str_replace("T", "", $routeNum);
            $routeNum = str_replace("Vt", "", $routeNum);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'nlds':
            // replace placeholder, remove prefix and suffix
            $routeNum = substr($row['route'], 1, 3);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'autb': case 'luxn':
            // replace placeholder, remove prefix, use wide svg file for 3-digit numbers
            $routeNum = str_replace("B", "", $row['route']);
            $routeNum = str_replace("L", "", $routeNum);
            $routeNum = str_replace("N", "", $routeNum);
            if (strlen($routeNum) > 2) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'cheh': case 'dnkpr': case 'finvt': case 'norrv': case 'swer':
            // replace placeholder, remove prefix, use wide svg files for 2-/3-digit numbers
            $routeNum = str_replace("Fv", "", $row['route']);
            $routeNum = str_replace("H", "", $routeNum);
            $routeNum = str_replace("N", "", $routeNum);
            $routeNum = str_replace("PR", "", $routeNum);
            $routeNum = str_replace("Rv", "", $routeNum);
            $routeNum = str_replace("R", "", $routeNum);
            $routeNum = str_replace("Vt", "", $routeNum);
            if (strlen($routeNum) > 1) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            if (strlen($routeNum) > 2) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide3.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'beln0': case 'beln1': case 'beln2': case 'beln3': case 'beln4': case 'beln5': case 'beln6': case 'beln7': case 'beln8': case 'beln9': case 'beln':
            // replace placeholder, remove prefix, use wide svg files for 2-/3-digit numbers (Belgian workaround till beln is merged again)
            $routeNum = str_replace("N", "", $row['route']);
            $svg = file_get_contents("{$dir}/template_beln.svg");
            if (strlen($routeNum) > 1) {
                    $svg = file_get_contents("{$dir}/template_beln_wide.svg");
            }
            if (strlen($routeNum) > 2) {
                    $svg = file_get_contents("{$dir}/template_beln_wide3.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'norfv0': case 'norfv1': case 'norfv2': case 'norfv3': case 'norfv4': case 'norfv5': case 'norfv6': case 'norfv7': case 'norfv8': case 'norfv9': case 'norfv':
            // replace placeholder, remove prefix, use wide svg files for 2-/3-digit numbers (Norwegian workaround till norfv is merged again)
            $routeNum = str_replace("Fv", "", $row['route']);
            $svg = file_get_contents("{$dir}/template_norfv.svg");
            if (strlen($routeNum) > 1) {
                    $svg = file_get_contents("{$dir}/template_norfv_wide.svg");
            }
            if (strlen($routeNum) > 2) {
                    $svg = file_get_contents("{$dir}/template_norfv_wide3.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'roudn':
            // replace placeholder, remove prefix, use wide svg file for 4-digit numbers
            $routeNum = str_replace("DN", "", $row['route']);
            if (strlen($routeNum) > 3) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'prta': case 'prtip': case 'prtic':
            // replace placeholder, add blank after prefix
            $routeNum = str_replace("A", "A ", $row['route']);
            $routeNum = str_replace("IC", "IC ", $routeNum);
            $routeNum = str_replace("IP", "IP ", $routeNum);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'fraa': case 'fran': case 'frht': case 'spmn': case 'mtqa': case 'glpn': case 'gufn': case 'reun': case 'mara': case 'tuna': case 'mtqn':
            // replace placeholder, add blank after prefix, use wide svg files
            $routeNum = str_replace("A", "A ", $row['route']);
            $routeNum = str_replace("N", "N ", $routeNum);
            //$routeNum = str_replace("T", "T", $routeNum); //no blank required!
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
            
        case 'nclt':
            // replace placeholder, add blank after prefix, use wide svg files
            $routeNum = str_replace("T", "RT ", $row['route']);
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'espn':
            // replace placeholder, add hyphen after prefix, use wide svg files
            $routeNum = str_replace("N", "N-", $row['route']);
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'bela': case 'belr': case 'eure': case 'luxa': case 'luxb': case 'roua':
            // replace placeholder, use wide svg file for 3-digit numbers
            if (strlen($row['route']) > 2) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $row['route'], $svg);
            break;

        case 'nlda':
            // replace placeholder, use wide svg files for 3-/4-digit numbers
            if (strlen($row['route']) > 2) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            if (strlen($row['route']) > 3) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide4.svg");
            }
            $svg = str_replace("***NUMBER***", $row['route'], $svg);
            break;

        case 'espa':
            // replace placeholder, use wide svg files for 3-/4-/5-digit numbers (Spain is simplified: national (blue) motorway signs are generally used for national and regional motorways, numbering w/o "-" (e.g. A1 instead of A-1))
            if (strlen($row['route']) > 2) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide3.svg");
            }
            if (strlen($row['route']) > 3) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide4.svg");
            }
            if (strlen($row['route']) > 4) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide5.svg");
            }
            $svg = str_replace("***NUMBER***", $row['route'], $svg);
            break;

        case 'deubwl': case 'deubyst': case 'deubbl': case 'deuhel': case 'deumvl': case 'deunil': case 'deunwl': case 'deurpl': case 'deusll': case 'deusns': case 'deustl': case 'deushl': case 'deuthl':
            // replace placeholder, use wide svg files (German Landesstrassen)
            $svg = file_get_contents("{$dir}/template_deul" . strlen($row['route']) . ".svg");
            $svg = str_replace("***NUMBER***", $row['route'], $svg);
            break;

        case 'gbnm':case 'nirm':
            $routeNum = str_replace("M", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_gbnm_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'gbnam':case 'niram':
            $routeNum = str_replace("M", "", $row['route']);
            $routeNum = str_replace("A", "", $routeNum);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_gbnam_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
            
        case 'twnf':
            $routeNum = str_replace("F", "", $row['route']);
            $routeNum = str_replace("A", "甲", $routeNum); //suffix - hope there's no unicode issues
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'usasf': case 'usanp': case 'cannf': case 'eursf': case 'usakyp': case 'gbrtr':
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
            
        case 'usaar':
            $matches = [];
            $routeNum = str_replace('AR', "", $row['route']);
            if (preg_match('/(?<number>[0-9]+)(?<letter>[A-Za-z]+)/', $routeNum, $matches)) {
                $svg = str_replace("***NUMBER***", $matches['number'], $svg);
                $svg = str_replace("***LETTER***", $matches['letter'], $svg);
                break;
            }
            else {
                $svg = str_replace("***NUMBER***", $routeNum, $svg);
                $svg = str_replace("***LETTER***", "", $svg);
                break;
            }

        case 'usanh':
            $matches = [];
            $routeNum = str_replace('NH', "", $row['route']);
            if (preg_match('/(?<number>[0-9]+)(?<letter>[A-Za-z]+)/', $routeNum, $matches)) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide4.svg");
                $svg = str_replace("***NUMBER***", $matches['number'], $svg);
                $svg = str_replace("***LETTER***", $matches['letter'], $svg);
                break;
            }
            elseif (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");  
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
                
            break;
            
        case 'usanes':
            $matches = [];
            $routeNum = str_replace('NE', "", $row['route']);
            if ($routeNum[0] == 'L') {
                $svg_path = "{$dir}/template_usanes_link.svg";
            	$svg = file_get_contents($svg_path);
            	$routeNum = str_replace('L', "", $routeNum);
            	preg_match('/(?<number>[0-9]+)(?<letter>[A-Za-z]+)/', $routeNum, $matches);
            }
            else { //S
                $svg_path = "{$dir}/template_usanes_spur.svg";
                $svg = file_get_contents($svg_path);
            	$routeNum = str_replace('S', "", $routeNum);
            	preg_match('/(?<number>[0-9]+)(?<letter>[A-Za-z]+)/', $routeNum, $matches);
            }
            $svg = str_replace("***NUMBER***", $matches['number'], $svg);
            $svg = str_replace("***LETTER***", $matches['letter'], $svg);
        break;
            
        //the following cases are meant to fall through to the default
        //TODO: fix this

        case 'usatx': case 'usatxl': case 'usatxs':
            if ($row['root'] == 'tx.nasa1' or $row['systemName'] != 'usatx' or $row['banner'] != "") {
                $system = "";
                $num = "";
                $svg_path = "{$dir}/template_usatx_aux.svg";

                $sys_map['Lp'] = "LOOP";
                $sys_map['Spr'] = "SPUR";
                $sys_map['Bus'] = "BUS";
                $sys_map['Trk'] = "TRUCK";

                if ($row['root'] == 'tx.nasa1') {
                    $system = "NASA";
                    $num = "1";
                } elseif ($row['root'] == 'tx.lp008') {
                    $system = "BELTWAY";
                    $num = "8";
                } else {
                    $matches = [];
                    preg_match('/(TX|)(?<system>[A-Za-z]+)(?<number>[0-9]+)/', $row['route'], $matches);
                    
                    if(array_key_exists($matches['system'], $sys_map)) $system = $sys_map[$matches['system']];
                    else $system = $sys_map[$row['banner']];

                    $num = $matches['number'];

                    if (strlen($num) >= 3) {
                        $svg_path = "{$dir}/template_usatx_aux_wide.svg";
                    }
                }

                $svg = file_get_contents($svg_path);
                $svg = str_replace("***NUMBER***", $num, $svg);
                $svg = str_replace("***SYS***", $system, $svg);
                break;
            }

        default:
            $region = strtoupper(explode(".", $r)[0]);
            $routeNum = str_replace($region, "", $row['route']);
            if (strlen($routeNum) > 3) {
                if (file_exists("{$dir}/template_" . $row['systemName'] . "_wide4.svg")) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide4.svg");
                }
                elseif (file_exists("{$dir}/template_" . $row['systemName'] . "_wide.svg")) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
                }
                else {
                    $svg = file_get_contents("{$dir}/generic_wide.svg");
                }
            }
            elseif (strlen($routeNum) > 2) {
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
