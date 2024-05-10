<?php
/*
 * Create a simple store that allows user to:
 * - Display list of products, their names, price tag
 * - Add item to a cart (not purchase right away as single item) (select product and enter amount)
 * - Display items in the cart, their price tag and total amount for cart
 * (make sure you count in amount of items)
 * - Purchase cart when items in the cart
 * !!!!!!!
 * Products within the store MUST come from a FILE and not defined as inline objects,
 * that means you should check about reading file and
 * using JSON format (there is a link and video about JSON format in the Materials section)
 * There must be VALIDATION for every possible scenario you can think of.
 * It's NOT required to have customer/payer object that contains cash as assumption is that
 * the customer CAN afford whole cart.
 */

$json = file_get_contents('products.json');
$json_data = json_decode($json, true);

$customer = [];
$items = [];
$productsList = [];
$selectedProducts = [];

function createProduct(string $name, int $price, int $amount): stdClass
{
    $product = new stdClass();
    $product->name = $name;
    $product->price = $price;
    $product->amount = $amount;
    return $product;
}

function displayProducts()
{
    global $productsList;
    echo "Today we offer:\n";
    foreach ($productsList as $key => $product) {
        $price = number_format($product->price / 100, 2);
        $name = strtoupper($product->name);
        echo $key + 1 . ". {$name} for {$price} euro(s).\n";
    }
}

function selectProduct()
{
    global $productsList;
    global $selectedProducts;
    $select = (int)readline("Enter number from the list to select an item: ");
    foreach ($productsList as $key => $product) {
        if ($select == ($key + 1)) {
            $amount = (int)readline("Enter amount of {$product->name}: ");
            if ($amount > $product->amount) {
                echo "We can't offer you {$product->name}.\n";
            } elseif ($amount > 0) {
                if (array_key_exists($product->name, $selectedProducts)) {
                    $selectedProducts[$product->name] += $amount;
                } else {
                    $selectedProducts[$product->name] = $amount;
                }
                $product->amount -= $amount;
            }
            askYesNo();
        }
    }
}

function askYesNo()
{
    $askAgain = strtolower((string)readline("Do you want to continue shopping? Press y/n: "));
    if ($askAgain === 'y' || $askAgain === 'yes') {
        displayProducts();
        selectProduct();
    } elseif ($askAgain === 'n' || $askAgain === 'no') {
        displayCart();
    } else {
        displayCart();
        askYesNo();
    }
}

function displayCart()
{
    global $productsList;
    global $selectedProducts;
    $productTotalCost = 0;
    $totalSum = 0;
    echo "You have in the cart:\n";
    foreach ($selectedProducts as $productName => $selectedAmount) {
        foreach ($productsList as $product) {
            if ($product->name === $productName) {
                $productTotalCost = number_format($selectedAmount * ($product->price / 100), 2);
            }
        }
        $totalSum += $productTotalCost;
        $lineLength = 33;
        $fillLength = $lineLength - strlen($productName) - strlen($productTotalCost);
        echo "$productName " . str_repeat("_", $fillLength) . " $productTotalCost " . "euro(s)\n";
    }
    echo "Total of the cart is: " . str_repeat(" ", 8) . number_format($totalSum, 2) . " euro(s)\n";
}

foreach ($json_data as $items) {
    foreach ($items as $item) {
        $productsList[] = createProduct($item['name'], $item['price'], $item['amount']);
    };
}

echo "Welcome to the Simple Store!\n";
displayProducts();
selectProduct();