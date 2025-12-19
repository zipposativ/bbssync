# BBSSync Server & Agent
BBSSync ist ein Serveranwendung, welche die Tagessicherung aus BBS Planung mittels Agent einließt und diese mittels Rest-API an die Serveranwendung sendet. Der Server verarbeitet die Schülerdaten und synchronisiert diese via LDAPS in das Active Directory.

<img width="1893" height="865" alt="base" src="https://github.com/user-attachments/assets/40ce2d1a-6ad7-4352-a496-8d2595ec8912" />


---
**⚠️INFORMATION⚠️**
## Lizenz und Nutzung

Dieses Projekt wird als Fair Code veröffentlich.

Der Quellcode ist öffentlich einsehbar, um Transparenz, Nachvollziehbarkeit und Vertrauen zu ermöglichen. Die Nutzung ist jedoch bewusst eingeschränkt.

### Erlaubte Nutzung

Alle Berufsbildenden Schulen im Land Niedersachsen dürfen diese Software

- kostenlos nutzen
- installieren
- an ihre schulischen Bedürfnisse anpassen
- im Unterricht und im Schulbetrieb einsetzen

Diese Erlaubnis gilt unabhängig vom Schulträger.

### Nicht erlaubte Nutzung

Ohne gesonderte Lizenz ist es nicht erlaubt

- die Software als Software as a Service anzubieten
- die Software durch externe Unternehmen für Schulen oder Dritte zu betreiben
- die Software weiterzuverkaufen oder als Teil eines kommerziellen Angebots bereitzustellen
- auf Basis dieses Codes ein konkurrierendes kommerzielles Produkt anzubieten

### Nutzung durch Unternehmen

Unternehmen dürfen diese Software nicht kostenlos nutzen.

Wenn Unternehmen die Software einsetzen möchten, insbesondere

- zur Bereitstellung für Schulen
- im Rahmen von Dienstleistungs- oder Hosting-Angeboten
- zur internen oder externen Nutzung im Bildungsumfeld

ist eine kostenpflichtige Lizenz pro Schule erforderlich.

Bitte nehmen Sie dafür direkt Kontakt mit mir auf.

### Begründung

Dieses Projekt wurde ausschließlich für Berufsbildende Schulen in Niedersachsen entwickelt.

Ich möchte ausdrücklich verhindern, dass

- Schulen für Software zur Kasse gebeten werden
- Kosten entstehen, die Schulen nie zu tragen haben dürften
- Dritte mit dieser Arbeit Geld verdienen, während die Zielgruppe zahlen muss

Der offene Code dient der Zusammenarbeit und Qualitätssicherung, nicht der kommerziellen Verwertung durch externe Akteure.

Diese Regelung stellt sicher, dass

- Schulen geschützt werden
- der ursprüngliche Zweck des Projekts erhalten bleibt
- kommerzielle Nutzung fair vergütet wird


---




## BBSSync Windows Agent
Der Windows Agent von BBSSync läuft als Dienst und liest die Datensicherung von BBS Planung ein. Aus den Sicherungsdateien werden die Felder `vname`, `nname` und `kl_name` übernommen und an die REST-API von BBSSync gesendet. Grundlage dafür sind die Dateien `SK_SIL.TXT` und `SK_ABL.TXT`.

### Installation BBSSync Windows Agent
1. Laden Sie im Bereich *Releases* die aktuelle Version des BBSSync Windows Agent herunter. Die Datei liegt als ZIP vor.
2. Entpacken Sie das Archiv direkt auf Laufwerk C.
3. Öffnen Sie das entpackte Verzeichnis und starten Sie dort eine PowerShell mit Administratorrechten.
4. Legen Sie den Dienst an:  
   `sc create BBSSync displayname= "BBSSync Windows Agent" binpath= "C:\BBSSync Windows Agent\BBSSync.exe"`  
   Falls Sie das Verzeichnis geändert haben, passen Sie den Pfad an.
5. Bearbeiten Sie die Datei `BBSSync.ddl.config`. Tragen Sie die Pfade zu den Sicherungsdateien ein, ergänzen Sie den Token und setzen Sie die URL Ihres Servers, etwa `https://10.10.0.2/bbssync/api.php`.
6. Öffnen Sie die Windows Dienstverwaltung und starten Sie den neuen Dienst.

## BBSSync Server

### Installation BBSSync Server
1. Klonen Sie das Repository:  
   `git clone https://github.com/zipposativ/bbssync.git`  
   Danach wechseln Sie in das Verzeichnis:  
   `cd bbssync`
2. Machen Sie das Installationsskript ausführbar:  
   `chmod +x install.sh`  
   Anschließend starten:  
   `./install.sh` oder `sh install.sh`
3. Während der Installation werden Sie nach einem SMB-Passwort gefragt. Legen Sie es fest. Sie können es später jederzeit mit  
   `smbpasswd bbssync`  
   neu setzen.
4. Rufen Sie anschließend die Weboberfläche auf:  
   `https://<IPv4>/bbssync/`  
   Der Zugriff ist ausschließlich per HTTPS möglich.

### BBSSync Nutzung
Der Server stellt eine SMB-Freigabe bereit. Diese wird unter Windows eingebunden und dient dazu, Schülerdaten im PDF-Format bereit zu stellen.
