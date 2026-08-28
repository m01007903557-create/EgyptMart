<?php
$message1='<div class="b9_m2 b10_m2" id="detable">
  <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
    <tbody>
      <tr class="f5_m2">
        <td class="sh_m2">
          <span style="width:750px;word-wrap:break-word;" id="wbr">
            <div style="width: 90%;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
              <div style="height: 100px; width: 100%; float: left; ">
                  <div style="height: 100px; width: 30%; float: left;">
                      <img src="http://arabyos.com/images/logo.png" style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="ARABYOS">
                  </div>
                  <div style="height:100px;width:43%;float:left;">
                      <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;">Today\'s Latest<br> Tender Purchase
                      </h2>
                  </div>
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">Notification</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">'.date("Y/m/d").'</span>
                  </div>
              </div>
              <div style="width:100%;color:#000000;">
                  <p style="font-size:14px;color:#000000"><strong>Dear '.$row_usr->name_prefix.$row_usr->fname.' '.$row_usr->lname.',</strong>
              </p></div>
              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                Thank You for Purchasing Tender Information!<br>
  This Tender is saved in your "<a href="//'.$_SERVER['HTTP_HOST'].'/manage-purchased-tenders.php">Purchased Tenders</a>" section.<br>
  You can submit your response from "<a href="//'.$_SERVER['HTTP_HOST'].'/manage-purchased-tenders.php">Purchased Tenders</a>".<br>
  This purchase will reflect in your "<a href="//'.$_SERVER['HTTP_HOST'].'/transaction_history.php">Transaction History</a>" as well.<br><br>
              </div>
			  
              <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;">

              </div>
              <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                  <a href="http://arabyos.com/dir.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Product &amp; Suppliers</a> | <a href="http://arabyos.com/sale-offers.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Sale Offers</a> | <a href="http://arabyos.com/buyleads.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Buy Requests</a> | <a href="http://arabyos.com/tenders.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Tenders</a>
              </div><br/>
              <div style="width:100%;padding-left: 0px;float:left;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;">For any assistance, feel free to call us at +201030029097 or just reply to this mail.<br/><a href="mailto:info@arabyos.com">mailto:info@arabyos.com</a></p>
              </div><br/><br/>
			  <div style="width:100%;padding-left: 0px;text-align:center;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;">Thanks for choosing <b><span style="color: blue">ARABYOS</span></b> as your business platform,<br/>Customers Helpdesk<br/>arabyos.com</p>
              </div>
            </div>
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>';
?>