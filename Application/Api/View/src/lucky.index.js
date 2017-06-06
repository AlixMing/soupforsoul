
function init() {

    loadUserRecord();
    loadResultRecord();
}

function loadUserRecord() {

    $.ajax({
        type: "POST",
        url: "?s=Activity/Lucky/getUserRecord",
        dataType: "json",
        data: {},
        success: function(data) {
            if (data.errorcode == 0) {
                var temp = '<ul>';
                for (var i = 0; i < data.list.length; i++) {
                    temp += '<li>';
                    temp += '<div class="item">'+data.list[i].phone+'</div>';
                    temp += '<div class="item">'+data.list[i].code+'</div>';
                    temp += '<div class="item">'+data.list[i].pay_time+'</div>';
                    temp += '</li>';
                }
                temp += '</ul>';
                $('#user-marquee').html(temp).kxbdMarquee({
                    direction: 'up',
                    isEqual: false
                });
            }
        }
    });
}

function loadResultRecord() {

    $.ajax({
        type: "POST",
        url: "?s=Activity/Lucky/getResultRecord",
        dataType: "json",
        data: {},
        success: function(data) {
            if (data.errorcode == 0) {
                var temp = '<ul>';
                for (var i = 0; i < data.list.length; i++) {
                    temp += '<li>';
                    temp += '<div class="item">'+data.list[i].period+'</div>';
                    temp += '<div class="item">'+data.list[i].phone+'</div>';
                    temp += '<div class="item">'+data.list[i].code+'</div>';
                    temp += '</li>';
                }
                temp += '</ul>';
                $('#result-marquee').html(temp).kxbdMarquee({
                    direction: 'up',
                    isEqual: false
                });
            }
        }
    });
}