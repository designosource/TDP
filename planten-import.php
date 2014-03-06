<?php
$start = microtime(true); // script timen
echo "Script gestart.<br />\n";
setlocale(LC_ALL, 'nlb.UTF8');

// verbinding met db maken
$link = mysqli_connect('localhost','xqx_tdp','MBOBnW83', 'xqx_tdp');
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
// tabel leegmaken
mysqli_query($link, 'TRUNCATE TABLE bolt_planten') or die(mysqli_error($link));
mysqli_query($link, 'TRUNCATE TABLE bolt_taxonomy') or die(mysqli_error($link));
echo "Databank geherinitialiseerd.<br />\n";

$csv_data = file("sql_namen.txt", FILE_IGNORE_NEW_LINES); // csv data in variabele steken
$csv_length = count($csv_data); // aantal lijnen

$csv_array = array(); // target array voor de opgekuiste data

// lussen over de csv
for ($i=0; $i < $csv_length; $i++) { 
	$tmp = explode(';', $csv_data[$i]); // huidige lijn
	$last = end($csv_array); // laatste lijn

	// controleren op lege strings, strings met punt erin en duplicate id's
	if (strlen($tmp[12]) > 0 && stripos($tmp[3], ".") === false && $tmp[2] !== $last[2]){
		$tmp[4] = explode(',', $tmp[4]); // categorieën verwerken
		// controleren of categorie binnen scope valt
		if(max($tmp[4]) < 21){
			// alle checks ok!
			$csv_array[] = $tmp; // huidige lijn toevoegen aan array
			insertPlant($link, $tmp); // en inserten in de db

			echo "Plant toegevoegd: $tmp[2] (" . ucfirst(strtolower($tmp[12])) . ").<br />\n"; 
			// DEBUG echo "<pre>".print_r($tmp)."</pre>";
		}
	}
}


/**
 * toAscii
 *
 * maakt een slug aan op basis van een string
 *
 * @param [string] willekeurige string
 * @return [string] slug
 */
function toAscii($str, $replace=array(), $delimiter='-') {
	if( !empty($replace) ) {
		$str = str_replace((array)$replace, ' ', $str);
	}

	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

	return $clean;
}

/**
 * insertPlant
 *
 * functie specifiek om planten te inserten in de db 
 * op basis van een array met bepaalde kolommen
 *
 * @param [msqli] link object voor db
 * @param  [array] data
 * @return none
 */
function insertPlant($link, $array){
	$nederlandse_naam = mysqli_real_escape_string($link,ucfirst(strtolower($array[12])));
	$latijnse_naam = mysqli_real_escape_string($link,ucfirst(strtolower($array[3])));
	$trefnaam = mysqli_real_escape_string($link,$array[2]);
	$afbeelding = "planten/". mysqli_real_escape_string($link,$array[6]);
	$kleur = mysqli_real_escape_string($link,ucfirst(strtolower($array[5])));
	$max_hoogte = mysqli_real_escape_string($link,$array[7]);
	$bloeimaand = mysqli_real_escape_string($link,$array[8]);
	$datum = date('Y-m-d G:i:s');
	$slug = toAscii($array[12]."-".$latijnse_naam);

	// insert query
	$q = "INSERT INTO bolt_planten(slug, datecreated, datechanged, datepublish, datedepublish, username, status, nederlandse_naam, latijnse_naam, trefnaam, afbeelding, kleur, max_hoogte, bloeimaand)";
	$q .= "VALUES('$slug','$datum','$datum','$datum','','cronjob','published','$nederlandse_naam','$latijnse_naam','$trefnaam','$afbeelding','$kleur','$max_hoogte','$bloeimaand')";
	mysqli_query($link,$q) or die(mysqli_error($link));

	$cat_ids = $array[4]; // categorie ids van de plant
	$lastid = mysqli_insert_id($link); // id ophalen die we net insertte
	$cat_namen = array("","heesters", "grassen & bamboe", "vaste planten", "bodembedekkers", "zuurminnende planten", "klimplanten", "bomen", "bolbomen", "leibomen & dakbomen", "fruitbomen & kleinfruit", "coniferen", "haagplanten", "buxus", "eenjarigen", "hortensia", "kruiden", "rozen", "terrasplanten", "zuiderse planten", "kerstbomen");
	$cat_slugs = array_map("toAscii", $cat_namen);

	// voor elke categorie die van toepassing is op de plant moeten we
	// een record in de tussentabel aanmaken
	foreach($cat_ids as $cat_id){
		$q  = 'INSERT INTO bolt_taxonomy(content_id, contenttype, taxonomytype, slug, name, sortorder)';
		$q .= "VALUES($lastid, 'planten', 'categories', '$cat_slugs[$cat_id]', '$cat_namen[$cat_id]', '0')";
		mysqli_query($link,$q) or die(mysqli_error($link));
	}
}
mysqli_close($link);

echo "<br />" . sizeof($csv_array) . " Planten toegevoegd. " . ($i - sizeof($csv_array)) . " Records overgeslaan.<br />\n";
$time_taken = microtime(true) - $start;
echo "Script klaar in $time_taken seconden.<br />\n";
?>