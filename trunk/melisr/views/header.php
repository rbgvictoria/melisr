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
    <div id="banner"><img src="http://203.55.15.78/melisr/images/banners/<?=$bannerimage;?>" alt="Harvey is watching you" /></div>
    <div id="menu">
            <a href="<?=site_url()?>/melisrlabels">Labels</a> |
            <a href="<?=site_url()?>/numbers">Numbers</a> |
            <a href="<?=site_url()?>/recordset">Record set creator</a> |
            <a href="<?=site_url()?>/genusstorage">Storage families</a> |
            <a href="<?=site_url()?>/fqcm">Fancy quality control machine</a> |
            <a href="<?=site_url()?>/imagemetadata">Image metadata</a>
    </div>
    <div id="content">

