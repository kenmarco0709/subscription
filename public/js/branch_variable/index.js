var branch_variable = {
    settings: {
        ajaxUrl: ''
    },
    init: function() {
        branch_variable.initDataTable();
    },
    initDataTable: function() {

        var callBack = function() {
        };

        branch_variable.dataList = $('#branch_variable-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': branch_variable.settings.ajaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 2 },
                { 'searchable': false, 'targets': 2 }
            ],
            drawCallback: function() {
                callBack();
            },
            responsive: {
                details: {
                    renderer: function( api,rowIdx ) {
                        return global.dataTableResponsiveCallBack(api, rowIdx, callBack);
                    }
                }
            }
        });

        $('.content-container').removeClass('has-loading');
        $('.content-container-content').removeClass('hide');
    }
};