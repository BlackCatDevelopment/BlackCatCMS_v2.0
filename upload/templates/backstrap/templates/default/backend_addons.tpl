        {include(file='fuelux_repeater.tpl' repeater_id='bs_rep_addons' repeater_title='' repeater_button_group='backend_addons_buttongroup.tpl')}

{literal}
        <script type="text/javascript">
        //<![CDATA[

{/literal}{*
    We just dump $modules_json here to get the JSON string.
    Note:
        NO QUOTES!
        {/literal} ... {literal} is necessary!
*}{literal}
            var data = {/literal}{$modules_json}{literal};
            var rep  = $('div#bs_rep_addons');

            var addonsDataSource = function (options, callback) {
                var items     = data,
                    firstItem = options.pageIndex * (options.pageSize || 0),
                    lastItem  = firstItem + (options.pageSize || 10),
                    pages     = Math.ceil(items.length / 10)
                    ;

                // define the datasource
                var dataSource = {
                    'page' : options.pageIndex,
                    'pages': pages,
                    'count': data.length,
                    'start': firstItem,
                    'end'  : lastItem,
                    'columns': [{
                        label: '',
                        property: 'type',
                        sortable: true
                    },{
                        label: '',
                        property: 'icon'
                    },{
                        label: cattranslate('Name'),
                        property: 'name',
                        sortable: true
                    },{
                        label: cattranslate('Version'),
                        property: 'version'
                    },{
                        label: cattranslate('Installed'),
                        property: 'install_date',
                        sortable: true
                    },{
                        label: cattranslate('Upgraded'),
                        property: 'update_date',
                        sortable: true
                    }],
                    'items': items.slice(firstItem,lastItem)
                };
                // pass the datasource back to the repeater
                callback(dataSource);
            };

            function customColumnRenderer(helpers, callback) {
                // Determine what column is being rendered and review
                var column = helpers.columnAttr;
                // get all the data for the entire row
                var rowData = helpers.rowData;
                var customMarkup = '';
                // Only override the output for specific columns.
                // This will default to output the text value of the row item
                switch(column) {
                    case 'icon':
                        if(rowData.icon && rowData.icon.length) {
                            customMarkup = '<img src="' + rowData.icon + '" alt="Icon" />';
                        }
                        break;
                    case 'type':
                        customMarkup = '<span class="fa fa-fw fa-' + rowData.type + '" title="'  + rowData.type + '"></span>';
                        break;
                    default:
                        // otherwise, just use the existing text value
                        customMarkup = helpers.item.text();
                        break;
                }
                helpers.item.html(customMarkup);
                callback();
            }

            $(function() {
                rep.repeater({
                    list_columnRendered: customColumnRenderer,
                    dataSource: addonsDataSource
                });
                rep.show();
                rep.repeater('resize');
            });
        //]]>
        </script>
{/literal}