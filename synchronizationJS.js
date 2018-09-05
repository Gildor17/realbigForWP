syncFunction();

function syncFunction()
{
    jQuery.ajax({
        url: "//"+document.domain+"/wordpress_test/wp-content/plugins/realbigForWP/synchronising.php",
        data:{funcActivator: 'ready'}, //for requesting in file 1
        async: true,
        type: 'post',
        dataType: 'text',
    })
}
