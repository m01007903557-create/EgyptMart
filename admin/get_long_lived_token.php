<?php
// بيانات التطبيق من Meta for Developers
$app_id = '1318490950412935'; // ضع معرف التطبيق هنا
$app_secret = 'f711dc33c20364af0d50d7dc5622dcd8'; // ضع سري التطبيق هنا
$short_lived_token = 'EAASvKRi9KocBRhuSt6OqJEJ28xu3zjnuh4sP79uXG9pmA7wNJFMVZCaLSCzO2WTuY1wgtijKmtsk3ipXC26S0eqTumIj9yVBZBLlzE578IxUOZBu1Il0NN1VXDoSTv67Y00OXKHSFJ41X2LZBfsRuUjdtmciUxmGFge9nwhrY05bZCDBu28dkYcUk4OvCAmNXiXIOtWPSEkkORZA1bCdmK4e4HtRd6AYt1piOQKnfJ3D1YcaNMVNaVmsUYBXa6ZBkGNUTiCu7NHWqqSQJviWGZCx9oOc';

// طلب Token طويل الأمد (60 يوم)
$url = "https://graph.facebook.com/v20.0/oauth/access_token?grant_type=fb_exchange_token&client_id={$app_id}&client_secret={$app_secret}&fb_exchange_token={$short_lived_token}";

$response = file_get_contents($url);
$data = json_decode($response, true);

echo "<pre>";
print_r($data);
echo "</pre>";

if (isset($data['access_token'])) {
    echo "<h3 style='color:green'>✅ Token طويل الأمد:</h3>";
    echo "<textarea rows='3' cols='100'>" . $data['access_token'] . "</textarea>";
    echo "<p><strong>ينتهي بعد:</strong> " . $data['expires_in'] . " ثانية (" . round($data['expires_in']/86400) . " يوم)</p>";
}
?>