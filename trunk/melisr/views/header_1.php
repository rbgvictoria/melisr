<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">
<head>
	<title>
            <?php if(isset($title)):?>
            <?=$title?>
            <?php else: ?>
            MELISR
            <?php endif; ?>
        </title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/default.css" />
        <?php if (isset($css)): ?>
        <?php foreach ($css as $style): ?>
        <?php if (substr($style, 0, 2) == '//' || substr($style, 0, 5) == 'http:'): ?>
        <link rel="stylesheet" type="text/css" href="<?=$style?>"/>
        <?php else: ?>
        <link rel="stylesheet" type="text/css" href="<?=base_url()?>css/<?=$style?>"/>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php endif; ?>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script type="text/javascript" src="<?=base_url()?>js/jquery.window.js"></script>
        <?php if (isset($js)): ?>
        <?php foreach ($js as $script): ?>
        <?php if (substr($script, 0, 2) == '//' || substr($script, 0, 5) == 'http:'): ?>
        <script type="text/javascript" src="<?=$script?>"></script>
        <?php else: ?>
        <script type="text/javascript" src="<?=base_url()?>js/<?=$script?>"></script>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php endif; ?>
</head>

<body>
<div id="container">
    <div id="banner"><img src="http://203.55.15.78/melisr/images/banners/<?=$bannerimage;?>" alt="Harvey is watching you" /></div>
    <div id="menu">
            <a href="<?=site_url()?>/transactions">Transactions paperwork</a> |
            <a href="<?=site_url()?>/loanreturn/loans">Find loan</a> |
            <a href="<?=site_url()?>/loanreturn">Loan returner</a> |
            <a href="<?=site_url()?>/loansorter">Messy loan sorter</a> |
            <a href="<?=site_url()?>/exchangedata">Exchange data</a> |
            <a href="<?=site_url()?>/borrower">Non MEL loans</a>
    </div>
    <div id="content">

