var billing = {
    settings : {
    },
    init: function(){
        billing.downloadCsv();
    },
    downloadCsv: function(){
      
        $('#reportCsvBtn').unbind('click').bind('click',function(){
            var dateFrom = $('#dateFrom').val() != '' ? $('#dateFrom').val().replaceAll('/', '-') : 'null';
            var dateTo = $('#dateTo').val() != '' ? $('#dateTo').val().replaceAll('/', '-') : 'null';
            var status = $('#status').val();

            var _this = $(this);

            _this.prop('href', global.settings.url + 'report/export_billing_csv/' + dateFrom + '/' + dateTo + '/' + status);
        })
     
    },
}