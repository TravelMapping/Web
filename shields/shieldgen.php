<!-- /shields/shieldgen.php: shield generator PHP for Travel Mapping -->
<?php
/*
   PHP to include in any Travel Mapping page that wishes to include
   a graphical shield for routes.
   
   The tmphpfuncs.php file needs to be included also to create the
   connection to the database.
   
   The functionality is provided by the tm_shield_generate
   function.
   
   A cache directory is created on the server to give quicker access
   to previously-generated shields.  It can be cleared manually at
   the command-line.
 */

// Generate a shield representing the route specified by the $r parameter,
// which should be a TM "root" such as ny.i090 or bc.tchyel .
// Specifying true as the optional second parameter will ignore any
// version of the shield that might be in the cache.
function tm_shield_generate($r, $force_reload = false) {

    // where is our shields directory?
    $dir = $_SERVER['DOCUMENT_ROOT']."/shields";

    // check for existence of the requested shield for this specific route 
    if (file_exists("{$dir}/shield_{$r}.svg")) {
        return file_get_contents("{$dir}/shield_{$r}.svg");
    }

    // check for existence of the requested shield in the cache
    if (file_exists("{$dir}/cache/shield_{$r}.svg") && !$force_reload) {
        // load from cache
        return file_get_contents("{$dir}/cache/shield_{$r}.svg");
    }

    // get all information about the requested root, store it in
    // the $row array, indexed by the column headers from the routes
    // table in the DB
    $sql_command = "SELECT * FROM routes WHERE root = '" . $r . "';";
    $res = tmdb_query($sql_command);
    $row = $res->fetch_assoc();
    $res->free();

    // see if there is a template specific to this system
    if (file_exists("{$dir}/template_" . $row['systemName'] . ".svg")) {
        $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . ".svg");
    }
    else {
        $svg = file_get_contents("{$dir}/generic.svg");
    }

    // special modifications for various systems
    switch ($row['systemName']) {

		case 'afrtah':
            $routeNum = str_replace("TAH", "", $row['route']);
            if (file_exists("{$dir}/template_afrtah_" . $routeNum . ".svg")) {
                $svg = file_get_contents("{$dir}/template_afrtah_" . $routeNum . ".svg");
            } 
            break;
		
		case 'argrn':
            $routeNum = str_replace("RN", "", $row['route']);
            if (strlen($routeNum) > 3) {
                $svg = file_get_contents("{$dir}/template_argrn_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'brabr':
            $routeNum = str_replace("BR", "", $row['route']);
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'brabac':
		case 'braesc':
		case 'bramgc':
		case 'braprc':
		case 'brarsc':
		case 'brascc':
			$routeNum = str_replace("BA", "BR-", $row['route']);
			$routeNum = str_replace("ES", "BR-", $routeNum);
			$routeNum = str_replace("MGC", "BR-", $routeNum);
			$routeNum = str_replace("PRC", "BR-", $routeNum);
			$routeNum = str_replace("RSC", "BR-", $routeNum);
			$routeNum = str_replace("SC", "BR-", $routeNum);
			$region = str_replace("BRA-", "", $row['region']);
			$svg = file_get_contents("{$dir}/template_braxxc.svg");
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			$svg = str_replace("***REGION***", $region, $svg);
			break;

		case 'brasp':
            $routeNum = str_replace("SP00", "", $row['route']);
			$routeNum = str_replace("SP0", "", $routeNum);
			$routeNum = str_replace("SP", "", $routeNum);
			if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
			else {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . ".svg");
            }
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'bradf':
			$routeNum = str_replace("DF", "", $row['route']);
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . ".svg");
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
		
		case 'chlrn':
			$routeNum = str_replace("R", "", $row['route']);
			if (str_ends_with($routeNum, 'CH')) {
				$routeNum = str_replace("CH", "", $routeNum);
				$svg = file_get_contents("{$dir}/template_chlrn_ch.svg");
			}
			elseif (strlen ($routeNum > 2)) {
				$svg = file_get_contents("{$dir}/template_chlrn_wide.svg");
			}
			else {
				$svg = file_get_contents("{$dir}/template_chlrn.svg");
			}
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;

		case 'crirp':
		case 'crirs':
		case 'crirt':
            $routeNum = str_replace("RP", "", $row['route']);
            $routeNum = str_replace("RS", "", $routeNum);
            $routeNum = str_replace("RT", "", $routeNum);
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'ecue':
			$routeNum = str_replace("E", "", $row['route']);
			if (preg_match('/(?<number>[0-9]+)(?<letter>[A-Za-z]+)/', $routeNum, $matches)) {
				if ($matches['number'] % 5 == 0) {
					$svg = file_get_contents("{$dir}/template_ecue_blue_wide.svg");
				}
				elseif (strlen($matches['number']) > 2) {
					$svg = file_get_contents("{$dir}/template_ecue_green_wide5.svg");
				}
				else {
					$svg = file_get_contents("{$dir}/template_ecue_green_wide4.svg");
				}
				$routeNum = $matches['number'] . $matches['letter'];
				$routeNum = substr_replace($routeNum, "E", 0, 0);
				$svg = str_replace("***NUMBER***", $routeNum, $svg);
				break;
            }
            else {
				if (strlen($routeNum) > 2) {
					$svg = file_get_contents("{$dir}/template_ecue_green_wide4.svg");
				}
				elseif ($routeNum % 5 == 0) {
					$svg = file_get_contents("{$dir}/template_ecue_blue.svg");
				}
				else {
					$svg = file_get_contents("{$dir}/template_ecue_green.svg");
				}
				$routeNum = substr_replace($routeNum, "E", 0, 0);
				$svg = str_replace("***NUMBER***", $routeNum, $svg);
				break;
            }
		
		case 'hndrnp':
			$routeNum = str_replace("NAC0", "NAC-", $row['route']);
			$svg = file_get_contents("{$dir}/template_hndrn.svg");
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
			
		case 'hndrns':
			$routeNum = str_replace("NAC", "NAC-", $row['route']);
			if ($row['route'] > 6) {
				$svg = file_get_contents("{$dir}/template_hndrn_wide.svg");
			}
			else {
				$svg = file_get_contents("{$dir}/template_hndrn.svg");
			}
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;

		case 'naca':
			$routeNum = str_replace("CA", "CA-", $row['route']);
			if (strlen($routeNum) > 6) {
                $svg = file_get_contents("{$dir}/template_naca_gtm_wide.svg");
            }
			else {
				$svg = file_get_contents("{$dir}/template_naca_" . strtolower($row['region']) . ".svg");
			}
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
		
		case 'nicnic':
			$routeNum = str_replace("NIC", "NIC-", $row['route']);
			$svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
		
		case 'perpe':
		case 'prypy':
            $routeNum = str_replace("PE", "", $row['route']);
			$routeNum = str_replace("PY", "", $routeNum);
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'slvrn':
            $routeNum = str_replace("RN", "", $row['route']);
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'uryrn':
            $routeNum = str_replace("RN", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_uryrn_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
		
		case 'asiah':
            $routeNum = $row['route'];
            $svg = file_get_contents("{$dir}/template_asiah_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
		
		case 'ausm':
		case 'ausa':
		case 'ausr';
		case 'ausnswb':
		case 'ausntb':
		case 'aussab':
		case 'austasb':
		case 'ausvicb':
		case 'ausntc':
		case 'austasc':
		case 'ausvicc':
            // Australia M, R, A, B & C Routes
            $routeNum = $row['route'];
            $svg = file_get_contents("{$dir}/template_ausx_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'ausn':
			// Australian National Highways
			$routeNum = str_replace("N", "", $row['route']);
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
		
		case 'ausnt':
		case 'ausqld':
		case 'auswa':	
			// Australian State Routes
			$routeNum = str_replace("NT", "", $row['route']);
			$routeNum = str_replace("QLD", "", $routeNum);
			$routeNum = str_replace("WA", "", $routeNum);
			if (strlen($routeNum) > 2) {
				$svg = file_get_contents("{$dir}/template_auss_wide.svg");	
			}
			else {
				$svg = file_get_contents("{$dir}/template_auss.svg");
			}
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
				
		case 'ausab':
            $routeNum = $row['route'];
			$bannerType = strtoupper($row['banner']);
			$svg = file_get_contents("{$dir}/template_ausab.svg");
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			$svg = str_replace("***BANNER***", $bannerType, $svg);
			break;
			
		case 'ausqldmr';
		case 'ausvicmr';
			// Australian Metroads
			$routeNum = str_replace("MR", "", $row['route']);
			$svg = file_get_contents("{$dir}/template_" . $row['systemName'] . ".svg");
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
			       
		case 'ausstr':	
			// Australian Strategic Touring Routes
			$routeNum = $row['route'];
			if (strlen($routeNum) > 2) {
				$svg = file_get_contents("{$dir}/template_ausstr_wide.svg");	
			}
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;

		case 'ausnswtd':
		case 'ausqldtd':
		case 'aussatd':
		case 'ausvictd':
		case 'auswatd':
			// Australia Tourist Drives
			$routeNum = $row['route'];
			if (str_starts_with($routeNum, 'TD')) {
				$routeNum = str_replace("TD", "", $routeNum);
				$svg = file_get_contents("{$dir}/template_austd_wide" . strlen($routeNum) . ".svg");
				$svg = str_replace("***NUMBER***", $routeNum, $svg);
				break;
			}
			else {
				$lines = explode(',',preg_replace('/(?!^)[A-Z]{3,}(?=[A-Z][a-z])|[A-Z][a-z]/', ',$0', $row['route']));
            	$index = 0;
				$svg = file_get_contents("{$dir}/template_austd_text.svg");
            	foreach ($lines as $line) {
                	if (strlen($line) > 0) {
                    	$svg = str_replace("***NUMBER".($index + 1)."***", $line, $svg);
                    	$index++;
                	}
            	}
            	while ($index < 3) {
                	$svg = str_replace("***NUMBER".($index + 1)."***", "", $svg);
                	$index++;
            	}
            	break;
			}

		case 'nzlsh':
			$matches = [];
			$routeNum = str_replace("SH", "", $row['route']);
			if (preg_match('/(?<number>[0-9]+)(?<letter>[A-Za-z]+)/', $routeNum, $matches)) {
               if (strlen($matches['number']) > 1) {
					$svg = file_get_contents("{$dir}/template_nzlsh_wide.svg");
			   }
			   else {
					$svg = file_get_contents("{$dir}/template_nzlsh.svg");
			   }
			   $svg = str_replace("***NUMBER***", $matches['number'], $svg);
               $svg = str_replace("***LETTER***", $matches['letter'], $svg);
               break;
            }
            else {
               if (strlen($routeNum) > 1) {
					$svg = file_get_contents("{$dir}/template_nzlsh_wide.svg");
			   }
			   else {
					$svg = file_get_contents("{$dir}/template_nzlsh.svg");
			   }
			   $svg = str_replace("***NUMBER***", $routeNum, $svg);
               $svg = str_replace("***LETTER***", "", $svg);
               break;
            }

		case 'nzlrr':
			$routeNum = str_replace("RR", "", $row['route']);
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
		
		case 'brbh':
			$routeNum = str_replace("H", "Hwy ", $row['route']);
			$svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
		
        case 'canab':
        case 'canqca':
            // these use different shields for 1, 2, 3 digits
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
            
        case 'cantch': //do nothing
            break;
            
        case 'cannst':
            $routeNum = str_replace("NS", "", $row['route']);
            if (strlen($routeNum) > 1) { //2-digit uses wide shield
                $svg = file_get_contents("{$dir}/template_cannst_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
            
        case 'canyt':
            // get rid of the extra YT on BC ones
            $routeNum = str_replace("YT", "", $row['route']);
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

        case 'mexf':
            $routeNum = str_replace("MEX", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_mexf_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
       
        case 'mexed':
            $region = explode(".", $r)[0];
            $region = strtoupper($region);
            if ($region === "MEXEMEX") {
                $region = "EMEX";
            }
            else {
                $region = str_replace("MEX","", $region);
            }
            $routeNum = str_replace($region, "", $row['route']);
            $routeNum = str_replace("D", "", $routeNum); //handled by template
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_mexed_wide.svg");
            }
            $svg = str_replace("***REGION***", $region, $svg);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'mexemex':
		case 'mexnlm':
		case 'mexqro':
			$routeNum = str_replace("EMEX", "", $row['route']);
			$routeNum = str_replace("M", "", $routeNum);
			$routeNum = str_replace("QRO", "", $routeNum);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
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
            if ($row['banner'] === 'BS') $type = 'SPUR';
            $svg = str_replace("***TYPE***", $type, $svg);
            break;

        case 'usaus':
            $routeNum = str_replace("US", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usaus_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
            
        case 'usaush':
            $routeNum = str_replace("US", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usaush_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'usausb':
            $routeNum = str_replace("US", "", $row['route']);
            // let's not put the letter in when it shouldn't be
        //$routeNum .= $row['banner'][0];
            if (strlen($routeNum) === 3) {
                $svg = file_get_contents("{$dir}/template_usausb_wide.svg");
            }
            if (strlen($routeNum) > 3) {
                $svg = file_get_contents("{$dir}/template_usausb_wide4.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'usansf':
            $region = explode(".", $r)[0];
            $routeNum = str_replace(strtoupper($region), "", $row['route']);
            if (strlen($routeNum) > 2) {
                if (file_exists("{$dir}/template_usa" . $region . "_wide.svg")) {
                    $svg = file_get_contents("{$dir}/template_usa" . $region . "_wide.svg");
                }
                else {
                    $svg = file_get_contents("{$dir}/generic_wide.svg");
                }
            }
            else {
                if (file_exists("{$dir}/template_usa" . $region . ".svg")) {
                    $svg = file_get_contents("{$dir}/template_usa" . $region . ".svg");
                }
                else {
                    $svg = file_get_contents("{$dir}/generic.svg");
                }
            }
            $region = strtoupper($region);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            $svg = str_replace("***SYS***", $region, $svg);
            $svg = str_replace("***LETTER***", "", $svg); //hack to kill off the suffix for usaar
            break;

		case 'usanht':
            $routeNum = strtolower($row['route']);
            if (file_exists("{$dir}/template_usanht_" . $routeNum . ".svg")) {
                $svg = file_get_contents("{$dir}/template_usanht_" . $routeNum . ".svg");
            } 
            break;

		case 'usatr':
            $routeNum = strtolower($row['route']);
            if (file_exists("{$dir}/template_usatr_" . $routeNum . ".svg")) {
                $svg = file_get_contents("{$dir}/template_usatr_" . $routeNum . ".svg");
            } 
            break;

        case 'belb':
        case 'bgra':
		case 'cisa':
		case 'cism':
        case 'cypa':
        case 'cypb':
        case 'hunm':
        case 'irlr': // Ireland Regional
        case 'islf':
        case 'jamt':
        case 'jama':
		case 'kazkaz':
		case 'kazkz':
        case 'lkaa':
        case 'lkae':
        case 'lvaa':
        case 'myse':
        case 'nldr':
		case 'nlds':
		case 'nplh':
        case 'phle':
        case 'pola':
        case 'pols':
		case 'rusa':
		case 'rusm':
		case 'rksn':
        case 'sgpex':
        case 'svkd':
        case 'svkr':
        case 'ugam':
            // replace placeholder
            $svg = str_replace("***NUMBER***", $row['route'], $svg);
            break;

		case 'mdar':
			$routeNum = $row['route'];
			if (strlen($routeNum) > 3) {
				$svg = file_get_contents("{$dir}/template_mdar_wide.svg");
			}
			else {
				$svg = file_get_contents("{$dir}/template_mdar.svg");
			}
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
		
        case 'andcg':
            $routeNum = str_replace("CG", "CG ", $row['route']);
            if (strlen($routeNum) > 4) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
		
        case 'azem':
		case 'gtmrn':
		case 'pakm':
            $routeNum = str_replace("M", "M-", $row['route']);
			$routeNum = str_replace("RN", "RN-", $routeNum);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'mnea':
		case 'mnem':
		case 'mner':
			$routeNum = str_replace("A", "A-", $row['route']);
            $routeNum = str_replace("M", "M-", $routeNum);
            $routeNum = str_replace("R", "R-", $routeNum);
			if (strlen($routeNum) > 3) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
			else {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . ".svg");
            }
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;

		case 'mkdr':
		case 'rksrr':
		    $routeNum = str_replace("R", "R-", $row['route']);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
		
        case 'itaa':
			$routeNum = $row['route'];
			if (strlen($routeNum) > 5) {
				$svg = file_get_contents("{$dir}/template_itasf.svg");
				$lines = explode(',',preg_replace('/(?!^)[A-Z]{3,}(?=[A-Z][a-z])|[A-Z][a-z]/', ',$0', $row['route']));
				$index = 0;
				foreach ($lines as $line) {
					if (strlen($line) > 0) {
						$svg = str_replace("***NUMBER".($index + 1)."***", $line, $svg);
						$index++;
					}
				}
				while ($index < 3) {
					$svg = str_replace("***NUMBER".($index + 1)."***", "", $svg);
					$index++;
				}
				break;
			}
			elseif(strlen($routeNum) > 4) {
				$svg = file_get_contents("{$dir}/template_itaa_wide5.svg");
				$svg = str_replace("***NUMBER***", $routeNum, $svg);
				break;
			}
			elseif(strlen($routeNum) > 3) {
				$svg = file_get_contents("{$dir}/template_itaa_wide.svg");
				$svg = str_replace("***NUMBER***", $routeNum, $svg);
				break;
			}
			else {
				$svg = file_get_contents("{$dir}/template_itaa.svg");
				$svg = str_replace("***NUMBER***", $routeNum, $svg);
				break;
			}
            
        case 'abwrt':
		case 'alakt':
        case 'alavt':
        case 'asim':
		case 'bdirn':
		case 'bgri':
		case 'bolf':
        case 'canmbw':
        case 'chea':
        case 'czed':
        case 'czei':
        case 'deua':
        case 'deub':
        case 'estp':
        case 'estt':
        case 'finkt':
        case 'finst':
        case 'frolv':
		case 'hrvd':
		case 'hrvz':
        case 'hunf':
        case 'idnn':
		case 'irnf':
        case 'islth':
        case 'isrf':
        case 'isrh':
        case 'isrr':
		case 'korex':
		case 'kwtr':
        case 'ltuk':
		case 'mltt':
        case 'mysjp':
        case 'nama':
        case 'namb':
		case 'pancn':
        case 'phlp':
        case 'poldk':
        case 'poldw':
		case 'srbm':
		case 'srbb':
		case 'srbr':
		case 'srbrb':
		case 'svng':
		case 'svnr':
        case 'swel':
		case 'vent':
		case 'zafn':
		case 'zafr':
            // replace placeholder, remove prefix
            // NOTE: seems a little silly to have all of these
            // str_replace calls done in this one common case
            $routeNum = str_replace("A", "", $row['route']);
            $routeNum = str_replace("B", "", $routeNum);
			$routeNum = str_replace("C", "", $routeNum);
            $routeNum = str_replace("DK", "", $routeNum);
            $routeNum = str_replace("DW", "", $routeNum);
            $routeNum = str_replace("D", "", $routeNum);
			$routeNum = str_replace("Ex", "", $routeNum);
            $routeNum = str_replace("FT", "", $routeNum);
            $routeNum = str_replace("F", "", $routeNum);
			$routeNum = str_replace("G", "", $routeNum);
            $routeNum = str_replace("H", "", $routeNum);
            $routeNum = str_replace("I", "", $routeNum);
            $routeNum = str_replace("Kt", "", $routeNum);
            $routeNum = str_replace("K", "", $routeNum);
            $routeNum = str_replace("Lv", "", $routeNum);
            $routeNum = str_replace("L", "", $routeNum);
            $routeNum = str_replace("M", "", $routeNum);
            $routeNum = str_replace("N", "", $routeNum);
            $routeNum = str_replace("Rte", "", $routeNum);
			$routeNum = str_replace("Rt", "", $routeNum);
			$routeNum = str_replace("RN", "", $routeNum);
            $routeNum = str_replace("R", "", $routeNum);
            $routeNum = str_replace("St", "", $routeNum);
            $routeNum = str_replace("TH", "", $routeNum);
            $routeNum = str_replace("T", "", $routeNum);
            $routeNum = str_replace("Vt", "", $routeNum);
			$routeNum = str_replace("Z", "", $routeNum);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'czeii':
			$routeNum = str_replace("II", "", $row['route']);
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;

		case 'svki':
			$routeNum = str_replace("I", "", $row['route']);
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
			
		case 'svkii':
			$routeNum = str_replace("II", "", $row['route']);
			if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
		
        case 'autb':
            // replace placeholder, remove prefix, use wide svg file for 3-digit numbers
            $routeNum = str_replace("B", "", $row['route']);
            $routeNum = str_replace("L", "", $routeNum);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'cheh':
        case 'dnkpr':
        case 'finvt':
        case 'norrv':
        case 'swer':
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

		case 'armm':
		case 'geos':
			$routeNum = str_replace("M", "", $row['route']);
			$routeNum = str_replace("S", "", $routeNum);
			$svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
		
		case 'dnkmr':
			$svg = file_get_contents("{$dir}/template_dnkmr.svg");
			break;

		case 'dnksr':
			$routeNum = str_replace("SR", "", $row['route']);
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
		
        case 'beln':
            // replace placeholder, remove prefix, use wide svg files
            $routeNum = str_replace("N", "", $row['route']);
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'autl3':
		case 'autl4':
		case 'autl5':
		case 'autl6':
		case 'autl7':
            // replace placeholder, remove prefix
            $routeNum = str_replace("L", "", $row['route']);
            $svg = file_get_contents("{$dir}/template_autl.svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'bihm':
			$routeNum = str_replace("M", "M-", $row['route']);
			if (strlen($routeNum > 5)) {
				$svg = file_get_contents("{$dir}/template_bihm_wide6.svg");
			}
			elseif (strlen ($routeNum > 4)) {
				$svg = file_get_contents("{$dir}/template_bihm_wide5.svg");
			}
			else {
				$svg = file_get_contents("{$dir}/template_bihm.svg");
			}
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;

        case 'norfv':
            // replace placeholder, remove prefix, use wide svg files
            // for 2-/3-digit numbers
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
            // replace placeholder, remove prefix, use wide svg files
            $routeNum = str_replace("DN", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_roudn_wide" . strlen($routeNum) . ".svg");
            }
			else {
				$svg = file_get_contents("{$dir}/template_roudn.svg");
			}
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'roudex':
            $routeNum = $row['route'];
			$svg = file_get_contents("{$dir}/template_roudex_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
			
		case 'roudj':
            $routeNum = str_replace("DJ", "", $row['route']);
			$svg = file_get_contents("{$dir}/template_roudj_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'andcs':
        case 'biha':
		case 'cypd':
        case 'hrva':
		case 'mkda':
        case 'prtip':
        case 'prtic':
		case 'prtn':
		case 'prtve':
        case 'prtvr':
        case 'rksr':
        case 'srba':
        case 'svna':
        case 'svnh':
        case 'turd':
		case 'ukrm':
        case 'vnmct':
            // replace placeholder, add blank after prefix
            $routeNum = str_replace("A", "A ", $row['route']);
            $routeNum = str_replace("CS", "CS ", $routeNum);
            $routeNum = str_replace("CT", "CT.", $routeNum);
            $routeNum = str_replace("D", "D ", $routeNum);
            $routeNum = str_replace("H", "H ", $routeNum);
            $routeNum = str_replace("IC", "IC ", $routeNum);
            $routeNum = str_replace("IP", "IP ", $routeNum);
			$routeNum = str_replace("M", "M ", $routeNum);
			$routeNum = str_replace("N", "N ", $routeNum);
            $routeNum = str_replace("R", "R ", $routeNum);
            $routeNum = str_replace("VE", "VE ", $routeNum);
			$routeNum = str_replace("VR", "VR ", $routeNum);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'prta':
            // replace placeholder, add blank after prefix
            $routeNum = str_replace("A", "A ", $row['route']);
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
		
		case 'deuhhr':
		case 'norr':
		    $routeNum = str_replace("Ring", "Ring ", $row['route']);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;

        case 'nldp':
            $routeNum = $row['route'];
            if (strlen($routeNum) > 4) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'nldrw':
			if (str_starts_with($row['route'], 'Cen')) {
				$svg = file_get_contents("{$dir}/template_nldrw_cr.svg");
			}
			else {
				$svg = file_get_contents("{$dir}/template_nldrw.svg");
			}
			break;
		
        case 'turo':
            // replace placeholder, add blank after prefix, use wide svg files
            $routeNum = str_replace("O", "O ", $row['route']);
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
	    
        case 'fraa':
        case 'fran':
        case 'frht':
        case 'spmn':
        case 'mtqa':
        case 'glpn':
        case 'gufn':
        case 'reun':
        case 'mara':
        case 'tuna':
        case 'mtqn':
        case 'sena':
        case 'senn':
        case 'gaba':
        case 'gabn':
		case 'cmra':
		case 'codn':
		case 'gnbn':
		case 'mytn':
            // replace placeholder, add blank after prefix, use wide svg files
            $routeNum = str_replace("A", "A ", $row['route']);
            $routeNum = str_replace("N", "N ", $routeNum);
            //$routeNum = str_replace("T", "T", $routeNum); //no blank required!
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'marn':
            // replace placeholder, add blank after prefix, use wide svg files
            $routeNum = str_replace("N", "N ", $row['route']);
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
		
        case 'ncle':
            $routeNum = str_replace("E", "VE ", $row['route']);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
       
        // frad France Routes Départementales
        // fraxxxdnn
        case preg_match('/fra[a-z]{3}d[0-9]{2}/', $row['systemName']) ? $row['systemName'] : !$row['systemName']:
        case 'fragesd6ae':
        case 'fracord': // Corsica Routes Départementales
        case 'glpd': // Guadaloupe Routes Départementales
        case 'gufd': // French Guiana Routes Départementales
        case 'mtqd': // Martinique Routes Départementales
        case 'mytd': // Mayotte Routes Départementales
        case 'reud': // Reunion Routes Départementales
            // replace placeholder, add blank after prefix, use wide svg files
            $prefix = substr($row['route'], 0, 1);
            $routeNum = substr_replace($row['route'], "{$prefix} ", 0, 1); // Use substr_replace here to avoid matching suffixes.
            
            if ( strlen($routeNum) > 7 ) {
                $svg = file_get_contents("{$dir}/template_frad_wide7.svg");
            } else {
                $svg = file_get_contents("{$dir}/template_frad_wide" . strlen($routeNum) . ".svg");
            }
            
            $matches = [];
            if (preg_match('/(?<number>[DN] [0-9]+)(?<suffix>[A-Za-z]+[0-9]?)/', $routeNum, $matches)) {
                $svg = str_replace("***NUMBER***", $matches['number'], $svg);
                $svg = str_replace("***SUFFIX***", $matches['suffix'], $svg);
            }
            else { // D 1.1 -> D 1^1
                $numParts = explode('.',  $routeNum);
                $svg = str_replace("***NUMBER***", $numParts[0], $svg);
                if ( isset($numParts[1]) ) {
                    $svg = str_replace("***SUFFIX***", $numParts[1], $svg);
                } else {
                    $svg = str_replace("***SUFFIX***", '', $svg);
                }
            }
            break;
            
		// fram France Routes Métropolitaines
		// fraxxxmnn
		case preg_match('/fra[a-z]{3}m[0-9]{2}/', $row['systemName']) ? $row['systemName'] : !$row['systemName']:
		case 'fragesm6ae':
		case 'fracort': // Corsica Routes Territoriales
			// replace placeholder, add blank after prefix, use wide svg files
			$routeNum = str_replace("M", "M ", $row['route']);
			// Whitespace after 'T' is purposefully excluded.
			if ($row['systemName'] == "frapdlm44") {
				if ( strlen($routeNum) > 7 ) {
					$svg = file_get_contents("{$dir}/template_frapdlm44_wide7.svg");
				} else {
					$svg = file_get_contents("{$dir}/template_frapdlm44_wide" . strlen($routeNum) . ".svg");
				}
			}
			else {
				if ( strlen($routeNum) > 7 ) {
					$svg = file_get_contents("{$dir}/template_fram_wide7.svg");
				} else {
					$svg = file_get_contents("{$dir}/template_fram_wide" . strlen($routeNum) . ".svg");
				}
			}

			$matches = [];
			if (preg_match('/(?<number>(M |T)[0-9]+)(?<suffix>[A-Za-z]+[0-9]?)/', $routeNum, $matches)) {
				$svg = str_replace("***NUMBER***", $matches['number'], $svg);
				$svg = str_replace("***SUFFIX***", $matches['suffix'], $svg);
			}
			else {
				$svg = str_replace("***NUMBER***", $routeNum, $svg);
				$svg = str_replace("***SUFFIX***", '', $svg);
			}
			break; 
		
        case 'nclt':
		case 'pyft':
		case 'wlft':
            // replace placeholder, add blank after prefix, use wide svg files
            $routeNum = str_replace("T", "RT ", $row['route']);
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'cogn':
            $routeNum = str_replace("N", "RN ", $row['route']);
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'tunrn':
            $routeNum = str_replace("RN", "RN ", $row['route']);
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'bwaa':
		case 'kena':
		case 'kenb':
		case 'lsoa':
		case 'mozn':
		case 'musa':
		case 'musb':
		case 'musm':
		case 'mwim':
		case 'rwanr':
		case 'swzmr':
		case 'tzat':
		case 'zmbm':
		case 'zmbt':
		case 'zwep':
		case 'zwer':
			// SADC with no space
            $routeNum = $row['route'];
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
		
		case 'albsh':
            // replace placeholder, add blank after prefix, use wide svg files
            $routeNum = str_replace("SH", "SH ", $row['route']);
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'espcg':
		case 'espn':
		case 'espvg':
            // replace placeholder, add hyphen after prefix, use wide svg files
            $routeNum = str_replace("N", "N-", $row['route']);
			$routeNum = str_replace("CG", "CG-", $routeNum);
			$routeNum = str_replace("VG", "VG-", $routeNum);
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'espct':
            // replace placeholder, add hyphen after prefix, use wide svg files
            // note that C can be a suffix also, so str_replace doesn't
            // get it done
            $routeNum = substr_replace($row['route'], "-", 1, 0);
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'espan':
        case 'espar':
        case 'espas':
        case 'espcb':
        case 'espcl':
        case 'espcm':
        case 'espex':
        case 'espga':
        case 'espib':
        case 'espmc':
        case 'espmd':
        case 'espnc':
        case 'esppv':
        case 'espri':
        case 'espvc':
            // replace placeholder, add hyphen after prefix, use wide svg files
            // note that prefix can be 1 of 2 letters, sometimes within the
            // same system..
            $hyphenpos = 2;
            if (ctype_digit($row['route'][1])) {
                $hyphenpos = 1;
            }
            $routeNum = substr_replace($row['route'], "-", $hyphenpos, 0);
            $svg = file_get_contents("{$dir}/template_espxx_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
            
        case 'eure':
            // replace placeholder
            $svg = str_replace("***NUMBER***", $row['route'], $svg);
            break;
            
        case 'bela':
        case 'belr':
		case 'gbnmkgr':
        case 'luxa':
        case 'luxb':
        case 'roua':
            // replace placeholder, use wide svg file for 3-digit numbers
            if (strlen($row['route']) > 2) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $row['route'], $svg);
            break;

		case 'luxn':
			$routeNum = str_replace("N", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
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
            
        case 'gbnb': // Great Britain B Roads
        case 'nirb': // Northern Ireland B Roads
        case 'imnb': // Isle of Man B Roads
        case 'jeyc': // Jersey C Roads
            // Replace placeholder, get proper width template
            $svg = file_get_contents("{$dir}/template_gbnb.svg");
            if (strlen($row['route']) > 3) {
                $svg = file_get_contents("{$dir}/template_gbnb_wide4.svg");
            }
            if (strlen($row['route']) > 4) {
                $svg = file_get_contents("{$dir}/template_gbnb_wide5.svg");
            }
            $svg = str_replace("***NUMBER***", $row['route'], $svg);
            break;

        case 'irlm':
        case 'irln':
            $routeNum = $row['route'];
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
	    
        case 'grca':
		case 'lvap':
            $routeNum = $row['route'];
            if (strlen($routeNum) > 3) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'grceo':
			$routeNum = $row['route'];
			if (strlen($routeNum) > 6) {
				$svg = file_get_contents("{$dir}/template_grceo_text.svg");
				$lines = explode(',',preg_replace('/(?!^)[A-Z]{3,}(?=[A-Z][a-z])|[A-Z][a-z]/', ',$0', $row['route']));
				$index = 0;
				foreach ($lines as $line) {
					if (strlen($line) > 0) {
						$svg = str_replace("***NUMBER".($index + 1)."***", $line, $svg);
						$index++;
					}
				}
				while ($index < 3) {
					$svg = str_replace("***NUMBER".($index + 1)."***", "", $svg);
					$index++;
				}
				break;
			}
			else {
				$routeNum = str_replace("EO", "", $row['route']);
				$svg = file_get_contents("{$dir}/template_grceo.svg");
				$svg = str_replace("***NUMBER***", $routeNum, $svg);
				break;
			}
	    
        case 'itass':
            // replace placeholder, add blank after prefix, use wide svg files
            $routeNum = str_replace("SS", "SS ", $row['route']);
            $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
            
        case 'espa':
            // replace placeholder, use wide svg files for
            // 3-/4-/5-digit numbers (Spain is simplified: national
            // (blue) motorway signs are generally used for national and
            // regional motorways, numbering w/o "-" (e.g. A1 instead of
            // A-1))
            
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

        case 'deubwl':
        case 'deubyst':
        case 'deubbl':
        case 'deuhel':
        case 'deumvl':
        case 'deunil':
        case 'deunwl':
        case 'deurpl':
        case 'deusll':
        case 'deusns':
        case 'deustl':
        case 'deushl':
        case 'deuthl':
            // replace placeholder, use wide svg files (German Landesstrassen)
            $svg = file_get_contents("{$dir}/template_deul" . strlen($row['route']) . ".svg");
            $svg = str_replace("***NUMBER***", $row['route'], $svg);
            break;

        case 'gbnm':
        case 'nirm':
            $routeNum = str_replace("M", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_gbnm_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'gbnam':
        case 'niram':
            $routeNum = str_replace("M", "", $row['route']);
            $routeNum = str_replace("A", "", $routeNum);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_gbnam_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'tjkrb':
			$routeNum = str_replace("RB", "РБ", $row['route']);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
		
		case 'kgzem':
			$routeNum = str_replace("EM", "ЭM-", $row['route']);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
			
		case 'rusr':
			$routeNum = str_replace("R", "P", $row['route']);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
			
		case 'blrm':
			$routeNum = str_replace("M0", "M", $row['route']);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
		
        case 'twnf':
            $routeNum = str_replace("F", "", $row['route']);
            $routeNum = str_replace("A", "甲", $routeNum); //suffix - hope there's no unicode issues
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
        
        case 'jpne':
            // might need to have a wide version of the template
            $svg = str_replace("***NUMBER***", $row['route'], $svg);
            break;
		
        case 'jpnh':
            $routeNum = str_replace("N", "", $row['route']);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'jpntk':
            $routeNum = str_replace("FE", "", $row['route']);
			$routeNum = str_replace("HE", "", $routeNum);
			$routeNum = str_replace("HiE", "", $routeNum);
			$routeNum = str_replace("KE", "", $routeNum);
			$routeNum = str_replace("NE", "", $routeNum);
			$routeNum = str_replace("SE", "", $routeNum);
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'chng':
            $routeNum = $row['route'];
            if (strlen($routeNum) > 3) {
                $svg = file_get_contents("{$dir}/template_chng_wide.svg");
            }
			else {
				$svg = file_get_contents("{$dir}/template_chng.svg");
			}
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;	
		
        case 'chnbjs':
		case 'chngds':
		case 'chnhis':
		case 'chnjjjs':
		case 'chnnxs':
		case 'chnsf':
		case 'chnshs':
		case 'chntjs':
		case 'chnxzs':
            $routeNum = $row['route'];
            if (strlen($routeNum) > 3) {
                $svg = file_get_contents("{$dir}/template_chns_wide.svg");
            }
			else {
				$svg = file_get_contents("{$dir}/template_chns.svg");
			}
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;		
       
       case 'hkgrt':
             $routeNum = substr_replace($row['route'], '', 0, 2); // Strip "RT"
                   
            if (strlen($routeNum) > 1) {
                if (file_exists("{$dir}/template_" . $row['systemName'] . "_wide.svg")) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
                }
                // Fall back on a smaller template if one to handle larger numbers doesn't exist.
                elseif (file_exists("{$dir}/template_" . $row['systemName'])) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName']);
                }
            }
            elseif (strlen($routeNum) > 0) {
                if (file_exists("{$dir}/template_" . $row['systemName'])) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName']);
                }
            }
             $svg = str_replace("***NUMBER***", $routeNum, $svg);
             break;
       
        case 'idnt':
            if (str_starts_with($row['route'], 'J')) {
           		$routeNum = $row['route'];
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_text.svg");
            	$svg = str_replace("***NUMBER***", $routeNum, $svg);
		break;
            }
            else {
            	$routeNum = str_replace("T", "", $row['route']);
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . ".svg");
				$svg = str_replace("***NUMBER***", $routeNum, $svg);
            	break;
	     }

        case 'afrrtr':
            $routeNum = str_replace("RTR", "", $row['route']);
            if (strlen($routeNum) > 3) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

		case 'tham':
			$routeNum = str_replace("M", "", $row['route']);
			$svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
		
		case 'thatp':
			$routeNum = str_replace("Thl", "", $row['route']);
			$svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide" . strlen($routeNum) . ".svg");
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
       
        case 'chegts':
        case 'usasf':
        case 'usanp':
        case 'cansf':
        case 'usakyp':
        case 'usanyp':
        case 'gbrtr':
        case 'sctntr':
        case 'nzltr':
        case 'swemot':
		case 'swesf':
        case 'tursf':
        case 'nzlmot':
        case 'index':
        case 'ngae':
		case 'argsf':
		case 'aussf':
		case 'dnkmot':
		case 'finmt':
		case 'normot':
		case 'zaff':
		case 'albsf':
		case 'chlsf':
		case 'colsf':
		case 'deusf':
		case 'grcsf':
		case 'itasf':
		case 'nldsf':
		case 'ecusf':
		case 'kensf':
		case 'myssf':
		case 'thaexat':
		case 'thasf':
		case 'pansf':
		case 'persf':
		case 'ttoh':
		case 'chesf':
		case 'czesf':
		case 'slvsf':
		case 'ttomr':
		case 'mexsf':
		case 'mkdap':
		case 'brasf':
		case 'vena':
		case 'nirtr':
		case 'ltuaut':
		case 'ukra':
		case 'espsf':
		case 'norntv':
            $lines = explode(',',preg_replace('/(?!^)[A-Z]{3,}(?=[A-Z][a-z])|[A-Z][a-z]/', ',$0', $row['route']));
            $index = 0;
            foreach ($lines as $line) {
                if (strlen($line) > 0) {
                    $svg = str_replace("***NUMBER".($index + 1)."***", $line, $svg);
                    $index++;
                }
            }
            while ($index < 3) {
                $svg = str_replace("***NUMBER".($index + 1)."***", "", $svg);
                $index++;
            }
            break;

		case 'gtmsf':
			if ($row['route'] == "VAS") {
				$svg = file_get_contents("{$dir}/template_gtmsf_vas.svg");
			}
			break;

		case 'eursf':
			$lines = explode(',',preg_replace('/(?!^)[A-Z]{3,}(?=[A-Z][a-z])|[A-Z][a-z]/', ',$0', $row['route']));
            $index = 0;
			if ($row['region'] == "BIH") {
				$svg = file_get_contents("{$dir}/template_bihsf.svg");
			}
			elseif ($row['region'] == "BLR") {
				$svg = file_get_contents("{$dir}/template_blrsf.svg");
			}
			elseif ($row['region'] == "ENG") {
				$svg = file_get_contents("{$dir}/template_engsf.svg");
			}
			elseif ($row['region'] == "RUS") {
				$svg = file_get_contents("{$dir}/template_russf.svg");
			}
			else {
				$svg = file_get_contents("{$dir}/template_eursf.svg");
			}
            foreach ($lines as $line) {
                if (strlen($line) > 0) {
                    $svg = str_replace("***NUMBER".($index + 1)."***", $line, $svg);
                    $index++;
                }
            }
            while ($index < 3) {
                $svg = str_replace("***NUMBER".($index + 1)."***", "", $svg);
                $index++;
            }
            break;

		case 'frasf':
            $lines = explode(',',preg_replace('/(?!^)[A-Z]{3,}(?=[A-Z][a-z])|[A-Z][a-z]/', ',$0', $row['route']));
			$lines = array_map('strtoupper', $lines);
            $index = 0;
            foreach ($lines as $line) {
                if (strlen($line) > 0) {
                    $svg = str_replace("***NUMBER".($index + 1)."***", $line, $svg);
                    $index++;
                }
            }
            while ($index < 3) {
                $svg = str_replace("***NUMBER".($index + 1)."***", "", $svg);
                $index++;
            }
            break;

		case 'indne':
		case 'indnh':
		case 'indka':
		case 'indkl':
		case 'indmh':
		case 'indtg':
		case 'indup':
		case 'indwb':
            $routeNum = substr_replace($row['route'], '', 0, 2); // Remove prefixes that are exactly 2 characters.
            if (strlen($routeNum) > 4) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide5.svg");
			}
            elseif (strlen($routeNum) > 3) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide4.svg");
            }
            elseif (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
            }
			else {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . ".svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
		
        // CAN suffixes
        // XX50A -> 50^A
        
        case 'canbc': // British Columbia
        case 'canmb': // Manitoba Trunk Hwys
        case 'canon': // Ontario King's Hwys
        case 'canons': // Ontario Secondary Hwys
            $routeNum = substr_replace($row['route'], '', 0, 2); // Remove prefixes that are exactly 2 characters.
            
            if (strlen($routeNum) > 3) {
                if (file_exists("{$dir}/template_" . $row['systemName'] . "_wide4.svg")) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide4.svg");
                }
                // Fall back on a smaller template if one to handle larger numbers doesn't exist.
                elseif (file_exists("{$dir}/template_" . $row['systemName'] . "_wide.svg")) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
                }
            }
            elseif (strlen($routeNum) > 2) {
                if (file_exists("{$dir}/template_" . $row['systemName'] . "_wide.svg")) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
                }
            }
            
            $matches = [];
            if (preg_match('/(?<number>[0-9]+)(?<letter>[A-Za-z]+)/', $routeNum, $matches)) {
                $svg = str_replace("***NUMBER***", $matches['number'], $svg);
                $svg = str_replace("***LETTER***", $matches['letter'], $svg);
            }
            else {
                $svg = str_replace("***NUMBER***", $routeNum, $svg);
                $svg = str_replace("***LETTER***", "", $svg);
            }
            break;
            
        case 'usaar': // Arkansas
            $matches = [];
            $routeNum = str_replace("AR", "", $row['route']);
	         if ($routeNum == 980) {
		         $svg = file_get_contents("{$dir}/template_usaar_980.svg");
	            break;
            }
            elseif (preg_match('/(?<number>[0-9]+)(?<letter>[A-Za-z]+)/', $routeNum, $matches)) {
               $svg = str_replace("***NUMBER***", $matches['number'], $svg);
               $svg = str_replace("***LETTER***", $matches['letter'], $svg);
               break;
            }
            else {
               $svg = str_replace("***NUMBER***", $routeNum, $svg);
               $svg = str_replace("***LETTER***", "", $svg);
               break;
            }

        case 'usaga': // Georgia
            $routeNum = str_replace("GA", "", $row['route']);
            $system = "";
            $sys_map['Alt'] = "ALT";
            $sys_map['Bus'] = "BUS";
            $sys_map['Byp'] = "BYP";
            $sys_map['Con'] = "CONN";
            $sys_map['Lp'] = "LOOP";
            $sys_map['Spr'] = "SPUR";
            $sys_map['Trk'] = "";
            $sys_map[''] = "";
            $system = $sys_map[$row['banner']];
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usaga_wide.svg");
            }
            else {
                $svg = file_get_contents("{$dir}/template_usaga.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            $svg = str_replace("***SYS***", $system, $svg);
            break;
		
		case 'usamd': // Maryland
			$routeNum = str_replace("MD", "", $row['route']);
			if ($row['banner'] == "Bus") {
				if (strlen($routeNum) > 2) {
					$svg = file_get_contents("{$dir}/template_usamd_bus_wide.svg");
					$svg = str_replace("***NUMBER***", $routeNum, $svg);
					break;
				}
				else {
					$svg = file_get_contents("{$dir}/template_usamd_bus.svg");
					$svg = str_replace("***NUMBER***", $routeNum, $svg);
					break;
				}
			}
			else {
				if (strlen($routeNum) > 2) {
					$svg = file_get_contents("{$dir}/template_usamd_wide.svg");
					$svg = str_replace("***NUMBER***", $routeNum, $svg);
					break;
				}
				else {
					$svg = file_get_contents("{$dir}/template_usamd.svg");
					$svg = str_replace("***NUMBER***", $routeNum, $svg);
					break;
				}
			}

		case 'usamn': // Minnesota
			$routeNum = str_replace("MN", "", $row['route']);
			if ($row['banner'] == "Bus") {
				$svg = file_get_contents("{$dir}/template_usamn_bus.svg");
				$svg = str_replace("***NUMBER***", $routeNum, $svg);
				break;
			}
			else {
				if (strlen($routeNum) > 2) {
					$svg = file_get_contents("{$dir}/template_usamn_wide.svg");
					$svg = str_replace("***NUMBER***", $routeNum, $svg);
					break;
				}
				else {
					$svg = file_get_contents("{$dir}/template_usamn.svg");
					$svg = str_replace("***NUMBER***", $routeNum, $svg);
					break;
				}
			}

        case 'usanh': // New Hampshire
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
            
        case 'usanes': // Nebraska Links and Spurs
            $matches = [];
            $routeNum = str_replace('NE', "", $row['route']);
            if ($routeNum[0] === 'L') {
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

        case 'usany': // New York
            $matches = [];
            $routeNum = str_replace("NY", "", $row['route']);
			if (strlen($routeNum) > 3) {
                $svg = file_get_contents("{$dir}/template_usany_wide4.svg");
            }
			elseif (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usany_wide.svg");
            }
            else {
                $svg = file_get_contents("{$dir}/template_usany.svg");
            }
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
			
        case 'usaok': // Oklahoma
            $matches = [];
            $routeNum = str_replace("OK", "", $row['route']);
			if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usaok_wide.svg");
            }
            else {
                $svg = file_get_contents("{$dir}/template_usaok.svg");
            }
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
		
        case 'usapr': // Puerto Rico
            $routeNum = str_replace("PR", "", $row['route']);
			$numOnly = str_replace("R", "", $routeNum);
            if ($numOnly < 100) {
                if (strlen($routeNum) > 2) {
                    $svg = file_get_contents("{$dir}/template_usapr1_wide.svg");
                }
                else {
                    $svg = file_get_contents("{$dir}/template_usapr1.svg");
                }
            }
            elseif ($numOnly < 250) {
                if (strlen($routeNum) > 3) {
                    $svg = file_get_contents("{$dir}/template_usapr2_wide4.svg");
                }
                elseif (strlen($routeNum) > 2) {
                    $svg = file_get_contents("{$dir}/template_usapr2_wide.svg");
                }
                else {
                    $svg = file_get_contents("{$dir}/template_usapr2.svg");
                }
            }
            else {
                if (strlen($routeNum) > 3) {
                    $svg = file_get_contents("{$dir}/template_usapr3_wide4.svg");
                }
                elseif (strlen($routeNum) > 2) {
                    $svg = file_get_contents("{$dir}/template_usapr3_wide.svg");
                }
                else {
                    $svg = file_get_contents("{$dir}/template_usapr3.svg");
                }
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
		
        case 'usatxl': // Texas Loops
            $routeNum = str_replace("TXLp", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usatxl_wide.svg");
            }
            else {
                $svg = file_get_contents("{$dir}/template_usatxl.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;

        case 'usatxs': // Texas Spurs
            $routeNum = str_replace("TXSpr", "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usatxs_wide.svg");
            }
            else {
                $svg = file_get_contents("{$dir}/template_usatxs.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;		
		
        // Virginia Wyes: also fall through to default if banner was not Wye
        case 'usava': 
            if ($row['banner'] === 'Wye') {
                $routeNum = str_replace('VA', "", $row['route']);
                if (strlen($routeNum) > 2) {
                    $svg = file_get_contents("{$dir}/template_usava_wye_wide.svg");
                }
                else {
                    $svg = file_get_contents("{$dir}/template_usava_wye.svg");
                }
                $svg = str_replace("***NUMBER***", $routeNum, $svg);
                break;
            }

		case 'usavt': // Vermont
            $matches = [];
            $routeNum = str_replace("VT", "", $row['route']);
			if (strlen($routeNum) > 3) {
                $svg = file_get_contents("{$dir}/template_usavt_wide4.svg");
            }
			elseif (strlen($routeNum) > 2) {
                $svg = file_get_contents("{$dir}/template_usavt_wide.svg");
            }
            else {
                $svg = file_get_contents("{$dir}/template_usavt.svg");
            }
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
		
        // Generic case for CAN/USA regional systems.
        // Removes exactly 2 uppercase letter prefixes: XX101 -> 101
        
        case 'canabs': // Alberta 500+
        case 'canmbp': // Manitoba Provincial Roads
        case 'cannba': // New Brunswick Arterial Hwys
        case 'cannbc': // New Brunswick Collector Hwys
        case 'cannbl': // New Brunswick Local Hwys
        case 'cannl': // Newfoundland and Labrador
        case 'cannsf': // Nova Scotia Arterial
        case 'cannsc': // Nova Scotia Collector
        case 'cannt': // Northwest Territory
        case 'canonf': // Ontario Provincial Freeways
        case 'canpe': // Prince Edward Island
        case 'canqc': // Quebec Provincial Routes
        case 'cansk': // Saskatchewan
        
        case 'usaak': // Alaska
        case 'usaal': // Alabama
        case 'usaas': // American Samoa
        case 'usaaz': // Arizona
        case 'usaca': // California
        case 'usaco': // Colorado
        case 'usact': // Connecticut
        case 'usadc': // District of Columbia
        case 'usade': // Delaware
        case 'usafl': // Florida
        case 'usagu': // Guam
        case 'usahi': // Hawaii
        case 'usaia': // Iowa
        case 'usaid': // Idaho
        case 'usail': // Illinois
        case 'usain': // Indiana
        case 'usaks': // Kansas
        case 'usaky': // Kentucky
        case 'usala': // Louisiana
        case 'usama': // Massachusetts
        case 'usame': // Maine
        case 'usami': // Michigan
        case 'usamo': // Missouri
        case 'usamp': // Northern Mariana Islands
        case 'usamt': // Montana
        case 'usamts': // Montana Secondary
        case 'usanc': // North Carolina
        case 'usand': // North Dakota
        case 'usane': // Nebraska
        case 'usanj': // New Jersey
        case 'usanm': // New Mexico
        case 'usanv': // Nevada
        case 'usaoh': // Ohio
        case 'usaor': // Oregon
        case 'usapa': // Pennsylvania
        case 'usari': // Rhode Island
        case 'usasc': // South Carolina
        case 'usasd': // South Dakota
        case 'usatn': // Tennessee
        case 'usatx': // Texas
        case 'usatxre': // Texas Recreation Roads
        case 'usaut': // Utah
        // usava Virginia generic case falls through to here
        case 'usavi': // US Virgin Islands
        case 'usawa': // Washington State
        case 'usawi': // Wisconsin
        case 'usawv': // West Virginia
        case 'usawy': // Wyoming
            
            $routeNum = substr_replace($row['route'], '', 0, 2); // Remove prefixes that are exactly 2 characters.
            if (strlen($routeNum) > 3) {
                if (file_exists("{$dir}/template_" . $row['systemName'] . "_wide4.svg")) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide4.svg");
                }
                // Fall back on a smaller template if one to handle larger numbers doesn't exist.
                elseif (file_exists("{$dir}/template_" . $row['systemName'] . "_wide.svg")) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
                }
            }
            elseif (strlen($routeNum) > 2) {
                if (file_exists("{$dir}/template_" . $row['systemName'] . "_wide.svg")) {
                    $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
                }
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            break;
        
        default:
            $region = strtoupper(explode(".", $r)[0]);
            $routeNum = str_replace($region, "", $row['route']);
            if (strlen($routeNum) > 4) {
                    if (file_exists("{$dir}/template_" . $row['systemName'] . "_wide4.svg")) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide4.svg");
                    }
                    elseif (file_exists("{$dir}/template_" . $row['systemName'] . "_wide.svg")) {
                $svg = file_get_contents("{$dir}/template_" . $row['systemName'] . "_wide.svg");
                    }
                    else {
                $svg = file_get_contents("{$dir}/generic_extrawide.svg");
                    }
            }
            elseif (strlen($routeNum) > 3) {
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
                    }
                    else {
                $svg = file_get_contents("{$dir}/generic_wide.svg");
                    }
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
            $svg = str_replace("***SYS***", $region, $svg);
            break;
    }
    
    // if the cache directory doesn't exist, create it
    if (!file_exists("{$dir}/cache/")) {
    mkdir("{$dir}/cache/", 0777, true);
    }
    
    // save this shield in the cache before returning
    file_put_contents("{$dir}/cache/shield_{$r}.svg", $svg);
    return $svg;
}
?>
