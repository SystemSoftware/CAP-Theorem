#!/usr/bin/php

<?php

function generateOrder($id){

	$bookCount = rand(1,5);
	echo "C".$id." : I want to buy ".$bookCount." book(s). Let's hope they have my order in stock.\n";

	$order = array();
	for($i = 0; $i < $bookCount; $i++){
		$order[$i] = rand(0, 9);
	}
	$order['id'] = $id;
	
	echoOrder($order, $bookCount);
	return $order;
}

function echoOrder($order, $bookCount){
	echo "C".$order['id']." : I order the following book(s) according to the bookId: ";
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

function placeOrder($id, $order){
	global $requester;
	$requester->send(json_encode($order));
	$status = $requester->recv();
	echo $status;
}

$context = new ZMQContext();
$requester = new ZMQSocket($context, ZMQ::SOCKET_REQ);
$requester->connect("tcp://localhost:6666");

for($i=0; $i < 5; $i++){
	sleep(rand(0, 5));

	$requester->send("hi");
	$id = $requester->recv();
	echo "C".$id." : Hi I'm a new customer. I want to buy something to read!\n";

	$order = generateOrder($id);
	placeOrder($id, $order);
}

?>
