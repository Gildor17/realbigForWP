syncFunction();

function syncFunction()
{
    jQuery.ajax({
        url: document.domain+"/wp-content/plugins/realbigForWP/synchronising.php",
        data:{funcActivator: 'ready'}, //for requesting in file 1
        async: true,
        type: 'post',
        dataType: 'text',
    })
}
