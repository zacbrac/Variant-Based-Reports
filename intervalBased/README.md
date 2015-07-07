#Interval Based

Runs a variant based product sales report given a start date, end date, and desired interval(e.g months, days, hours)

Example Setup in admin:

Requires `../db/dbConnect.php`

```HTML
<mvt:assign name="l.void" value="miva_output_header( 'Content-Type', 'application/octet-stream' )" />
<mvt:assign name="l.void" value="miva_output_header( 'Content-Disposition', 'attachment; filename=sales.csv;' )" />
<mvt:assign name="l.void" value="miva_output_header( 'Content-Transfer-Encoding', 'binary' )" />

<mvt:assign name="l.settings:desired_interval" value="'INTERVAL'" />

<mvt:assign name="l.settings:finishdate" value="l.settings:admin_order:orders[1]:orderdate" />
<mvt:do file="g.module_library_utilities" name="l.settings:success" value="QuickSortArray( l.settings:admin_order:orders, ':id', 1 )" />
<mvt:assign name="l.settings:startdate" value="l.settings:admin_order:orders[1]:orderdate" />

<mvt:call action = "'ABSOLUTE_LOCATION_OF_intervals.php'" method = "'POST'" fields = "'
l.settings:startdate,
l.settings:finishdate,
l.settings:desired_interval
'" >
<mvt:eval expr="s.callvalue" />
</mvt:call>
```