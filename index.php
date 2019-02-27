<?php
	//Ambil data dari file csv dan ubah ke array
    $theData = array();
	$filename = "TestsetTugas1ML.csv";
	if (($handle = fopen($filename, "r")) !== FALSE) {
		$key = 0;
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$count = count($data);
			for ($i=0; $i < $count; $i++) {
				$theData[$key][$i] = $data[$i];
			}
			$key++;
		}
		fclose($handle);
	}
	$theData[0][8] = 'Income';
	
	$theTrain = array();
	$filename = "TrainsetTugas1ML.csv";
	if (($handle = fopen($filename, "r")) !== FALSE) {
		$key = 0;
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$count = count($data);
			for ($i=0; $i < $count; $i++) {
				$theTrain[$key][$i] = $data[$i];
			}
			$key++;
		}
		fclose($handle);
	}
	
	$row = sizeof($theTrain);
	$col = sizeof($theTrain[1]);
	$inc = $col - 1; //income index
	//$theCategory[kategori][nilai] ex [age][young]
	for($i = 1;$i < $col;$i++){
		$theCategory[$i] = array_values(array_unique(array_column($theTrain, $i)));
		array_shift($theCategory[$i]);
		//print_r($theCategory[$i]);echo '<br>';
	}

	//$count[kategori][nilai][income] ex [age][young][income] IGNORE index 0
	$count = array(array(array(0)));
	for($i = 1;$i < $row;$i++){
		for($j = 1;$j < ($col - 1);$j++){
			$size = sizeof($theCategory[$j]);			
			for($k = 0;$k < $size;$k++){
				if($theTrain[$i][$j] == $theCategory[$j][$k]){
					if($theTrain[$i][$inc] == $theCategory[$inc][0]){
						if(empty($count[$j][$k][0])){
							$count[$j][$k][0] = 1;
						}else{
							$count[$j][$k][0] = $count[$j][$k][0] + 1;
						}
					}else if($theTrain[$i][$inc] == $theCategory[$inc][1]){
						if(empty($count[$j][$k][1])){
							$count[$j][$k][1] = 1;
						}else{
							$count[$j][$k][1] = $count[$j][$k][1] + 1;
						}
					}
				}
			}
		}
	}
	//print_r($count);
	
	//$countIncome[0] = >50, [1] <= 50
	$countIncome = array(0,0);
	for($i = 1;$i < $row;$i++){
		if($theTrain[$i][$inc] == $theCategory[$inc][0]){
			$countIncome[0] = $countIncome[0] + 1;
		}else{
			$countIncome[1] = $countIncome[1] + 1;
		}
	}
	//print_r($countIncome);
	
	//$probs[kategori][nilai][income] ex [age][young][income] IGNORE index 0
	$probs = array(array(array(0)));
	for($i = 1;$i < sizeof($count);$i++){
		for($j = 0;$j < sizeof($count[$i]);$j++){
			$probs[$i][$j][0] = $count[$i][$j][0] / $countIncome[0];
			$probs[$i][$j][1] = $count[$i][$j][1] / $countIncome[1];
		}
	}
	//print_r($probs)
	
	//$probsIncome[0] = >50, [1] <= 50
	$probsIncome[0] = $countIncome[0] / ($row - 1);
	$probsIncome[1] = $countIncome[1] / ($row - 1);
	//print_r($probsIncome);
	
	//$prediction [0] = >50, [1] <= 50
	$rowData = sizeof($theData);
	$colData = sizeof($theData[1]);
	$prediction = array();
	for($i = 1;$i < $rowData;$i++){
		$multiplication[0] = 1;
		$multiplication[1] = 1;
		for($j = 1;$j < $colData;$j++){
			$size = sizeof($theCategory[$j]);
			for($k = 0;$k < $size;$k++){
				if($theData[$i][$j] == $theCategory[$j][$k]){
					$multiplication[0] = $multiplication[0] * $probs[$j][$k][0];
					$multiplication[1] = $multiplication[1] * $probs[$j][$k][1];
				}
			}
		}
		$multiplication[0] = $multiplication[0] * $probsIncome[0];
		$multiplication[1] = $multiplication[1] * $probsIncome[1];
		if($multiplication[0] > $multiplication[1]){			
			$prediction[$i] = $theCategory[$inc][0];
		}else if($multiplication[0] < $multiplication[1]){
			$prediction[$i] = $theCategory[$inc][1];
		}
	}
	//print_r($prediction);
	
	//Prepare output array
	$out = array();
	for($q = 1;$q < $rowData;$q++){
		$i = $q - 1;
		$out[$i][0] = $prediction[$q];
		//echo $theData[$q][0] . ' - ' . ($theData[$q][1]) . ' - ' . ($theData[$q][2]) . ' - ' . (($theData[$q][2])/($theData[$q][1])) . ' - ' . $theData[$q][3] . '<br>';
	}
	
	//Export to CSV
	$output = fopen("php://output",'w') or die("Can't open php://output");
	header("Content-Type:application/csv"); 
	header("Content-Disposition:attachment;filename=TebakanTugas1ML.csv");
	foreach($out as $outp) {
		fputcsv($output, $outp);
	}
	fclose($output) or die("Can't close php://output");
?>