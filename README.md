# BBSSync Server & Agent
BBSSync ist ein Serveranwendung, welche die Tagessicherung aus BBS Planung mittels Agent einließt und diese mittels Rest-API an die Serveranwendung sendet. Der Server verarbeitet die Schülerdaten und synchronisiert diese via LDAPS in das Active Directory.

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
BBSSync verfügt über einen Windows Service Worker, welcher die Datensicherung von BBS Planung ein ließt. Dafür werden aus der Sicherung die Schülerdaten `vname`, `nname`, `kl_name` eingelesen und an die Rest-API von BBSSync geschickt. Als Basis werden dafür die Dateien `SK_SIL.TXT` & `SK_ABL.TXT` genutzt.
### Installation BBSSync Windows Agent
1. Laden Sie in den Releases die letzte Version von BBSSync Windows Agent herunter. Diese ist als `.zip` Datei hinterlegt.
2. Entpacken Sie die Zip-Datei in dem Stammverzeichnis von Windows (C:).
3. Navigieren Sie in das Verzeichnis und starten Sie dort mit Administratorrechten eine Powershell Kommandozeile
4. Geben Sie folgenden Befehl `sc create BBSSync displayname= "BBSSync Windows Agent" binpath= "C:\BBSSync Windows Agent\BBSSync.exe"` ein. Ändern Sie gegebenenfalls den `binpath` auf Ihren Pfad, wo die Exe liegt.
5. Bearbeiten Sie die Datei `BBSSync.ddl.config`. Tragen Sie die Pfade zu den jweiligen Dateien ein, ändern Sie den Token und tragen Sie die URL zu Ihrem Server ein (z.B: https://10.10.0.2/bbssync/api.php)
6. Öffnen Sie die Windows Verwaltungsplattform Dienste und starten Sie den Dienst neu.

## BBSSync Server

### Installation BBSSync Server
1. Laden Sie mit `git clone ` die Repo herunter und navigieren Sie in das Verzeichnis `cd bbssync`.
2. Machen Sie das Installationsskript ausführbar `chmod +x install.sh`.
3. Sie werden im Script nach einem SMB Passwort gefragt. Richten Sie hier Ihr Passwort ein. Dieses kann immer über den Befehl `smbpasswd bbssync` neu gesetzt werden.
4. Rufen Sie die Website des Servers auf `https://<IPv4>/bbssync/`. Der Server ist nur über HTTPS erreichbar.
