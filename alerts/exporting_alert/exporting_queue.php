<?php
date_default_timezone_set('America/New_York');
//just testing
//$user = Oracle Connection variable
//$pass = Oracle Connection variable
//$host = MySQL Connection variable
//$conn = MySQL Connection variable
//$con = mysqli_connect(MYSQL Connection credentials);
                $connectionString = "192.168.3.182:1521/UGPRD_ADMIN"; // Host name and Database
                $dbUser = "daniel";// Mysql username
                $dbPassword = "daniel"; // Mysql password
                $conn = oci_connect($dbUser, $dbPassword, $connectionString);
 
                $check_sql = "/* Formatted on 11/21/2013 4:47:16 PM (QP5 v5.149.1003.31008) */
  SELECT ewt.work_type,
         ewt.id work_type_id,
         NVL (allsh.all_shipments, 0) \"All Shipments\"
    FROM (  SELECT work_type_id, COUNT (DISTINCT SHIPMENT_ID) all_shipments
              FROM (SELECT DISTINCT EF.SHIPMENT_ID,
                                    ES.REQUEUE,
                                    EF.WORK_TYPE_ID,
                                    EF.PRIORITY_CODE PRIORITY_CODE_1
                      FROM exporting_flags ef,
                           exporting_shipments es,
                           shipment s,
                           orders o
                     WHERE     EF.SHIPMENT_ID = ES.SHIPMENT_ID
                           AND EF.SHIPMENT_ID = S.SHIPMENT_ID
                           AND S.ORDER_ID = O.ORDER_ID
                           AND S.IS_BACKORDER = 0
                           AND s.is_cancelled <> 1
                           AND (s.is_hold IS NULL OR s.is_hold = 0)
                           AND s.is_temp_hold = 0
                           AND O.IS_CANCELLED <> 1
                           AND (o.is_hold IS NULL OR o.is_hold = 0)
                           AND o.is_temp_hold = 0

                           AND EXISTS
                                  (SELECT *
                                     FROM exporting_shipments es
                                    WHERE     ES.SHIPMENT_ID = EF.SHIPMENT_ID
                                          AND ES.REQUEUE = 1
                                          AND ES.REQUEUE_PROCESSED = 0)
                    UNION ALL
                    SELECT DISTINCT EF.SHIPMENT_ID,
                                    0,
                                    EF.WORK_TYPE_ID,
                                    EF.PRIORITY_CODE PRIORITY_CODE_1
                      FROM exporting_flags ef, shipment s, orders o
                     WHERE     EF.SHIPMENT_ID = S.SHIPMENT_ID
                           AND S.ORDER_ID = O.ORDER_ID
                           AND S.IS_BACKORDER = 0
                           AND s.is_cancelled <> 1
                           AND (s.is_hold IS NULL OR s.is_hold = 0)
                           AND s.is_temp_hold = 0
                           AND O.IS_CANCELLED <> 1
                           AND (o.is_hold IS NULL OR o.is_hold = 0)
                           AND o.is_temp_hold = 0

                           AND NOT EXISTS
                                  (SELECT *
                                     FROM exporting_shipments es
                                    WHERE ES.SHIPMENT_ID = EF.SHIPMENT_ID)
                           AND S.IMPORTED_DATE IS NULL
                           AND S.SHIPPED_DATE IS NULL) a
          GROUP BY a.work_type_id) allsh,
         (  SELECT work_type_id, COUNT (DISTINCT SHIPMENT_ID) fourteenHrs
              FROM (SELECT DISTINCT EF.SHIPMENT_ID,
                                    ES.REQUEUE,
                                    EF.WORK_TYPE_ID,
                                    EF.PRIORITY_CODE PRIORITY_CODE_1
                      FROM exporting_flags ef,
                           exporting_shipments es,
                           shipment s,
                           orders o
                     WHERE     EF.SHIPMENT_ID = ES.SHIPMENT_ID
                           AND EF.SHIPMENT_ID = S.SHIPMENT_ID
                           AND S.ORDER_ID = O.ORDER_ID
                           AND S.IS_BACKORDER = 0
                           AND s.is_cancelled <> 1
                           AND (s.is_hold IS NULL OR s.is_hold = 0)
                           AND s.is_temp_hold = 0
                           AND O.IS_CANCELLED <> 1
                           AND (o.is_hold IS NULL OR o.is_hold = 0)
                           AND o.is_temp_hold = 0

                           AND EXISTS
                                  (SELECT *
                                     FROM exporting_shipments es
                                    WHERE     ES.SHIPMENT_ID = EF.SHIPMENT_ID
                                          AND ES.REQUEUE = 1
                                          AND ES.REQUEUE_PROCESSED = 0)
                           AND EF.PRIORITY_CODE = 4
                    UNION ALL
                    SELECT DISTINCT EF.SHIPMENT_ID,
                                    0,
                                    EF.WORK_TYPE_ID,
                                    EF.PRIORITY_CODE PRIORITY_CODE_1
                      FROM exporting_flags ef, shipment s, orders o
                     WHERE     EF.SHIPMENT_ID = S.SHIPMENT_ID
                           AND S.ORDER_ID = O.ORDER_ID
                           AND S.IS_BACKORDER = 0
                           AND s.is_cancelled <> 1
                           AND (s.is_hold IS NULL OR s.is_hold = 0)
                           AND s.is_temp_hold = 0
                           AND O.IS_CANCELLED <> 1
                           AND (o.is_hold IS NULL OR o.is_hold = 0)
                           AND o.is_temp_hold = 0

                           AND NOT EXISTS
                                  (SELECT *
                                     FROM exporting_shipments es
                                    WHERE ES.SHIPMENT_ID = EF.SHIPMENT_ID)
                           AND S.IMPORTED_DATE IS NULL
                           AND S.SHIPPED_DATE IS NULL
                           AND EF.PRIORITY_CODE = 5) a
          GROUP BY a.work_type_id) fourteenHrs,
           (  SELECT work_type_id, COUNT (DISTINCT SHIPMENT_ID) twentyHrs
              FROM (SELECT DISTINCT EF.SHIPMENT_ID,
                                    ES.REQUEUE,
                                    EF.WORK_TYPE_ID,
                                    EF.PRIORITY_CODE PRIORITY_CODE_1
                      FROM exporting_flags ef,
                           exporting_shipments es,
                           shipment s,
                           orders o
                     WHERE     EF.SHIPMENT_ID = ES.SHIPMENT_ID
                           AND EF.SHIPMENT_ID = S.SHIPMENT_ID
                           AND S.ORDER_ID = O.ORDER_ID
                           AND S.IS_BACKORDER = 0
                           AND s.is_cancelled <> 1
                           AND (s.is_hold IS NULL OR s.is_hold = 0)
                           AND s.is_temp_hold = 0
                           AND O.IS_CANCELLED <> 1
                           AND (o.is_hold IS NULL OR o.is_hold = 0)
                           AND o.is_temp_hold = 0

                           AND EXISTS
                                  (SELECT *
                                     FROM exporting_shipments es
                                    WHERE     ES.SHIPMENT_ID = EF.SHIPMENT_ID
                                          AND ES.REQUEUE = 1
                                          AND ES.REQUEUE_PROCESSED = 0)
                           AND EF.PRIORITY_CODE = 4
                    UNION ALL
                    SELECT DISTINCT EF.SHIPMENT_ID,
                                    0,
                                    EF.WORK_TYPE_ID,
                                    EF.PRIORITY_CODE PRIORITY_CODE_1
                      FROM exporting_flags ef, shipment s, orders o
                     WHERE     EF.SHIPMENT_ID = S.SHIPMENT_ID
                           AND S.ORDER_ID = O.ORDER_ID
                           AND S.IS_BACKORDER = 0
                           AND s.is_cancelled <> 1
                           AND (s.is_hold IS NULL OR s.is_hold = 0)
                           AND s.is_temp_hold = 0
                           AND O.IS_CANCELLED <> 1
                           AND (o.is_hold IS NULL OR o.is_hold = 0)
                           AND o.is_temp_hold = 0

                           AND NOT EXISTS
                                  (SELECT *
                                     FROM exporting_shipments es
                                    WHERE ES.SHIPMENT_ID = EF.SHIPMENT_ID)
                           AND S.IMPORTED_DATE IS NULL
                           AND S.SHIPPED_DATE IS NULL
                           AND EF.PRIORITY_CODE = 4) a
          GROUP BY a.work_type_id) twentyHrs,
         (  SELECT work_type_id, COUNT (DISTINCT SHIPMENT_ID) twentyFourHrs
              FROM (SELECT DISTINCT EF.SHIPMENT_ID,
                                    ES.REQUEUE,
                                    EF.WORK_TYPE_ID,
                                    EF.PRIORITY_CODE PRIORITY_CODE_1
                      FROM exporting_flags ef,
                           exporting_shipments es,
                           shipment s,
                           orders o
                     WHERE     EF.SHIPMENT_ID = ES.SHIPMENT_ID
                           AND EF.SHIPMENT_ID = S.SHIPMENT_ID
                           AND S.ORDER_ID = O.ORDER_ID
                           AND S.IS_BACKORDER = 0
                           AND s.is_cancelled <> 1
                           AND (s.is_hold IS NULL OR s.is_hold = 0)
                           AND s.is_temp_hold = 0
                           AND O.IS_CANCELLED <> 1
                           AND (o.is_hold IS NULL OR o.is_hold = 0)
                           AND o.is_temp_hold = 0

                           AND EXISTS
                                  (SELECT *
                                     FROM exporting_shipments es
                                    WHERE     ES.SHIPMENT_ID = EF.SHIPMENT_ID
                                          AND ES.REQUEUE = 1
                                          AND ES.REQUEUE_PROCESSED = 0)
                           AND EF.PRIORITY_CODE = 3
                    UNION ALL
                    SELECT DISTINCT EF.SHIPMENT_ID,
                                    0,
                                    EF.WORK_TYPE_ID,
                                    EF.PRIORITY_CODE PRIORITY_CODE_1
                      FROM exporting_flags ef, shipment s, orders o
                     WHERE     EF.SHIPMENT_ID = S.SHIPMENT_ID
                           AND S.ORDER_ID = O.ORDER_ID
                           AND S.IS_BACKORDER = 0
                           AND s.is_cancelled <> 1
                           AND (s.is_hold IS NULL OR s.is_hold = 0)
                           AND s.is_temp_hold = 0
                           AND O.IS_CANCELLED <> 1
                           AND (o.is_hold IS NULL OR o.is_hold = 0)
                           AND o.is_temp_hold = 0

                           AND NOT EXISTS
                                  (SELECT *
                                     FROM exporting_shipments es
                                    WHERE ES.SHIPMENT_ID = EF.SHIPMENT_ID)
                           AND S.IMPORTED_DATE IS NULL
                           AND S.SHIPPED_DATE IS NULL
                           AND EF.PRIORITY_CODE = 3) a
          GROUP BY a.work_type_id) twentyFourHrs,
         (  SELECT work_type_id, COUNT (DISTINCT SHIPMENT_ID) bySevenThirty
              FROM (SELECT DISTINCT EF.SHIPMENT_ID,
                                    ES.REQUEUE,
                                    EF.WORK_TYPE_ID,
                                    EF.PRIORITY_CODE PRIORITY_CODE_1
                      FROM exporting_flags ef,
                           exporting_shipments es,
                           shipment s,
                           orders o
                     WHERE     EF.SHIPMENT_ID = ES.SHIPMENT_ID
                           AND EF.SHIPMENT_ID = S.SHIPMENT_ID
                           AND S.ORDER_ID = O.ORDER_ID
                           AND S.IS_BACKORDER = 0
                           AND s.is_cancelled <> 1
                           AND (s.is_hold IS NULL OR s.is_hold = 0)
                           AND s.is_temp_hold = 0
                           AND O.IS_CANCELLED <> 1
                           AND (o.is_hold IS NULL OR o.is_hold = 0)
                           AND o.is_temp_hold = 0

                           AND EXISTS
                                  (SELECT *
                                     FROM exporting_shipments es
                                    WHERE     ES.SHIPMENT_ID = EF.SHIPMENT_ID
                                          AND ES.REQUEUE = 1
                                          AND ES.REQUEUE_PROCESSED = 0)
                           AND EF.PRIORITY_CODE IN (1, 2)
                    UNION ALL
                    SELECT DISTINCT EF.SHIPMENT_ID,
                                    0,
                                    EF.WORK_TYPE_ID,
                                    EF.PRIORITY_CODE PRIORITY_CODE_1
                      FROM exporting_flags ef, shipment s, orders o
                     WHERE     EF.SHIPMENT_ID = S.SHIPMENT_ID
                           AND S.ORDER_ID = O.ORDER_ID
                           AND S.IS_BACKORDER = 0
                           AND s.is_cancelled <> 1
                           AND (s.is_hold IS NULL OR s.is_hold = 0)
                           AND s.is_temp_hold = 0
                           AND O.IS_CANCELLED <> 1
                           AND (o.is_hold IS NULL OR o.is_hold = 0)
                           AND o.is_temp_hold = 0

                           AND NOT EXISTS
                                  (SELECT *
                                     FROM exporting_shipments es
                                    WHERE ES.SHIPMENT_ID = EF.SHIPMENT_ID)
                           AND S.IMPORTED_DATE IS NULL
                           AND S.SHIPPED_DATE IS NULL
                           AND EF.PRIORITY_CODE IN (1, 2)) a
          GROUP BY a.work_type_id) bySevenThirty,
         exporting_work_type ewt
   WHERE     allsh.work_type_id(+) = ewt.id
         AND fourteenHrs.work_type_id(+) = ewt.id
         AND twentyHrs.work_type_id(+) = ewt.id
         AND twentyFourHrs.work_type_id(+) = ewt.id
         AND bySevenThirty.work_type_id(+) = ewt.id
         AND ewt.hidden = 0
ORDER BY print_order

";
 
$check = oci_parse($conn, $check_sql);
oci_execute($check);
 

//$mail_it = true; 
 
//------------------ START OF EMAIL BODY ------------------------------//
 
$message = "<html><head></head><body style=\"\">";
$exportRows;
$totalShipments = 0;
$exportRows = "";

//LOOPING ARRAYS OF ARRAYS
while($checkz = oci_fetch_array($check)) {
   
    $work_type = $checkz[0];
    $work_type_id = $checkz[1];
    $twShipments = $checkz[2];

    if($twShipments != 0) {
    $exportRows .= "<tr><td>$work_type</td> <td style=\"text-align: right; font-weight: bold;\">$twShipments</td></tr>";
      }
    $totalShipments = $totalShipments + $twShipments;


} //end while loop
 
$message .= "<p class=\"h_message\">Salutations Operations Team,";
$message .= "<br><br>We currently have into $totalShipments shipments in queue.</p>";

if($totalShipments == 0) {

} else {
$message .= "<br>Here is a breakdown of each worktype:<br><br>";
$message .= "<table style=\"border: 1px solid black;\">";
$message .= "<tr><th style=\"font-weight: bold;\">WorkType</th> <th style=\"font-weight: bold;\">QTY</th> </tr>";
            
$message .=  "$exportRows";
$message .= "</table><br />";
}
 
 
//-------------------------- END OF EMAIL BODY ------------------------------//

$message .= "<br />Best Regards,<br />";
$message .= "Exporting-Bot";
$message .= "</body></html>";
 
echo $message;


 /*
if($mail_it==true){
 
                                                $to = "mcadet@uncommongoods.com";
                                                $from = "donotreply@exportingalerts.info";
 
                                                $subject = "Exporting Queue ($todaysDate)";
 
                                                $headers = "From:" . $from . "\r\n";
                                                $headers .= 'MIME-Version: 1.0' . "\r\n";
                                                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
 
 
 
                                                echo $message;
                                                mail($to,$subject,$message,$headers);
}
                                 
 */
oci_free_statement($check);
 
?>