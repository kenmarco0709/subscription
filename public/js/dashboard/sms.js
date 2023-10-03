var sms = {
    settings: {
        ajaxSmsList: '',
    },
    init: function() {
        sms.initDataTable();
    },    
    initDataTable: function() {
        var callBack = function() {
        };

        sms.dataList = $('#sms-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': sms.settings.ajaxSmsList,
                'data': function(d) {
                    d.url = global.settings.url;
                }
            },
            'order': [[0, 'desc']],
            'deferRender': true,
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