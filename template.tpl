<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="index.css">
<meta name="description" content="Post reply">
<title>IP Adress Mask for SMF <?php echo $title; ?></title>
<meta name="robots" content="noindex">
</head>
<body>
<div id="wrapper" style="width: 700px;">
  <div id="header">
    <div id="logo">
      <h1 align="center">IP Adress Mask for SMF</h1>
    </div>
  </div>
  <div id="bodyarea">
    <form action="<?php echo $action; ?>" method="post" class="flow_hidden">
      <div class="cat_bar">
        <h3 class="catbg"><?php echo $small_title; ?></h3>
      </div>
      <div> <span class="upperframe"><span></span></span>
        <div class="roundframe">
          <div class="errorbox" style="display:<?php echo $display_errors; ?>;" id="errors">
            <dl>
              <dt> <strong id="error_serious">The following error or errors occurred while posting this message: </strong> </dt>
              <dt class="error" id="error_list"> <?php echo $errors; ?></dt>
            </dl>
          </div>
          <?php echo $content; ?>
        </div>
        <span class="lowerframe"> <span> </span> </span> </div>
      <br class="clear">
    </form>
  </div> 
  <div id="footerarea">Copyright &copy; <a target="_blank" href="http://insecurity.ro/forum">InSecurity Romania</a> 2006 - 2012</div>
</div>
</body>
</html>