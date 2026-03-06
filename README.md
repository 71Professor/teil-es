# QR Code Manager - Dynamische QR Codes

Ein modernes, benutzerfreundliches Tool zur Verwaltung dynamischer QR Codes auf deinem all-inkl.com Webspace.

## 🎯 Was macht dieses Tool?

- Erstelle QR Codes, die auf deine Domain verweisen (z.B. `https://deine-domain.de/qr/abc123`)
- Ändere jederzeit das Ziel, wohin der Code weiterleitet - **ohne den QR Code neu drucken zu müssen**
- Modernes Admin-Interface mit Alpine.js + Tailwind CSS
- Tracking: Sieh wie oft deine Codes gescannt wurden
- Download-Funktion für QR Codes in PNG-Format

## 📦 Installationsanleitung für all-inkl.com

### Schritt 1: Datenbank erstellen

1. Bei all-inkl.com einloggen
2. Gehe zu **KAS** (Kunden-Administration)
3. Navigiere zu **MySQL-Datenbank** > **Neue Datenbank**
4. Erstelle eine neue Datenbank und notiere:
   - Datenbankname
   - Benutzername
   - Passwort

### Schritt 2: Dateien hochladen

1. Verbinde dich via **FTP** mit deinem all-inkl Webspace
2. Lade alle Dateien in einen Ordner auf deinem Server (z.B. `/qr-dynamisch/`)
3. Achte darauf, dass die Ordnerstruktur erhalten bleibt:
   ```
   qr-dynamisch/
   ├── admin/
   │   ├── index.php
   │   ├── login.php
   │   └── api.php
   ├── config.php
   ├── redirect.php
   ├── database.sql
   └── .htaccess
   ```

### Schritt 3: Datenbank einrichten

1. Öffne **phpMyAdmin** in deinem KAS
2. Wähle deine neu erstellte Datenbank aus
3. Gehe zum Tab **SQL**
4. Öffne die Datei `database.sql` in einem Texteditor
5. Kopiere den kompletten Inhalt und füge ihn in phpMyAdmin ein
6. Klicke auf **OK** - Die Tabellen werden erstellt

### Schritt 4: config.php anpassen

Öffne die Datei `config.php` und trage deine Zugangsdaten ein:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'deine_db_name');      // ← Hier eintragen
define('DB_USER', 'dein_db_username');    // ← Hier eintragen
define('DB_PASS', 'dein_db_passwort');    // ← Hier eintragen

define('BASE_URL', 'https://deine-domain.de/qr-dynamisch');  // ← Anpassen!
```

### Schritt 5: Admin-Passwort setzen

1. Öffne in deinem Browser: `https://deine-domain.de/qr-dynamisch/admin/setup.php`
2. Setze dein Admin-Passwort (mindestens 8 Zeichen)
3. **WICHTIG**: Lösche danach die Datei `setup.php` vom Server!

### Schritt 6: .htaccess anpassen (falls nötig)

Falls dein Ordner **nicht** `/qr-dynamisch/` heißt, passe in der `.htaccess` die `RewriteBase` an:

```apache
RewriteBase /dein-ordner-name/
```

### Schritt 7: Testen!

1. Öffne in deinem Browser: `https://deine-domain.de/qr-dynamisch/admin/`
2. Logge dich mit dem Passwort aus Schritt 5 ein
3. Erstelle deinen ersten QR Code!

## 🚀 Verwendung

### Neuen QR Code erstellen

1. Im Admin-Interface auf **"Neuer QR Code"** klicken
2. Felder ausfüllen:
   - **Titel**: Beschreibender Name (z.B. "Workshop Material")
   - **Shortcode**: Optional - wird automatisch generiert wenn leer
   - **Ziel-URL**: Wohin soll der Code aktuell führen?
   - **Beschreibung**: Optional - Notizen für dich
3. **"QR Code erstellen"** klicken
4. QR Code wird sofort generiert und angezeigt

### QR Code verwenden

- Klicke auf **Download** um den QR Code als PNG zu speichern
- Drucke ihn aus, füge ihn in Dokumente ein, etc.
- Der Code verweist auf: `https://deine-domain.de/qr/abc123`

### Ziel ändern

1. Klicke auf **"Bearbeiten"** bei einem QR Code
2. Ändere die **Ziel-URL**
3. Speichern - fertig! 
4. Der gedruckte QR Code führt jetzt zum neuen Ziel

### Weitere Funktionen

- **Auge-Symbol**: QR Code aktivieren/deaktivieren
- **Download-Symbol**: QR Code als PNG herunterladen
- **Papierkorb**: QR Code löschen

## 🔒 Sicherheit

### Wichtig!

1. **Setze ein starkes Passwort** über `setup.php` (min. 8 Zeichen)
2. **Lösche `setup.php`** nach dem ersten Setup vom Server!
3. Passwort wird sicher mit bcrypt gehashed in der Datenbank gespeichert
4. Bei Problemen: Fehler-Reporting in `config.php` ausschalten:
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

### Zusätzliche Absicherung (optional)

Du kannst den `/admin/` Ordner zusätzlich mit einem `.htpasswd` schützen:

1. In KAS: **Verzeichnisschutz** einrichten
2. Ordner `/admin/` auswählen
3. Benutzername und Passwort festlegen

## 📊 Tracking & Statistiken

Jeder Scan wird getrackt:
- Zeitpunkt des Scans
- IP-Adresse (anonymisiert)
- Anzahl der Scans pro Code

Die Statistiken werden im Admin-Interface angezeigt (Scan-Counter).

## 🎨 Design anpassen

Das Interface nutzt **Tailwind CSS**. Du kannst Farben einfach anpassen:

In der `admin/index.php` findest du Klassen wie:
- `bg-indigo-600` → Hauptfarbe (Indigo)
- `text-indigo-600` → Textfarbe
- `hover:bg-indigo-700` → Hover-Effekt

Ersetze `indigo` z.B. durch:
- `blue` (Blau)
- `green` (Grün)
- `purple` (Lila)
- `red` (Rot)

## 🐛 Fehlerbehebung

### "Datenbankverbindung fehlgeschlagen"
- Prüfe Zugangsdaten in `config.php`
- Bei all-inkl ist der Host meist `localhost`

### QR Codes werden nicht angezeigt
- Prüfe ob `qrcode.min.js` geladen wird (Browser-Konsole)
- Teste mit einem anderen Browser

### "404 Not Found" beim Scannen
- Prüfe `.htaccess` - ist `RewriteBase` richtig?
- Teste ob mod_rewrite aktiv ist (bei all-inkl standardmäßig ja)

### Login funktioniert nicht
- Hast du das Passwort in `config.php` geändert?
- Prüfe ob Sessions funktionieren (PHP-Version min. 7.4)

## 💡 Erweiterungsideen

Phase 2-4 (siehe Roadmap):
- Statistik-Dashboard mit Grafiken
- Gruppen/Kategorien für QR Codes
- Mehrere Admin-Nutzer
- Ablaufdatum für Codes
- Export-Funktion (CSV)
- Bulk-Operations

## 📝 Technische Details

- **Backend**: PHP 7.4+
- **Datenbank**: MySQL/MariaDB
- **Frontend**: Alpine.js 3.x + Tailwind CSS 3.x
- **QR-Generator**: qrcode.js
- **Hosting**: Optimiert für all-inkl.com

## 🤝 Support

Bei Fragen oder Problemen:
1. Prüfe die Fehlerbehebung oben
2. Schau in die Browser-Konsole (F12)
3. Aktiviere Error-Reporting in `config.php` für Details

---

**Viel Erfolg mit deinen dynamischen QR Codes! 🚀**
