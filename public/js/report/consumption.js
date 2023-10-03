var consumption = {
    settings : {
        reportConsumptionAjaxUrl: ''
    },
    init: function(){
        consumption.initDataTable();
        consumption.initSearchForm();
        consumption.downloadCsv();
    },

    initSearchForm: function(){
        $('#searchBtn').unbind('click').bind('click',function(){
           consumption.dataList.draw();
        });
    },
    downloadCsv: function(){
      
        $('#reportCsvBtn').unbind('click').bind('click',function(){
            var date = $('#date').val().replaceAll('/', '-');
            var purok = atob($('#purok').val());

            var _this = $(this);

            _this.prop('href', global.settings.url + 'report/export_consumption_csv/' + date + '/' + purok);
        })
     
    },
    initDataTable: function() {
        var callBack = function() {
        };

        consumption.dataList = $('#consumption-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'searching' : false,
            'ajax': {
                'url': consumption.settings.reportConsumptionAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.date = $('#date').val();
                    d.purok = $('#purok option:selected').val();
                }
            },
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
}