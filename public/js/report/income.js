var income = {
    settings : {
    },
    init: function(){
        income.downloadCsv();
    },
    downloadCsv: function(){
      
        $('#reportCsvBtn').unbind('click').bind('click',function(){
            var dateFrom = $('#dateFrom').val() != '' ? $('#dateFrom').val().replaceAll('/', '-') : 'null';
            var dateTo = $('#dateTo').val() != '' ? $('#dateTo').val().replaceAll('/', '-') : 'null';
            var purok = atob('All');

            var _this = $(this);

            _this.prop('href', global.settings.url + 'report/export_income_csv/' + dateFrom + '/' + dateTo + '/' + purok);
        })
     
    },
}