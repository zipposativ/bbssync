<?php
  session_start();
  include_once("src/main.php");
  logout();
?>

<!DOCTYPE html>
<html lang="de" data-theme="light">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>BBSSync</title>

  	<link rel="icon" href="image/logo.png">
  	<link rel="apple-touch-icon" href="image/logo.png">
  	<link rel="icon" type="image/x-icon" href="image/logo.ico">

    <link rel="stylesheet" href="lib/bulma/css/bulma.min.css">
    <link rel="stylesheet" href="lib/fontsawesome/css/all.min.css">

		<link rel="stylesheet" href="style/style.css?id<?php echo uniqid(); ?>">
  	<!-- <link rel="stylesheet" media="only screen and (max-device-width: 680px)" href="style/mobile.css?id<?php echo uniqid(); ?>"> -->



  	<meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="BBSSync">

    <!-- <link rel="manifest" href="manifest.json"> -->

    <meta name="apple-mobile-web-app-status-bar-style" content="white">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1, minimal-ui">
    <meta name="format-detection" content="telephone=no">
  	<meta charset="utf-8">

  	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  	<meta http-equiv="Pragma" content="no-cache" />
  	<meta http-equiv="Expires" content="0" />



  </head>
  <body id="body">
    <div id="main">
      <div id="app" class="content">
        <h1 class="title is-1 has-text-weight-light	">BBSSync Adminpanel</h1>
        <?php install(); ?>
        <?php login(); ?>
        <!-- Konnektivitätscheck ANFANG -->
          <?php stats(); ?>
        <!-- Konnektivitätscheck ENDE -->

        <form method="post" autocomplete="off">
          <!-- System Settings Anfang -->
            <?php system_settings(); ?>
          <!-- System Settings ENDE -->

          <!-- User Settings ANFANG -->
            <?php user_settings(); ?>
          <!-- User Settings ENDE -->

          <!-- Server Settings ANFANG -->
            <?php server_settings(); ?>
          <!-- Server Settings ENDE -->

          <!-- Controls Anfang -->
            <?php controls_settings(); ?>
          <!-- Controls ENDE -->
        </form>
        <br/>

        <!-- Fußzeile ANFANG -->
        <footer class="footer">
          <div class="content has-text-centered">
            <p>
              BBSSync Server & BBSSync Windows Agent werden von Simon Zipperling (<a class="link" target="_blank" href="https://simon.zipperling.net">simon.zipperling.net</a>) entwickelt und sind geistiges Eigentum von Simon Zipperling. Die Nutzung ist ausschließlich für niedersächsiche Berufsschulen Schulen vorbehalten und darf nicht Kommerziel von dritten genutzt werden.
            </p>
          </div>
        </footer>
        <!-- Fußzeile ENDE -->
      </div>
    </div>
  </body>
</html>
