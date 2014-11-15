var _md5 = false;
var _begin;
var _end;
var _queue_id;

var count = 0;
var enabled = false;

var time = 0;

$(document).ready(function () {

    function loader(_enabled) {
        if (_enabled) {
            $('#loader').show();
        } else {
            $('#loader').hide();
        }
    }

    function loadQueue() {

        if (!enabled) {
            return false;
        }

        loader(true);
        $.ajax(
            {
                url: '/get_queue.php'
            })
            .done(function (data) {
                loader(false);

                if (checkResponse(data)) {
                    return false;
                }

                if (_md5 && _md5 != data.body.md5.md5) {
                    $('.jumbotron').html('<div class="alert alert-success" role="alert"><strong>Кажется,</strong> предыдущий пароль был найден!' + $('.jumbotron').html())
                }

                _md5 = data.body.md5.md5;
                _begin = data.body.queue.begin;
                _end = data.body.queue.end;

                var id = data.body.queue.id;
                _queue_id = id;

                $('#caption').text(_md5);

                $('#text').text("Поехали!");

                $('#table').show();

                $('#table tbody').html('<tr><td>' + id + '</td> <td>' + _begin + '</td> <td>' + _end + '</td><td id="result-' + id + '">wait</td></tr>' + $('#table tbody').html());

                var result = checkMD5(_md5, _begin, _end);

                if (!result) {
                    $('#result-' + id).text('Sorry :(');
                    reportUnSuccess(data.body.queue);
                    setTimeout(function(){loadQueue()}, 300);
                    return false;
                }
                else {
                    $('#result-' + id).text(result);
                    $('#caption').text("Мои поздравления!");
                    $('#text').text("Мы нашли ваш несчастный пароль!");

                    enabled = false;
                    $('.btn').hide();
                    $('#start-btn').show();

                    reportSuccess(data.body.md5, result);

                    return true;
                }

            }).fail(function () {
                loader(false)
            });
    }

    function reportUnSuccess(_queue) {
        $.ajax({
            method: 'post',
            url: '/report_unsuccess.php',
            data: {
                queue: _queue
            }
        }).done(function(data){
            if (checkResponse(data)) {
                return false;
            }
        }).fail(function(){
            reportUnSuccess(_md5, password)
        })
    }

    function reportSuccess(_md5, password) {
        loader(true);
        $.ajax({
            method: 'post',
            url: '/report_success.php',
            data: {
                md5: _md5,
                password: password
            }
        }).done(function(data){
            if (checkResponse(data)) {
                return false;
            }
            loader(false);
        }).fail(function(){
            reportSuccess(_md5, password)
        })
    }

    function reportLog(count, time)
    {
        if (enabled) {
            $.ajax({
                method: 'post',
                url: '/report_log.php',
                data: {
                    count: count,
                    time: time,
                    queue_id: _queue_id
                }
            })
        }
    }

    function checkMD5(_md5, _begin, _end) {
        var begin = base_convert(_begin, 36, 10);
        var end = base_convert(_end, 36, 10);
        var pass;

        var date = new Date();

        //console.log(begin, end);
        for (var i = begin; i <= end; i++) {
            pass = base_convert(i, 10, 36);
            //console.log(pass);
            count++;
            if (_md5 == hex_md5(pass)) {
                return pass;
            }
        }

        date = (new Date()) - date;

        reportLog(end - begin, date);

        return false;
    }

    function checkResponse(response) {
        if (response.status == 'ERROR') {
            $('.btn').hide();
            $('.jumbotron').html('<div class="alert alert-danger" role="alert"><strong>:(</strong> ' + response.body + '</div>' + $('.jumbotron').html())
            enabled = false;
        }
    }


    $('.btn').click(function () {
        $('.btn').hide();
    });

    $('#start-btn').click(function () {
        enabled = true;
        $('#stop-btn').show();

        loadQueue();

    });

    $('#stop-btn').click(function () {
        enabled = false;
        $('#start-btn').show();
    });

});