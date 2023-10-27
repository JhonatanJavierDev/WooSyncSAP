<?php
include '../wp-load.php';
require __DIR__ . '/vendor/autoload.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/woocommerce/woocommerce.php');

ini_set('max_execution_time', 6000); // Increase max execution time

function getWoocommerceProductArray() {
    // Use the WooCommerce API to get product data
    $products_array = array();
    $page = 1;

    while (true) {
        $wc_products = wc_get_products(array(
            'status' => 'publish',
            'limit' => 100,
            'page' => $page,
        ));

        if (empty($wc_products)) {
            break;
        }

        foreach ($wc_products as $product) {
            $sku = $product->get_sku();
            $product_id = $product->get_id();
            $single_product_array = array($sku, $product_id);
            array_push($products_array, $single_product_array);
        }
        $page++;
    }

    return $products_array;
}

function getSpecialPrice($product) {
    // Use the WooCommerce product data to get and update price
    global $base_url, $sessionID;

    $product_id = $product[1];
    $woo_product = wc_get_product($product_id);

    if (!$woo_product) {
        return;
    }

    $url = $base_url . "/b1s/v1/SpecialPrices(CardCode='*1',ItemCode='" . $product[0] . "')?\$select=ItemCode,Price,SpecialPriceDataAreas,ValidFrom,ValidTo";
    $response = makeCurlRequest($url, $sessionID);

    if (!$response) {
        return;
    }

    $response_json = json_decode($response);
    $item_price = $response_json->Price;
    $sale_price_array = $response_json->SpecialPriceDataAreas;

    if ($item_price != 0.0) {
        $woo_product->set_regular_price($item_price);
        $woo_product->set_sale_price(!empty($sale_price_array) ? $sale_price_array[0]->SpecialPrice : null);
        $woo_product->save();
    } else {
        // Call the other webservice to get the price
        $url2 = $base_url . "/b1s/v1/Items('" . $product[0] . "')?\$select=ItemPrices";
        $response2 = makeCurlRequest($url2, $sessionID);

        if (!$response2) {
            return;
        }

        $response_json2 = json_decode($response2);
        $item_prices = $response_json2->ItemPrices;
        $price = $item_prices[0]->Price;

        $woo_product->set_regular_price($price);
        $woo_product->save();
    }

    // Log the update
    $txt = $product[0] . ", updated with price and sale_price, " . $item_price . ", " . $woo_product->get_sale_price();
    $filename = 'price-sync-' . date("Ymd") . '.txt';
    file_put_contents($filename, $txt . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function makeCurlRequest($url, $sessionID) {
    // Create and execute a cURL request
    global $base_url;

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Prefer: odata.maxpagesize=0',
            'Cookie: B1SESSION=' . $sessionID . '; CompanyDB=',
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
        print_r($error_msg);
        return false;
    }

    return $response;
}

// Initialize the base URL and session ID
$base_url = 'https://192.168.1.1:50000';
$sessionID = '';

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => $base_url . '/b1s/v1/Login',
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => '{"CompanyDB": "", "UserName": "", "Password": ""}',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
    ),
));

$response = curl_exec($curl);

if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    print_r($error_msg);
} else {
    $obj = json_decode($response);
    $sessionID = $obj->SessionId;
    $products_array = getWoocommerceProductArray();

    foreach ($products_array as $product) {
        getSpecialPrice($product);
    }

    $complete_txt = "Sync completed";
    $filename = 'price-sync-' . date("Ymd") . '.txt';
    file_put_contents($filename, $complete_txt . PHP_EOL, FILE_APPEND | LOCK_EX);

    $to = 'example@gmail.sr';
    $subject = 'Sync';
    $body = 'Sync price completed with ' . count($products_array) . ' records updated.';
    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail($to, $subject, $body, $headers);
}

// Close the cURL session
curl_close($curl);
