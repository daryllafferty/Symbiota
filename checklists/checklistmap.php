<?php
include_once('../config/symbini.php');
include_once($SERVER_ROOT.'/classes/ChecklistManager.php');
header("Content-Type: text/html; charset=".$CHARSET);

$clid = $_REQUEST['clid'];
$thesFilter = array_key_exists("thesfilter",$_REQUEST)?$_REQUEST["thesfilter"]:0;
$taxonFilter = array_key_exists("taxonfilter",$_REQUEST)?$_REQUEST["taxonfilter"]:""; 

$clManager = new ChecklistManager();
$clManager->setClValue($clid);
if($thesFilter) $clManager->setThesFilter($thesFilter);
if($taxonFilter) $clManager->setTaxonFilter($taxonFilter);

$coordArr = $clManager->getCoordinates(0);
$swBound; 
$neBound; 
if($coordArr){
	$swBound = $coordArr['sw'];
	$neBound = $coordArr['ne'];
	unset($coordArr['sw']);
	unset($coordArr['ne']);
}
?>
<html>
<head>
	<title><?php echo $DEFAULT_TITLE; ?> - Checklist Coordinate Map</title>
	<link href="../css/base.css?ver=<?php echo $CSS_VERSION; ?>" type="text/css" rel="stylesheet" />
	<link href="../css/main.css<?php echo (isset($CSS_VERSION_LOCAL)?'?ver='.$CSS_VERSION_LOCAL:''); ?>" type="text/css" rel="stylesheet" />
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<script src="//maps.googleapis.com/maps/api/js?<?php echo (isset($GOOGLE_MAP_KEY) && $GOOGLE_MAP_KEY?'key='.$GOOGLE_MAP_KEY:''); ?>"></script>
	<script type="text/javascript">
		var map;
		var useLLDecimal = true;
	    var infoWins = new Array();
	    var puWin;

	    function initialize(){
	    	var dmOptions = {
				zoom: 3,
				center: new google.maps.LatLng(41,-95),
				mapTypeId: google.maps.MapTypeId.TERRAIN,
				scaleControl: true
			};


            <?php
			if($coordArr){
				?>
		    	map = new google.maps.Map(document.getElementById("map_canvas"), dmOptions);
				var vIcon = new google.maps.MarkerImage("../images/google/smpin_red.png");
				var pIcon = new google.maps.MarkerImage("../images/google/smpin_blue.png");
				<?php 
				$mCnt = 0;
				foreach($coordArr as $tid => $cArr){
					foreach($cArr as $pArr){
						if(array_key_exists('occid',$pArr)){
							echo 'var m'.$mCnt.' = new google.maps.Marker({position: new google.maps.LatLng('.$pArr['ll'].'),map:map,title:"'.$pArr['notes'].'",icon:vIcon});'."\n";
							echo 'google.maps.event.addListener(m'.$mCnt.',"click",function(){ openIndPU('.$pArr['occid'].'); });'."\n";
						}
						else{
							echo 'var m'.$mCnt.' = new google.maps.Marker({position: new google.maps.LatLng('.$pArr['ll'].'),map:map,title:"'.$pArr['sciname'].'",icon:pIcon});'."\n";
						}
						$mCnt++;
					}
				}
				echo 'var swLatLng = new google.maps.LatLng('.$swBound.')'."\n";  
				echo 'var neLatLng = new google.maps.LatLng('.$neBound.')'."\n";  
				?>
				var llBounds = new google.maps.LatLngBounds(swLatLng, neLatLng);
				map.fitBounds(llBounds);
				<?php 
            }
            ?>
        }

		function openIndPU(occId){
			if(puWin != null) puWin.close();
			var puWin = window.open('../collections/individual/index.php?occid='+occId,'indspec' + occId,'scrollbars=1,toolbar=1,resizable=1,width=900,height=600,left=20,top=20');
			if(puWin.opener == null) puWin.opener = self;
			setTimeout(function () { puWin.focus(); }, 0.5);
			return false;
		}

    </script>
    <style>
		html, body, #map_canvas {
			width: 100%;
			height: 100%;
			margin: 0;
			padding: 0;
		}
	</style>
</head> 
<body style="background-color:#ffffff;" onload="initialize();">
<?php
    if(!$coordArr){ //no results
    	?>
		<div style='font-size:120%;font-weight:bold;'>
			Your query apparently does not contain any records with coordinates that can be mapped.
		</div>
		<div style="margin:15px;">
			It may be that the vouchers have rare/threatened status that require the locality coordinates be hidden.
		</div>
        <?php 
    }
    ?>
	<div id='map_canvas'></div>
</body>
</html>
