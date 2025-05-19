<?php
// vnpay_config.php – cấu hình VNPAY TEST

return [
    // Mã Terminal do VNPAY cấp
    'vnp_TmnCode'    => 'K9C1N1E4',
    // Secret key để tạo checksum
    'vnp_HashSecret' => '5HS4Z0ENQD5TLLLOLX72L7DISH185BIS',
    // URL thanh toán sandbox
    'vnp_Url'        => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
    // URL VNPAY trả về sau khi thanh toán (phải trùng với cấu hình merchant)
    'vnp_ReturnUrl'  => 'http://bxdat.infinityfreeapp.com/vnpay_return.php',
];