<?php

  function install(){
    $config = include("config.php");
    if($config['App']['installed'] == "false"){
      if(isset($_POST['install_send'])){
        $username = $_POST['install_username'];
        $password = hash('sha256', $_POST['install_password']);
        $password2 = hash('sha256', $_POST['install_password2']);
        if($password == $password2){
          $config['App']['localadmin'] = $username;
          $config['App']['localpassword'] = $password;
          $config['App']['installed'] = "true";
          $config['App']['token'] = genToken();

          $update  = "<?php\nreturn " . var_export($config, true) . ";\n?>";
          file_put_contents('config.php', $update);

          echo '<meta http-equiv="refresh" content="0; URL=?">';
        }else{
          ?>
            <article class="message is-danger">
              <div class="message-header">
                <p>Error</p>
              </div>
              <div class="message-body">
                Passwort stimmt nicht überein.
              </div>
            </article>
          <?php
        }
      }
      ?>
        <form method="post" autocomplete="off">
          <article class="message is-success">
            <div class="message-header">
              <p>Willkommen - Installer</p>
            </div>
            <div class="message-body">
              Für die Installation, tragen Sie bitte einen Benutzernamen und ein Password ein.
            </div>
          </article>
          <label class="label">Account</label>
          <input type="text" class="input" name="install_username" placeholder="Account" required>
          <label class="label">Passwort</label>
          <input type="password" class="input" name="install_password" placeholder="Passwort" required>
          <label class="label">Passwort wiederholen</label>
          <input type="password" class="input" name="install_password2" placeholder="Passwort" required>
          <br>
          <br>
          <input type="submit" class="button is-success is-fullwidth has-text-white" name="install_send"value="Registrieren">
        </form>
      <?php
      die();
    }
  }

  function login(){
    $config = include("config.php");
    if(!isset($_SESSION['login']) && $config['App']['installed'] == "true"){
      if(isset($_POST['login_send'])){
        $username = $_POST['login_username'];
        $password = hash('sha256', $_POST['login_password']);
        if($username == $config['App']['localadmin'] && $password == $config['App']['localpassword']){
          $_SESSION['login'] = true;
          echo '<meta http-equiv="refresh" content="0; URL=?">';
        }else{
          ?>
          <article class="message is-danger">
            <div class="message-header">
              <p>Error</p>
            </div>
            <div class="message-body">
              Account oder Passwort falsch.
            </div>
          </article>
          <?php
        }
      }
      ?>
        <form method="post" autocomplete="off">
          <label class="label">Account</label>
          <input type="text" class="input" name="login_username" placeholder="Account" required>
          <label class="label">Passwort</label>
          <input type="password" class="input" name="login_password" placeholder="Passwort" required>
          <br>
          <br>
          <input type="submit" class="button is-success is-fullwidth has-text-white" name="login_send"value="Anmelden">
        </form>
      <?php
      die();
    }
  }

  function logout(){
    if(isset($_GET['logout'])){
      session_destroy();
      echo '<meta http-equiv="refresh" content="0; URL=?">';
    }
  }


  function stats(){
    $config = include("config.php");
    ?>
      <h3 class="title is-3 has-text-weight-light	">Konnektivitätscheck</h3>
      <div class="field has-addons">
        <p class="control"><a class="button is-static">Konfigurationsupdate</a></p>
        <p class="control"><span class="input"><?php echo $config['App']['configUpdate']; ?></span></p>
      </div>
      <!--<div class="field has-addons">
        <p class="control"><a class="button is-static">Letzte Agent Verbindung</a></p>
        <p class="control"><span class="input"><?php echo $config['stats']['lastAgentCall']; ?></span></p>
      </div>-->
      <div class="field has-addons">
        <p class="control"><a class="button is-static">Verbindungstyp</a></p>
        <p class="control"><span class="input"><?php if($config['App']['synctype'] == "ldap"){echo "Active Directory";}elseif($config['App']['synctype'] == "eid"){echo "Entra ID Connector";}?></span></p>
      </div>
      <!--<div class="field has-addons">
        <p class="control"><a class="button is-static">Synchronisierte Benutzer</a></p>
        <p class="control"><span class="input"><?php echo $config['stats']['syncedUser']; ?></span></p>
      </div>
      <div class="field has-addons">
        <p class="control"><a class="button is-static">LDAP Status</a></p>
        <p class="control"><?php if($config['stats']['ldapState'] == true){echo '<span class="input has-background-success has-text-white"><i class="fa-solid fa-check"></i></span>';}else{echo '<span class="input has-background-danger has-text-white"><i class="fa-solid fa-x"></i></span>';}?></p>
      </div>
      <div class="field has-addons">
        <p class="control"><a class="button is-static">Entra ID Connector Status</a></p>
        <p class="control"><?php if($config['stats']['eidState'] == true){echo '<span class="input has-background-success has-text-white"><i class="fa-solid fa-check"></i></span>';}else{echo '<span class="input has-background-danger has-text-white"><i class="fa-solid fa-x"></i></span>';}?></p>
      </div>-->
    <?php
  }

  function system_settings(){
    $config = include("config.php");
    ?>
      <h3 class="title is-3 has-text-weight-light	">System Settings</h3>
      <label class="label">API-Token</label>
      <div class="notification icon-text">
        <span class="icon has-text-warning">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </span>
        <span>Tragen Sie den API-Token in die Konfiguration des BBSSync Windows Agent ein.</span>
      </div>
      <input class="input" type="text" placeholder="abc1234" disabled value="<?php echo $config['App']['token']; ?>">
      <label class="label">Sync-Type</label>

      <input type="radio" name="app_synctype" id="ad_radio" value="ldap" <?php if($config['App']['synctype'] == "ldap"){echo "checked";} ?>>
      <label class="radio" for="ad_radio">Microsoft/OpenLDAP Active Directory</label>
      <br/>
      <input type="radio" name="app_synctype" id="mseic_radio" value="eid" <?php if($config['App']['synctype'] == "eid"){echo "checked";} ?>>
      <label class="radio" for="mseic_radio">Microsoft Entra ID Connector</label>

      <div id="ad" class="section">
        <h3 class="title is-3 has-text-weight-light">Microsoft/OpenLDAP Active Directory</h3>
        <label class="label">LDAP Host</label>
        <input class="input" name="ldap_host" type="text" placeholder="ldaps://127.0.0.1:636" value="<?php echo $config['LDAP']['host']; ?>">
        <label class="label">Bind User CN</label>
        <input class="input" name="ldap_binduser" type="text" placeholder="Bind User CN" value="<?php echo $config['LDAP']['binduser']; ?>">
        <label class="label">Bind User Password</label>
        <input class="input" name="ldap_bindpassword" type="password" placeholder="Bind User Password" value="<?php echo $config['LDAP']['bindpassword']; ?>">
        <label class="label">Base Directory</label>
        <input class="input" name="ldap_basedn" type="text" placeholder="OU=example,DC=example,DC=com" value="<?php echo $config['LDAP']['basedn']; ?>">
        <label class="label">Domain/Realm</label>
        <input class="input" name="ldap_domain" type="text" placeholder="example.com" value="<?php echo $config['LDAP']['domain']; ?>">
      </div>

      <div id="mseic" class="section">
        <h3 class="title is-3 has-text-weight-light">Microsoft Entra ID Connector</h3>
        <label class="label">Graph API URL</label>
        <input class="input" type="text" placeholder="https://graph.microsoft.com/v1.0/users" disabled value="<?php echo $config['EntraID']['baseURL']; ?>">
        <label class="label">Microsoft Token API URL</label>
        <input class="input" type="text" placeholder="https://login.microsoftonline.com" disabled value="<?php echo $config['EntraID']['baseLoginURL']; ?>">
        <label class="label">Tenant ID</label>
        <input class="input" name="eid_tenantid" type="text" placeholder="Tenant ID" value="<?php echo $config['EntraID']['tenantID']; ?>">
        <label class="label">Client ID</label>
        <input class="input" name="eid_clientid" type="text" placeholder="Client ID" value="<?php echo $config['EntraID']['clientID']; ?>">
        <label class="label">Client Secret</label>
        <input class="input" name="eid_clientsecret" type="text" placeholder="Client Secret" value="<?php echo $config['EntraID']['clientSecret']; ?>">
      </div>
    <?php
  }

  function user_settings(){
    $config = include("config.php");
    ?>
      <h3 class="title is-3 has-text-weight-light	">User Settings</h3>
      <div class="notification icon-text">
        <span class="icon has-text-warning">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </span>
        <span>Als Primärschlüssel für jeden Account wird das Fax Feld genutzt (außer bei Microsoft Entra ID Connector). Die einmalige Schüler ID <strong>{{uid}}</strong> die in das Fax Feld eingetragen wird, ist als EmployeeID im Active Directory wiederzufinden.</span>
      </div>
      <label class="label">SamAccountName & UserPrincipalName (UPN)</label>
      Verfügbare Variabeln:
      <span class="tag">{{vorname}}</span>
      <span class="tag">{{nachname}}</span>
      <span class="tag">{{klasse}}</span>
      <span class="tag">{{uid}}</span>
      <input class="input" name="user_samaccountname" type="text" placeholder="{{vorname}}.{{nachname}}" value="<?php echo $config['User']['samaccountname']; ?>">
      <label class="label">Anzeigename</label>
      Verfügbare Variabeln:
      <span class="tag">{{vorname}}</span>
      <span class="tag">{{nachname}}</span>
      <span class="tag">{{klasse}}</span>
      <span class="tag">{{uid}}</span>
      <input class="input" name="user_displayname" type="text" placeholder="{{nachname}}, {{vorname}}, {{klasse}}" value="<?php echo $config['User']['displayname']; ?>">
      <label class="label">Klassenbezeichnung (Eigene Organisationseinheit)</label>
      Verfügbare Variabeln:
      <span class="tag">{{klasse}}</span>
      <input class="input" name="user_group" type="text" placeholder="{{klasse}}" value="<?php echo $config['User']['group']; ?>">
      <label class="label">Benutzer Beschreibung</label>
      Verfügbare Variabeln:
      <span class="tag">{{vorname}}</span>
      <span class="tag">{{nachname}}</span>
      <span class="tag">{{klasse}}</span>
      <span class="tag">{{uid}}</span>
      <input class="input" name="user_description" type="text" placeholder="{{klasse}}" value="<?php echo $config['User']['description']; ?>">
      <label class="label">User E-Mail-Adresse</label>
      Verfügbare Variabeln:
      <span class="tag">{{vorname}}</span>
      <span class="tag">{{nachname}}</span>
      <span class="tag">{{klasse}}</span>
      <span class="tag">{{uid}}</span>
      <div class="field has-addons is-fullwidth">
        <p class="control"><input class="input" type="text" name="user_mail_user" placeholder="{{vorname}}.{{nachname}}" value="<?php echo $config['User']['mailUser']; ?>"></p>
        <p class="control"><a class="button is-static">@</a></p>
        <p class="control"><input class="input" type="text" name="user_mail_domain" placeholder="example.com" value="<?php echo $config['User']['mailDomain']; ?>"></p>
      </div>
      <label class="label">Benutzer Sperren</label>
      <div class="notification icon-text">
        <span class="icon has-text-warning">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </span>
        <span>Standardmäßig werden alle Benutzer, die in Abgänger geschoben werden gelöscht. Durch das aktivieren dieser Funktion, werden Abgänger nur deaktiviert.</span>
      </div>
      <input name="user_disabled" type="checkbox" id="user_disabled" value="disabled" <?php if($config['User']['disabled'] == "true"){echo "checked";}?>>
      <label for="user_disabled">Benutzer nur deaktivieren</label>
    <?php
  }

  function server_settings(){
    $config = include("config.php");
    ?>
      <h3 class="title is-3 has-text-weight-light	">Server Settings</h3>
      <div class="notification icon-text">
        <span class="icon has-text-warning">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </span>
        <span>Bitte geben Sie hier das neue Passwort für Ihren Benutzer ein. Lassen Sie das Feld leer, wenn Sie das Passwort nicht ändern möchten.</span>
      </div>
      <div class="field">
        <p class="control has-icons-left">
          <input class="input" name="app_localadmin" type="text" placeholder="Benutzername" value="<?php echo $config['App']['localadmin']; ?>">
          <span class="icon is-small is-left">
            <i class="fa-solid fa-user"></i>
          </span>
        </p>
      </div>
      <div class="field">
        <p class="control has-icons-left">
          <input class="input" name="app_localpassword" type="password" placeholder="Neues Passwort">
          <span class="icon is-small is-left">
            <i class="fas fa-lock"></i>
          </span>
        </p>
      </div>
      <div class="field">
        <p class="control has-icons-left">
          <input class="input" name="app_localpassword2" type="password" placeholder="Neues Passwort wiederholen">
          <span class="icon is-small is-left">
            <i class="fas fa-lock"></i>
          </span>
        </p>
      </div>
    <?php
  }

  function controls_settings(){
    $config = include("config.php");
    if(isset($_POST['app_save'])){
      $config['App']['synctype'] = $_POST['app_synctype'];
      $config['LDAP']['host'] = $_POST['ldap_host'];
      $config['LDAP']['binduser'] = $_POST['ldap_binduser'];
      $config['LDAP']['bindpassword'] = $_POST['ldap_bindpassword'];
      $config['LDAP']['basedn'] = $_POST['ldap_basedn'];
      $config['LDAP']['domain'] = $_POST['ldap_domain'];
      $config['EntraID']['tenantID'] = $_POST['eid_tenantid'];
      $config['EntraID']['clientID'] = $_POST['eid_clientid'];
      $config['EntraID']['clientSecret'] = $_POST['eid_clientsecret'];
      $config['User']['samaccountname'] = $_POST['user_samaccountname'];
      $config['User']['displayname'] = $_POST['user_displayname'];
      $config['User']['group'] = $_POST['user_group'];
      $config['User']['description'] = $_POST['user_description'];
      $config['User']['mailUser'] = $_POST['user_mail_user'];
      $config['User']['mailDomain'] = $_POST['user_mail_domain'];
      $user_disabled = $_POST['user_disabled'];
      $config['App']['localadmin'] = $_POST['app_localadmin'];
      $app_localpassword = $_POST['app_localpassword'];
      $app_localpassword2 = $_POST['app_localpassword2'];

      if($user_disabled == "disabled"){
        $config['User']['disabled'] = "true";
      }else{
        $config['User']['disabled'] = "false";
      }

      $config['App']['configUpdate'] = date("H:i - d.m.Y");

      if($app_localpassword == $app_localpassword2 && !empty($app_localpassword)){
        $config['App']['localpassword'] = hash('sha256', $app_localpassword);
      }

      $update  = "<?php\nreturn " . var_export($config, true) . ";\n?>";
      file_put_contents('config.php', $update);
      echo '<meta http-equiv="refresh" content="0; URL=?">';
    }
    ?>
      <h3 class="title is-3 has-text-weight-light	">Controls</h3>
      <input type="submit" name="app_save" class="button is-success is-fullwidth has-text-white" name="save"value="Speichern">
      <br>
      <a href="?logout" class="button is-danger is-fullwidth has-text-white">Abmelden</a>
    <?php
  }

  function genToken($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $randomString;
  }
?>
