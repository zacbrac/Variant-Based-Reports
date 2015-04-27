# Variant-Based-Sales-Report
Runs a variant based product sales report given a start date, end date, and desired interval(e.g months, days, hours)

Outputs in csv format, can be adjusted to just provide array of information

Example Setup:
```HTML
<mvt:do file="g.module_library_utilities" name="l.settings:success" value="QuickSortArray(l.settings:admin_order:orders, ':id',1)" />

<mvt:assign name="l.settings:startdate" value="l.settings:admin_order:orders[1]:orderdate" />
<mvt:assign name="l.settings:desired_interval" value="'months'" />

<mvt:foreach iterator="order" array="admin_order:orders">
	<mvt:assign name="l.settings:finishdate" value="l.settings:order:orderdate" />
</mvt:foreach>

<mvt:call action = "'http://mm9.mivatest.com/php/sales_report/sales_report.php'" method = "'POST'" fields = "'
l.settings:startdate,
l.settings:finishdate,
l.settings:desired_interval
'" >
<mvt:eval expr="s.callvalue" />
</mvt:call>
```
