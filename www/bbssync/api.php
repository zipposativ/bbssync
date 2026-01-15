<?php

  include_once("src/api-main.php");
  require_once("./lib/tcpdf/tcpdf.php");
  $config = include("./config.php");
  // load Error Handler => return a 503 Bad Gateway Error if Script fails
  error_handler();


  //read Input & load payload
  $raw = file_get_contents("php://input");
  $payload = json_decode($raw, true);
  $token = $payload['token'] ?? '';
  // check if payload == json
  if (json_last_error() !== JSON_ERROR_NONE) {
      http_response_code(400);
      echo 'No JSON';
      exit;
  }
  // check token
  if ($token !== $config['App']['token']) {
      http_response_code(401);
      echo 'Ungültiger Token';
      exit;
  }


  // check payload if it´s empty
  if(!empty($payload['data'])){
    // check if synctype is ldap or eid
    if($config['App']['synctype'] == "ldap"){
      //include AD Lib
      require_once("./src/ad.php");
      //create LDAP bind & connection
      $ldap = new ldap($config['LDAP']['host'], $config['LDAP']['binduser'], $config['LDAP']['bindpassword'], $config['LDAP']['basedn']);
      $conn = $ldap->connect();
      // explode user data
      foreach ($payload['data'] as $userdata){
        //check data || all values needed
        if(!empty($userdata['ID']) AND !empty($userdata['VNAME']) AND !empty($userdata['NNAME']) AND !empty($userdata['KL_NAME'])){
          // define User ID
          $id = $userdata['ID'];
          // create array of german umlauts
          $umlauts = array('ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ß' => 'ss');
          //define vars for names used in Displayname/Description
          $vname_nom = $userdata['VNAME'];
          $nname_nom = $userdata['NNAME'];
          //define vars for names used for shorting
          $vnameOriginal = $userdata['VNAME'];
          $nnameOriginal = $userdata['NNAME'];
          // change names to max 8 signs & update umlauts
          $vname = substr(strtr(strtolower($vnameOriginal), $umlauts), 0, 8);
          $nname = substr(strtr(strtolower($nnameOriginal), $umlauts), 0, 8);
          //define groupname
          $kl_name =  $userdata['KL_NAME'];
          //define array for replace variables from adminpanel
          $replace = [
            '{{vorname}}'  => $vname,
            '{{nachname}}' => $nname,
            '{{klasse}}'   => $kl_name,
            '{{uid}}'      => $id,
          ];
          // create var user with replaced content
          foreach ($config['User'] as $key => $value) {
            if (!is_string($value)) {
              $user[$key] = $value;
              continue;
            }
            // only for displayname & decription
            if ($key === 'displayname' OR $key === 'description') {
              $replaceOther = [
                '{{vorname}}'  => $vname_nom,
                '{{nachname}}' => $nname_nom,
                '{{klasse}}'   => $kl_name,
                '{{uid}}'      => $id,
              ];
              $user[$key] = str_replace(array_keys($replaceOther), array_values($replaceOther), $value);
            } else {
              $user[$key] = str_replace(array_keys($replace), array_values($replace), $value);
            }
          }
          //define user E-Mail Adress
          $mail = $user['mailUser']."@".$config['User']['mailDomain'];
          //check if user exist by id
          if($ldap->userExist($conn, $id)){
            //if user exist and state is add => Change User values
            if($userdata['status'] == "add"){
              $change_user = $ldap->changeUser($conn, $user['samaccountname'], $user['displayname'], $vname_nom, $nname_nom, $user['description'], $mail, $id, $user['group'], $config['LDAP']['domain']);
            }elseif($userdata['status'] == "remove"){
              //if user exist and state is remove => delete or disable user
              if($config['User']['disabled'] == "true"){
                $disable_user = $ldap->deleteUser($conn, $id, true);
              }else{
                $delete_user = $ldap->deleteUser($conn, $id, false);
              }
            }
          }else{
            // if user arn´t exist create user, group,
            if($userdata['status'] == "add"){
              $password = password();
              $create_ou = $ldap->createOU($conn, $user['group']);
              $create_ou = $ldap->createGroup($conn, $user['group']);
              $create_user = $ldap->createUser($conn, $user['samaccountname'], $password, $user['displayname'], $vname_nom, $nname_nom, $user['description'], $mail, $id, $user['group'], $config['LDAP']['domain']);
              $add_member = $ldap->addMember($conn, $user['group'], $id);
              // Create user pdf
              $html = '
                <h1>Zugangsdaten</h1>
                <p>Hallo '.$vname_nom.' '.$nname_nom.',</p>
                <p>mit diesem Schreiben erhalten Sie Ihre persönlichen Zugangsdaten für Ihren Account:</p>
                <br>
                <table cellpadding="5" cellspacing="0" style="width: 100%;" border="0">
                  <tr style="background-color: #cccccc; padding:5px;">
                    <td style="padding:5px;"><b>Bezeichnung</b></td>
                    <td style="padding:5px;"><b>Wert</b></td>
                  </tr>
                  <tr>
                    <td><b>Benutzername</b></td>
                    <td>'.$user['samaccountname'].'</td>
                  </tr>
                  <tr>
                    <td><b>Passwort</b></td>
                    <td>'.$password.'</td>
                  </tr>
                </table>
                <br>
                <p>Bitte melden Sie sich zeitnah an und ändern Sie das Passwort nach der ersten Anmeldung. Wählen Sie ein sicheres Passwort und bewahren Sie es sorgfältig auf.</p>
                <p>Die Zugangsdaten sind personenbezogen und dürfen nicht an Dritte weitergegeben werden. Sie sind für alle Aktivitäten verantwortlich, die über Ihren Account erfolgen.</p>
                <p>Bei Fragen oder Problemen wenden Sie sich bitte an die zuständige Ansprechperson.</p>
              ';
              $basePath = __DIR__ . '/userdata/' . $user['group'];

              if (!is_dir($basePath)) {
                if (!mkdir($basePath, 0777, true)) {

                }
              }
              $pdfName = __DIR__."/userdata/".$user['group']."/".$vname_nom."_".$nname_nom.".pdf";
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor("BBSSync Server - Simon Zipperling");
              $pdf->SetTitle("Benutzerzugang");
              $pdf->SetSubject("Zugangsdaten für den Benutzer $vname_nom $nname_nom aus der Klasse ".$user['group'].".");
              $pdf->setPrintHeader(false);
              $pdf->setPrintFooter(false);
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              $pdf->SetFont('helvetica', '', 10);
              $pdf->AddPage();
              $pdf->writeHTML($html, true, false, true, false, '');
              $pdf->Output($pdfName, 'F');
              return "User Created";
            }
          }
        }
      }
      // Disconnect from LDAP Server
      $disconnect = $ldap->disconnect($conn);
    }elseif($config['App']['synctype'] == "eid"){
      // //include
      // require_once("./src/eid.php");
      // $entra = new eid($config['EntraID']['tenantID'], $config['EntraID']['clientID'] ,$config['EntraID']['clientSecret']);
      // foreach ($payload['data'] as $userdata){
      //   //check data || all values needed
      //   if(!empty($userdata['ID']) AND !empty($userdata['VNAME']) AND !empty($userdata['NNAME']) AND !empty($userdata['KL_NAME'])){
      //     // define User ID
      //     $id = $userdata['ID'];
      //     // create array of german umlauts
      //     $umlauts = array('ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ß' => 'ss');
      //     //define vars for names used in Displayname/Description
      //     $vname_nom = $userdata['VNAME'];
      //     $nname_nom = $userdata['NNAME'];
      //     //define vars for names used for shorting
      //     $vnameOriginal = $userdata['VNAME'];
      //     $nnameOriginal = $userdata['NNAME'];
      //     // change names to max 8 signs & update umlauts
      //     $vname = substr(strtr(strtolower($vnameOriginal), $umlauts), 0, 8);
      //     $nname = substr(strtr(strtolower($nnameOriginal), $umlauts), 0, 8);
      //     //define groupname
      //     $kl_name =  $userdata['KL_NAME'];
      //     //define array for replace variables from adminpanel
      //     $replace = [
      //       '{{vorname}}'  => $vname,
      //       '{{nachname}}' => $nname,
      //       '{{klasse}}'   => $kl_name,
      //       '{{uid}}'      => $id,
      //     ];
      //     // create var user with replaced content
      //     foreach ($config['User'] as $key => $value) {
      //       if (!is_string($value)) {
      //         $user[$key] = $value;
      //         continue;
      //       }
      //       // only for displayname & decription
      //       if ($key === 'displayname' OR $key === 'description') {
      //         $replaceOther = [
      //           '{{vorname}}'  => $vname_nom,
      //           '{{nachname}}' => $nname_nom,
      //           '{{klasse}}'   => $kl_name,
      //           '{{uid}}'      => $id,
      //         ];
      //         $user[$key] = str_replace(array_keys($replaceOther), array_values($replaceOther), $value);
      //       } else {
      //         $user[$key] = str_replace(array_keys($replace), array_values($replace), $value);
      //       }
      //     }
      //     //Arbeitsebene
      //   }
      // }
    }
  }


?>
