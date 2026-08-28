<?php
$conn = mysqli_connect("localhost","newarabyos","Arabyos123$@","ab2016");

// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  
  
  
$sql = "select measurement_unit.*,country.*,business_profile.*,products.* from products,measurement_unit,country,business_profile,plan_member_id where bnsprof_uid = pd_uid and b_id = bnsprof_id and mu_id=pd_unit and (pd_title LIKE '%onion%') and pd_currency=cn_id and ( (pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='98')) or (pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='98')) or (pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='98')))) and pd_status='1' and pd_image!='' GROUP BY pd_id ORDER BY pd_title asc LIMIT 0,20";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["pd_title"]. "<br>";
    }
} else {
    echo "0 results";
}
$conn->close();
?> 