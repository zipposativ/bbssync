# BBSSync Server & Agent
BBSSync ist ein Serveranwendung, welche die Tagessicherung aus BBS Planung mittels Agent einließt und diese mittels Rest-API an die Serveranwendung sendet. Der Server verarbeitet die Schülerdaten und synchronisiert diese via LDAPS in das Active Directory.

---
**INFORMATION**

BBSSync Server & BBSSync Windows Agent sind ausschließlich für die niedersächsischen Berufsbildenden Schulen entwickelt worden und darf nicht Kommerziel oder oder als Software as a Service angeboten werden.

---




## BBSSync Windows Agent
BBSSync verfügt über einen Windows Service Worker, welcher die Datensicherung von BBS Planung ein ließt. Dafür werden aus der Sicherung die Schülerdaten `vname`, `nname`, `kl_name` eingelesen und an die Rest-API von BBSSync geschickt. Als Basis werden dafür die Dateien `SK_SIL.TXT` & `SK_ABL.TXT` genutzt.
### Installation BBSSync Windows Agent
Laden Sie in den Releases die letzte Version von BBSSync Windows Agent herunter. Diese ist als `.zip` Datei hinterlegt.
