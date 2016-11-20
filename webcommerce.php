<?php
error_reporting(0);
class Webcommerce {
	private static $webComInstance = null;
	private $host = 'localhost';
	private $user = 'root';
	private $pass = '';
	private $db = 'ecommerce';
	private $connection = '';
	private $dbQuery = '';
	private $returnResponse = '';
	public function __construct(){
		$this->connectDB();
		$this->jsonData =  file_get_contents("php://input");
        $this->requestData = json_decode($this->jsonData,true); 
	}

	//connect to database
	private function connectDB(){
		$this->connection = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
		if(mysqli_connect_error()){
			echo mysqli_connect_error();exit;
		}
	}

	//create singleton class
	public static function getInstance()
  	{
    	if(!self::$webComInstance)
    	{
      		self::$webComInstance = new Webcommerce();
    	}
    	return self::$webComInstance;
  	}
  	

	//check for input method
	private function isRequest($type){
		$returnStatus = false;
		if($_SERVER['REQUEST_METHOD'] == $type){
			$returnStatus = true;
		}
		return $returnStatus;
	}

	//response builder
	private function actionQuery($type){
		// prepare and bind
		$returnStatus = false;
		$requestData = $this->requestData;
		switch($type){
			case 'addCategory':
				$description='';
				$this->dbQuery = $this->connection->prepare("INSERT INTO categories (categoryName, categoryDesc, categoryTax) VALUES (?, ?, ?)");
				$name = mysql_real_escape_string($requestData['name']);
				$description = mysql_real_escape_string($requestData['description']);
				$tax = floatval($requestData['tax']);
				$this->dbQuery->bind_param('ssd', $name, $description, $tax);
				$returnStatus = $this->exeQuery();
			break;

			case 'updateCategory':				
				$this->dbQuery = $this->connection->prepare("UPDATE categories SET categoryName=?,categoryDesc=?,categoryTax=? WHERE categoryId=? AND categoryIsDeleted=1");
				$name = mysql_real_escape_string($requestData['name']);
				$description = mysql_real_escape_string($requestData['description']);
				$tax = floatval($requestData['tax']);
				$categoryId = intval($requestData['categoryId']);
				$this->dbQuery->bind_param('ssdi', $name, $description, $tax,$categoryId);
				$returnStatus = $this->exeQuery();
			break;

			case 'deleteCategory':
				$this->dbQuery = $this->connection->prepare("UPDATE categories SET categoryIsDeleted=? WHERE categoryId=? AND categoryIsDeleted=1");
				$categoryId = intval($requestData['categoryId']);
				$deleteStatus = 0;
				$this->dbQuery->bind_param('ii',$deleteStatus,$categoryId);
				$returnStatus = $this->exeQuery();
			break;

			case 'categoryList':
				$this->dbQuery = $this->connection->prepare("SELECT * FROM categories WHERE categoryIsDeleted=1");
				$returnStatus = $this->exeQuery(1);
				while($row = $returnStatus->fetch_array(MYSQLI_NUM)){
					$category = array(
						'id'=>$row[0],
						'name'=>$row[1],
						'description'=>$row[2],
						'tax'=>$row[3]
					);
					$categoryList[] = $category;
				}
				$returnStatus = $categoryList;
			break;

			case 'addProduct':
				$this->dbQuery = $this->connection->prepare("INSERT INTO products (categoryId, productName, productDesc,productPrice,productDiscount) VALUES (?, ?, ?, ?, ?)");
				$categoryId = intval($requestData['categoryId']);
				$name = mysql_real_escape_string($requestData['name']);
				$desc = mysql_real_escape_string($requestData['description']);
				$price = floatval($requestData['price']);
				$discount = floatval($requestData['discount']);
				$this->dbQuery->bind_param('issdd', $categoryId, $name, $desc, $price, $discount);
				$returnStatus = $this->exeQuery();
			break;

			case 'updateProduct':				
				$this->dbQuery = $this->connection->prepare("UPDATE products SET categoryId=?, productName=?, productDesc=?,productPrice=?,productDiscount=? WHERE productId=? AND productIsDeleted=1");
				$categoryId = intval($requestData['categoryId']);
				$name = mysql_real_escape_string($requestData['name']);
				$desc = mysql_real_escape_string($requestData['description']);
				$price = floatval($requestData['price']);
				$discount = floatval($requestData['discount']);
				$productId = intval($requestData['productId']);
				$this->dbQuery->bind_param('issddi',$categoryId, $name, $desc, $price, $discount, $productId);
				$returnStatus = $this->exeQuery();
			break;

			case 'deleteProduct':
				$this->dbQuery = $this->connection->prepare("UPDATE products SET productIsDeleted=? WHERE productId=? AND productIsDeleted=1");
				$productId = intval($requestData['productId']);
				$deleteStatus = 0;
				$this->dbQuery->bind_param('ii',$deleteStatus,$productId);
				$returnStatus = $this->exeQuery();
			break;

			case 'productList':
				$this->dbQuery = $this->connection->prepare("SELECT p.* FROM products p LEFT JOIN categories c ON(c.categoryId=p.categoryId) WHERE p.productIsDeleted=1 AND c.categoryIsDeleted=1");
				$returnStatus = $this->exeQuery(1);
				while($row = $returnStatus->fetch_array(MYSQLI_NUM)){
					$product = array(
						'id'=>$row[0],
						'categoryId'=>$row[1],
						'name'=>$row[2],
						'description'=>$row[3],
						'price'=>$row[4],
						'discount'=>$row[5]
					);
					$productList[] = $product;
				}
				$returnStatus = $productList;
			break;

			case 'addToCart':
				if($this->checkCart($requestData['deviceId'],$requestData['categoryId'],$requestData['productId'],$requestData['quantity'])){
					$returnStatus = true;
				}else{
					$returnStatus = false;
				}
			break;

			case 'deleteCart':
				$this->dbQuery = $this->connection->prepare("UPDATE cart SET cartIsDeleted=0 WHERE deviceId=? AND productId=? AND categoryId=?");
				$categoryId = intval($requestData['categoryId']);
				$productId = intval($requestData['productId']);
				$deviceId = mysql_real_escape_string($requestData['deviceId']);
				$this->dbQuery->bind_param('sii',$deviceId,$productId,$categoryId);
				$returnStatus = $this->exeQuery();
			break;

			case 'showCart':
				$deviceId = intval($requestData['deviceId']);
				if($deviceId>0 && $deviceId!=''){
					$returnStatus = $this->getCartData($deviceId);
					

				}else{
					$returnStatus = false;
				}
			break;

			case 'cartTotal':
				$deviceId = intval($requestData['deviceId']);
				if($deviceId>0 && $deviceId!=''){
					$returnStatus = $this->getCartData($deviceId);
					$cartSize = sizeof($returnStatus);
					$returnStatus = $returnStatus[$cartSize-1];
					$returnStatus = $returnStatus['grandTotal'];
				}else{
					$returnStatus = false;
				}
			break;

			case 'totalDiscount':
				$deviceId = intval($requestData['deviceId']);
				if($deviceId>0 && $deviceId!=''){
					$returnStatus = $this->getCartData($deviceId);
					$cartSize = sizeof($returnStatus);
					$returnStatus = $returnStatus[$cartSize-1];
					$returnStatus = $returnStatus['cartTotalDiscount'];
				}else{
					$returnStatus = false;
				}
			break;

			case 'totalTax':
				$deviceId = intval($requestData['deviceId']);
				if($deviceId>0 && $deviceId!=''){
					$returnStatus = $this->getCartData($deviceId);
					$cartSize = sizeof($returnStatus);
					$returnStatus = $returnStatus[$cartSize-1];
					$returnStatus = $returnStatus['cartTotalTax'];
				}else{
					$returnStatus = false;
				}
			break;

		}
		return $returnStatus;
	}

	private function getCartData($deviceId=0){
		$grandTotal = 0;
		$cartTotalDiscount = 0;
		$cartTotalTax = 0;
		$sumDiscount=0;
		$sumTax = 0;
		$this->dbQuery = $this->connection->prepare("SELECT c.productId,c.categoryId,p.productName,sum(c.productQuantity) as quantity,c.productPrice as price,c.productDiscount,c.categoryTax,sum(c.productPrice * c.productQuantity) as total,sum((c.productPrice * c.productQuantity * c.productDiscount)/100) as totalDiscount, (sum(c.productPrice * c.categoryTax)/100) as totalTax FROM cart c LEFT JOIN products p ON(p.productId = c.productId) WHERE c.deviceId = ? AND c.cartIsDeleted=1 GROUP BY c.productId");
		$deviceId = intval($deviceId);
		$this->dbQuery->bind_param('i',$deviceId);
		$returnStatus = $this->exeQuery(1);
		if($returnStatus->num_rows > 0){
			while($row = $returnStatus->fetch_array(MYSQLI_NUM)){
				
				$totalWithDiscount = floatval($row[7]) - floatval($row[8]);
				$totalWithTax = floatval($row[7])+floatval($row[9]);
				$subTotal = floatval($totalWithDiscount)+floatval($row[9]); 

				$sumDiscount +=floatval($row[8]);
				$sumTax +=floatval($row[9]);

				$cart = array(
					'productId'=>$row[0],
					'categoryId'=>$row[1],
					'productname'=>ucfirst($row[2]),
					'quantity'=>$row[3],
					'price'=>$row[4],
					'discount'=>$row[5],
					'tax'=>$row[6],
					'total'=>$row[7],
					'totalDiscount'=>$row[8],
					'totalTax'=>$row[9],
					'totalWithDiscount'=>$totalWithDiscount,
					'totalWithTax'=>$totalWithTax,
					'subTotal'=>$subTotal
				);
				$cartList[] = $cart;
				$grandTotal += floatval($row[7]);
				$cartTotalDiscount +=floatval($totalWithDiscount);
				$cartTotalTax +=floatval($totalWithTax);
			}

			$returnStatus = $cartList;
		}
		$grandTotal = intval($grandTotal)-intval($sumDiscount);
		$grandTotal = intval($grandTotal)+intval($sumTax);
		$grandTotal = array(
			'grandTotal'=>$grandTotal,
			'cartTotalDiscount'=>$sumDiscount,
			'cartTotalTax'=>$sumTax
		);
		array_push($returnStatus,$grandTotal);
		return $returnStatus;
	}

	private function getProductById($productId=0){
		$this->dbQuery = $this->connection->prepare("SELECT p.* FROM products p LEFT JOIN categories c ON(c.categoryId=p.categoryId) WHERE p.productId=? AND p.productIsDeleted=1 AND c.categoryIsDeleted=1");
		$this->dbQuery->bind_param('i',$productId);
		$returnStatus = $this->exeQuery(1);
		if($returnStatus->num_rows > 0){
			$row = $returnStatus->fetch_array(MYSQLI_NUM);
			$result = array(
				'name'=>$row[2],
				'price'=>$row[4],
				'discount'=>$row[5]
			);	
		}else{
			$result = array(
				'name'=>'',
				'price'=>0,
				'discount'=>0
			);
		}
		return $result;
	}

	private function getCategoryById($categoryId=0){
		$this->dbQuery = $this->connection->prepare("SELECT * FROM categories WHERE categoryId=? AND categoryIsDeleted=1");
		$this->dbQuery->bind_param('i',$categoryId);
		$returnStatus = $this->exeQuery(1);
		if($returnStatus->num_rows > 0){
			$row = $returnStatus->fetch_array(MYSQLI_NUM);
			$result = array(
				'tax'=>$row[3]
			);	
		}else{
			$result = array(
				'tax'=>0
			);
		}
		return $result;
	}

	private function checkCart($deviceId=0,$categoryId=0,$productId=0,$quantity=0){
		if(($deviceId!='' || $deviceId > 0) && $categoryId>0 && $productId>0 && $quantity > 0){
			$this->dbQuery = $this->connection->prepare("SELECT * FROM cart WHERE productId=? AND categoryId=? AND deviceId=?");
			$categoryId = intval($categoryId);
			$productId = intval($productId);
			$deviceId = intval($deviceId);
			$quantity = intval($quantity);
			$this->dbQuery->bind_param('iis',$productId,$categoryId,$deviceId);
			$returnStatus = $this->exeQuery(1);
			$categoryData = $this->getCategoryById($categoryId);
			$productData = $this->getProductById($productId);

			if($returnStatus->num_rows > 0){
				
				//update quantity
				$row = $returnStatus->fetch_array(MYSQLI_NUM);
				$cartId = $row[0];
				$quantity = $quantity + intval($row[4]);
				$this->dbQuery = $this->connection->prepare("UPDATE cart SET productQuantity=?, productPrice=?,productDiscount=?,categoryTax=? WHERE cartId=?");
				$this->dbQuery->bind_param('idddi',$quantity,$productData['price'],$productData['discount'],$categoryData['tax'],$cartId);
				$returnStatus = $this->exeQuery();
			}else{
				
				//add to cart
				$this->dbQuery = $this->connection->prepare("INSERT INTO cart (deviceId,productId, categoryId,productQuantity,productPrice,productDiscount,categoryTax) VALUES (?,?,?,?,?,?,?)");
				$this->dbQuery->bind_param('iiiiddd', $deviceId, $productId, $categoryId, $quantity, $productData['price'],$productData['discount'],$categoryData['tax']);
				$returnStatus = $this->exeQuery();
			}
		}else{
			$returnStatus = false;
		}
		return $returnStatus;
	}

	private function exeQuery($k=0){
		$exeQry = false;
		if($this->dbQuery->execute()){
			if($k > 0){
				$exeQry = $this->dbQuery->get_result();	
			}else{
				$exeQry = true;
			}
			
			$this->dbQuery->close();	
		}
		return $exeQry;
	}

	private function getLastInsertedId(){
		return mysqli_insert_id($this->connection);
	}

	//success data bind 
	private function successResponse($dataAry,$successMsg){
		$returnAry = array(
			'resultData'=>$dataAry,
			'responseStatus'=>'Success',
			'message'=>$successMsg
		);
		return json_encode($returnAry);
	}
	//failed data bind
	private function failedResponse($failedMsg){
		$returnAry = array(
			'responseStatus'=>'failed',
			'message'=>$failedMsg
		);
		return json_encode($returnAry);
	}

	//Add Category
	public function addCategory(){
		if($this->isRequest('POST')){
			if($this->actionQuery('addCategory')){
				$returnAry = array(
					'categoryId'=>$this->getLastInsertedId()
				);
				$this->returnResponse = $this->successResponse($returnAry,'Category added successfully');
			}
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}
	
	//Update Category 
	public function updateCategory(){
		if($this->isRequest('PUT')){
			if($this->actionQuery('updateCategory')){
				$this->returnResponse = $this->successResponse('','Category updated successfully');
			}
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}
	
	//Delete Category
	public function deleteCategory(){
		if($this->isRequest('DELETE')){
			if($this->actionQuery('deleteCategory')){
				$this->returnResponse = $this->successResponse('','Category deleted successfully');
			}
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}

	//List Categories
	public function categoryList(){
		if($this->isRequest('GET')){
			$catData = $this->actionQuery('categoryList');
			$this->returnResponse = $this->successResponse($catData,'Category listed successfully');
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}

	//Add Product
	public function addProduct(){
		if($this->isRequest('POST')){
			if($this->actionQuery('addProduct')){
				$returnAry = array(
					'productId'=>$this->getLastInsertedId()
				);
				$this->returnResponse = $this->successResponse($returnAry,'Product added successfully');
			}
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}

	//Update Product
	public function updateProduct(){
		if($this->isRequest('PUT')){
			if($this->actionQuery('updateProduct')){
				$this->returnResponse = $this->successResponse('','Product updated successfully');
			}
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}

	//Delete Product
	public function deleteProduct(){
		if($this->isRequest('DELETE')){
			if($this->actionQuery('deleteProduct')){
				$this->returnResponse = $this->successResponse('','Product deleted successfully');
			}
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}

	//List Products
	public function productList(){
		if($this->isRequest('GET')){
			$productData = $this->actionQuery('productList');
			$this->returnResponse = $this->successResponse($productData,'Product listed successfully');
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}

	//Create Cart
	public function createCart(){
		if($this->isRequest('POST')){
			if($this->actionQuery('addToCart')){
				$this->returnResponse = $this->successResponse('','Added to cart successfully');	
			}else{
				$this->returnResponse = $this->failedResponse('Invalid input params');
			}
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}

	public function deleteCart(){
		if($this->isRequest('DELETE')){
			if($this->actionQuery('deleteCart')){
				$this->returnResponse = $this->successResponse('','Cart item deleted successfully');
			}
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}

	//Update Cart 
	public function updateCart(){
		if($this->isRequest('PUT')){
			if($this->actionQuery('addToCart')){
				$this->returnResponse = $this->successResponse('','Cart updated successfully');	
			}else{
				$this->returnResponse = $this->failedResponse('Invalid input params');
			}
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}

	public function showCart(){
		if($this->isRequest('POST')){
			$cartData = $this->actionQuery('showCart');
			if(is_array($cartData)){
				$this->returnResponse = $this->successResponse($cartData,'Cart listed successfully');
			}else{
				$this->returnResponse = $this->failedResponse('Invalid deviceId');	
			}
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}

	//Get Cart Total
	public function cartTotal(){
		if($this->isRequest('POST')){
			$cartData = $this->actionQuery('cartTotal');
			if($cartData){
				$returnAry = array(
					'grandTotal'=>$cartData,
				);
				$this->returnResponse = $this->successResponse($returnAry,'Cart total');
			}else{
				$this->returnResponse = $this->failedResponse('Invalid deviceId');	
			}
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}

	//Get Cart total discount
	public function cartTotalDiscount(){
		if($this->isRequest('POST')){
			$cartData = $this->actionQuery('totalDiscount');
			if($cartData){
				$returnAry = array(
					'cartTotalDiscount'=>$cartData,
				);
				$this->returnResponse = $this->successResponse($returnAry,'Cart total discount');
			}else{
				$this->returnResponse = $this->failedResponse('Invalid deviceId');	
			}
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}

	//Get Cart total tax
	public function cartTotalTax(){
		if($this->isRequest('POST')){
			$cartData = $this->actionQuery('totalTax');
			if($cartData){
				$returnAry = array(
					'cartTotalTax'=>$cartData,
				);
				$this->returnResponse = $this->successResponse($returnAry,'Cart total tax');
			}else{
				$this->returnResponse = $this->failedResponse('Invalid deviceId');	
			}
		}else{
			$this->returnResponse = $this->failedResponse('Request type not found');
		}
		echo $this->returnResponse;
	}

}

$myWbCom = Webcommerce::getInstance();
$requestMethod = $_GET['request'];
switch($requestMethod){
	case $requestMethod:
		$myWbCom->$requestMethod();
	break;
	default:
		echo 'Invalid request';
	break;
}
