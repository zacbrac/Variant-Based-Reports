# Variant-Based-Sales-Report
Runs a variant based product sales report given a start date, end date, and desired interval(e.g months, days, hours)

Outputs in CSV format, can be adjusted to just provide array of information

Example Setup:

Requires `db/db_connect.php` file if in a different directory alter the require on line 3 of  `sales_report.php`
```HTML
<mvt:assign name="l.void" value="miva_output_header( 'Content-Type', 'application/octet-stream' )" />
<mvt:assign name="l.void" value="miva_output_header( 'Content-Disposition', 'attachment; filename=sales.csv;' )" />
<mvt:assign name="l.void" value="miva_output_header( 'Content-Transfer-Encoding', 'binary' )" />

<mvt:assign name="l.settings:finishdate" value="l.settings:admin_order:orders[1]:orderdate" />
<mvt:do file="g.module_library_utilities" name="l.settings:success" value="QuickSortArray( l.settings:admin_order:orders, ':id', 1 )" />
<mvt:assign name="l.settings:startdate" value="l.settings:admin_order:orders[1]:orderdate" />

<mvt:call action="'http://zbrachmanis.mivamerchantdev.com/php/sales_report/salesReport.php'" method="'POST'" fields="'
l.settings:startdate,
l.settings:finishdate
'" >
    <mvt:eval expr="s.callvalue" />
</mvt:call>
```
Then just set up your http headers on the miva page for a CSV attachment and be done!
