<?php
// products_api.php
header('Content-Type: application/json');

$products = [
    ['id'=>1,  'title'=>'iPhone 15 Pro Max 256GB',                'price'=>28990000, 'image'=>'assets/images/phone1.jpg'],
    ['id'=>2,  'title'=>'Samsung Galaxy S24 Ultra 256GB',         'price'=>25990000, 'image'=>'assets/images/phone2.jpg'],
    ['id'=>3,  'title'=>'Xiaomi 14 Pro 512GB',                    'price'=>19990000, 'image'=>'assets/images/phone3.jpg'],
    ['id'=>4,  'title'=>'OPPO Find X7 Ultra 256GB',               'price'=>21990000, 'image'=>'assets/images/phone4.jpg'],
    ['id'=>5,  'title'=>'Vivo V30 Pro 128GB',                     'price'=>12990000, 'image'=>'assets/images/phone5.jpg'],
    ['id'=>6,  'title'=>'Realme GT 5 Pro 256GB',                  'price'=>14990000, 'image'=>'assets/images/phone6.jpg'],
    ['id'=>7,  'title'=>'iPhone 15 128GB',                        'price'=>20990000, 'image'=>'assets/images/phone7.jpg'],
    ['id'=>8,  'title'=>'Samsung Galaxy Z Flip5 256GB',           'price'=>22990000, 'image'=>'assets/images/phone8.jpg'],
    ['id'=>9,  'title'=>'Google Pixel 8 Pro 128GB',               'price'=>19990000, 'image'=>'assets/images/phone9.jpg'],
    ['id'=>10, 'title'=>'Huawei P60 Pro 512GB',                   'price'=>15990000, 'image'=>'assets/images/phone10.jpg'],
    ['id'=>11, 'title'=>'Xiaomi Redmi Note 13 Pro 128GB',         'price'=>7990000,  'image'=>'assets/images/phone11.jpg'],
    ['id'=>12, 'title'=>'Samsung Galaxy A54 5G 128GB',            'price'=>9990000,  'image'=>'assets/images/phone12.jpg'],
    ['id'=>13, 'title'=>'OPPO Reno10 Pro 256GB',                  'price'=>13990000, 'image'=>'assets/images/phone13.jpg'],
    ['id'=>14, 'title'=>'iPhone 14 256GB',                        'price'=>18990000, 'image'=>'assets/images/phone14.jpg'],
    ['id'=>15, 'title'=>'Vivo Y36 128GB',                         'price'=>5990000,  'image'=>'assets/images/phone15.jpg'],
    ['id'=>16, 'title'=>'Samsung Galaxy S23 FE 128GB',            'price'=>11990000, 'image'=>'assets/images/phone16.jpg'],
    ['id'=>17, 'title'=>'Realme 12 Pro 128GB',                    'price'=>9990000,  'image'=>'assets/images/phone17.jpg'],
    ['id'=>18, 'title'=>'Xiaomi Poco F5 256GB',                   'price'=>10990000, 'image'=>'assets/images/phone18.jpg'],
    ['id'=>19, 'title'=>'iPhone SE (2022) 128GB',                 'price'=>11990000, 'image'=>'assets/images/phone19.jpg'],
    ['id'=>20, 'title'=>'Samsung Galaxy Z Fold5 512GB',           'price'=>35990000, 'image'=>'assets/images/phone20.jpg'],
];

echo json_encode($products, JSON_UNESCAPED_UNICODE);
?>