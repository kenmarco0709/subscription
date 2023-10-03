var client_details = {
    settings: {
        clientId: null,
        ajaxClientAccountFormUrl: '',
        clientAccountAjaxUrl: ''
    },
    init: function() {
        client_details.initForm();
        client_details.initDataTable();
    },
    initForm: function(){

        $.each($('.href-modal'), function(){
            var _this = $(this);
            
            $(_this).unbind('click').bind('click',function(){
                    
                $('.modal').removeClass('modal-fullscreen');
    
                $.ajax({
                    url: client_details.settings.ajaxClientAccountFormUrl,
                    type: 'POST',
                    data: { id: _this.data('id'), action: _this.data('action'), clientId: client_details.settings.clientId},
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
            client_details.initForm();
        };

        client_details.accountDataList = $('#clientAccount-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': client_details.settings.clientAccountAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.clientId=  client_details.settings.clientId;
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