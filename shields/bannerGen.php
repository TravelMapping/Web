<!-- /shields/bannerGen.php: generate banner plates -->
<?php
/*
	Generate a single specified svg banner for the specified system.
	
	Params:
		String $banner - The type of bannered route (Bus, Alt etc...)
		String $system - The system code
		Boolean $force_reload - force regeneration of the banner. Default is false.
		
	Returns a string:
		Returns the svg code if successful.
		Returns an empty string if the banner isn't recognized.
		Returns 'not external' for systems that don't use external banners.

*/
function tm_banner_generate($banner, $system, $force_reload = false) {
	
	// Specify the shields directory
  $dir = $_SERVER['DOCUMENT_ROOT']."/shields";
	
	$borderColor = '#000';
	$fillColor = '#fff';
	$textColor = '#000';
	$svgNameSuffix = '';
	$useFillTemplate = false;
	$usamsScenic = false;
	
	// Identify system, color scheme, and template type
	switch ($system) {
		case 'usaib': // Return a 'not external' string for systems that don't generally use external banners
		case 'usatx':
		case 'ausab':
		case 'ausnb':
		case 'ausqld':
		// usaga will go here eventually
			return 'not external';
		
		
		case 'usai': // White text on blue bg
		case 'usaif':
			$textColor = '#fff';
			$borderColor = '#fff';
			$fillColor = '#003f87';
			$svgNameSuffix = '_wb';
			$useFillTemplate = true;
			
			break;
		
		case 'auswa': // White text on slightly different blue bg
			$textColor = '#fff';
			$borderColor = '#fff';
			$fillColor = '#174f90';
			$svgNameSuffix = '_auswa';
			$useFillTemplate = true;
			
			break;

		case 'usamd': // No banner for Bus, regular otherwise
			if ($banner == "Bus") {
				return 'not external';
			}
			else {
				break;
			}

		case 'usamn': // No banner for Bus, regular otherwise
			if ($banner == "Bus") {
				return 'not external';
			}
			else {
				$textColor = '#fff';
				$borderColor = '#fff';
				$fillColor = '#003f87';
				$svgNameSuffix = '_wb';
				$useFillTemplate = true;
				break;
			}

		case 'usams': // Special banner for Sce, regular otherwise
			if ($banner == "Sce") {
				$usamsScenic = true;
				$svgNameSuffix = '_sr';
			}
			else {
				break;
			}
		
		case 'usaca': // White text on green bg
			$textColor = '#fff';
			$borderColor = '#fff';
			$fillColor = '#006b54';
			$svgNameSuffix = '_wg';
			$useFillTemplate = true;
			
			break;
	
		case 'usasc': // Blue text on white bg
			$borderColor = '#003f87';
			$textColor = '#003f87';
			$svgNameSuffix = '_bw';
			
			break;
			
		case 'usavt': // Green text on white bg
			$borderColor = '#006b54';
			$textColor = '#006b54';
			$svgNameSuffix = '_gw';
			
			break;
			
		case 'usasd': // Black text with green border on white bg
			$borderColor = '#006b54';
			$textColor = '#000';
			$svgNameSuffix = '_bgw';
			
			break;
	
		default: // Black text on white bg
			break;
	}
	
	
	// Check for existence of the requested banner in the cache
	if ( file_exists( "{$dir}/cache/banner_{$banner}{$svgNameSuffix}.svg" ) && !$force_reload ) {
		// Load from cache
		return file_get_contents("{$dir}/cache/banner_{$banner}{$svgNameSuffix}.svg");
	}
	
	// Svg doesn't exist yet or regeneration was requested.
	// Generate the svg
	
	$svg = '';
	
	// Select the initial template
	if ($useFillTemplate) {
		// Select the filled background template 
		$svg = file_get_contents("{$dir}/banner_template_fill.svg");
	} 
	elseif ($usamsScenic) {
		// Select the blue Scenic Route banner
		$svg = file_get_contents("{$dir}/banner_usams_scenic.svg");
	} 
	else {
		// Select the default background template
		$svg = file_get_contents("{$dir}/banner_template.svg");
	}
	
	// Get the text and set text dimensions
	// This makes sure each text string is centered
	$text = '';
	$x = '300.5';
	$y = '224.75';
	$fontSize = '260px';
	$fontSeries = 'D';
	
	switch ($banner) {
		case 'Alt':
			$text = 'ALT';
			$x = '300.5';
			$y = '224.75';
			$fontSize = '260px';
			$fontSeries = 'D';
			
			break;
		case 'Bus':
			$text = 'BUSINESS';
			$x = '296.81409';
			$y = '211.74295';
			$fontSize = '215px';
			$fontSeries = 'B';
			
			break;
		case 'Byp':
			$text = 'BY-PASS';
			$x = '296.81409';
			$y = '211.74295';
			$fontSize = '215px';
			$fontSeries = 'B';
			
			break;
		case 'Con':
			$text = 'CONNECTOR';
			$x = '299.04395';
			$y = '198.92572';
			$fontSize = '170px';
			$fontSeries = 'B';
			
			break;
		case 'Fut':
			$text = 'FUTURE';
			$x = '297.35699';
			$y = '212.28857';
			$fontSize = '220px';
			$fontSeries = 'C';
			
			break;
		case 'Lp':
			$text = 'LOOP';
			$x = '293.81';
			$y = '224.75';
			$fontSize = '260px';
			$fontSeries = 'D';
			
			break;
		case 'Sce':
			$text = 'SCENIC';
			$x = '302.6';
			$y = '220.5';
			$fontSize = '245px';
			$fontSeries = 'C';
			
			break;
		case 'Spr':
			$text = 'SPUR';
			$x = '300.5';
			$y = '224.75';
			$fontSize = '260px';
			$fontSeries = 'C';
			
			break;
		case 'Trk':
			$text = 'TRUCK';
			$x = '302.72';
			$y = '224.75';
			$fontSize = '260px';
			$fontSeries = 'C';
			
			break;
		case 'Wye':
			return 'not external';  // usava Wye is a Y in the shield
		default:
			return ''; // Return an empty string when $banner is not valid.
	}
	
	// Replace variables in the template
	$svg = str_replace("***BORDER_COLOR***", $borderColor, $svg);
	$svg = str_replace("***TEXT_COLOR***", $textColor, $svg);
	$svg = str_replace("***FILL_COLOR***", $fillColor, $svg);
	
	$svg = str_replace("***TEXT***", $text, $svg);
	$svg = str_replace("***X***", $x, $svg);
	$svg = str_replace("***Y***", $y, $svg);
	$svg = str_replace("***TEXT_SIZE***", $fontSize, $svg);
	$svg = str_replace("***FONT_SERIES***", $fontSeries, $svg);
	
	
	// If the cache directory doesn't exist, create it
	if (!file_exists("{$dir}/cache/")) {
		mkdir("{$dir}/cache/", 0777, true);
	}

	// Save this banner in the cache before returning
	file_put_contents("{$dir}/cache/banner_{$banner}{$svgNameSuffix}.svg", $svg);
	
	return $svg;
	
}

/*
	Parse a raw banner string from the database and get an array of one or more banners.
	
	Params:
		String $bannerString - The string containing one or more banners (Bus, Alt, AltTrk, etc...)
		
	Returns an array of strings containing a single banner.
		For example: 'AltTrk' -> ['Alt', 'Trk']

*/
function getBannerArray ($bannerString) {
	return preg_split('/([[:upper:]][[:lower:]]+)/', $bannerString, 0, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
}
