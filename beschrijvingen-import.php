<?php
$start = microtime(true); // script timen
echo "Script gestart.<br />\n";
setlocale(LC_ALL, 'nlb.UTF8');
ini_set("auto_detect_line_endings", true);

// verbinding met db maken
$link = mysqli_connect('localhost','xqx_tdp','MBOBnW83', 'xqx_tdp');
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
// tabel leegmaken
echo "Verbinding met databank gemaakt.<br />\n";

$csv_data = file("uitgebreide-beschrijvingen.csv"); // csv data in variabele steken
$csv_length = count($csv_data); // aantal lijnen

// lussen over de csv
for ($i=0; $i < $csv_length; $i++) { 
	$tmp = explode(';', $csv_data[$i]); // huidige lijn
	$beschrijving = mysqli_real_escape_string($link, $tmp[1]);
	$trefnaam = mysqli_real_escape_string($link, $tmp[0]);

	// insert query
	$q = "UPDATE bolt_planten SET beschrijving = '$beschrijving' WHERE trefnaam = '$trefnaam' LIMIT 1";
	mysqli_query($link,$q) or die(mysqli_error($link));
	echo "Beschrijving toegevoegd voor $tmp[0].<br />\n"; 
}
mysqli_close($link);

$time_taken = microtime(true) - $start;
echo "$i Beschrijvingen verwerkt. ";
echo "Script klaar in $time_taken seconden.<br />\n";
?>