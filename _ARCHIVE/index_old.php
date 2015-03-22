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
           when s.tracking_num is NOT null and s.settled is null then 'Not_Settled'
           when UPPER(rs.ship_method) in ('OVERGOOD', 'OVERGOODS') then 'Overgoods'
           when UPPER(o.notes) like '%OVERSOLD%' then 'Oversold'
           else 'Missing_FF'
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
           when s.tracking_num is NOT null and s.settled is null then 'Not_Settled'
           when UPPER(rs.ship_method) in ('OVERGOOD', 'OVERGOODS') then 'Overgoods'
           when UPPER(o.notes) like '%OVERSOLD%' then 'Oversold'
           else 'Missing_FF'
        end
   ,case
           when s.is_drop_ship = 1 then 'Drop Ship'
           else ewt.work_type
        end
    ORDER BY es.date_exported desc";

$result = cachedOracle($check_sql, 50000);
$json =  json_encode($result);
?>


<html ng-app="OutstandingApp">
   <head>
      <meta charset="utf-8">
      <title>Outstanding_Orders</title>
        <script src="angular.min.js"></script>
      <script>
         var OutstandingApp = angular.module('OutstandingApp', []);
         OutstandingApp.controller('OOCtrl', function ($scope){
             $scope.ooreport = <?php echo $json ?>;
            var show = null;
             $scope.on = function(){
              show = true;
                }

             $scope.off = function(){
               show = false;
             } 



           $scope.showButton = function(){
             return show;
              }
            $scope.showButton1 = function(){
             return show;
              }

           });

      </script>
      <link href="dropdowns-enhancement.css" rel="stylesheet">
      <link href="css/bootstrap.min.css" rel="stylesheet">
      <link href="css/style.css" rel="stylesheet">
   </head>
   <body ng-controller="OOCtrl">
      <header id="_top">
         <div class="search_container">
          <div class="inputwrap">
            <input ng-model="query" type="text" ng-change="filter()" placeholder="Search Order Information" class="form-control" maxlength="20"></input><button type="button" class="btn btn-info insidebutton" ng-show="query.length" ng-click="query = ''">Clear</button></div>
            <!-- <input type="button" class="btn btn-default btn-sm" value="search"> -->
            <p id="helpblock">
              <div class="btn-group btn-group-md" role="group" aria-label="...">
<button type="button" class="btn btn-default" ng-click="on()" ng-hide="showButton1()">View OO Report</button>
  <button type="button" class="btn btn-primary" ng-click="off()" ng-show="showButton1()">View OO Report</button>
  <button type="button" class="btn btn-primary" ng-click="on()" ng-hide="showButton()">View Action Log</button>
  <button type="button" class="btn btn-default" ng-click="off()" ng-show="showButton()">View Action Log</button>

</div>
           </p>

      
         </div>

      </header>

         <div class="wrapper" ng-show="showButton()">
         <div class="row">
                        <div class="col-xs-3">
                            <div class="small-stat clearfix">
                            <span ng-click="query = 'Oversold'" class="small-stat-icon orange"><i class="glyphicon glyphicon glyphicon-th"></i></span>
                                <div class="small-stat-info">
                                    <span>320</span>
                                    Oversolds
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <div class="small-stat clearfix">
                                <span ng-click="query = 'Hold'" class="small-stat-icon tar"><i class="glyphicon glyphicon glyphicon-th"></i></span>
                                <div class="small-stat-info">
                                    <span>0</span>
                                    Holds
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <div class="small-stat clearfix">
                                <span ng-click="query = 'Not_Settled'" class="small-stat-icon pink"><i class="glyphicon glyphicon glyphicon-th"></i></span>
                                <div class="small-stat-info">
                                    <span>30</span>
                                    Not Settled
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <div class="small-stat clearfix">
                                <span ng-click="query = 'Missing_FF'" class="small-stat-icon green"><i class="glyphicon glyphicon glyphicon-th"></i></span>
                                <div class="small-stat-info">
                                    <span>327</span>
                                    Missing FF
                                </div>
                            </div>
                        </div>
                    </div>
                  </div>
      <div class="wrapper" ng-show="showButton()">
      
<button type="button" class="btn btn-primary btn-lg btn-block"  ng-show="query.length>='5'" ng-click="query = ''">Filtering Results by <b>"<u>{{query}}</u>"</b>. Want to Clear?</button>
<button type="button" class="btn btn-primary btn-lg btn-block"  ng-show="query == 'shit'" style="background: red;" ng-click="query = ''">DO NOT CURSE PLEASE <b>"<u>{{query}}</u>"</b>. Want to Clear?</button>
      
  <div class="panel panel-default">
  <!-- Default panel contents -->
  <div id="reportoo" class="panel-heading"><b>Outstanding Order Report</b> (2/28/2015) - (3/11/2015)</div>
  <div class="panel-body">
  <p>

     I am leaving early to make sure any details are taken care of before your arrival! Three people are driving, David A., Jackie, and Shah. Both David A. and Jackie can hold 2-3 people in their car. Also, I’ve ordered two cabs to meet everyone by the turnstiles at 4pm:
         </p>
         <hr>
         <p style="text-align: center">
            <a href="" ng-click="sortField = 'ORDER_ID' ; reverse = !reverse"><button type="button" class="btn btn-default">Sort by Order Number</button></a>
            <a href="" ng-click="sortField = 'EXPORT_TIME' ; reverse = !reverse"><button type="button" class="btn btn-default">Sort by Export Date</button></a>
            <a href="" ng-click="sortField = 'EXPORT_TIME' ; reverse = !reverse"><button type="button" class="btn btn-default">Sort by Authorize Date</button></a>
         </p>
  </div>
         <table class="table table-striped table-hover table-condensed table-bordered">
            <tr>
               <th>
                  <input type="checkbox" 
                     ng-click="selectAll($event)"
                     ng-checked="isSelectedAll()">
               </th>
               <th><a href="" ng-click="sortfield = 'SHIPMENT_ID' ; reverse = !reverse">#</a></th>
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
               <td><a href="http://admin4:8080/admin/tasks/OrderDetail.jsp?Id={{shipment.ORDER_ID}}">{{shipment.ORDER_ID}}</a></td>
               <td>{{shipment.EXPORT_TIME  | date}}</td>
               <td><span class="label label-{{shipment.STATUS}}">{{shipment.STATUS}}</span></td>
               <td>{{shipment.WORKTYPE}}</td>
               <td>{{shipment.TRACKING_NUMBER}}</td>
               <td>


<div class="btn-group">
            <button class="btn btn-default" data-label-placement=""><i style="color: #8CB28C;"class="glyphicon glyphicon-ok"></i></button>
            <button data-toggle="dropdown" class="btn btn-default dropdown-toggle"><span class="caret"></span> </button>
            <ul class="dropdown-menu">
               <li>
                <input type="radio" id="ex7_2" name="ex7" value="1">
                <label for="ex7_2"><i class="glyphicon glyphicon-trash data-label"></i> Uograde</label>
              </li>
              <li>
                <input type="radio" id="ex7_2" name="ex7" value="2">
                <label for="ex7_2"><i class="glyphicon glyphicon-trash data-label"></i> Requeue</label>
              </li>
              <li>
                <input type="radio" id="ex7_3" name="ex7" value="3">
                <label for="ex7_3"><i class="glyphicon glyphicon-print data-label"></i> Resolve</label>
              </li>
            </ul>
          </div>
        
        
               </td>
            </tr>
         </table>

<br></div>
</div>

 <div class="wrapper" ng-hide="showButton()">
 <div class="panel panel-default">
  <div class="panel-heading"><b>Action Taken Log</b> (2/28/2015) - (3/11/2015)</div> 
      <div class="panel-body">
      <p>
         I am leaving early to make sure any details are taken care of 
             </p>
             <hr>
      </div>
         <table id="actionoo" class="table table-striped table-hover table-condensed table-bordered">
             <tr>

               <th><a href="" ng-click="sortfield = 'SHIPMENT_ID' ; reverse = !reverse">Order</a></th>
               <th><a href="" ng-click="sortField = 'SHIPMENT_ID' ; reverse = !reverse">Shipment</a></th>
               <th><a href="" ng-click="sortField = 'ORDER_ID' ; reverse = !reverse">Action Taken</a></th>
               <th><a href="" ng-click="sortField = 'EXPORT_TIME' ; reverse = !reverse">Name</a></th>
               <th><a href="" ng-click="sortField = 'EXPORT_TIME' ; reverse = !reverse">Time</a></th>
               <th><a href="" ng-click="sortField = 'WORKTYPE' ; reverse = !reverse">Options</a></th>
               
            </tr>

            <tr>
            <td>1930022</td>
            <td>3729832</td>
            <td>Fraud</td>
            <td>Hello</td>
            <td>Hello</td>
            <td>
            <div class="btn-group">
            <button class="btn btn-default" data-label-placement=""><i style="color: #BB285B;" class="glyphicon glyphicon-remove"></i></button>
            <button data-toggle="dropdown" class="btn btn-default dropdown-toggle"><span class="caret"></span> </button>
            <ul class="dropdown-menu">
              <li>
                <input type="radio" id="ex7_2" name="ex7" value="2">
                <label for="ex7_2"><i class="glyphicon glyphicon-trash data-label"></i>Requeue</label>
              </li>
              <li>
                <input type="radio" id="ex7_3" name="ex7" value="3">
                <label for="ex7_3"><i class="glyphicon glyphicon-print data-label"></i> Resolve</label>
              </li>
            </ul>
          </div>
            </td>
            </tr>
                        <tr>
            <td>19300464</td>
            <td>8292090</td>
            <td>Requeue Request</td>
            <td>Hello</td>
            <td>Hello</td>
            <td>   
            <div class="btn-group">
            <button class="btn btn-default" data-label-placement=""><i style="color: #BB285B;" class="glyphicon glyphicon-remove"></i></button>
            <button data-toggle="dropdown" class="btn btn-default dropdown-toggle"><span class="caret"></span> </button>
            <ul class="dropdown-menu">
              <li>
                <input type="radio" id="ex7_2" name="ex7" value="2">
                <label for="ex7_2"><i class="glyphicon glyphicon-trash data-label"></i>Requeue</label>
              </li>
              <li>
                <input type="radio" id="ex7_3" name="ex7" value="3">
                <label for="ex7_3"><i class="glyphicon glyphicon-print data-label"></i> Resolve</label>
              </li>
            </ul>
          </div>
          </td>
          
            </tr>
                        <tr>
            <td>19141333</td>
            <td>7892922</td>
            <td>Cancel Request</td>
            <td>Hello</td>
            <td>Hello</td>
            <td>
                 <div class="btn-group">
            <button class="btn btn-default" data-label-placement=""><i style="color: #BB285B;" class="glyphicon glyphicon-remove"></i></button>
            <button data-toggle="dropdown" class="btn btn-default dropdown-toggle"><span class="caret"></span> </button>
            <ul class="dropdown-menu">
              <li>
                <input type="radio" id="ex7_2" name="ex7" value="2">
                <label for="ex7_2"><i class="glyphicon glyphicon-trash data-label"></i>Requeue</label>
              </li>
              <li>
                <input type="radio" id="ex7_3" name="ex7" value="3">
                <label for="ex7_3"><i class="glyphicon glyphicon-print data-label"></i> Resolve</label>
              </li>
            </ul>
          </div>
            </td>
           
            </tr>
         </table>

</div>

         </div>

  </div>
<div id="footer">
      <p id="helpblock" style="margin: auto;">
            <a href="#_top"><button type="button" class="btn btn-primary btn-lg">Take me to the Top</button></a>
           </p>

</div>
        <div id="b-title">
            <h1>
               <p class="bg-info">Displaying {{ooreport.length}} of <b>{{ooreport.length}}</b> Outstanding Shipments</p>
                    <button ng-click="on()" ng-hide="showButton()"><strong>On</strong></button>  
        <button ng-click="off()" ng-show="showButton()"><strong>Off</strong></button>   
            </h1>
         </div>
   <script src="jquery.min.js"></script>
   <script src="bootstrap.min.js"></script>
    <script src="dropdowns-enhancement.js"></script>
  
   </body>
</html>