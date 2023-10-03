var audit_trail = {
    settings: {
        ajaxUrl: ''
    },
    init: function() {
        audit_trail.initDataTable();
    },
    initDataTable: function() {

        var callBack = function() {
        };

        audit_trail.dataList = $('#audit-trail-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': audit_trail.settings.ajaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': null },
                { 'searchable': false, 'targets': null }
            ],
            'order': [[0, 'desc']],
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