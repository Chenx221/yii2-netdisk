//dynamic fetch of system information & display
//every 5 seconds ajax request to fetch system information
//backport: admin/get-sysinfo
//return json object with system information

var sysinfo = {
    init: function () {
        setInterval(sysinfo.fetch, 2000);
        // WARNING
        // For Windows users, the interval should be equal or greater than 1500ms, because the system information gathering process is slow. (Please dynamically adjust the interval based on system performance.)
        // For Linux users, the interval can be set to below 1000ms.
    },
    fetch: function () {
        $.ajax({
            url: 'index.php?r=admin%2Fget-sysinfo',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                sysinfo.display(data);
            },
            error: function (xhr, status, error) {
                console.log('Error: ' + error.message);
            }
        });
    },
    display: function (data) {
        // serverTime
        $('#rdata_serverTime').text(data.serverTime);
        // serverUpTime
        $('#rdata_serverUpTime').text(data.serverUpTime);
        // load
        $('#rdata_load').text(data.load);
        // cpuUsage
        $('#rdata_cpuUsage').text(data.cpuUsage);
        // ramUsage
        $('#rdata_ramUsage').text(data.ramUsage);
        // swapUsage
        $('#rdata_swapUsage').text(data.swapUsage);
        // mp_avail
        $('#rdata_mp_avail').text(data.mp_avail);
        // mp_used
        $('#rdata_mp_used').text(data.mp_used);
        // mp_usage
        $('#rdata_mp_usage').text(data.mp_usage);

        // cpu graph
        var cpuData_x = myChart.getOption().xAxis[0].data;
        var cpuData = myChart.getOption().series[0].data;
        cpuData_x.push(data.serverTime.substr(11, 8));
        cpuData.push(data.cpuUsage);
        if (cpuData_x.length > 10) {
            cpuData_x.shift();
        }
        if (cpuData.length > 10) {
            cpuData.shift();
        }
        myChart.setOption({
            xAxis: [{
                data: cpuData_x
            }],
            series: [{
                data: cpuData
            }]

        })

        // load graph
        if (data.osType === 1) {
            var loadData_x = myChart1.getOption().xAxis[0].data;
            var loadData = myChart1.getOption().series[0].data;
            loadData_x.push(data.serverTime.substr(11, 8));
            loadData.push(data.load);
            if (loadData_x.length > 10) {
                loadData_x.shift();
            }
            if (loadData.length > 10) {
                loadData.shift();
            }
            myChart1.setOption({
                xAxis: [{
                    data: loadData_x
                }],
                series: [{
                    data: loadData
                }]
            });
        }

        // memory graph
        var memoryData_x = myChart2.getOption().xAxis[0].data;
        var memoryData = myChart2.getOption().series[0].data;
        var swapData = myChart2.getOption().series[1].data;
        memoryData_x.push(data.serverTime.substr(11, 8));
        memoryData.push(data.ramUsage);
        swapData.push(data.swapUsage);
        if (memoryData_x.length > 10) {
            memoryData_x.shift();
        }
        if (memoryData.length > 10) {
            memoryData.shift();
        }
        if (swapData.length > 10) {
            swapData.shift();
        }
        myChart2.setOption({
            xAxis: [{
                data: memoryData_x
            }],
            series: [{
                data: memoryData
            },{
                data: swapData
            }]
        });

        // disk chart
        var free = 100-data.mp_usage;
        myChart3.setOption({
            series: [{
                data: [
                    { value: data.mp_usage, name: 'Used Space' },
                    { value: free, name: 'Free Space' }
                ]
            }]
        });
    }
}
sysinfo.init();
// *Some data need to refresh display
// rdata_serverTime: serverTime
// rdata_serverUpTime: serverUpTime
// (osType ==2) rdata_load: load
// rdata_cpuUsage: cpuUsage
// rdata_ramUsage: ramUsage
// rdata_swapUsage: swapUsage
// rdata_mp_avail: mp_avail
// rdata_mp_used: mp_used
// rdata_mp_usage: mp_usage

