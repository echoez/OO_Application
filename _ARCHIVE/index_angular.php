<?php

include($_SERVER['DOCUMENT_ROOT'] . "/site_includes/commonFunctionIncludes.php");

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
        ,COUNT(ot.ticket_id) NUMBER_ZD
        ,MAX(ot.ticket_id) LAST_ZD

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

$result = cachedOracle($check_sql, 3000);
$json =  json_encode($result);
?>
<html ng-app="OutstandingApp">
  <head>
    <meta charset="utf-8">
    <title>Outstanding_Orders</title>

<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.3.14/angular.min.js"></script>
<!--    <script>
      var OutstandingApp = angular.module('OutstandingApp', []);
      OutstandingApp.controller('OOCtrl', function ($scope, $http){
        $http.get('oo_report.json').success(function(data) {
          $scope.ooreport = data;
        });
        $scope.sortField = 'ORDER_ID';
      });
    </script>
-->
    <script>
      var OutstandingApp = angular.module('OutstandingApp', []);
      OutstandingApp.controller('OOCtrl', function ($scope){
          $scope.ooreport = <?php echo $json ?>;
                });
    </script>
    <style>
    .worktype {
      width: 100px;
    }
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

table, th, tr, td{white-space:nowrap;border-collapse:collapse;font-family:"san-serif", Arial, Helvetica, sans-serif; color: #333;}
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
    </style>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet">
  </head>
  <body ng-controller="OOCtrl">
      <header>
    <div class="search_container">
      <h1>OO Report</h1>
      <input ng-model="query" type="text" placeholder="Search Order Information" class="form-control" maxlength="10"></input>
      <!-- <input type="button" class="btn btn-default btn-sm" value="search"> -->
      <p id="helpblock"></p>
    
    </div>
  </header>
    <div class="wrapper">
    <div id="b-title"><h1><p class="bg-info">There are <b>{{ooreport.length}}</b> total Outstanding Shipments</p></h1></div></p>
   
    <p style="text-align: center">
    <a href="" ng-click="sortField = 'ORDER_ID' ; reverse = !reverse"><button type="button" class="btn btn-default">Sort by Order Number</button></a>
  <a href="" ng-click="sortField = 'EXPORT_TIME' ; reverse = !reverse"><button type="button" class="btn btn-default">Sort by Export Date</button></a>

    <a href="" ng-click="sortField = 'EXPORT_TIME' ; reverse = !reverse"><button type="button" class="btn btn-default">Sort by Authorize Date</button></a>
</p>
    
    <table class="table table-striped table-hover table-condensed table-bordered">

      <tr>
         <th>
        <input type="checkbox" 
          ng-click="selectAll($event)"
          ng-checked="isSelectedAll()">
      </th>
        <th><a href="" ng-click="sortfield = '' ; reverse = !reverse">#</a></th>
        <th><a href="" ng-click="sortField = 'SHIPMENT_ID' ; reverse = !reverse">Shipment ID</a></th>
        <th><a href="" ng-click="sortField = 'ORDER_ID' ; reverse = !reverse">Order N</a></th>
        <th><a href="" ng-click="sortField = 'EXPORT_TIME' ; reverse = !reverse">Exported</a></th>
        <th><a href="" ng-click="sortField = 'STATUS' ; reverse = !reverse">Status</a></th>
        <th><a href="" ng-click="sortField = 'WORKTYPE' ; reverse = !reverse">Worktype</a></th>
        <th><a href="" ng-click="sortField = 'TRACKING_NUMBER'; reverse = !reverse">Tracking</a></th>
        <th><a href="">Action</a></th>
      </tr>

      <tr ng-repeat="shipment in ooreport | filter:query | orderBy:sortField:reverse" ng-class="getSelectedClass(e)">
          <td>
        <input type="checkbox" name="selected"
          ng-checked="isSelected(e.id)"
          ng-click="updateSelection($event, e.id)">
      </td>















        <td>{{$index + 1}}</td>
        <td>{{shipment.SHIPMENT_ID}}</td>
       <td><a href="">{{shipment.ORDER_ID}}</a></td>
        <td>{{shipment.EXPORT_TIME}}</td>
        <td>{{shipment.STATUS}}</td>
        <td class="worktype">{{shipment.WORKTYPE}}</td>
        <td>{{shipment.TRACKING_NUMBER}}</td>
        <td>  <button class="btn_slim btn-default" ng-click="launch('notify')">View Options</button></td>
      </tr>

    </table>

    </div>
  </body>
</html>
