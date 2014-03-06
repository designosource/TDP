<?php 

$connect = mysqli_connect('localhost','bolt','2@L4o0bl', 'bolt');
if (!$connect) { 
    die('Could not connect to MySQL: ' . mysql_error()); 
} 

//$cid =mysql_select_db('xqx_tdp',$connect); 
// supply your database name

define('CSV_PATH',''); 
// path where your CSV file is located

    $csv_file = CSV_PATH . "sql_namen.txt"; // Name of your CSV file
    $query = "INSERT INTO bolt_planten(actie,idgp,trefnaam,latijnse_n,groep,artcolor,afbeelding,maxhoogte,bloeimaand,blad,vorstbest,bodem,namenl,standplaat, status, username, datecreated, datechanged, datepublish, slug) 
              VALUES";

    $query_cat = "INSERT INTO bolt_taxonomy(content_id, contenttype, taxonomytype, slug, name,sortorder) 
                  VALUES";
    $datum = date('Y-m-d G:i:s');

    $csvdata = file($csv_file, FILE_IGNORE_NEW_LINES);
    $linenumbers = count($csvdata);
    $categorien = array("","heesters", "grassen-bamboe", "vaste-planten", "bodembedekkers","zuurminnende-planten","klimplanten","bomen","bolbomen","leibomen-dakbomen","fruitbomen-kleinfruit","coniferen","haagplanten","buxus","eenjarigen","hortensia","kruiden","rozen","terrasplanten","zuiderse-planten","kerstbomen","varia1","varia2");

    for ($i=0; $i < $linenumbers; $i++) { 

        $csv_array = explode(';', $csvdata[$i]);

        if(is_array($csv_array[4])){

            $groepen = explode(",", $csv_array[4]);
            $max_value = max($groepen);

        }else{

            $groepen = $csv_array[4];
            $max_value = $groepen;

        }

        if($max_value<=20){
            if( $insert_csv['TREFNAAM'] != $csv_array[2] && $insert_csv['NAMENL'] != $csv_array[12]){
                $insert_csv['ACTIE'] = $csv_array[0];
                $insert_csv['IDGP'] = $csv_array[1];
                $insert_csv['TREFNAAM'] = $csv_array[2];
                $insert_csv['LATIJNSE_N'] = $csv_array[3];
                $insert_csv['GROEP'] = $csv_array[4];
                $insert_csv['ARTCOLOR'] = $csv_array[5];
                $insert_csv['KENMERKX'] = $csv_array[6];
                $insert_csv['MAXHOOGTE'] = $csv_array[7];
                $insert_csv['BLOEIMAAND'] = $csv_array[8];
                $insert_csv['BLAD'] = $csv_array[9];
                $insert_csv['VORSTBEST'] = $csv_array[10];
                $insert_csv['BODEM'] = $csv_array[11];
                $insert_csv['NAMENL'] = $csv_array[12];
                $insert_csv['STANDPLAAT'] = $csv_array[13];
                $query .= "('".mysqli_real_escape_string($connect, $insert_csv['ACTIE'])."',
                        '".mysqli_real_escape_string($connect, $insert_csv['IDGP'])."',
                        '".mysqli_real_escape_string($connect, $insert_csv['TREFNAAM'])."',
                        '".mysqli_real_escape_string($connect, $insert_csv['LATIJNSE_N'])."',
                        '".mysqli_real_escape_string($connect, $insert_csv['GROEP'])."',
                        '".mysqli_real_escape_string($connect, $insert_csv['ARTCOLOR'])."',
                        '".mysqli_real_escape_string($connect, $insert_csv['KENMERKX'])."',
                        '".mysqli_real_escape_string($connect, $insert_csv['MAXHOOGTE'])."',
                        '".mysqli_real_escape_string($connect, $insert_csv['BLOEIMAAND'])."',
                        '".mysqli_real_escape_string($connect, $insert_csv['BLAD'])."',
                        '".mysqli_real_escape_string($connect, $insert_csv['VORSTBEST'])."',
                        '".mysqli_real_escape_string($connect, $insert_csv['BODEM'])."',
                        '".mysqli_real_escape_string($connect, ucfirst(strtolower($insert_csv['NAMENL'])))."',
                        '".mysqli_real_escape_string($connect, $insert_csv['STANDPLAAT'])."',
                        'published',
                        'cronjob',
                        '".$datum."',
                        '".$datum."',
                        '".$datum."',
                        '".mysqli_real_escape_string($connect, $insert_csv['IDGP'])."'),";
            }
  
        }           

    }

    $query = substr($query, 0, -1);
    
    //echo $query_cat;

    $truncateTable = "TRUNCATE TABLE bolt_planten";
    $truncateResult = mysqli_query($connect, $truncateTable);

    $result = mysqli_query($connect, $query);
    

    if($result){
        echo "";
    }
    else
    {
        echo mysqli_error($connect) . "Dat klopt hier niet";
    }

    for ($i=0; $i < $linenumbers; $i++) { 

        $csv_array = explode(';', $csvdata[$i]);
        $selectresult = mysqli_query($connect,"SELECT id, trefnaam from bolt_planten WHERE idgp = ".$csv_array[1]."");
        $categorien = array("","heesters", "grassen-bamboe", "vaste-planten", "bodembedekkers","zuurminnende-planten","klimplanten","bomen","bolbomen","leibomen-dakbomen","fruitbomen-kleinfruit","coniferen","haagplanten","buxus","eenjarigen","hortensia","kruiden","rozen","terrasplanten","zuiderse-planten","kerstbomen","varia1","varia2");
        var_dump($categorien);
        $index = $csv_array[4];
        $categorie = $categorien[$index];
        echo "<ul><li>$categorie</li></ul>";

        while($row = mysqli_fetch_array($selectresult))
        {

            $query_cat .= "('".$row['id']."',
                        'planten',
                        'categories',
                        '".strtolower($categorie)."',
                        '".strtolower($categorie)."',
                        '0'),";
        }

    }

    $truncatetaxonomy = "TRUNCATE TABLE bolt_taxonomy";
    $truncatetasonomyresult = mysqli_query($connect, $truncatetaxonomy);

    $query_cat = substr($query_cat, 0, -1);
    $result_taxonomy = mysqli_query($connect, $query_cat);
    
    //fclose($csvfile);
    mysqli_close($connect);
?>