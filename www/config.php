<?php
return array (
  'App' =>
  array (
    'installed' => 'false',
    'token' => '',
    'localadmin' => '',
    'localpassword' => '',
    'logfile' => 'bbssync.log',
    'synctype' => 'ldap',
    'configUpdate' => '',
  ),
  'stats' =>
  array (
    'lastAgentCall' => '',
    'syncedUser' => 0,
    'ldapState' => true,
    'eidState' => false,
  ),
  'LDAP' =>
  array (
    'host' => '',
    'binduser' => '',
    'bindpassword' => '',
    'basedn' => '',
    'domain' => '',
  ),
  'EntraID' =>
  array (
    'tenantID' => '',
    'clientID' => '',
    'clientSecret' => '',
    'baseURL' => 'https://graph.microsoft.com/v1.0/users',
    'baseLoginURL' => 'https://login.microsoftonline.com',
  ),
  'User' =>
  array (
    'samaccountname' => '{{vorname}}.{{nachname}}',
    'displayname' => '{{nachname}}, {{vorname}}, {{klasse}} ',
    'group' => '{{klasse}}',
    'description' => '{{klasse}}',
    'mailUser' => '{{vorname}}.{{nachname}}',
    'mailDomain' => 'example.com',
    'disabled' => 'false',
  )
);
?>
