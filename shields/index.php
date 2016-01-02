<?php
    /*
     * Generates an SVG shield given a root param.
     * URL Params:
     *  r = root to create shield for
     *
     * TODO: Add more shields for more systems
     * TODO: Use server versions of Roadgeek fonts
     * TODO: Add shield cache
     */
	header("Content-type: image/svg+xml");

	$r = $_GET['r'];
	$db = new mysqli("localhost","travmap","clinch","TravelMapping") or die("Failed to connect to database");
	$sql_command = "SELECT * FROM routes WHERE root = '".$r."';";
	$res = $db->query($sql_command);
	$row = $res->fetch_assoc();

	$svg = file_get_contents("template_".$row['systemName'].".svg");

	switch ($row['systemName']) {
		case 'usai': case 'usaif':
			$routeNum = explode("-", $row['route'])[1];
			if (strlen($routeNum) > 2) {
				$svg = file_get_contents("template_usai_wide.svg");
			}
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;

		case 'usaus': case 'usausb':
			$routeNum = str_replace("US", "", $row['route']);
			if (strlen($routeNum) > 2) {
				$svg = file_get_contents("template_usaus_wide.svg");
			}
			$svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
		
		default:
			$region = strtoupper(explode(".", $r)[0]);
			$routeNum = str_replace($region, "", $row['route']);
            if (strlen($routeNum) > 2) {
                $svg = file_get_contents("template_".$row['systemName']."_wide.svg");
            }
            $svg = str_replace("***NUMBER***", $routeNum, $svg);
			break;
	}
    $insert = strpos($svg, ".svg\">") + strlen(".svg\">");
    $svgdefs = <<<SVGDEFS
    <defs>
        <style type="text/css">@import url('/fonts/roadgeek.css');</style>
    </defs>
SVGDEFS;

    echo substr($svg, 0, $insert).$svgdefs.substr($svg, $insert);
?>
