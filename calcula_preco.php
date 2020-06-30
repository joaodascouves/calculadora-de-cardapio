<?php

/*
	Simple script to symmetrically calculate the price of a restaurant menu items.

	To run, edit array $items then execute script.
	print_result() can be replaced in order to output result as JSON or whatever.

	Example output:

	[void@localhost ~]$ php calcula_preco.php 

	Hamburguer (pao, hamburguer, salada) ................................................................... R$ 7.00
	Egg Burguer (pao, hamburguer, salada, ovo) ............................................................. R$ 8.00
	Cheese Burguer (pao, hamburguer, salada, queijo) ....................................................... R$ 8.00
	Cheese Bacon (pao, hamburguer, salada, queijo, bacon) .................................................. R$ 11.00
	Cheese Egg Bacon (pao, hamburguer, salada, queijo, bacon, ovo) ......................................... R$ 12.00
	Cheese Egg Burguer (pao, hamburguer, salada, queijo, ovo) .............................................. R$ 9.00
	Cheese Bacon Frango (pao, salada, queijo, bacon, frango) ............................................... R$ 12.00
	Frango Burguer (pao, hamburguer, salada, frango) ....................................................... R$ 11.00
	Cheese Frango (pao, salada, frango, queijo) ............................................................ R$ 9.00
	Egg Frango (pao, salada, frango, ovo) .................................................................. R$ 9.00
	Cheese Egg Frango (pao, salada, frango, ovo, queijo) ................................................... R$ 10.00
	Cheese Egg Bacon Frango (pao, salada, frango, ovo, queijo, bacon) ...................................... R$ 13.00
	Bacon Frango (pao, salada, frango, bacon) .............................................................. R$ 11.00
	Cantina Burguer (pao, hamburguer, salada, frango, bacon, ovo, presunto, queijo) ........................ R$ 17.00
	Misto (pao, presunto, queijo) .......................................................................... R$ 5.00


	Author: João G. Santos (joaosantos.2000@alunos.utfpr.edu.br)
	github.com/joaodascouves
*/

/* Price to be added to each item. */
$base_price = 2.00;

/* Common ingredients. */
$base_ingredients = [
	/* ingredientName:String		=> [ingredientCost:Double, [blacklistedItems:String,]] */
	'pao'	 						=> [1.00, []],
	'hamburguer' 					=> [3.00, ['Misto', 'Frango']],
	'salada' 						=> [1.00, ['Misto']],
];

/* Extra ingredients. */
$ingredients = [
	/* [ingredientName:String		=> ingredientCost:Double] */
	'ovo'							=> 1.00,
	'queijo'						=> 1.00,
	'bacon'							=> 3.00,
	'frango'						=> 4.00,
	'presunto'						=> 1.00
];

/* 
	Menu items

	If the itemName has an exclamation point as first character,
	the base_ingredients blacklist will be bypassed.
*/
$items = [
	/* [itemName:String, 			[itemIngredient:String,]] */
	['Hamburguer',					[]],
	['Egg Burguer', 				['ovo']],
	['Cheese Burguer', 				['queijo']],
	['Cheese Bacon', 				['queijo', 'bacon']],
	['Cheese Egg Bacon', 			['queijo', 'bacon', 'ovo']],
	['Cheese Egg Burguer', 			['queijo', 'ovo']],
	['Cheese Bacon Frango', 		['queijo', 'bacon', 'frango']],
	['!Frango Burguer',				['frango']],
	['Cheese Frango', 				['frango', 'queijo']],
	['Egg Frango', 					['frango', 'ovo']],
	['Cheese Egg Frango', 			['frango', 'ovo', 'queijo']],
	['Cheese Egg Bacon Frango', 	['frango', 'ovo', 'queijo', 'bacon']],
	['Bacon Frango', 				['frango', 'bacon']],
	['Cantina Burguer', 			['frango', 'bacon', 'ovo', 'presunto', 'queijo']],
	['Misto', 						['presunto', 'queijo']]
];

function calc_base_price($item_name)
{
	global $base_price;
	global $base_ingredients;

	$ingredients = $base_ingredients;

	array_walk($ingredients, function (&$items, $name, $item_name) {

		$skip = (array_sum(array_map(function ($item) use ($item_name) {

			return (strpos($item_name, $item) !== false && strpos($item_name, '!') !== 0 )
				? 1
				: 0;
		}, $items[1])) > 0);

		if ( $skip ) {
			$items[0] = 0;
		}
	}, $item_name);

	$total	= $base_price;
	$total += array_sum(array_map(function($ingredient) {
		return $ingredient[0];

	}, array_values($ingredients)));

	return $total;
}

function iterate_ingredients($ingredient)
{
	global $ingredients;

	if (array_key_exists($ingredient, $ingredients)) {
		return $ingredients[$ingredient];
	} else {
		echo "Warning: {$ingredient} price not set.\n";
	}
}

function iterate_items($item)
{
	$price	= calc_base_price($item[0]);
	$price += array_sum(array_map('iterate_ingredients', $item[1]));

	return [
		$item[0],
		$item[1],
		$price
	];
}

function print_result($item)
{
	global $base_ingredients;

	$name = $item[0];
	$ingredients = $base_ingredients;

	array_walk($ingredients, function(&$items, $name, $item_name) {
		$skip = (array_sum(array_map(function($item) use ($item_name) {
			return ( strpos($item_name, $item) !== false && strpos($item_name, '!') !== 0 )
				? 1
				: 0;
		}, $items[1])) > 0);

		if( $skip )
		{
			$items[0] = -1;
		}
		
	}, $name);

	$my_base_ingredients = [];

	array_walk($ingredients, function($items, $name) use (&$my_base_ingredients) {
		if( $items[0] !== -1 )
		{
			$my_base_ingredients[] = $name;
		}

	});

	$ingredients = array_merge($my_base_ingredients, $item[1]);
	$ingredients = implode(', ', $ingredients);

	$formatted_price = number_format($item[2], 2);

	if( $name[0] === '!' )
	{
		$name = substr($name, 1, strlen($name));
	}

	$dots = str_repeat('.', 100 - (strlen($name) + strlen($ingredients)) );

	echo sprintf("%s (%s) %s R$ %.2f\n",
		$name,
		$ingredients,
		$dots,
		$formatted_price);
}

$result = array_map('iterate_items', $items);

echo "\n";
array_map('print_result', $result);
echo "\n\n";