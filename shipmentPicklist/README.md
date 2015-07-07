#Shipment Picklist

Runs a variant based Shipment Picklist given a start date and end date

Example Setup:

Requires `../db/dbConnect.php`

```HTML
<mvt:assign name="l.settings:finishdate" value="l.settings:admin_order:orders[1]:orderdate" />
<mvt:do file="g.module_library_utilities" name="l.settings:success" value="QuickSortArray( l.settings:admin_order:orders, ':id', 1 )" />
<mvt:assign name="l.settings:startdate" value="l.settings:admin_order:orders[1]:orderdate" />

<mvt:call action="'ABSOLUTE_LOCATION_OF_shipmentPicklist.php'" method="'POST'" fields="'
l.settings:startdate,
l.settings:finishdate
'" >
    <mvt:eval expr="s.callvalue" />
</mvt:call>
```