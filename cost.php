<?php

header("Access-Control-Allow-Origin: *");
header('Content-type: application/json');
$curl = curl_init();
$origin=457; //origin city id
$destination=$_GET["destination"];
$weight=$_GET["weight"];
$courier=$_GET["courier"];
curl_setopt_array($curl, array(
  CURLOPT_URL => "http://api.rajaongkir.com/starter/city",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "key: your-api-key"
  ),
));


$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  //$provincelist=array(3,6,9);
//search your target city
  $obj=json_decode($response);
  $filter= $obj->rajaongkir->results;
  $get="";
  if(!is_null($_GET["destination"]))
  {
	$city_name=$_GET["destination"];
	for($i=0;$i<count($filter);$i++)
	{
		if(strtolower($filter[$i]->city_name)==strtolower($city_name))
		{
			$get=$filter[$i]->city_id;
			break;
		}
	}
  }
  if($get!="")
  {
		$curl2=curl_init();
		curl_setopt_array($curl2, array(
		  CURLOPT_URL => "http://api.rajaongkir.com/starter/cost",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "origin=". $origin ."&destination=" .$get. "&weight=".$weight."&courier=".$courier,
		  CURLOPT_HTTPHEADER => array(
			"content-type: application/x-www-form-urlencoded",
			"key: your-api-key"
		  ),
		));
		$response2 = curl_exec($curl2);
		$err2 = curl_error($curl2);
		curl_close($curl2);
		if ($err2) {
		  echo "cURL Error #:" . $err2;
		}
		else{
			$obj=json_decode($response2);
			if ($obj->rajaongkir->status->description=="OK") {
		    if (!is_null($_GET["service"])) 
		    {
		      $service=strtolower($_GET["service"]);
		      $result=$obj->rajaongkir->results[0]->costs;
		      $getlist = array();
		      for ($i=0; $i < count($result); $i++) { 
		        if (strtolower($result[$i]->service)==$service) {
		          array_push($getlist, $result[$i]);
		          break;   
		        }
		        else
		        {
		           if ($service=="reg") {
		              if (strtolower($result[$i]->service)=="ctc") {
		                array_push($getlist, $result[$i]);
		                break;   
		              }
		           }
		           else if ($service=="yes") {
		              if (strtolower($result[$i]->service)=="ctcyes") {
		                array_push($getlist, $result[$i]);
		                break;   
		              }
		           }

		        }
		      }
		      $obj->rajaongkir->results[0]->costs=$getlist;
		      if (count($getlist)==0) {
		        $obj->rajaongkir->status->code="400";
		        $obj->rajaongkir->status->description="Service not found";
		      }
		     
		     }
		    echo json_encode($obj); 		  
		}
	}
  }
}