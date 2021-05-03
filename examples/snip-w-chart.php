<?php
include('./src/Incidence.php');

### Configure here ###

# Find your region here and get the OBJECTID: 
# https://npgeo-corona-npgeo-de.hub.arcgis.com/datasets/917fc37a709542548cc3be077a786c17_0

$id = 105; //Bochum
$cache_file = './data.json';
$threshold = 165;
$minVal    = '100';

### End of configs ###

$chartData = array (
		    array ('Datum','Inzidenz', 'Color') ## 1-mal nur Überschriften !
		
			);
$ai  = 1;
$dc1 = 0;
			
$incidence = new Incidence($id, $cache_file);

$today = $incidence->getDaily(0);

echo "<div class='widget'>";

echo "<h3>Inzidenz-Ampel für " . $today['GEN'] . "</h3>";
echo "<h6>(Fälle pro 100.000 Einwohner in 7 Tagen)</h6>";

## ---- array chartData mit den Daten der letzten 5 Tage füllen ------------------------
for ($i = 4 ; $i >= 0 ; $i-- ) 
{
	$dailyData = $incidence->getDaily($i);

	if ($dailyData) 
	{
		    $inc = round($dailyData['cases7_per_100k'], 2);
			$day = date("d.m.", $dailyData['ts']);
			
			$chartData[$ai][0] = $day;
			$chartData[$ai][1] = $inc;	
	
			if($inc < 100) 
			{
			   $bc = "gold";
			}
			else 
			{ 
			    if ($inc < 165) 
				{
			      $bc = "red";      // unter 165 
				  $dc1++;			// day count unter 165
				}
				else 
				{ 
			      $bc = "darkred";   // höchste Stufe  
				}
			}	
			$chartData[$ai][2] = $bc;
			$ai++;
	}
}
	
$chartDatainJson = json_encode($chartData); ## nach schleife 			
			
# echo "<h6>Data " . $cdinJson . "</h6>"; ## -- fuer Test ...

# ------------------------------------------------------------------------
?>

<html>
<!-- Einbinden von google charts für Balken Diagramm 
 --------------------------------------------------->
 <head>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script type="text/javascript">
    google.charts.load("current", {packages:['corechart']});
    google.charts.setOnLoadCallback(drawChart);
  
  function drawChart() {
      var data = google.visualization.arrayToDataTable(<?php echo $chartDatainJson; ?>);
      
      var view = new google.visualization.DataView(data);

      view.setColumns([0, 1,
                       { calc: "stringify",      
                         sourceColumn: 1,
                         type: "string",
                         role: "annotation" },         
					   { sourceColumn: 2,
						 type: "string",
					     role: "style" } 	
                       ]);
      var options = {
        title: "Inzidenzen der letzten 5 Tage ",
        width: 370,
        height: 300,
		vAxis: {minValue: <?php echo $minVal; ?>},
        bar: {groupWidth: "45%"},
        legend: { position: "none" },
      };
      var chart = new google.visualization.ColumnChart(document.getElementById("columnchart_values"));
      chart.draw(view, options);
  }
  </script>
  </head>
  <body>
    <div id="columnchart_values" style="width: 370px; height: 300px;"></div>
  </body>
</html>  


<?php
# ---------------------------------hier geht es weiter mit php------- -------> 
echo "<div class='widget'>";

drawStoplight($today, $threshold);

echo "<table id='tbl_incidence'>";
echo drawLine($today);
echo drawLine($incidence->getDaily(1));
echo drawLine($incidence->getDaily(2));
echo drawLine($incidence->getDaily(3));
echo drawLine($incidence->getDaily(4));
echo "</table>";
echo "<h6>Quelle: www.rki.de </h6>";
#echo "<h6>Quelle: <a href='https://www.rki.de'>RKI</a></h6>";
echo "</div>";

function drawLine($data)
{
    if ($data) {

        $inc = round($data['cases7_per_100k'], 2);
        if ($inc < 100) {
            $co = "value_ok";
        } else {
            $co = "value_stop";
        }

        echo "<tr>
                <td>" . germanDay($data['ts']) . ", " . date("d.m.Y", $data['ts']) . "</td>
                <td class='" . $co . "'>" . round($data['cases7_per_100k'], 2) . "</td>
            </tr>";
    }
}

function drawStoplight($data, $threshold)
{
    if ($data['cases7_per_100k'] > $threshold) {
        $color = "stoplight_stop";
        $text = "Notbetrieb";
    } else {
        $color = "stoplight_ok";
        $text = "Inzidenz > 100 !";
    }
    echo "<div id='div_stoplight' class='" . $color . "'>";
    echo $text;
    echo "</div>";
}

function germanDay($ts)
{
    $d = [
        1 => "Montag",
        2 => "Dienstag",
        3 => "Mittwoch",
        4 => "Donnerstag",
        5 => "Freitag",
        6 => "Samstag",
        7 => "Sonntag"
    ];
    return $d[date("N", $ts)];
}

?>
<style>
    body,
    html {
        font-family: Arial, Helvetica, sans-serif;
    }

    h3 {
        text-align: center;
        margin: 1%;
    }

    h6 {
        text-align: center;
        margin: 1%;
        font-size: 0.7em;
    }

    .widget {
        width: 370px;
        border: thin solid #ccc;
        min-height: 300px;
    }

    #tbl_incidence {
        width: 100%;
        text-align: center;
    }

    #tbl_incidence td {
        width: 50%;
        border-bottom: thin solid #ccc;
    }



    #div_stoplight {
        margin-top: 5%;
        margin-bottom: 5%;
        padding-top: 5%;
        width: 100%;
        height: 50px;
        text-align: center;
        vertical-align: middle;
        font-size: 2em;
        color: #ccc;
    }

    .stoplight_stop {
        background-color: darkred;
    }

    .stoplight_ok {
        background-color: red;
    }

    .value_stop {
        color: darkred;
    }

    .value_ok {
        color: darkgreen;
    }
</style>
