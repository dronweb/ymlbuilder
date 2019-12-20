<?php

define('YML_CLASS_FOLDER',__DIR__.'/ymlbuilder');

include YML_CLASS_FOLDER.'/ymlbuilder.php';

$currencies = array();
$categories = array();
$deliveries = array();
$offers     = array();

$currencies = array(
    array("id"=> "UAN","rate"=> 1),
    array("id"=> "RUR","rate"=> 2)
);
$categories = array(
    array("id"=> 1,"name"=> 'Category1','parent_id'=>''),
    array("id"=> 2,"name"=> 'Category2','parent_id'=>1),
    array("id"=> 3,"name"=> 'Category3','parent_id'=>1),    
);
$deliveries = array(
    array('cost' => 500,'days' => '1-2', 'order-before' => 14),
    array('cost' => 0,'days' => '1-2', 'order-before' => 14),
    array('cost' => 500,'days' => '2-3', 'order-before' => ''),
    array('cost' => 0,'days' => '2-3', 'order-before' => '')
);
//iconv('utf-8', 'windows-1251','Продукция')
$offers = array();

$offers[] = array(
    'id' => 1,
    'type' => 'vendor.model',
    'bid' => '1',
    //'typePrefix' => '',
    'name' =>  'Товар',
    'vendor' => 'brand',
    'url' => 'c/product/test_product/',
    'price' => 1000,
    'currencyId' => 'RUR',
    'categoryId' => $category_id,
    'picture' => array(
        'http://test.ua/product/test_product/1.png',
        'http://test.ua/product/test_product/2.png',
        'http://test.ua/product/test_product/3.png'
    ),
    'store' => true,
    'pickup' => true,
    'delivery' => true,
    'delivery-options' => array(),
    'description' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Repudiandae suscipit recusandae minus maxime magnam corporis reiciendis ex. Accusantium quae sunt voluptatum. Quibusdam, accusamus neque fugit error consequatur at natus beatae?',
    'param' => array(
        array('name'=> 'Вес','unit'=> 'кг','value'=> '10'),
        array('name'=> 'Материал','unit'=> '','value'=> 'полиэстер'),
        array('name'=> 'Диаметр','unit'=> 'мм','value'=> '3'),
        array('name'=> 'Длина проволоки в катушке','unit'=> 'м','value'=> '1000'),
        array('name'=> 'Стойкость к разрыву','unit'=> 'кг','value'=> '330')    
    ),
    'sales_notes' => '',
    'manufacturer_warranty' => '',
    'country_of_origin' => 'Україна',
    'barcode' => '123456789'
);


$yml = new YMLBuilder();
$yml->setName('Test.ua');
$yml->setCompany('Test.ua - интернет-магазин товаров');
$yml->setMainUrl('https://test.ua');
$yml->setEmail('info@test.ua');
$yml->addCurrencies($currencies);
$yml->addDeliveries($deliveries);
$yml->addCategories($categories);
$yml->addOffers($offers);
$yml->buildYml();

header("Content-type: text/xml");

echo $yml->yml;
