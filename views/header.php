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
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>css/default.css" />
    <?php if (isset($css)): ?>
    <?php foreach ($css as $style): ?>
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>css/<?=$style?>"/>
    <?php endforeach; ?>
    <?php endif; ?>
    <script type="text/javascript" src="<?=base_url()?>js/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="<?=base_url()?>js/jquery.window.js"></script>
    <?php if (isset($js)): ?>
    <?php foreach ($js as $script): ?>
    <script type="text/javascript" src="<?=base_url()?>js/<?=$script?>"></script>
    <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
<div id="container">
    <div id="banner"><img src="http://melisr.rbg.vic.gov.au/melisr/images/banners/<?=$bannerimage;?>" alt="Harvey is watching you" /></div>
    <div id="menu">
            <a href="<?=site_url()?>/melisrlabels">Labels</a> |
            <a href="<?=site_url()?>/numbers">Numbers</a> |
            <a href="<?=site_url()?>/recordset">Record set creator</a> |
            <a href="<?=site_url()?>/genusstorage">Storage families</a> |
            <a href="<?=site_url()?>/fqcm">Fancy quality control machine</a> |
            <a href="<?=site_url()?>/imagemetadata">Attachment metadata</a>
    </div>
    <div id="content">

