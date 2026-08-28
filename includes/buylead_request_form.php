<?php
if (!function_exists('renderBuyleadRequestForm')) {
    function renderBuyleadRequestForm($extraClass = '') {
        $class = trim('rfq-request-card ' . $extraClass);
        ?>
        <div class="<?php echo htmlspecialchars($class, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="rfq-request-card-head">
                <h3>طلب عرض أسعار (RFQ)</h3>
                <ul>
                    <li>انشر طلب شراء واستقبل عروض أسعار مناسبة.</li>
                    <li>حدد المنتج والكمية ليصلك الموردون المناسبون.</li>
                    <li>تابع الردود عبر حسابك والبريد والواتساب.</li>
                </ul>
            </div>
            <form action="search.php" method="get" class="rfq-request-card-form">
                <input type="hidden" name="rctyp" value="buy_lead">
                <input type="hidden" name="search_mode" value="auto">
                <h4>احصل على عروض الآن</h4>
                <input type="text" name="keywords" placeholder="اسم المنتج" required>
                <input type="text" name="quantity" placeholder="الكمية">
                <textarea name="details" placeholder="اكتب تفاصيل الطلب"></textarea>
                <button type="submit">بحث تلقائى</button>
            </form>
        </div>
        <?php
    }
}
?>
