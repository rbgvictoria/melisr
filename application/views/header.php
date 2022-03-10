<!DOCTYPE html>
<html lang="en-US">
<head>
    <title>
        <?php if(isset($title)):?>
        <?=$title?>
        <?php else: ?>
        MELISR
        <?php endif; ?>
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <link rel="shortcut icon" href="https://www.rbg.vic.gov.au/common/img/favicon.ico">
    <link rel="stylesheet" type="text/css" href="https://www.rbg.vic.gov.au/common/fonts/451576/645A29A9775E15EA2.css" />
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="<?=base_url()?><?= autoVersion('css/jqueryui.autocomplete.css')?>" />
    <link rel="stylesheet" type="text/css" href="<?=base_url()?><?= autoVersion('css/styles.css')?>" />
    <?php if (isset($css)): ?>
    <?php foreach ($css as $style): ?>
    <link rel="stylesheet" type="text/css" href="<?=base_url()?><?= autoVersion('css/' . $style)?>"/>
    <?php endforeach; ?>
    <?php endif; ?>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript" src="<?=base_url()?>bower_components/bootstrap-sass/assets/javascripts/bootstrap.min.js"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?=base_url()?><?=autoVersion('js/jquery.melisr.js'); ?>"></script>
    <?php if (isset($js)): ?>
    <?php foreach ($js as $script): ?>
    <script type="text/javascript" src="<?=base_url()?><?= autoVersion('js/' . $script)?>"></script>
    <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="melisr vicflora">
    <div id="banner">

        <div class="container">
              <div class="row">
                  <div class="col-lg-12 clearfix">
                    <ul class="social-media">
                        <li><a href="https://twitter.com/RBG_Victoria" target="_blank"><span class="icon icon-twitter-solid"></span></a></li>
                        <li><a href="https://www.facebook.com/BotanicGardensVictoria" target="_blank"><span class="icon icon-facebook-solid"></span></a></li>
                        <li><a href="https://instagram.com/royalbotanicgardensvic/" target="_blank"><span class="icon icon-instagram-solid"></span></a></li>
                    </ul>
                  </div> <!-- /.col -->

                <nav class="navbar navbar-default">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <div class="navbar-brand">
                            <a class="brand-rbg" href="http://www.rbg.vic.gov.au"><img src="<?=base_url()?>images/rbg-logo-with-text.png" alt=""/></a>
                            <a class="brand-vicflora" href="<?=base_url()?>">MELISR</a>
                        </div>
                    </div>

                    <div id="navbar" class="navbar-collapse collapse">
                      <ul class="nav navbar-nav">
                          <li class="home-link"><a href="<?=site_url()?>"><span class="glyphicon glyphicon-home"></span></a></li>
                        <li><a href="<?=site_url()?>labels">Labels</a></li>
                        <li><a href="<?=site_url()?>numbers">Numbers</a></li>
                        <li><a href="<?=site_url()?>recordset">Record set creator</a></li>
                        <li><a href="<?=site_url()?>fqcm">FQCM</a></li>
                        <li><a href="<?=site_url()?>determinator">Determinator</a></li>
                        <li><a href="<?=site_url()?>imagemetadata">Attachment metadata</a></li>
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">DNA sequences <span class="caret"></span></a>
                          <ul class="dropdown-menu" role="menu">
                            <li><a href="<?=site_url()?>dnasequence">Upload sequences</a></li>
                            <li><a href="<?=site_url()?>dnasequence/markers">Markers</a></li>
                            <li><a href="<?=site_url()?>dnasequence/projects">Projects</a></li>
                          </ul>
                        </li>
                        <li><a href="<?=site_url()?>utm-converter">AMG/MGA</a></li>
                      </ul>
                    </div><!--/.navbar-collapse -->
                </nav>


                <div class="col-md-6">
                    <div id="header">
                        <div id="logo">
                            <a href='http://www.rbg.vic.gov.au'>
                                <img class="img-responsive" src="<?=base_url()?>images/rbg-logo-with-text.png" alt="" />
                            </a>
                        </div>
                        <div id="site-name">
                            <a href="<?=base_url()?>">MELISR</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="counter">Counter</div>
                </div>
            </div><!--/.row -->
        </div><!--/.container -->
    </div> <!-- /#banner -->

    <?php require_once APPPATH . 'views/includes/messages.php'; ?>

