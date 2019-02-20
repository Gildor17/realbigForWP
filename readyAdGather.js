function sendReadyBlocksNew(blocks) {
    let xhttp = new XMLHttpRequest();
    let sendData = 'type=blocksGethering&data='+blocks;
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log('cache succeed');
            // document.getElementById("demo").innerHTML = this.responseText;
        }
    };
    xhttp.open("POST", ajaxurl, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(sendData);
}

if (document.readyState === "complete" || (document.readyState !== "loading" && !document.documentElement.doScroll)) {
    timeBeforeGathering();
} else {
    document.addEventListener("DOMContentLoaded", timeBeforeGathering, false);
}

function timeBeforeGathering() {
    setTimeout(gatherReadyBlocks,2000);
}

function gatherReadyBlocks() {
    let dottaCounter = 0;
    let blocks = '';
    let gatheredBlocks = document.getElementsByClassName('content_rb');

    if (gatheredBlocks.length > 0) {
        blocks += '{"data":[';
        for (let i = 0; i < gatheredBlocks.length; i++) {
            if (dottaCounter > 0) {
                blocks += ',';
            }
            blocks += '{"id":"'+gatheredBlocks[i]['dataset']['id']+'","code":"'+gatheredBlocks[i]['innerHTML'].replace(/\"/g, "\'")+'"}';
            dottaCounter++;
        }
        blocks += "]}";

        // if (!needleUrl) {
        //     needleUrl = "//"+document.domain+"/wp-content/plugins/realbigForWP/realbigForWP";
        // }

        sendReadyBlocksNew(blocks);
    }
}