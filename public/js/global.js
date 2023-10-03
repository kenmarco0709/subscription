
  var  global = {
        settings: {
            url: ''
        },
        init: function(){
            global.mask();
            global.flash_message();
            global.bindFormSubmitted(); 
            global.checkWindowOpen();
        },
        checkWindowOpen:function(){

            $('body').click(function(){
                console.log('dd');
            });            
        },
        flash_message: function(){

            if($('.error').length){
                setTimeout( () => { 
                    $('.error').css('display', 'none');
                } , 3000);
            }

            if($('.success').length){
                setTimeout( () => { 
                    $('.success').css('display', 'none');
                } , 3000);
            }
        },
        mask: function(){

            if($('.datepicker').length){
                $('.datepicker').mask('99/99/9999').datepicker({singleDatePicker: true,autoUpdateInput: false,locale: {
                    cancelLabel: 'Clear'
                }});
            }

            if($('.amt').length){
                $.each($('.amt'), function(){
                    $(this).maskMoney();
                });
            }
        },
        autocomplete: {
            processes: [],
            bind: function(url, el, hidEl, callBacks) {

                // To set additional parameters call $(element).devbridgeAutocomplete('setOptions', {params: {sampleParam: 'sampleParamValue'}});

                $(el).devbridgeAutocomplete({
                    serviceUrl: url,
                    dataType: 'json',
                    noCache: true,
                    showNoSuggestionNotice: true,
                    minChars: 0,
                    onSearchStart: function() {
                        $(this).removeClass('autocomplete-loading').addClass('autocomplete-loading');
                        if(typeof callBacks === 'object' && typeof callBacks.onSearchStart === 'function') {
                            callBacks.onSearchStart();
                        }
                        $(hidEl).val('');
                        global.autocomplete.processes.push(el);
                        global.autocomplete.checkProcesses();
                    },
                    onSearchComplete: function() {
                        $(this).removeClass('autocomplete-loading');
                        if($(this).val() === '') {
                          
                            if(hidEl !== null) {
                                $(hidEl).val('');
                            }
                            if(!$(this).data('on-focus')) {
                                $(el).devbridgeAutocomplete('hide');
                            }
                        }
                        if(typeof callBacks === 'object' && typeof callBacks.onSearchComplete === 'function') {
                            callBacks.onSearchComplete();
                        }
                        global.autocomplete.removeProcess(el);
                    },
                    onSearchError: function() {
                        if($(this).val() === '' && hidEl !== null) {
                            $(hidEl).val('');
                        }
                        if(typeof callBacks === 'object' && typeof callBacks.onSearchError === 'function') {
                            callBacks.onSearchError();
                        }
                        global.autocomplete.removeProcess(el);
                    },
                    onSelect: function(s) {
                        if(hidEl !== null) {
                            $(hidEl).val(s.id);
                        }
                        if(typeof callBacks === 'object' && typeof callBacks.onSelect === 'function') {
                            callBacks.onSelect(s);
                        }
                        global.autocomplete.removeProcess(el);
                    }
                });

                $(el).unbind('keyup.checkEmpty').bind('keyup.checkEmpty', function(){
                    if($(this).val() === '') {
                        $(this).removeClass('autocomplete-loading');
                        if(hidEl !== null) {
                            $(hidEl).val('');
                        }
                        $(el).devbridgeAutocomplete('hide');
                        global.autocomplete.removeProcess(el);
                        if(typeof callBacks === 'object' && typeof callBacks.checkEmpty === 'function') {
                            callBacks.checkEmpty();
                        }
                    }
                }).unbind('focus.checkFocus').bind('focus.checkFocus', function(){
                    $(this).data('onFocus', true);
                }).unbind('keydown.checkFocus').bind('keydown.checkFocus', function(){
                    $(this).data('onFocus', false);
                });
            },
            removeProcess: function(el) {

                for(var i=0; i < global.autocomplete.processes.length; i++) {
                    if(global.autocomplete.processes[i] === el) {
                        global.autocomplete.processes.splice(i, 1);
                    }
                }

                global.autocomplete.checkProcesses();
            },
            checkProcesses: function() {

                if(global.autocomplete.processes.length > 0) {
                    global.formSubmitted();
                } else {
                    global.formSubmittedReverse();
                }
            }
        },   
         formSubmitted: function() {
            if($('.button-container').length > 0) {
                $('.button-container').children().each(function() {
                    if($(this).is('a')) {
                        $(this).data('href', $(this).attr('href')).removeAttr('href').addClass('disabled');
                    } else {
                        $(this).prop('disabled', true).addClass('disabled');
                    }
                });
            }
        },
        formSubmittedReverse: function() {
            if($('.button-container').length > 0) {
                $('.button-container').children().each(function() {
                    if($(this).is('a')) {
                        $(this).attr('href', $(this).data('href')).removeClass('disabled');
                    } else {
                        $(this).prop('disabled', false).removeClass('disabled');
                    }
                });
            }
        },

        askContinue: function() {
            if($('.ask-continue').length > 0) {
                $('.ask-continue').unbind('click.askContinue').bind('click.askContinue', function(e) {
                    if(!confirm($(this).data('message'))) {
                        e.preventDefault();
                    } else {
                        if($('.form-action').length > 0) $('.form-action').val($(this).val());
                    }
                });
            }
        },
        bindFormSubmitted: function() {
            if($('form').length > 0) {
                $('form').unbind('submit.submitted').bind('submit.submitted', global.formSubmitted());
                global.askContinue();
                global.justContinue();
            }
        },
        justContinue: function() {
            if($('.just-continue').length > 0) {
                $('.just-continue').unbind('click.justContinue').bind('click.justContinue', function() {
                    if($('.form-action').length > 0) $('.form-action').val($(this).val());
                });
            }
        },
        dataTableResponsiveCallBack: function(api, rowIdx, callBack) {

            var data = api.cells( rowIdx, ':hidden' ).eq(0).map( function ( cell ) {
                var header = jQ( api.column( cell.column ).header() );
                var idx = api.cell( cell ).index();

                if ( header.hasClass( 'control' ) || header.hasClass( 'never' ) ) {
                    return '';
                }

                // Use a non-public DT API method to render the data for display
                // This needs to be updated when DT adds a suitable method for
                // this type of data retrieval
                var dtPrivate = api.settings()[0];
                var cellData = dtPrivate.oApi._fnGetCellData(
                    dtPrivate, idx.row, idx.column, 'display'
                );
                var title = header.text();
                if ( title ) {
                    title = title + ':';
                }

                return '<li data-dtr-index="'+idx.column+'">'+
                    '<span class="dtr-title">'+
                    title+
                    '</span> '+
                    '<span class="dtr-data">'+
                    cellData+
                    '</span>'+
                    '</li>';
            } ).toArray().join('');

            return data ?
            { append: jQ('<ul data-dtr-index="'+rowIdx+'"/>').append( data ), callBack: callBack } :
                false;
        }
    };