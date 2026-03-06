# ✅ Installations-Checkliste

Schneller Überblick für die Installation auf all-inkl.com:

## Vor dem Upload

- [ ] MySQL-Datenbank bei all-inkl erstellt
- [ ] Datenbank-Zugangsdaten notiert (Name, User, Passwort)

## Nach dem Upload

- [ ] `database.sql` in phpMyAdmin importiert
- [ ] `config.php` geöffnet und angepasst:
  - [ ] DB_NAME eingetragen
  - [ ] DB_USER eingetragen
  - [ ] DB_PASS eingetragen
  - [ ] BASE_URL angepasst (z.B. https://deine-domain.de/qr-dynamisch)
- [ ] `.htaccess` RewriteBase geprüft (falls Ordner anders heißt)

## Testen

- [ ] `/admin/setup.php` im Browser aufgerufen
- [ ] Admin-Passwort gesetzt (mindestens 8 Zeichen)
- [ ] `setup.php` vom Server gelöscht (WICHTIG für Sicherheit!)
- [ ] `/admin/` im Browser aufgerufen
- [ ] Login mit neuem Passwort erfolgreich
- [ ] Ersten Test-QR-Code erstellt
- [ ] QR Code gescannt und Weiterleitung funktioniert

## Fertig! 🎉

Dein QR Code Manager ist einsatzbereit!

---

### Schnell-Links für all-inkl.com

- KAS Login: https://kas.all-inkl.com/
- MySQL-Verwaltung: KAS → MySQL-Datenbank
- phpMyAdmin: KAS → phpMyAdmin
- FTP-Zugangsdaten: KAS → FTP
