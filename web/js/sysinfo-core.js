//dynamic fetch of system information & display
//every 5 seconds ajax request to fetch system information
//backport: admin/get-sysinfo
//return json object with system information

var sysinfo = {
    init: function () {
        setInterval(sysinfo.fetch, 5000);
    },
    fetch: function () {
        $.ajax({
            url: '/admin/get-sysinfo',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                sysinfo.display(data);
            }
        });
    },
    display: function (data) {
        //TODO: display system information
    }
}

// *Some data need to display
// rdata_hostname: hostname
// rdata_os: os
// rdata_cpu: cpu
// rdata_ram: ram
// rdata_serverTime: serverTime
// rdata_serverUpTime: serverUpTime
// (osType ==2) rdata_load: load
// rdata_cpuUsage: cpuUsage
// rdata_ramUsage: ramUsage
// rdata_swapUsage: swapUsage
// rdata_dataMountPoint: dataMountPoint
// rdata_mp_fs: mp_fs
// rdata_mp_size: mp_size
// rdata_mp_avail: mp_avail
// rdata_mp_used: mp_used
// rdata_mp_usage: mp_usage
// rdata_hostname2: hostname
// rdata_dns: dns
// rdata_gateway: gateway
// rdata_interfaceName: nic[interfaceName]
// rdata_mac: nic[mac]
// rdata_speed: nic[speed]
// rdata_ipv4: nic[ipv4]
// rdata_ipv6: nic[ipv6]
// rdata_users: users
// rdata_activeUsers: activeUsers
// rdata_shares: shares
// rdata_collections: collections
// rdata_phpVersion: phpVersion
// rdata_memoryLimit: memoryLimit
// rdata_maxExecutionTime: maxExecutionTime
// rdata_uploadMaxFilesize: uploadMaxFilesize
// rdata_postMaxSize: postMaxSize
// rdata_extensions: extensions
// rdata_dbType: dbType
// rdata_dbVersion: dbVersion
// rdata_dbSize: dbSize

// var myChart = echarts.init(document.getElementById('cpu-graph'));
// var myChart1 = echarts.init(document.getElementById('load-graph'));
// var myChart2 = echarts.init(document.getElementById('memory-graph'));
// var myChart3 = echarts.init(document.getElementById('disk-chart'));