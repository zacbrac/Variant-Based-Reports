# Variant-Based-Shipping-Picklist
Gets all variants to be shipped from a batch, along with some custom field values.

Requires `db/db_connect.php` file.

Example Setup:

```HTML
<mvt:assign name="l.settings:finishdate" value="l.settings:admin_order:orders[1]:orderdate" />
<mvt:do file="g.module_library_utilities" name="l.settings:success" value="QuickSortArray( l.settings:admin_order:orders, ':id', 1 )" />
<mvt:assign name="l.settings:startdate" value="l.settings:admin_order:orders[1]:orderdate" />

<mvt:call action="'http://ABSOLUTE_LOCATION_OF_salesReport.php'" method="'POST'" fields="'
l.settings:startdate,
l.settings:finishdate
'" >
    <mvt:eval expr="s.callvalue" />
</mvt:call>
```
