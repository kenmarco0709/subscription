var client = {
    settings: {
        ajaxUrl: '',
        client_ajax_form_process: ''
    },
    init: function() {
        client.initDataTable();
        client.initForm();
    },

    initForm: function(){
        
        $('.href-modal').unbind('click').bind('click',function(){
            var _this = $(this);
            
            $('.modal').removeClass('modal-fullscreen');

            
            $.ajax({
                url: client.settings.ajaxClientFormUrl,
                type: 'POST',
                data: { id: _this.data('id'), action: _this.data('action')},
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
        
    },
    initDataTable: function() {

        var callBack = function() {
            client.initForm();
        };

        client.dataList = $('#client-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': client.settings.ajaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                }
            },
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

        $('.content-container').removeClass('has-loading');
        $('.content-container-content').removeClass('hide');
    }
};