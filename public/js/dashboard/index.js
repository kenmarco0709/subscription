var dashboard = {
    settings : {
        clientGetCtrAjax: '',
        paymentGetCtrAjax: '',
        paymentGetYearAnalyticsAjax: '',
        paymentGetMonthlyAnalyticsAjax: '',
        clientGetMonthlyAnalyticsAjax: '',
        clientGetYearlyAnalyticsAjax: ''

    },
    init: function(){
        dashboard.getDailyClient();
        dashboard.getMonthlyClient();
        dashboard.getDailySales();
        dashboard.getMonthlySales();
        dashboard.getYearlyAnalytics();
        dashboard.getMonthlyAnalytics();
        dashboard.getClientMonthlyAnalytics();
        dashboard.getClientYearlyAnalytics();




    }, 
    getDailyClient: function(){
        $.ajax({
            url: dashboard.settings.clientGetCtrAjax,
            type: "POST",
            data: {
                type: 'daily'
            },
            success: function(d){

                $('#dailyClient').html(d.ctr);
                $('#dailyClient').removeClass('loader');

            }
        });
    },

    getMonthlyClient: function(){
        $.ajax({
            url: dashboard.settings.clientGetCtrAjax,
            type: "POST",
            data: {
                type: 'monthly'
            },
            success: function(d){

                $('#monthlyClient').html(d.ctr);
                $('#monthlyClient').removeClass('loader');
            }
        });
    },
    getDailySales: function(){
        $.ajax({
            url: dashboard.settings.paymentGetCtrAjax,
            type: "POST",
            data: {
                type: 'daily'
            },
            success: function(d){

                $('#dailySales').html(d.ctr);
                $('#dailySales').removeClass('loader');

            }
        });
    },

    getMonthlySales: function(){
        $.ajax({
            url: dashboard.settings.paymentGetCtrAjax,
            type: "POST",
            data: {
                type: 'monthly'
            },
            success: function(d){

                $('#monthlySales').html(d.ctr);
                $('#monthlySales').removeClass('loader');
            }
        });
    },
    getYearlyAnalytics: function(){
       
        $.ajax({
            url: dashboard.settings.paymentGetYearAnalyticsAjax,
            type: "POST",
            success: function(d){

                var parseData = JSON.parse(d);
                var areaChartData = {
                    labels  : parseData.days,
                    datasets: [
                            {
                            label               : 'Yearly Sales',
                            backgroundColor     : 'rgba(60,141,188,0.9)',
                            borderColor         : 'rgba(60,141,188,0.8)',
                            pointRadius          : false,
                            data                : parseData.stats,
                            }
                        ]
                    }
    
                  var stackedBarChartCanvas = $('#lineChart').get(0).getContext('2d')
    
                    var stackedBarChartOptions = {

                        responsive              : true,
                        maintainAspectRatio     : false,
                        scales: {
                            xAxes: [{
                            stacked: true,
                            }],
                            yAxes: [{
                            stacked: true
                            }]
                        }
                    }
    
                    new Chart(stackedBarChartCanvas, {
                        type: 'bar',
                        data: areaChartData,
                        options: stackedBarChartOptions
                    });
            }
        });
    },
    getMonthlyAnalytics: function(){
       
        $.ajax({
            url: dashboard.settings.paymentGetMonthlyAnalyticsAjax,
            type: "POST",
            success: function(d){

                var parseData = JSON.parse(d);
                var areaChartData = {
                    labels  : parseData.days,
                    datasets: [
                            {
                            label               : 'Monthly Sales',
                            backgroundColor     : 'rgba(60,141,188,0.9)',
                            borderColor         : 'rgba(60,141,188,0.8)',
                            pointRadius          : false,
                            data                : parseData.stats,
                            }
                        ]
                    }
    
                  var stackedBarChartCanvas = $('#lineChart-daily').get(0).getContext('2d')
    
                    var stackedBarChartOptions = {

                        responsive              : true,
                        maintainAspectRatio     : false,
                        scales: {
                            xAxes: [{
                            stacked: true,
                            }],
                            yAxes: [{
                            stacked: true
                            }]
                        }
                    }
    
                    new Chart(stackedBarChartCanvas, {
                        type: 'bar',
                        data: areaChartData,
                        options: stackedBarChartOptions
                    });
            }
        });
    },
    getClientMonthlyAnalytics: function(){
       
        $.ajax({
            url: dashboard.settings.clientGetMonthlyAnalyticsAjax,
            type: "POST",
            success: function(d){

                var parseData = JSON.parse(d);
                var areaChartData = {
                    labels  : parseData.days,
                    datasets: [
                            {
                            label               : 'Daily Clients',
                            backgroundColor     : 'rgba(60,141,188,0.9)',
                            borderColor         : 'rgba(60,141,188,0.8)',
                            pointRadius          : false,
                            data                : parseData.stats,
                            }
                        ]
                    }
    
                  var stackedBarChartCanvas = $('#lineChart-daily-clients').get(0).getContext('2d')
    
                    var stackedBarChartOptions = {

                        responsive              : true,
                        maintainAspectRatio     : false,
                        scales: {
                            xAxes: [{
                            stacked: true,
                            }],
                            yAxes: [{
                            stacked: true
                            }]
                        }
                    }
    
                    new Chart(stackedBarChartCanvas, {
                        type: 'bar',
                        data: areaChartData,
                        options: stackedBarChartOptions
                    });
            }
        });
    },
    getClientYearlyAnalytics: function(){
       
        $.ajax({
            url: dashboard.settings.clientGetYearlyAnalyticsAjax,
            type: "POST",
            success: function(d){

                var parseData = JSON.parse(d);
                var areaChartData = {
                    labels  : parseData.days,
                    datasets: [
                            {
                            label               : 'Monthly Clients',
                            backgroundColor     : 'rgba(60,141,188,0.9)',
                            borderColor         : 'rgba(60,141,188,0.8)',
                            pointRadius          : false,
                            data                : parseData.stats,
                            }
                        ]
                    }
    
                  var stackedBarChartCanvas = $('#lineChart-monthly-clients').get(0).getContext('2d')
    
                    var stackedBarChartOptions = {

                        responsive              : true,
                        maintainAspectRatio     : false,
                        scales: {
                            xAxes: [{
                            stacked: true,
                            }],
                            yAxes: [{
                            stacked: true
                            }]
                        }
                    }
    
                    new Chart(stackedBarChartCanvas, {
                        type: 'bar',
                        data: areaChartData,
                        options: stackedBarChartOptions
                    });
            }
        });
    }
}