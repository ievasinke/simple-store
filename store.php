<?php
/*
 * Create a simple store that allows user to:
 * - Display list of products, their names, price tag
 * - Add item to a cart (not purchase right away as single item) (select product and enter amount)
 * - Display items in the cart, their price tag and total amount for cart
 *   (make sure you count in amount of items)
 * - Purchase cart when items in the cart
 * Products within the store MUST come from a FILE and not defined as inline objects,
 * that means you should check about reading file and
 * using JSON format (there is a link and video about JSON format in the Materials section)
 * It's NOT required to have customer/payer object that contains cash as assumption is that
 * the customer CAN afford whole cart.
 */

$json = file_get_contents('products.json');
$json_data = json_decode($json, true);

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

    if (empty($productsList)) {
        echo "No products are currently available.\n";
        return;
    }
    
    echo "Today we offer:\n";
    foreach ($productsList as $index => $product) {
        $price = number_format($product->price / 100, 2);
        $name = strtoupper($product->name);
        $indexNumber = $index + 1;
        echo "$indexNumber. $name for $price euro(s).\n";
    }
}

function selectProduct()
{
    global $productsList;
    global $selectedProducts;

    $select = (int)readline("Enter the number from the list to select an item: ");
    $productFound = false;

    if ($select > 0 && $select <= count($productsList)) {
        $product = $productsList[$select - 1];
        $amount = (int)readline("Enter amount of {$product->name}: ");
        if ($amount > $product->amount) {
            echo "We are out of {$product->name}.\n";
        } elseif ($amount > 0) {
            if (isset($selectedProducts[$product->name])) {
                $selectedProducts[$product->name] += $amount;
            } else {
                $selectedProducts[$product->name] = $amount;
            }
            $product->amount -= $amount;
            echo "You have added $amount of {$product->name} to your cart.\n";
            $productFound = true;
        } else {
            echo "Invalid amount. Please enter a positive number.\n";
        }
        askYesNo();
    } else {
        echo "Invalid product selection.\n";
    }
    if (!$productFound) {
        askYesNo();
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
        $askCheckOut = strtolower((string)readline("Do you want to checkout? Press y/n: "));
        if ($askCheckOut === 'y' || $askCheckOut === 'yes') {
            checkOut();
        } elseif ($askCheckOut === 'n' || $askCheckOut === 'no') {
            displayProducts();
            selectProduct();
        } else {
            askYesNo();
        }
    } else {
        displayCart();
        askYesNo();
    }
}

function displayCart()
{
    global $productsList;
    global $selectedProducts;

    if (empty($selectedProducts)) {
        echo "Your cart is empty.\n";
        exit;
    }
    $productMap = [];
    foreach ($productsList as $product) {
        $productMap[$product->name] = $product;
    }

    $totalSum = 0;
    echo "You have in the cart:\n";
    foreach ($selectedProducts as $productName => $selectedAmount) {
        if (!isset($productMap[$productName])) {
            echo "Product $productName not found in the product list.\n";
            continue;
        }

        $product = $productMap[$productName];
        $productTotalCost = (float)number_format($selectedAmount * ($product->price / 100), 2);
        $totalSum += $productTotalCost;
        $lineLength = 33;
        $fillLength = $lineLength - strlen($productName) - strlen($productTotalCost);
        $fillLength = max($fillLength, 0);
        echo "$productName "
            . "(Qty: $selectedAmount)"
            . str_repeat("_", $fillLength)
            . " $productTotalCost euro(s)\n";
    }
    echo "Total of the cart is: "
        . str_repeat(" ", 16)
        . number_format($totalSum, 2)
        . " euro(s)\n";
}

function checkOut()
{
    $askEmail = strtolower((string)readline("Enter your email to receive a receipt: "));
    if (filter_var($askEmail, FILTER_VALIDATE_EMAIL)) {
        echo "Receipt sent to $askEmail. Thank you for shopping at the Simple Store!\n";
    } else {
        echo "Purchase denied. Come back Later!\n";
    }
    exit;
}

foreach ($json_data as $items) {
    foreach ($items as $item) {
        $productsList[] = createProduct($item['name'], $item['price'], $item['amount']);
    };
}

echo "Welcome to the Simple Store!\n";
displayProducts();
selectProduct();