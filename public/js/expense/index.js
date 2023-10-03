var expense = {
    settings: {
        ajaxUrl: '',
        ajaxExpenseFormUrl: ''
    },
    init: function() {
        expense.initDataTable();
        expense.initForm();
    },

    initForm: function(){
        
        $('.href-modal').unbind('click').bind('click',function(){
            var _this = $(this);
            
            $('.modal').removeClass('modal-fullscreen');

            
            $.ajax({
                url: expense.settings.ajaxExpenseFormUrl,
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
            expense.initForm();
        };

        expense.dataList = $('#expense-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': expense.settings.ajaxUrl,
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