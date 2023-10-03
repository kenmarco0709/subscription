var client_account_details = {
    settings: {
        clientAccountId: null,
        ajaxClientAccountBillingUrl: '',
        ajaxClientAccountBillingFormUrl: '',
        ajaxClientAccountPaymentUrl: '',
        ajaxClientAccountPaymentFormUrl: '',
        ajaxClientAccountDetailUrl: ''
    },
    init: function() {
        client_account_details.initForm();
        client_account_details.initDataTable();
        client_account_details.initDetails();
    },
    initDetails: function(){
        $.ajax({
            url: client_account_details.settings.ajaxClientAccountDetailUrl,
            type: 'GET',
            data: { clientAccountId: client_account_details.settings.clientAccountId},
            success: function(r){
                if(r.success){
            
                    $("#accountDetail").html(r.html);
                }
            }
        });
    },
    initForm: function(){

        $.each($('.href-modal'), function(){
            var _this = $(this);
            var url = _this.data('type') == 'billing' ? client_account_details.settings.ajaxClientAccountBillingFormUrl : client_account_details.settings.ajaxClientAccountPaymentFormUrl;
            
            $(_this).unbind('click').bind('click',function(){
                    
                $('.modal').removeClass('modal-fullscreen');
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { id: _this.data('id'), action: _this.data('action'), clientAccountId: client_account_details.settings.clientAccountId},
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
            client_account_details.initForm();
        };

        client_account_details.accountDataList = $('#clientAccountBilling-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': client_account_details.settings.ajaxClientAccountBillingUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.clientAccountId=  client_account_details.settings.clientAccountId;
                }
            },
            'order': [[0, 'desc']],
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 4 },
                { 'searchable': false, 'targets': 4 }
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

        client_account_details.paymentDataList = $('#clientAccountPayment-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': client_account_details.settings.ajaxClientAccountPaymentUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.clientAccountId=  client_account_details.settings.clientAccountId;
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