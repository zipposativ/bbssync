<?php
  class ldap{
    private string $tenantId;
    private string $clientId;
    private string $clientSecret;
    private ?string $token = null;

    /*
      @author:        Simon Zipperling
      @created:       18.12.2025
      @description:   start lib
    */
    public function __construct($tenantId, $clientId, $clientSecret){
      $this->tenantId = $tenantId;
      $this->clientId = $clientId;
      $this->clientSecret = $clientSecret;
    }

    private function getAccessToken(){
      if ($this->token) {
        return $this->token;
      }

      $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";
      $data = [
        'client_id' => $this->clientId,
        'scope' => 'https://graph.microsoft.com/.default',
        'client_secret' => $this->clientSecret,
        'grant_type' => 'client_credentials'
      ];

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($ch);
      curl_close($ch);

      $result = json_decode($response, true);
      if (!isset($result['access_token'])) {
        throw new Exception("Fehler beim Abrufen des Tokens: $response");
      }

      $this->token = $result['access_token'];
      return $this->token;
    }

    private function request($method, $url, $data = []){
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
      if (!empty($data)) {
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
      }
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
          "Authorization: Bearer " . $this->getAccessToken(),
          "Content-Type: application/json"
      ]);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $response = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      return ['httpCode' => $httpCode, 'body' => json_decode($response, true)];
    }
    private function getDefaultDomain(){
      if ($this->defaultDomain) {
        return $this->defaultDomain;
      }

      $res = $this->request(
        'GET',
        'https://graph.microsoft.com/v1.0/domains'
      );

      foreach ($res['body']['value'] ?? [] as $domain) {
        if ($domain['isDefault'] && $domain['isVerified']) {
          $this->defaultDomain = $domain['id'];
          return $this->defaultDomain;
        }
      }
      throw new RuntimeException('Keine Default-Domain gefunden');
    }

    public function findUserByEmployeeId(string $employeeId){
      $filter = urlencode("employeeId eq '$employeeId'");
      $url = "https://graph.microsoft.com/v1.0/users?\$filter=$filter";

      $res = $this->request('GET', $url);
      $users = $res['body']['value'] ?? [];

      if (count($users) > 1) {
          throw new RuntimeException("Mehrere Benutzer mit employeeId $employeeId gefunden");
      }

      return $users[0] ?? null;
    }
  }
?>
