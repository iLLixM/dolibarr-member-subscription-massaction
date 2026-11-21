# CF03-POS02 – Technischer Abschluss der positiven CoreGuarded-Einzelbeitragstests A1/A2

**Datum der Testausführung:** 21.11.2026
**Prüfumfang:** Zwei reale, einmalige, vom Owner ausgeführte UI-Beitragsbuchungen und unabhängig ausgewertete Read-only-Evidence-Sätze
**Gesamtstatus:** **TECHNICAL PASS – POS02-A1 und POS02-A2 abgeschlossen**
**Aktuelle Owner-Dispositionsentscheidung:** **POS02-A1 = TECHNICAL PASS; POS02-A2 = TECHNICAL PASS; POS02-A (A1/A2) = COMPLETED**. Der unabhängige Code-Delta-Review bestätigt **F32-W01-01: CLOSED** auf dem getesteten HEAD. Der extern zuvor ausgesprochene POS02-Status `INSUFFICIENT EVIDENCE` bezog sich auf die dem Reviewer nicht zugänglichen Original-Evidenzen; die spätere separate technische Evidence-Auswertung und die ausdrückliche Owner-Entscheidung führen die Positivfälle als PASS. Eine externe nachträgliche POS02-Freigabe wird damit nicht behauptet. Kein allgemeines CF03-, Merge-, Release- oder Production-GO.

## Einordnung zur unabhängigen Delta-Prüfung

Der [externe Abschlussreview](PR32-F32-W01-01-POS02-final-delta-review.md) hat am geprüften Source-HEAD `e7b911b707508e439f9acbfad43f5fd55d34cc44` das **Code-Finding F32-W01-01 als CLOSED** bestätigt (Dolibarr-Mitgliedschaftsart `statut=1` aktiv; `statut=0` inaktiv; 10/10 CI-Checks PASS).

Sein zeitlich überschnittener **kombinierter** POS02-Entscheid `INSUFFICIENT EVIDENCE` beruhte ausdrücklich auf fehlendem Zugang des externen Reviewers zu den originalen A1/A2-Evidence-Dateien und dem vorliegenden technischen Abschlussbericht. Die nachträgliche, dokumentierte Prüfung der hochgeladenen Evidence-Kopien und die explizite Owner-Disposition **TECHNICAL PASS** haben für die Projektfortschrittsdokumentation der beiden Positivfälle Vorrang. Die Aussage des externen Reviewers bleibt unverändert und als historischer, anders fundierter Prüfentscheid archiviert.

**Verbleibende formelle Einschränkung:** Der unabhängige Reviewer hat die ihm damals fehlenden Originaldateien nicht selbst geprüft; eine vorgängige separate fallbezogene Owner-GO-Urkunde ist nicht nachgewiesen. Das ist kein technischer FAIL der beiden gebuchten Beiträge. Originale ausschließlich privat bewahren; keine Buchungs-/Verify-Wiederholung.

## 1. Prüfidentität

- Repository: https://github.com/iLLixM/dolibarr-member-subscription-massaction
- Draft-PR: https://github.com/iLLixM/dolibarr-member-subscription-massaction/pull/32
- Issue: https://github.com/iLLixM/dolibarr-member-subscription-massaction/issues/22
- Geprüfter Operator-/PR-HEAD: `e7b911b707508e439f9acbfad43f5fd55d34cc44` (laut Operator-Shell; zum Dokumentationszeitpunkt GitHub-PR-HEAD identisch, OPEN/DRAFT).
- Vorheriger W01-Review-HEAD: `3f2c08024b88d031055c27aba980e7156b4a2957`. Die technische W01-Review-Zulassung dieses älteren HEAD wurde wegen der korrigierten F32-W01-01-Statusannahme nicht automatisch auf den Test-HEAD übertragen.
- CI auf Test-HEAD: Standard 9/9 SUCCESS (https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44846591125), Hosted 1/1 SUCCESS (https://github.com/iLLixM/dolibarr-member-subscription-massaction/actions/runs/44846591170). Die optionale vollständige 21-Profil-Matrix ist dadurch nicht belegt.
- Testumgebung A: Dolibarr 24.0.3, PHP CLI 8.2.35, MySQL 8.4.13, `dolibarr_i105_v24`, effektiver eingeschränkter DB-Account `dolibarr_i105_v24_limited@10.0.2.100`, Profil `CoreGuarded`, `autocommit=1`, `NO_CHAIN`, P_S `select_denied_1142`.
- Evidenzquelle: hier im Chat dokumentierte Shell-/Web-Frontend-Ergebnisprotokolle des Owners; zehn hier hochgeladene Dateien mit `baseline.json`, `baseline.sha256`, `verify.started`, `after.json`, `verdict.json` für jeden Fall. Kein eigenständiger Browser- oder MySQL-Zugriff während dieser Auswertung.

## 2. Testfälle und Resultate

| Merkmal | POS02-A1 – Mitglieder-Agenda ON | POS02-A2 – Mitglieder-Agenda OFF |
| --- | --- | --- |
| Mitglied | `59`, Ref. `MEM2611-0059` | `60`, Ref. `MEM2611-0060` |
| Synthetischer Marker | `CF03LAB-A-POS02A120261121` | `CF03LAB-A-POS02A220261121` |
| Mitgliedschaftsart | ID `1`, `statut=1` (aktiv), Entity `1`, `subscription=1` | ID `1`, `statut=1` (aktiv), Entity `1`, `subscription=1` |
| Betrag / Zeitraum | `120.00 EUR`; 01.01.2080–31.12.2080 | `120.00 EUR`; 01.01.2080–31.12.2080 |
| Agenda-Einstellung | `on`; erwarteter authentifizierter Dolibarr-Actor `rowid=4` | `off`; CLI **ohne** `--actor-id`, `expected.actor_id=0` ist ein interner Sentinel, kein Web-User |
| Check-only | `PASS` laut Shell | `PASS` laut Shell |
| Baseline | `PASS` laut Shell | `PASS` laut Shell |
| UI (Owner-Beobachtung) | `1 successful, 0 skipped, 0 failed, 0 unknown`; „Subscription saved; commit confirmed“ | Identisches Erfolgsmuster; „Subscription saved; commit confirmed“ |
| Neue Subscription | ID `68` | ID `69` |
| Member-Agenda / Ressource | Genau 1 Ereignis `106` / genau 1 Ressource zu Actor `4` | Jeweils `0` |
| Rechnung / Invoice-Agenda | Keine / `0` | Keine / `0` |
| Witness-Verify | `PASS`, `persisted_core_contribution_observed` | `PASS`, `persisted_core_contribution_observed` |
| Abschluss | **TECHNICAL PASS** | **TECHNICAL PASS** |

Bei A2 ist die UI-Beschreibung länger als der nackte Marker. Die gespeicherte Subscription enthält den Marker laut Datenbank-Readback (`marker=true`), womit die Postcondition erfüllt ist. Beide Mitglieder besitzen im Vorzustand `datefin=NULL` und danach `datefin=2080-12-31 00:00:00`.

## 3. Unabhängig nachvollzogene Evidence-Integrität

Die Prüfsummen wurden **aus den Rohbytes der hochgeladenen `baseline.json`-Dateien neu berechnet**, nicht aus erneut serialisiertem JSON. Sie stimmen mit den jeweiligen 65-Byte-Dateien `baseline.sha256` (64 Hex-Zeichen plus LF) überein. Beide `verify.started`-Marker sind vorhanden (je 26 Byte) und enthalten einen zum Nachzustand passenden UTC-Zeitstempel. Alle JSON-Dateien sind gültig. Die Vorher-/Nachher-Kontextfelder sind je Fall unverändert, darunter Profil und Profil-Hash, DB-Identität, PHP-Version, Konfiguration, Schema-Spalten, Storage-Engines, 29 erfasste Modul-/Tool-Quellen, die 25 abgeglichenen bereitgestellten Moduldateien und 12 kritische Dolibarr-Core-Dateien.

| Evidence | POS02-A1 | POS02-A2 |
| --- | --- | --- |
| Upload-Files | `baseline(4).json`, `baseline(4).sha256`, `verify(4).started`, `after(4).json`, `verdict(4).json` | `baseline(5).json`, `baseline(5).sha256`, `verify(5).started`, `after(5).json`, `verdict(5).json` |
| Originales privates Fallverzeichnis | `/var/lib/dolibarr-test-results/cf03-a-pos02-a1-20261121` | `/var/lib/dolibarr-test-results/cf03-a-pos02-a2-20261121` |
| Neuberechneter SHA-256 von `baseline.json` | `760574c203e9035c8e59d8d582e862cb839f3e91e1d71197eccb190e75fde94e` | `ad468b74394e0f54718fab5aa657b097e25e042ffa2ab077b57cbfec7e733387` |
| Baseline (UTC) | `2026-11-21T00:58:56+00:00` | `2026-11-21T08:00:24+00:00` |
| Verify-Marker und After (UTC) | `2026-11-21T01:01:34+00:00` | `2026-11-21T08:02:25+00:00` |
| Integrität / Kontextstabilität | **PASS** | **PASS** |

Die A1-Dateien mit Upload-Suffix `(4)` sind bytegleich mit dem früher übermittelten Evidence-Satz `(3)`: Dies ist **keine zweite A1-Buchung**. Die Dateien sind Kopien; Dateisystem-Originalberechtigungen, Historie sämtlicher Aufrufe und die Einmaligkeit einer Operation auf dem Host lassen sich allein aus diesen Kopien nicht vollständig unabhängig verifizieren. Das Terminal-Protokoll und der Witness-One-shot-Vertrag ergänzen die Evidenz.

## 4. Datenbank-Deltas: zwölf überwachte Tabellen

| Tabelle | A1 vorher | A1 danach | A1 Delta | A2 vorher | A2 danach | A2 Delta |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| `adherent` | 59 | 59 | 0 | 60 | 60 | 0 |
| `subscription` | 63 | 64 | **+1** | 64 | 65 | **+1** |
| `actioncomm` | 100 | 101 | **+1** | 101 | 101 | **0** |
| `actioncomm_resources` | 100 | 101 | **+1** | 101 | 101 | **0** |
| `societe` | 11 | 11 | 0 | 11 | 11 | 0 |
| `socpeople` | 0 | 0 | 0 | 0 | 0 | 0 |
| `facture` | 18 | 18 | 0 | 18 | 18 | 0 |
| `facturedet` | 18 | 18 | 0 | 18 | 18 | 0 |
| `element_element` | 18 | 18 | 0 | 18 | 18 | 0 |
| `paiement` | 0 | 0 | 0 | 0 | 0 | 0 |
| `paiement_facture` | 0 | 0 | 0 | 0 | 0 | 0 |
| `bank` | 0 | 0 | 0 | 0 | 0 | 0 |

Keine unerwarteten Deltas. Die A2-Ausgangszähler für `subscription`, `actioncomm` und `actioncomm_resources` knüpfen unmittelbar an den A1-Endstand an. Die Erhöhung der Gesamtzahl der Mitglieder von 59 (A1-Nachzustand) auf 60 (A2-Vorzustand) entspricht der *vor A2* angelegten neuen synthetischen Fixture; sie ist keine im A2-Buchungsfenster beobachtete zusätzliche Mitgliedsanlage. Beide Fälle zeigen je genau eine neue, dem richtigen Mitglied zugeordnete Subscription mit Typ, Betrag, Start-/Enddatum und Marker.

### Präzisierung zur Agenda-OFF-Abdeckung

`agenda_enabled` war bei A1 `true` und bei A2 `false`. Auch die **effektiven Invoice-Agenda-Flags** unterschieden sich: A1 hatte `BILL_VALIDATE=true`, A2 `BILL_VALIDATE=false`; `BILL_CREATE=false` und `LINEBILL_INSERT=false` waren in beiden Fällen. Da in beiden Fällen keine Rechnung erzeugt wurde, verändert dies das erwartete Contribution-Ergebnis nicht. Der A2-Test belegt die korrekte Beitragsbuchung bei *effektiv ausgeschalteter Mitglieder-Agenda*, **nicht** einen gesonderten Test, in dem ausschließlich der Member-Trigger bei ansonsten identischer Agenda-Konfiguration deaktiviert wurde.

## 5. Ergebnisgrenzen und Governance

**Belegt:** Persistente CoreGuarded-Subscription je Fall; korrekte Mitgliedschaftszeiträume; Agenda ON/OFF mit erwarteter Zeilen- und Ressourcenanzahl; keine Invoice-/Customer-/Payment-Nebenwirkungen in den überwachten Tabellen; stabile Ausführungsidentität innerhalb jedes Evidence-Fensters; UI-Erfolg **als Owner-Protokoll**.

**Nicht belegt:** physische COMMIT-Ereignisidentität, exakter HTTP-ACK-Transport, PDF-/SMTP-Nebenwirkungen, Auswirkungen auf unüberwachte Tabellen und Dateisystem, vollständige Container-/Host-Ausführungshistorie, globaler konkurrierender Schreibzugriff, ZIP-Installation, Rollback/Fault/Replay/Concurrency, andere Profile/Umgebungen oder ein systemweiter Negativpfad. Deshalb meldet der Witness zutreffend `acknowledgement=not_determined_by_database_readback` und `pdf_smtp=not_determined_by_database_readback`.

**Freigabevermerk:** Der unabhängige Code-Delta-Review hat inzwischen **F32-W01-01: CLOSED** bestätigt. Die technische POS02-PASS-Disposition ist die separate, auf den hier ausgewerteten Evidence-Kopien beruhende Projektentscheidung und keine rückdatierte externe Evidence-Abnahme. Eine vor der Realbuchung gesondert dokumentierte fallbezogene Owner-GO-Urkunde liegt in den hier geprüften Nachweisen nicht vor; der Reviewer soll dies als Governance-Nachweislücke behandeln, sofern sie nicht gesondert vorgelegt wird, nicht als technischen FAIL oder als Beweis fehlender tatsächlicher Zustimmung. Keine nachträgliche Genehmigung darf behauptet werden. Beide Original-Evidence-Verzeichnisse bleiben unverändert; **keine Neubuchung, kein erneutes Verify, kein Cleanup und keine Roh-Evidence-Veröffentlichung in GitHub**.

## 6. Schlussentscheidung

**POS02-A1: TECHNICAL PASS – CLOSED (Agenda ON).**
**POS02-A2: TECHNICAL PASS – CLOSED (Agenda OFF).**
**Testreihe POS02-A / positive Einzelbeiträge: TECHNICAL PASS – COMPLETED.**

Die beiden Positivfälle qualifizieren ausschließlich diesen Teil des CF03-Plans auf Umgebung A und dem festgelegten Source-HEAD. **CF03/Issue #22 bleibt offen**, ebenso alle gesonderten Rollback-, Fehler-, Batch-, Konkurrenz-, Replay-, MariaDB-, EnhancedMySQL-, Hosting- und Post-Commit-Tests. **F32-W01-01 ist als Code-Finding CLOSED; POS02-A1/A2 sind TECHNICAL PASS.**

**Quellen:** Owner-Shell-/UI-Ergebnisprotokolle dieser Unterhaltung, je fünf separat übermittelte private Evidence-Dateikopien pro Fall und GitHub-PR-/CI-Rundaten zum gepinnten HEAD. Dieser Bericht wurde ursprünglich als separates Markdown-Artefakt auf Basis der privaten Owner-Evidence erstellt und anschließend als reine Dokumentation in PR #32 übernommen. Der getestete Source-HEAD bleibt der oben ausgewiesene SHA; ein späterer Dokumentationscommit ist kein neuer Realtest und keine Änderung der damaligen CI-/Review-Bindung. Kein PR-Merge.
