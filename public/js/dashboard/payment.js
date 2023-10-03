var payment = {
    settings: {
        ajaxClientAccountPaymentFormUrl: '',
        ajaxClientAccountPaymentPendingList: ''
    },
    init: function() {
        payment.initForm();
        payment.initDataTable();
        payment.printBilling();
        $('#purok').unbind('change').bind('change',function(){
            
            payment.paymentDataList.draw();
        });
    },
    printBilling: function(){
      
        $('#printBilling').unbind('click').bind('click',function(){
          
            var purok = $('#purok').val();
            var _this = $(this);
            _this.prop('href', global.settings.url + 'dashboard/print/billing/' + purok);
        })
     
    },
    initForm: function(){

        $.each($('.href-modal'), function(){
            var _this = $(this);

            $(_this).unbind('click').bind('click',function(){
                    
                $('.modal').removeClass('modal-fullscreen');
                
                $.ajax({
                    url: payment.settings.ajaxClientAccountPaymentFormUrl,
                    type: 'POST',
                    data: { id: _this.data('id'), action: _this.data('action'), clientAccountId: _this.data('accountid')},
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
            payment.initForm();
        };

        payment.paymentDataList = $('#payment-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': payment.settings.ajaxClientAccountPaymentPendingList,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.purok = $('#purok').val();

                }
            },
            'order': [[0, 'desc']],
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 6 },
                { 'searchable': false, 'targets': 6 }
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