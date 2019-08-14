<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="format-detection" content="telephone=no" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $_REQUEST['title']?></title>
  
  <?php if(!$_REQUEST['headless']) { ?>
  <style> body { overflow:hidden; } </style>
  
  <script src="<?php echo $site->base; ?>/js/iframeResizer.min.js"></script>
  <script src="<?php echo $site->base; ?>/js/iframeResizer.contentWindow.min.js"></script>
  <?php } ?>
  
  <!-- Bootstrap CSS -->
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css" rel="stylesheet">

  <!-- Font awesome --> 
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet">
  <!--[if IE 7]>
    <link href="<?php echo $this->path?>/css/font-awesome-ie7.css" rel="stylesheet">
  <![endif]-->  
  
  <!-- Rezgo stylesheet -->
  <link href="<?php echo $this->path?>/css/rezgo.css?v=<?php echo REZGO_VERSION?>" rel="stylesheet">
  <!--[if IE 9]>
    <link href="<?php echo $this->path?>/css/rezgo-ie9.css" rel="stylesheet">
  <![endif]--> 
  
  <!-- jQuery & Bootstrap JS -->
  <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
  <script src="//code.jquery.com/ui/1.12.0/jquery-ui.min.js"></script>
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    
  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
    <script src="<?php echo $this->path?>/js/html5shiv.min.js"></script>
    <script src="<?php echo $this->path?>/js/respond.min.js"></script>
  <![endif]-->
    
  <?php if($site->exists($site->getStyles())) { ?>
  <style>
    <?php echo $site->getStyles()?>
  </style>
  <?php } ?>
  
  <base target="_parent">

</head>

<body>

<?php if($preview_mode && $_SERVER['SCRIPT_NAME'] != '/modal.php') { ?>
	<div class="rezgo-preview-mode"><i class="fa fa-eye"></i>&nbsp;you are in preview mode</div>
<?php } ?>

<?php 
if ( SERVER_ENVIRONMENT == 'beta') { 
	$redirect_link = str_replace('beta.', '', $_SERVER['HTTP_REFERER']);
?>
<div class="alert alert-danger" role="alert" style="margin-top:10px; font-size: larger;">
  <strong>Warning!</strong>&nbsp;
  You are currently using a sandbox version of this site.  DO NOT use this site for live bookings.&nbsp;
  <a href="<?php echo $redirect_link; ?>">Please go here instead.</a>
</div>
<?php 
} 
?>