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
 
                $check_sql = "SELECT
         s.order_id
         ,es.shipment_id AS shipment_ID
      ,case
           when UPPER(adr.postal_code) in ('%EMPL', 'EMPL%') then 'Employee'
           when UPPER(adr.company) in ('DO NOT SHIP') then 'Employee'
           when s.is_hold = 1 then 'Hold'
           when s.IS_BACKORDER = 1 then 'Backorder'
           when s.tracking_num is NOT null and s.settled is null then 'Not Settled'
           when UPPER(rs.ship_method) in ('OVERGOOD', 'OVERGOODS') then 'Overgoods'
           when UPPER(o.notes) like '%OVERSOLD%' then 'Oversold'
           else 'Missing FF'
        end AS Status
        ,case
           when s.is_drop_ship = 1 then 'Drop Ship'
           else ewt.work_type
        end AS worktype
       
        -- FULFILLMENT_PROCESS_TIMES
        ,to_char(s.authorized, 'MM/DD/YY') AS Authorized
        ,to_char(es.date_exported, 'MM/DD/YY HH:MI AM') AS Export_Time
        ,to_char(sgbs.date_created, 'MM/DD/YY HH:MI AM') AS Pick_Start_Time
        ,to_char(sgss.picking_completed_date, 'MM/DD/YY HH:MI AM') AS Pick_End_Time
        ,to_char(qal.log_date, 'MM/DD/YY HH:MI AM') AS Gift_Box_Time
        ,to_char(s.shipped_date, 'MM/DD/YY HH:MI AM') AS Proship_Time
        ,s.tracking_num AS tracking_number
        ,COUNT(ot.ticket_id) \"# of ZD Tickets\"
        ,MAX(ot.ticket_id) \"Latest ZD Ticket\"
 
      FROM
         shipment s
        ,address adr
        ,return_sku rs
        ,shipment_requeue sr
        ,order_ticket ot
        ,exporting_shipments es
        ,exporting_flags ef
        ,exporting_work_type ewt
        ,orders o
        ,qa_outbound_log qal --Find Gift Boxer
        ,daniel.sg_picker_batch_state sgbs -- Find Pick StartTime
        ,daniel.sg_shipment_state sgss
        ,order_shipment_gift osg -- to make the gift distinction
     
      WHERE
      (s.tracking_num is null OR s.settled is null)
        AND s.imported_date <= trunc(sysdate -1) + 18/24
        AND s.Authorized IS NOT NULL
        AND s.is_cancelled = 0
        AND o.order_id(+) = s.order_id
        AND s.shipment_id = es.shipment_id
        AND ot.order_id(+) = s.order_id
        AND s.address_id = adr.address_id
        AND rs.shipment_id(+) = es.shipment_id
        AND sr.shipment_id(+) = s.shipment_id
        AND es.shipment_id = qal.shipment_id(+)
        AND es.shipment_id = osg.shipment_id(+)
        AND sgss.sg_picker_batch_state_id = sgbs.id(+)
        AND es.shipment_id = sgss.shipment_id(+)
        AND s.shipment_id = ef.shipment_id
        AND ewt.id = ef.work_type_id

     
      GROUP BY s.order_id
        ,es.shipment_id
        ,s.authorized
        ,es.date_exported
        ,sgbs.date_created
        ,sgss.picking_completed_date
        ,qal.log_date
        ,s.shipped_date
        ,s.tracking_num
        ,case
           when UPPER(adr.postal_code) in ('%EMPL', 'EMPL%') then 'Employee'
           when UPPER(adr.company) in ('DO NOT SHIP') then 'Employee'
           when s.is_hold = 1 then 'Hold'
           when s.IS_BACKORDER = 1 then 'Backorder'
           when s.tracking_num is NOT null and s.settled is null then 'Not Settled'
           when UPPER(rs.ship_method) in ('OVERGOOD', 'OVERGOODS') then 'Overgoods'
           when UPPER(o.notes) like '%OVERSOLD%' then 'Oversold'
           else 'Missing FF'
        end
   ,case
           when s.is_drop_ship = 1 then 'Drop Ship'
           else ewt.work_type
        end
    ORDER BY es.date_exported desc";
 
$check = oci_parse($conn, $check_sql);
oci_execute($check);
 
$itcBuildTable = false;
$ffBuildTable = false;
$csBuildTable = false;
$securityBuildTable = false;
$tableCSS = "<style>
 
h1 {
  font-weight: bold;
}
 
table, th, tr, td{white-space:nowrap;border-collapse:collapse;width: 100%;font-family:\"san-serif\", Arial, Helvetica, sans-serif; color: #333;}
 

#security td {border: 1px solid #00B302; padding: 3px 7px 2px 7px;} 
#security th {border: 1px solid #00B302; padding: 3px 7px 2px 7px; color: white; background-color: #00B302;} 
#security tr:nth-child(odd) {background: #F6F6F6;}
#security tr:nth-child(even) {background: #D7FFDE;}
 
#itc td {border: 1px solid #A8A518; padding: 3px 7px 2px 7px;}
#itc th {border: 1px solid #A8A518; padding: 3px 7px 2px 7px; color: white; background-color: #A8A518;}
#itc tr:nth-child(odd) {background: #F6F6F6;}
#itc tr:nth-child(even) {background: #FFFECA;}
 
#cs td {border: 1px solid #94ADF8; padding: 3px 7px 2px 7px;} 
#cs th {border: 1px solid #94ADF8; padding: 3px 7px 2px 7px; color: white; background-color: #94ADF8; }
#cs tr:nth-child(odd) {background: #F6F6F6;}
#cs tr:nth-child(even) {background: #CAD7FF;}
 
#fulfillment td {border: 1px solid #FC7D7D; padding: 3px 7px 2px 7px;}
#fulfillment th {border: 1px solid #FC7D7D; padding: 3px 7px 2px 7px; color: white;  background-color: #FC7D7D;}
#fulfillment tr:nth-child(odd) {background: #F6F6F6;}
#fulfillment tr:nth-child(even) {background: #FFE5E5;}
 
.h_message, ul , h1 , ol { font-family: sans-serif} 
.r_security, .r_itc, .r_cs,.r_fulfillment, {font-weight: bold;}
.wrapper {background: #E4E4E4; border-radius: 4px; margin: auto; padding: 5px;}
h2 {margin: 25px 0px 0px; background: #bbb; padding: 10px 0 5px 10px; color: #333; font-size: 20px; border-top-left-radius: 5px; border-top-right-radius: 5px; }
 
</style>";
$tableInit = "<table border = '1'>";
$tableQuit = "</table>";
$itcCounter = 0;
$ffCounter = 0;
$csCounter = 0;
$securityCounter = 0;
$mail_it = true;
 
//------------------ START OF EMAIL BODY ------------------------------//
 
$message = "<html><head> $tableCSS </head><body>";
 
 
//LOOPING ARRAYS OF ARRAYS
while($checkz = oci_fetch_array($check))
   {
    $order_id = $checkz[0];
    $ship_id = $checkz[1];
    $status = $checkz[2];
    $worktype = $checkz[3];
    $authorized = $checkz[4];
    $exported = $checkz[5];
    $pick_start = $checkz[6];
    $pick_end = $checkz[7];
    $gift_time= $checkz[8];
    $proship_time = $checkz[9];
    $tracking = $checkz[10];
    $zdTotal= $checkz[11];
    $zdRecent = $checkz[12];
 
        //EMPLOYEE
             if($status == 'Employee') {
                  //Create Top part of Table
                 if ($securityBuildTable == false) {
                        //$tableCSS //CSS for Table
                        //$tableInit //Creates table header
                        $securityTable = "<table id='security'>";
                        $securityTable .= "<tr>
                                        <th>Order</th>
                                        <th>Shipment ID</th>
                                        <th>Status</th>
                                        <th>Exported</th>
                                        <th>Pick Started at</th>
                                        <th>Pick Ended at</th>";
                        $securityTable .= "</tr>";
                        $securityBuildTable = true;
                     }
 
                  //Builds Rows of Content
                  $securityTable .="<tr>";
                  $securityTable .= "<td> $order_id </td> <td> $ship_id </td> <td class='r_security'> $status </td> <td>$exported</td> <td>$pick_start</td> <td>$pick_end</td>";
                  $securityTable .="</tr>";
                  $securityCounter++;
            }
 
        //CUSTOMER SERVICE
            if($status == 'Hold') {
 
                 if ($csBuildTable == false) {
                        //$tableCSS //CSS for Table
                        //$tableInit //Creates table header
                        $csTable .= "<table id='cs'>";
                        $csTable .= "<tr>
                                        <th>Order </th>
                                        <th>Shipment ID</th>
                                        <th>Status</th>
                                        <th>Authorized</th>
                                        <th>Exported</th>
                                        <th>Proship Time</th>
                                        <th>Tracking</th>
                                        <th># of ZD Tickets</th>";
                        $csTable .= "</tr>";
                        $csBuildTable = true;
                       
                     }
 
                  //Builds Content
                  $csTable .="<tr>";
                  $csTable .= "<td> $order_id </td><td> $ship_id </td><td class='r_cs'> $status </td><td> $authorized </td><td> $exported </td><td> $proship_time </td><td> $tracking </td><td> $zdTotal </td>";
                  $csTable .="</tr>";
                  $csCounter++;
            }
 
 
           //ITC is for this loop
            if($status == 'Oversold' || $status == 'Not Settled' || $status == 'Overgoods') {
 
                 if ($itcBuildTable == false) {
                        //$tableCSS //CSS for Table
                        //$tableInit //Creates table header
                        $itcTable = "<table id='itc'>";
                        $itcTable .= "<tr>
                                        <th>Order </th>
                                        <th>Shipment ID</th>
                                        <th>Status</th>
                                        <th>Authorized</th>
                                        <th>Exported</th>
                                        <th>Tracking</th>
                                        <th>of ZD Tickets</th>
                                        <th>Latest ZD Tickets</th>";
                        $itcTable .= "</tr>";
                        $itcBuildTable = true;
                     }
 
                  //Builds Content
                  $itcTable .="<tr>";
                  $itcTable .="<td> $order_id </td><td> $ship_id </td><td class = 'r_itc'> $status</td><td> $authorized </td><td> $exported </td><td> $tracking </td><td> $zdTotal </td><td><a target=\"_blank\" href=\"https://ugoods.zendesk.com/agent/#/tickets/$zdRecent\">$zdRecent</a></td>";
                  $itcTable .="</tr>";
                  $itcCounter++;
 
            }
 
                    //FULFILLMENT
            if($status == 'Missing FF') {
 
                 if ($ffBuildTable == false) {
                        //$tableCSS //CSS for Table
                        //$tableInit //Creates table header
                        $ffTable = "<table id='fulfillment'>";
                        $ffTable .= "<tr>
                                        <th>Shipment ID</th>
                                        <th>Status</th>
                                        <th>Exported</th>
                                        <th>Worktype</th>
                                        <th>Pick Start</th>
                                        <th>Pick End</th>
                                        <th>Gift Time</th>
                                        <th>Proship Time</th>
                                        <th>Tracking</th>
                                        <th>Recent ZD Ticket</th>";
                        $ffTable .= "</tr>";
                        $ffBuildTable = true;
 
                     }
 
                  //Builds Content
                  $ffTable .="<tr>";
                  $ffTable .= "<td> $ship_id </td><td class = 'r_fulfillment'> $status</td><td> $exported </td><td> $worktype </td><td> $pick_start </td><td> $pick_end </td><td> $gift_time </td><td> $proship_time </td><td> $tracking </td><td> <a target=\"_blank\" href=\"https://ugoods.zendesk.com/agent/#/tickets/$zdRecent\">$zdRecent</a></td>";
                  $ffTable .="</tr>";
                  $ffCounter++;
            }
 
} //end while loop
 

$ooSum = $securityCounter + $csCounter + $ffCounter + $itcCounter;

$todaysDate = date('m/d');
$oldDate = date('m/d', strtotime('-20 days', strtotime('$todaysDate')));

$message .= "<h1>Outstanding Orders</h1>  <br />";
$message .= " <p class=\"h_message\">Good Morning Ops Team,<br>The following is a breakdown of all $ooSum Outstanding orders from $oldDate to $todaysDate :</p>";
$message .= "<ul>
            <li><u>Security Team:</u> You have <b>$securityCounter</b> orders that needs to be settled.</li>
            <li><u>CS Department:</u> You have <b>$csCounter</b> orders that are on Hold or Corporate.</li>  
            <li><u>ITC:</u> We have <b>$itcCounter</b> orders that are on oversold or need settling. </li>
            <li><u>Exporting/FF:</u> We have <b>$ffCounter</b> missing fulfillment flow </li></ul>";
 $message .= "<div class=\"wrapper\">";
 
if ($securityBuildTable == false && $csBuildTable == false && $ffBuildTable == false&& $itcBuildTable == false){
                $mail_it = false;
}
if ($securityBuildTable == true){
                $securityTable .= $tableQuit;
                $message .= "<h2>Security Department ( $securityCounter )</h2>";
                $message .= $securityTable;
}
 
if ($csBuildTable == true){
                $csTable .= $tableQuit;
                $message .= "<h2>Customer Service ( $csCounter )</h2>";
                $message .= $csTable;
}
 
if ($itcBuildTable == true){
                $itcTable .= $tableQuit;
                $message .= "<h2>ITC Department ( $itcCounter )</h2>";
                $message .= $itcTable;
}
 
if ($ffBuildTable == true){
                $ffTable .= $tableQuit;
                $message .= "<h2>Exporting & Fulfillment ( $ffCounter )</h2>";
                $message .= $ffTable;
}
 
//-------------------------- END OF EMAIL BODY ------------------------------//

$message .= "</div>Best Regards,<br />";
$message .= "Exporting-Bot";
$message .= "</body></html>";
 
if($mail_it==true){
 
                                                $to = "mcadet@uncommongoods.com";
                                                $from = "donotreply@exportingalerts.info";
 
                                                $subject = "Outstanding Orders ($oldDate - $todaysDate)";
 
                                                $headers = "From:" . $from . "\r\n";
                                                $headers .= 'MIME-Version: 1.0' . "\r\n";
                                                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
 
 
 
                                                echo $message;
                                                mail($to,$subject,$message,$headers);
}
                                 
 
oci_free_statement($check);
 
?>