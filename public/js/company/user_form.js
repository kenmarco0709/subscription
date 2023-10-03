var company_user_form = {
    settings : {
        branchAutocompleteUrl : '',
        companyId: ''
    }, 
    init: function(){
        global.autocomplete.bind(this.settings.branchAutocompleteUrl, '#user_form_branch_desc', '#user_form_branch');   
       $( '#user_form_branch_desc').devbridgeAutocomplete('setOptions', {params: { companyId :  company_user_form.settings.companyId}});

    },
   
}