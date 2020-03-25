<!doctype html>
<html lang="en">

<head>

    <!-- Material Icon -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta content="#6200ff" name="theme-color">

    <meta itemprop="icon" content="<?= base_url('assets/img/') ?>logo_only.png">
    <meta itemprop="name" content="pantau-corona.pasbe.id">
    <meta itemprop="url" content="https://pantau-corona.pasbe.id">
    <meta itemprop="description" content="Live Data Pantau Covid-17 (Corona)">


    <meta property="og:title" content="pantau-corona.pasbe.id" />
    <meta property="og:description" content="Live Data Pantau Covid-17 (Corona)" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://pantau-corona.pasbe.id/" />

    <meta name="icon" content="<?= base_url('assets/img/') ?>logo_only.png">
    <meta name="name" content="pantau-corona.pasbe.id">
    <meta name="url" content="https://pantau-corona.pasbe.id">
    <meta name="description" content="Live Data Pantau Covid-17 (Corona)">

    <meta name="rating" content="adult" />
    <meta name="rating" content="RTA-5042-1996-1400-1577-RTA" />

    <meta name="google-site-verification" content="BoqrF70kx71frWRWSpVrzzz75ikLG4ZaJd90vqUuiOs" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/') ?>main.css" type="text/css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/') ?>bootstrap-4.3.1/css/bootstrap.min.css" type="text/css">

    <script src="<?= base_url('assets/vendor/') ?>jquery/jquery.min.js"></script>

    <script src="<?= base_url('assets/js/') ?>main.js"></script>

    <link rel="shortcut icon" href="<?= base_url() ?>assets/img/logo_only.png" />

    <title>Pantau Corona - Situs Pemantauan Live Data</title>
</head>

<body class="" style="background-color: #18191a">
<nav class="navbar navbar-expand-lg bg-night fixed-top">
    <div class="container">
        <a class="navbar-brand" href="">
            <img src="<?= base_url('assets/img/') ?>logo.png" width="auto" height="30" class="d-inline-block align-top" alt="">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div id="navbarNavDropdown" class="navbar-collapse collapse">
            <ul class="navbar-nav mr-auto">
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active text-light" href="<?= base_url() ?>">HOME</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light btn btn-sm btn-outline-light" href="<?= base_url('hotline') ?>">  HOTLINE CORONA  </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div style="margin-top: -100px; background-color: #5b646e">
    <iframe id="map" style="width:100%"; height="520" src="" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
</div>

<div class="container">
    <div class="row">
        <div class="card-feature col-lg-10 mx-auto">
            <div class="card bg-night">
                <div class="card-body card-body-feature">
                    <div class="row">
                        <div class="col-lg-4 text-center m-0 pb-4">
                            <img src="<?= base_url('assets/img/') ?>positif.png" alt="Positif" height="60px" width="auto">
                            <div class="text-benefit">
                                <h4 class="medium-title text-light" id="TotalConfirmed">...</h4>
                                <a href="#" id="btnPositif" class="btn btn-lg btn-block btn-success-active anim-slideup text-center align-middle">
                                    <span class="font-btn-small align-middle">POSITIF</span>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-4 text-center m-0 pb-4">
                            <img src="<?= base_url('assets/img/') ?>sembuh.png" alt="Sembuh" height="60px" width="auto">
                            <div class="text-benefit">
                                <h4 class="medium-title text-light" id="TotalRecovered">...</h4>
                                <a href="#" id="btnSembuh" class="btn btn-lg btn-block btn-green-border anim-slideup text-center align-middle">
                                    <span class="font-btn-small align-middle">SEMBUH</span>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-4 text-center m-0 pb-4">
                            <img src="<?= base_url('assets/img/') ?>meninggal.png" alt="Meninggal" height="60px" width="auto">
                            <div class="text-benefit">
                                <h4 class="medium-title text-light" id="TotalDeaths">...</h4>
                                <a href="#" id="btnMeninggal" class="btn btn-lg btn-block btn-purple-border anim-slideup text-center align-middle">
                                    <span class="font-btn-small align-middle">MENINGGAL</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <section>
        <div class="col-md-7 mx-auto mb-4">
            <h2 class="title text-center text-light" style="margin-top: 80px;">
                Pantau Covid <b>&#8212;</b> 19 (Corona)
            </h2>
        </div>
        <div class="col-md-8 mx-auto">
            <p class="text-secondary text-center">Situs pemantauan Covid-19 (corona)
                <span class="round-span-purple">Live Data</span>
                Global & Indonesia.</p>
        </div>
    </section>
</div>
<div class="feature-bg">
    <div class="container" style="margin-top: 50px; ">
        <section>
            <div class="row">
                <div class="col-lg-1 col-md-1 col-sm-12"></div>
                <div class="col-lg-5 col-md-12 col-sm-12" style="margin-top: 30px; margin-bottom: 30px;">
                    <h3 class="medium-title text-light"><b>&#8212;</b> Indonesia 🇮🇩</h3>
                    <p class="text-secondary">Data Kasus Covid-19 di Indonesia.</p>
                    <div class="font-btn-small font-weight-thin align-middle">
                        <span class="round-span-purple" id="lastupdate">...</span>
                    </div>
                </div>
                <div class="col-lg-5 col-md-12 col-sm-12">
                    <div class="card bg-night">
                        <div class="card-body card-body-feature">
                            <ul class="list-unstyled list-feature">
                                <li>
                                    <img class="float-left" src="<?= base_url('assets/img/') ?>positif.png" alt="Responsive" height="50px"
                                         width="auto">
                                    <h5 class="align-middle text-light" id="positif">...</h5>
                                    <p class="text-secondary-feature">Total orang <span class="text-light">Positif</span>.</p>
                                </li>
                                <li>
                                    <img class="float-left" src="<?= base_url('assets/img/') ?>sembuh.png" alt="Modern" height="50px"
                                         width="auto">
                                    <h5 class="align-middle text-light" id="sembuh">...</h5>
                                    <p class="text-secondary-feature">Total orang <span class="text-light">Sembuh</span>.</p>
                                </li>
                                <li>
                                    <img class="float-left" src="<?= base_url('assets/img/') ?>meninggal.png" alt="Galery" height="50px"
                                         width="auto">
                                    <h5 class="align-middle text-light" id="meninggal">...</h5>
                                    <p class="text-secondary-feature">Total orang <span class="text-light">Meninggal</span>.</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>



<div class="tanya-bg" style="background-color: #1c1d1e !important;">
    <div class="container" style="margin-top: -10px; padding-top: 90px">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="card bg-night">
                    <div class="card-body card-body-feature">
                        <div class="row">
                            <div class="col-lg-6">
                                <br>
                                <div class="align-items-center float-left text-light" style="margin-bottom: 20px;">
                                    <h4 class="medium-title">Memiliki Pertanyaan ? — Tanya yuk..</h4>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <br>
                                <div class="align-items-center" style="margin-top:-10px; margin-bottom: 20px;">
                                        <span class="text-secondary align-middle" style="float: right;">Hubungi —
                                            &nbsp;&nbsp;</span>
                                    <a href="https://api.whatsapp.com/send?phone=6282199838282&text=Saya%20mau%20tanya%20undangan%20"
                                       class="btn btn-lg btn-outline-light anim-slideup text-center align-middle"
                                       style="float: right;">
                                        <span class="font-btn-small font-weight-thin align-middle">HOTLINE CORONA</span>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div style=" background-color: #5b646e; margin-top: -20px;">
    <section>
        <!-- Footer -->
        <footer class="page-footer font-small blue pt-4">

            <!-- Footer Links -->
            <div class="container text-center text-md-left text-white" style="margin-top: 40px;">

                <!-- Grid row -->
                <div class="row">

                    <!-- Grid column -->
                    <div class="col-md-6 mt-md-0 mt-3">

                        <!-- Content -->
                        <h5 class=" title"><img src="<?= base_url('assets/img/') ?>logo_only.png" height="30px" width="auto"> —
                            pantau-corona.pasbe.id
                        </h5>
                        <p class="text-secondary" style="color: #a8b9cb !important;">Situs pemantauan Covid-19 (corona)
                            Live Data
                            Global & Indonesia.
                            <br>
                            <small><span id="lastupdate_footer"></span></small>
                        </p>
                        <br>

                    </div>
                    <!-- Grid column -->

                    <!-- Grid column -->
                    <div class="col-md-3 mb-md-0 mb-3">

                        <!-- Links -->
                        <h5 class="text-uppercase">Contacts</h5>

                        <ul class="list-unstyled text-white">
                            <li>
                                <a href="mailto:haerul.muttaqin@gmail.com" class="link-contact"
                                   class="link-contacts">
                                    <div class="font-btn-small font-weight-thin align-middle">
                                        <i class="material-icons md-18" style="color: #a8b9cb !important;">
                                            mail
                                        </i> <span style="color: #a8b9cb !important;">haerul.muttaqin@gmail.com</span>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="tel:+6282199838282" class="link-contact" class="link-contacts">
                                    <div class="font-btn-small font-weight-thin align-middle">
                                        <i class="material-icons md-18" style="color: #a8b9cb !important;">
                                            phone
                                        </i> <span style="color: #a8b9cb !important;">+62 821 99 83 8282</span>
                                    </div>
                                </a>
                            </li>
                        </ul>

                    </div>
                    <!-- Grid column -->

                    <!-- Grid column -->
                    <div class="col-md-3 mb-md-0 mb-3">

                        <!-- Links -->
                        <h5 class="text-uppercase">Social Media</h5>

                        <ul class="list-unstyled text-white">
                            <li>
                                <a target="_blank" href="https://www.instagram.com/haerulmuttaqin.id" class="link-contact"
                                   class="link-contacts">
                                    <div class="font-btn-small font-weight-thin align-middle">
                                        <span style="color: #a8b9cb !important;">Instagram</span>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a target="_blank" href="https://www.facebook.com/muttaqinhaerul"
                                   class="link-contact" class="link-contacts">
                                    <div class="font-btn-small font-weight-thin align-middle">
                                        <span style="color: #a8b9cb !important;">Facebook</span>
                                    </div>
                                </a>
                            </li>
                        </ul>


                    </div>
                    <!-- Grid column -->

                </div>
                <!-- Grid row -->

            </div>
            <br><br>
            <!-- Footer Links -->

            <!-- Copyright -->
            <div class="footer-copyright text-center py-3 text-white">
                © 2019 pantau-corona.pasbe.id - Made with ❤️ by Programmer Adzan Subuh
            </div>
            <!-- Copyright -->

        </footer>
        <!-- Footer -->
    </section>
</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->

<script src="<?= base_url('assets/vendor/') ?>jquery/jquery.min.js"></script>

<script src="<?= base_url('assets/vendor/') ?>bootstrap/js/bootstrap.bundle.js"></script>

<script src="<?= base_url('assets/vendor/') ?>fastclick/fastclick.js"></script>

<script src="<?= base_url('assets/vendor/') ?>overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>

<script src="<?= base_url('assets/vendor/') ?>datatables/jquery.dataTables.js"></script>

<script src="<?= base_url('assets/vendor/') ?>datatables/dataTables.bootstrap4.js"></script>

<script src="<?= base_url('assets/vendor/') ?>datatables/extensions/Mark/mark.min.js"></script>

<script src="<?= base_url('assets/vendor/') ?>datatables/extensions/Mark/datatables.mark.js"></script>

<script src="<?= base_url('assets/vendor/') ?>moment/moment.min.js"></script>

<script src="<?= base_url('assets/vendor/') ?>daterangepicker/daterangepicker.js"></script>

<script src="<?= base_url('assets/vendor/') ?>icheck/icheck.js"></script>

<script src="<?= base_url('assets/vendor/') ?>sweetalert2/sweetalert2.min.js"></script>

<script src="<?= base_url('assets/vendor/') ?>chart.js/Chart.min.js"></script>

<script src="<?= base_url('assets/vendor/') ?>crop/js/cropper.js"></script>

<script src="<?= base_url('assets/js/') ?>main.js"></script>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-132942028-5"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'UA-132942028-5');
</script>

</body>

</html>