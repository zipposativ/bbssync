<?php
  class ldap{
    private string $host;
    private string $binduser;
    private string $bindpassword;
    private string $basedn;

    /*
      @author:        Simon Zipperling
      @created:       17.12.2025
      @description:   start lib
    */
    public function __construct($host, $binduser, $bindpassword, $basedn){
      $this->host = $host;
      $this->binduser = $binduser;
      $this->bindpassword = $bindpassword;
      $this->basedn = $basedn;
    }

    /*
      @author:        Simon Zipperling
      @created:       17.12.2025
      @description:   connect to the LDAP Server
    */
    public function connect(){
      $connect = ldap_connect($this->host);
      ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);
      ldap_set_option(NULL, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
      $bind = ldap_bind($connect, $this->binduser, $this->bindpassword);
      if($bind){
        return $connect;
      }else{
        return false;
      }
    }

    /*
      @author:        Simon Zipperling
      @created:       17.12.2025
      @description:   disconnect from LDAP Server
    */
    public function disconnect($bind){
      if(ldap_unbind($bind)){
        return true;
      }else{
        return false;
      }
    }

    /*
      @author:        Simon Zipperling
      @created:       17.12.2025
      @description:   create ou for a class in AD
    */
    public function createOU($ldap, $group){
      //Filtersettings
      $filter = "(ou=$group)";
      $search = ldap_search($ldap, $this->basedn, $filter);
      $entries = ldap_get_entries($ldap, $search);
      if($entries["count"] == 0){
        //OU Base DN
        $dn = "OU=".$group.",".$this->basedn;
        // Values for OU
        $entry = [];
        $entry["objectClass"][0] = "top";
        $entry["objectClass"][1] = "organizationalUnit";
        $entry["ou"] = $group;
        //Create OU
        if(ldap_add($ldap, $dn, $entry)){
          return true;
        }else{
          return false;
        }
      }else{
        return "OU $group already extist";
      }
    }


    /*
      @author:        Simon Zipperling
      @created:       17.12.2025
      @description:   create Security Group for user in OU
    */
    public function createGroup($ldap, $group){
      // Filtersettings
      $filter = "(sAMAccountName=$group)";
      $search = ldap_search($ldap, $this->basedn, $filter);
      $entries = ldap_get_entries($ldap, $search);
      if($entries["count"] == 0){
        // Base OU & DN for the Group
        $dnbase = "OU=".$group.",".$this->basedn;
        $dn = "CN=$group," . $dnbase;
        // Values for the Group
        $entry = [];
        $entry["objectClass"] = ["top", "group"];
        $entry["sAMAccountName"] = $group;
        $entry["groupType"] = -2147483646;
        // Create Group
        if(ldap_add($ldap, $dn, $entry)){
          return true;
        }else{
          return false;
        }
      }else{
        return "Group (Secure) $group already exist";
      }
    }

    /*
      @author:        Simon Zipperling
      @created:       17.12.2025
      @description:   create the user
    */
    public function createUser($ldap, $samaccountname, $password, $displayname, $firstname, $lastname, $description, $mail, $id, $group, $domain){
      // Filtersettings for user
      $filter = "(sAMAccountName=$samaccountname)";
      $search = ldap_search($ldap, $this->basedn, $filter);
      $entries = ldap_get_entries($ldap, $search);
      if($entries["count"] == 0){
        // Values for new user
        $dn = "CN=$samaccountname,OU=$group,$this->basedn";
        $entry = [];
        $entry["objectClass"] = ["top", "person", "organizationalPerson", "user"];
        $entry["cn"] = $samaccountname;
        $entry["givenName"] = $firstname;
        $entry["sn"] = $lastname;
        $entry["displayName"] = $displayname;
        $entry["sAMAccountName"] = $samaccountname;
        $entry["userPrincipalName"] = "$samaccountname@$domain";
        $entry["mail"] = $mail;
        $entry["description"] = $description;
        $entry["employeeID"] = $id;
        $entry["userAccountControl"] = 512;
        //create User
        if (ldap_add($ldap, $dn, $entry)) {
          // Values for User Password
          $quoted = "\"" . $password . "\"";
          $hash_password = mb_convert_encoding($quoted, "UTF-16LE");
          $passwd["unicodePwd"] = $hash_password;
          // Set new Password
          if(ldap_modify($ldap, $dn, $passwd)){
            return true;
          }
        }else{
          return false;
        }
      }else{
        return false;
      }
    }

    /*
      @author:        Simon Zipperling
      @created:       17.12.2025
      @description:   remove/disable the user by id
    */
    public function deleteUser($ldap, $id, $disable = false){
      // Filtersettings
      $filter = "(employeeID=$id)";
      $search = ldap_search($ldap, $this->basedn, $filter);
      $entries = ldap_get_entries($ldap, $search);
      if($entries["count"] == 1){
        // Define DN for user
        $dn = $entries[0]['distinguishedname'][0];
        if($disable == false){
          // Delete user if settings allow
          if(ldap_delete($ldap, $dn)){
            return true;
          }
        }elseif($disable == true){
          // Disable User if settings allow | Set UAC
          $uac = (int)$entries[0]['useraccountcontrol'][0];
          $uac |= 2;
          $entry['userAccountControl'] = $uac;
          if(ldap_mod_replace($ldap, $dn, $entry)){
            return true;
          }
        }
      }else{
        return false;
      }
    }

    /*
      @author:        Simon Zipperling
      @created:       17.12.2025
      @description:   check if user exist | search via id
    */
    public function userExist($ldap, $id){
      // Filtersettings
      $filter = "(employeeID=$id)";
      $search = ldap_search($ldap, $this->basedn, $filter);
      $entries = ldap_get_entries($ldap, $search);
      if($entries["count"]==1){
        // Return user exist
        return true;
      }else{
        return false;
      }
    }

    /*
      @author:        Simon Zipperling
      @created:       17.12.2025
      @description:   Add Member to a Security Group
    */
    public function addMember($ldap, $group, $id){
      // Filtersettings
      $filter = "(employeeID=$id)";
      $search = ldap_search($ldap, $this->basedn, $filter);
      $entries = ldap_get_entries($ldap, $search);
      if($entries["count"] == 1){
        // Define Group DN & read User-DN & sAMAccountName
        $groupDn = "CN=$group,OU=$group,$this->basedn";
        $userDn = $entries[0]['distinguishedname'][0];
        $samaccountname = $entries[0]['samaccountname'][0];
        $entry = [];
        $entry["member"] = $userDn;
        // Add Member
        if(@ldap_mod_add($ldap, $groupDn, $entry)){
          return true;
        }else {
          return false;
        }
      }else{
        return false;
      }
    }

    /*
      @author:        Simon Zipperling
      @created:       17.12.2025
      @description:   remove Member via user id in a Security Group
    */
    public function delMember($ldap, $group, $id){
      // Filtersettings
      $filter = "(employeeID=$id)";
      $search = ldap_search($ldap, $this->basedn, $filter);
      $entries = ldap_get_entries($ldap, $search);
      if($entries["count"] == 1){
        // Define Group DN & read User-DN & sAMAccountName
        $groupDn = "CN=$group,OU=$group,$this->basedn";
        $userDn = $entries[0]['distinguishedname'][0];
        $samaccountname = $entries[0]['samaccountname'][0];
        $entry = [];
        $entry["member"] = $userDn;
        //Delete Member if it´s possable
        if(@ldap_mod_del($ldap, $groupDn, $entry)){
          return true;
        }else {
          return false;
        }
      }else{
        return false;
      }
    }

    /*
      @author:        Simon Zipperling
      @created:       17.12.2025
      @description:   change a user by user id
    */
    public function changeUser($ldap, $samaccountname, $displayname, $firstname, $lastname, $description, $mail, $id, $group, $domain){
      //Filtersettings
      $filter = "(employeeID=$id)";
      $search = ldap_search($ldap, $this->basedn, $filter);
      $entries = ldap_get_entries($ldap, $search);
      if($entries["count"]==1){
        // Define User Vaulues
        $old_sam = $entries[0]['samaccountname'][0];
        $old_dn = $entries[0]['distinguishedname'][0];
        $old_cn = $entries[0]['cn'][0];
        $old_upn = $entries[0]['userprincipalname'][0];
        // Create Old Group name
        $old_group_base = str_replace("CN=$old_sam,", "", $old_dn);
        $old_group_explode = explode(",", $old_group_base);
        $old_group_name = str_replace("OU=", "", $old_group_explode[0]);
        // Create new User Values & Group path
        $new_user_dn = "CN=$samaccountname,OU=$group,$this->basedn";
        $new_cn = "CN=$samaccountname";
        $new_ou_base = "OU=$group,$this->basedn";

        //Create OU & Secure Group
        $this->createOU($ldap, $group);
        $this->createGroup($ldap, $group);

        //Change Secure Group
        $this->delMember($ldap, $old_group_name, $id);
        $this->addMember($ldap, $group, $id);

        //Change User VALUES
        $entry = [];
        $entry["givenName"] = $firstname;
        $entry["sn"] = $lastname;
        $entry["displayName"] = $displayname;
        $entry["mail"][] = $mail;
        $entry["description"] = $description;
        $entry["userAccountControl"] = 512;
        //Change OU
        ldap_rename($ldap, $old_dn, $new_cn, $new_ou_base, true);
        //change Values
        ldap_mod_replace($ldap, $new_user_dn, $entry);
        //change sAMAccountName
        $value["samaccountname"] = $samaccountname;
        $value["userprincipalname"] = $samaccountname."@".$domain;
        ldap_mod_replace($ldap, $new_user_dn, $value);
        return true;
      }else{
        return false;
      }
    }
  }
?>
