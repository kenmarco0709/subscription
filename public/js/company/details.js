var company_details = {
    settings: {
        userAjaxUrl: '',
        branchAjaxUrl: '',
        smsAjaxUrl: '',
        id:''
    },
    init: function() {
        company_details.initUserDataTable();
        company_details.initBranchDataTable();
        company_details.initSmsDataTable();
    },
    initUserDataTable: function() {

        var callBack = function() {
        };

        company_details.dataList = $('#user-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': company_details.settings.userAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.companyId = company_details.settings.id; 
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
    },

    initBranchDataTable: function() {

        var callBack = function() {
        };
    
        company_details.dataList = $('#branch-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': company_details.settings.branchAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.companyId = company_details.settings.id; 
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
    },

    initSmsDataTable: function() {

        var callBack = function() {
        };
    
        company_details.dataList = $('#sms-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': company_details.settings.smsAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.companyId = company_details.settings.id; 
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 1 },
                { 'searchable': false, 'targets': 1 }
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