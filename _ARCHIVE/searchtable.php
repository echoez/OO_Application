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
 
header {
  /*  background-image: -webkit-linear-gradient(#C800FF,
    #55DFFF); */
    background: url(../night-sky.jpg) no-repeat center center fixed; 
    -webkit-background-size: cover;
    -moz-background-size: cover;
    -o-background-size: cover;
    background-size: cover;
  margin-bottom: 50px;
  height: 250px;
  margin-top: -20px;
}

.table-hover>tbody>tr:hover>td, .table-hover>tbody>tr:hover>th {
  background-color: #6C73A1;
  color:#eeeeee;
}
.search_container {
  color: #fff;
  text-align: center; 
  padding-top: 50px;
  width: 20em;
  margin: auto;
}

#newForm {
  margin-top: 200px;

}
.form-control {
  text-align: center;
}

.bg-info {
  background-color: #d9edf7;
  font-size: 22px;
  padding: 5px 10px;
  margin: 0px 10%;
  border: 1px solid #bbb;
  border-radius: 7px;
}

body {
  background: #6C73A1;
}

.wrapper {
  margin: auto;
  margin-top: 10px;
  background: #f4f4f4;
  width: 85%;
  min-width: 500px;
  border: 4px solid #636393;
}
.wrapper {border-radius: 10px; padding: 5px;}

h1 {
  font-weight: bold;
  color: #9D7AAE;
}
#b-title {
  padding: auto;
  margin: auto; 
}

#b-title h1 {
  text-align: center;
  color: #4C6A94;
}

.btn_slim {
  display: inline-block;
  padding: 1px 12px;
  margin-bottom: 0;
  font-size: 14px;
  color: #555;
  font-weight: 400;
  line-height: 1.42857143;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  -ms-touch-action: manipulation;
  touch-action: manipulation;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  background-image: none;
  border: 1px solid #999;
  border-radius: 4px;
}
 
table, th, tr, td{white-space:nowrap;border-collapse:collapse;font-family:\"san-serif\", Arial, Helvetica, sans-serif; color: #333;}
table {width: 80%; margin: auto;} 

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
 
.h_message, ul , h1 { font-family: sans-serif} 
.r_security, .r_itc, .r_cs,.r_fulfillment, {font-weight: bold;}

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
 
$message = "<!DOCTYPE html> <html><head> $tableCSS 
<script type=\"text/javascript\" src=\"//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/js/bootstrap.min.js\"></script>
<script type=\"text/javascript\" src=\"//cdnjs.cloudflare.com/ajax/libs/angular.js/1.3.14/angular.min.js\"></script>
</head><body>";
 
 
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
 
                    //FULFILLMENT
            if($status == 'Missing FF') {
 
                 if ($ffBuildTable == false) {
                        //$tableCSS //CSS for Table
                        //$tableInit //Creates table header
                        $ffTable = "<table class=\"table table-striped table-hover table-condensed table-bordered\">";
                        $ffTable .= "      <tr>
         <th>
        <input type=\"checkbox\" 
          ng-click=\"selectAll($event)\"
          ng-checked=\"isSelectedAll()\">
      </th>
        <th><a href=\"\" ng-click=\"sortfield = '' ; reverse = !reverse\">#</a></th>
        <th><a href=\"\" ng-click=\"sortField = '$ship_id' ; reverse = !reverse\">Shipment ID</a></th>
        <th><a href=\"\" ng-click=\"sortField = '$order_id' ; reverse = !reverse\">Order N</a></th>
        <th><a href=\"\" ng-click=\"sortField = '$exported' ; reverse = !reverse\">Exported</a></th>
        <th><a href=\"\" ng-click=\"sortField = '$status' ; reverse = !reverse\">Status</a></th>
        <th><a href=\"\" ng-click=\"sortField = '$worktype' ; reverse = !reverse\">Worktype</a></th>
        <th><a href=\"\" ng-click=\"sortField = '$tracking'; reverse = !reverse\">Tracking</a></th>
        <th><a href=\"\">Action</a></th>
      </tr>

      <tr ng-repeat=\"shipment in ooreport | filter:query | orderBy:sortField:reverse\" ng-class=\"getSelectedClass(e)\">";
                        $ffTable .= "</tr>";
                        $ffBuildTable = true;
 
                     }
 
                  //Builds Content
                  $ffTable .="<tr>";
                  $ffTable .= "<td> {{shipment.$ship_id}} </td><td class = 'r_fulfillment'> {{shipment.$status}} </td><td> {{shipment.$exported}} </td><td> {{shipment.$worktype}} </td><td> {{shipment.$pick_start}} </td><td> {{shipment.$pick_end}} </td><td> {{shipment.$gift_time}} </td><td> $proship_time}} </td><td> {{shipment.$tracking}} </td><td> <a target=\"_blank\" href=\"https://ugoods.zendesk.com/agent/#/tickets/{{shipment.$zdRecent}}\">{{shipment.$zdRecent}}</a></td>";
                  $ffTable .="</tr>";
                  $ffCounter++;
            }
 
} //end while loop
 


 
if ($securityBuildTable == false && $csBuildTable == false && $ffBuildTable == false&& $itcBuildTable == false){
                $mail_it = false;
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
 
                                                $to = "mcadet@uncommongoods.com,paulk@uncommongoods.com";
                                                $from = "donotreply@exportingalerts.info";
 
                                                $subject = "Outstanding Order Alert ($todaysDate - $oldDate)";
 
                                                $headers = "From:" . $from . "\r\n";
                                                $headers .= 'MIME-Version: 1.0' . "\r\n";
                                                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
 
 
 
                                                echo $message;
                                                mail($to,$subject,$message,$headers);
}
                                 
 
oci_free_statement($check);
 
?>