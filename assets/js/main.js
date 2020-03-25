(function($){

    const BASE_URL = 'https://pantau-corona.pasbe.id/19/';

    $('#map').attr('src', 'https://coronavirus.app/map?embed=true');
    $('#btnPositif').click(function () {
        $('#map').attr('src', 'https://coronavirus.app/map?embed=true&mode=infected');
        $('#btnPositif').addClass('btn-success-active');
        $('#btnPositif').removeClass('btn-success-border');
        $('#btnSembuh').addClass('btn-success-border');
        $('#btnSembuh').removeClass('btn-success-active');
        $('#btnMeninggal').addClass('btn-success-border');
        $('#btnMeninggal').removeClass('btn-success-active');
    });
    $('#btnSembuh').click(function () {
        $('#map').attr('src', 'https://coronavirus.app/map?embed=true&mode=recovered');
        $('#btnPositif').addClass('btn-success-border');
        $('#btnPositif').removeClass('btn-success-active');
        $('#btnSembuh').addClass('btn-green-active');
        $('#btnSembuh').removeClass('btn-green-border');
        $('#btnMeninggal').addClass('btn-purple-border');
        $('#btnMeninggal').removeClass('btn-purple-active');
    });
    $('#btnMeninggal').click(function () {
        $('#map').attr('src', 'https://coronavirus.app/map?embed=true&mode=dead');
        $('#btnPositif').addClass('btn-success-border');
        $('#btnPositif').removeClass('btn-success-active');
        $('#btnSembuh').addClass('btn-green-border');
        $('#btnSembuh').removeClass('btn-green-active');
        $('#btnMeninggal').addClass('btn-purple-active');
        $('#btnMeninggal').removeClass('btn-purple-border');
    });

    $.get("https://api.kawalcorona.com/indonesia/", function(data, status) {
            $("#positif").html(data[0].positif + ' <small><span class="font-weight-light">(Indonesia)</span></small>');
            $("#sembuh").html(data[0].sembuh + ' <small><span class="font-weight-light">(Indonesia)</span></small>');
            $("#meninggal").html(data[0].meninggal + ' <small><span class="font-weight-light">(Indonesia)</span></small>');
        }
    );

        $.get("https://api.kawalcorona.com/confirmed/", function(data, status) {
                for (var i = 0; i < data.length - 1; i++) {
                    if (data[i].nameConfirmed == "Positif") {
                        $("#TotalConfirmed").text(data[i].TotalConfirmed);
                    }
                }
            }
        );

        $.get("https://api.kawalcorona.com/recovered/", function(data, status) {
                for (var i = 0; i < data.length - 1; i++) {
                    if (data[i].nameRecovered == "Sembuh") {
                        $("#TotalRecovered").text(data[i].TotalRecovered);
                    }
                }
            }
        );

        $.get("https://api.kawalcorona.com/deaths/", function(data, status) {
                for (var i = 0; i < data.length - 1; i++) {
                    if (data[i].nameDeaths == "Meninggal") {
                        $("#TotalDeaths").text(data[i].TotalDeaths);
                    }
                }
            }
        );

        $.get("main/get_data_last_update", function (data, status) {
            $('#lastupdate_footer').html(data) ;
            let lastupdate = data.replace('<p>', '');
            let lastupdate2 = lastupdate.replace('</p>', '');
            $('#lastupdate').html(lastupdate2.replace('Sumber data : Kementerian Kesehatan & JHU. Update terakhir :', '')) ;
        });

}(jQuery));