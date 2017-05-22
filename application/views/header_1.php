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
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs/dt-1.10.15/datatables.min.css"/>
 
    <link rel="stylesheet" type="text/css" href="<?=base_url()?><?= autoVersion('css/styles.css')?>" />
    <?php if (isset($css)): ?>
    <?php foreach ($css as $style): ?>
    <link rel="stylesheet" type="text/css" href="<?=base_url()?><?= autoVersion('css/' . $style)?>"/>
    <?php endforeach; ?>
    <?php endif; ?>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript" src="<?=base_url()?>bower_components/bootstrap-sass/assets/javascripts/bootstrap.min.js"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?=base_url()?>bower_components/jspath/jspath.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs/dt-1.10.15/datatables.min.js"></script>
    <?php if (isset($js)): ?>
    <?php foreach ($js as $script): ?>
    <?php if (substr($script, 0, 2) == '//' || substr($script, 0, 4) == 'http'): ?>
    <script type="text/javascript" src="<?=$script?>"></script>
    <?php else: ?>
    <script type="text/javascript" src="<?=base_url()?><?= autoVersion('js/' . $script)?>"></script>
    <?php endif; ?>
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
                        <li><a href="<?=site_url()?>transactions">Transactions paperwork</a></li>
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Loans <span class="caret"></span></a>
                          <ul class="dropdown-menu" role="menu">
                            <li><a href="<?=site_url()?>loanreturn/loans">Find loan</a></li>
                            <li><a href="<?=site_url()?>loanreturn">Loan returner</a></li>
                            <li><a href="<?=site_url()?>loansorter">Messy loan sorter</a></li>
                          </ul>
                        </li>
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Exchange <span class="caret"></span></a>
                          <ul class="dropdown-menu" role="menu">
                            <li><a href="<?=site_url()?>exchangedata">Exchange data</a></li>
                            <li><a href="<?=site_url()?>dehispidator">Dehispidator</a></li>
                          </ul>
                        </li>
                        <li><a href="<?=site_url()?>borrower">Non MEL loans</a></li>
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Administration <span class="caret"></span></a>
                          <ul class="dropdown-menu" role="menu">
                            <li><a href="<?=site_url()?>admin">Administration</a></li>
                            <li><a href="<?=site_url()?>audit">Audit</a></li>
                            <li><a href="<?=site_url()?>recordset/manage">Manage record sets</a></li>
                          </ul>
                        </li>
                        <li><a href="<?=site_url()?>melcensus">MEL Census</a></li>
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">More <span class="caret"></span></a>
                          <ul class="dropdown-menu" role="menu">
                            <li><a href="<?=site_url()?>gpi">GPI metadata</a></li>
                            <li><a href="<?=site_url()?>destroyer">Damage reporter</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="<?=site_url()?>vrs">Vic. Ref. Set</a></li>
                          </ul>
                        </li>
                      </ul>
                    </div><!--/.navbar-collapse -->
                </nav>


                <div class="col-lg-12">
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
            </div><!--/.row -->
        </div><!--/.container -->
    </div> <!-- /#banner -->
    
    <?php require_once APPPATH . 'views/includes/messages.php'; ?>



