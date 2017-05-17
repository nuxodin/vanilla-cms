if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(appURL+'cms.app1.service-worker.js', {scope: appURL}).then(function(reg) {
        //reg.unregister();
    }).catch(function(error) {
        console.log('Registration failed with ', error);
    });
}
