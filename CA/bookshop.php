#!/usr/bin/php

<?php

function echoOrder($order){
	echo "C".$order['id']." : I want to order the following book(s) according to the bookId: ";
	$bookCount = count($order) - 1;
	for($i = 0; $i < $bookCount; $i++){
		echo $order[$i];
		if($i == $bookCount - 1){
			echo ".\n";
		}
		else{
			echo ", ";
		}
	}
}

function checkAvailability($order){
	global $stock;
	$stockHlp = $stock;

	for($i = 0; $i < sizeof($order) - 1; $i++){
		$stockHlp[$order[$i]]--; 
		if($stockHlp[$order[$i]] == -1){
			echo "S : Not all requested books are in stock. Order of customer C".$order['id']." cancelled.\n";
			return false;	 	
		}
	}
	return true;
}

function sendBooks($order){
	global $stock;

	for($i = 0; $i < sizeof($order) - 1; $i++){
		$stock[$order[$i]]--; 
	}
	echo "S : All requested books were in stock. Order of customer C".$order['id']." completed.\n";
}

$stock = array(7, 6, 8, 2, 1, 5, 3, 4, 3, 9);

$context = new ZMQContext(1);
$responder = new ZMQSocket($context, ZMQ::SOCKET_REP);
$responder->bind("tcp://*:6666");

echo "S : Welcome to the bookshop\nHere you can find a variety of interesting and entertaining books - enjoy\n_______________________________________\n";

echo "S: In this version of our bookshop, we don't work with partitioning. Only availability and consistency are important!\n";

$idCounter = 0;

while(true){
	$request = $responder->recv();

	if(strcmp($request, "hi") == 0){
		$idCounter++;
		echo "S : There's a new customer, it's called C".$idCounter."\n";
		$responder->send($idCounter);
	}
	else{
		$order = json_decode($request, true);
		echoOrder($order);
		$available = checkAvailability($order);

		if(!$available){
			$responder->send("S : Sorry, not all books are in stock. Order cancelled.\n");
		}
		else{
			sendBooks($order);
			$responder->send("S : Order successfully completed.\n");
		}
	}

}

?>
