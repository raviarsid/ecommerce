<?php
/*****************************************************
 HOW TO EXECUTE PHPUnit

STEP 1: Install the PHPUnit.phar
STEP 2: Configure it as given in https://phpunit.de/ website
STEP 3: Go to the destination file/folder
STEP 4: Execute the unit test by following command

phpunit <filename> 

Example : Single test with 2 assertions

ravi@ravi-Vostro-3558:/opt/lampp/htdocs$ phpunit webcomtest.php

PHPUnit 3.7.28 by Sebastian Bergmann.

Time: 1.58 seconds, Memory: 1.05Mb

OK (1 test, 2 assertions)

*****************************************************/

//use GuzzleHttp\Client;
class WebcomTests extends PHPUnit_Framework_TestCase
{
	public $ch;
	public $jsondata;
	public $jsonRequestData;

	function setUp() {
	    $this->ch = curl_init();
	}

	function tearDown() {
	    curl_close($this->ch);
	}

	function serverRequest($requestFun=0,$requestAry=0,$requestMethod=0,$comapStr=''){
		$requestUrl = 'http://localhost/webcommerce.php?request='.$requestFun;
		if(is_array($requestAry)){
			$data_string = json_encode($requestAry); 			
		}else{
			$data_string = 0;		
		}
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_URL,$requestUrl);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $requestMethod);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS,$data_string);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);                                                                      
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(                                                                          
	    		'Content-Type: application/json',                                                                                
	    		'Content-Length: ' . strlen($data_string))                                                                       
		); 
		$server_output = curl_exec ($this->ch);
		$this->jsondata =  file_get_contents("php://input");
		$this->jsonRequestData = json_decode($server_output,true);
		return $this->assertContains($comapStr,$this->jsonRequestData['responseStatus']);	
	}
	 
	public function testAddCategory(){
		$requestAry = array("name"=>"nonVeg","description"=>"NonVeg not healthy","tax"=>4);
		$requestApi = 'addCategory';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'PUT','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'POST','Success');	
	}
	
	public function testUpdateCategory(){
		$requestAry = array("name"=>"nonVeg1","description"=>"NonVeg not healthy1","tax"=>5,"categoryId"=>4);
		$requestApi = 'updateCategory';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'POST','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'PUT','Success');	
	}
	
	public function testDeleteCategory(){
		$requestAry = array("categoryId"=>4);
		$requestApi = 'deleteCategory';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'PUT','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'POST','failed');
		
		//assert 3
		$this->serverRequest($requestApi,$requestAry,'DELETE','Success');
	}
	
	public function testCategoryList(){
		$requestAry = 0;
		$requestApi = 'categoryList';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'GET','Success');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'POST','failed');
	}
	
	public function testAddProduct(){
		$requestAry = array("name"=>"Banana","description"=>"A Banana for fit body","price"=>70,"discount"=>5,"categoryId"=>3);
		$requestApi = 'addProduct';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'PUT','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'POST','Success');
	}
	
	public function testUpdateProduct(){
		$requestAry = array("name"=>"Banana1","description"=>"A Banana for fit body1","price"=>70,"discount"=>5,"categoryId"=>3,"productId"=>27);
		$requestApi = 'updateProduct';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'POST','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'PUT','Success');
	}
	
	public function testDeleteProduct(){
		$requestAry = array("productId"=>27);
		$requestApi = 'deleteProduct';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'POST','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'PUT','failed');
		
		//assert 3
		$this->serverRequest($requestApi,$requestAry,'DELETE','Success');
	}
	
	public function testProductList(){
		$requestAry = 0;
		$requestApi = 'productList';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'POST','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'GET','Success');
	}
	
	public function testCreateCart(){
		$requestAry = array("categoryId"=>3,"productId"=>21,"quantity"=>1,"deviceId"=>2547854785547);
		$requestApi = 'createCart';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'PUT','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'POST','Success');	
	}
	
	public function testUpdateCart(){
		$requestAry = array("categoryId"=>3,"productId"=>21,"quantity"=>4,"deviceId"=>2547854785547);
		$requestApi = 'updateCart';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'POST','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'PUT','Success');
	}
	
	public function testDeleteCart(){
		$requestAry = array("categoryId"=>3,"productId"=>21,"quantity"=>4,"deviceId"=>2547854785547);
		$requestApi = 'deleteCart';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'POST','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'DELETE','Success');
	}
	
	public function testShowCart(){
		$requestAry = array("deviceId"=>2547854785547);
		$requestApi = 'showCart';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'GET','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'POST','Success');
	}
	
	public function testCartTotal(){
		$requestAry = array("deviceId"=>2547854785547);
		$requestApi = 'cartTotal';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'GET','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'POST','Success');
	}
	
	public function testCartTotalDiscount(){
		$requestAry = array("deviceId"=>2547854785547);
		$requestApi = 'cartTotalDiscount';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'GET','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'POST','Success');
	}
	
	public function testCartTotalTax(){
		$requestAry = array("deviceId"=>2547854785547);
		$requestApi = 'cartTotalTax';
		//assert 1
		$this->serverRequest($requestApi,$requestAry,'GET','failed');
		
		//assert 2
		$this->serverRequest($requestApi,$requestAry,'POST','Success');
	}
	
}
