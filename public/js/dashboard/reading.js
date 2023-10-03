var reading = {
    settings: {
        ajaxClientMeterReadingFormUrl: '',
        ajaxClientMeterForReadingList: ''
    },
    init: function() {
        reading.initForm();
        reading.initDataTable();
        reading.printMasterList();

        $('#purok').unbind('change').bind('change',function(){
            
            reading.meterDataList.draw();
        });
    },
    printMasterList: function(){
      
        $('#printMasterList').unbind('click').bind('click',function(){
          
            var purok = $('#purok').val();
            var _this = $(this);
            _this.prop('href', global.settings.url + 'client_meter/print/master_list/' + purok);
        })
     
    },
    initForm: function(){

        $.each($('.href-modal'), function(){
            var _this = $(this);

            $(_this).unbind('click').bind('click',function(){
                    
                $('.modal').removeClass('modal-fullscreen');
                
                $.ajax({
                    url: reading.settings.ajaxClientMeterReadingFormUrl,
                    type: 'POST',
                    data: { id: _this.data('id'), action: _this.data('action'), clientMeterId: _this.data('meterid')},
                    beforeSend: function(){
                    $(".modal-content").html('');
                        
                    },
                    success: function(r){
                        if(r.success){
                    
                            $(".modal-content").html(r.html);
                            $('#modal').modal('show');
                        }
                    }
                });
            });
        })
    },
    initDataTable: function() {
        var callBack = function() {
            reading.initForm();
        };

        reading.meterDataList = $('#meterReading-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': reading.settings.ajaxClientMeterForReadingList,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.purok = $('#purok').val();
                }
            },
            'order': [[0, 'desc']],
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 5 },
                { 'searchable': false, 'targets': 5 }
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