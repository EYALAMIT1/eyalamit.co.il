# מדריך גיבוי מלא - אתר WordPress

## שיטות גיבוי

### שיטה 1: סקריפט אוטומטי (מומלץ) ⭐

הכי פשוט ומהיר - הסקריפט עושה הכל אוטומטית:

```bash
# דרך PowerShell
.\backup-site.ps1

# או דרך CMD
backup-site.bat
```

**מה הסקריפט עושה:**
- ✅ בודק שהקונטיינרים רצים
- ✅ יוצר תיקיית גיבוי עם תאריך
- ✅ מגבה את בסיס הנתונים (SQL dump)
- ✅ מגבה את כל קבצי WordPress (ZIP)
- ✅ יוצר קובץ מידע עם פרטי הגיבוי

**איפה נשמר הגיבוי?**
```
backups/
  └── backup_2025-01-XX_HH-mm-ss/
      ├── database_backup.sql
      ├── files_backup.zip
      └── backup_info.txt
```

---

### שיטה 2: גיבוי ידני דרך Docker

#### גיבוי בסיס הנתונים:

```bash
# מצא את שם הקונטיינר
docker ps

# גיבוי SQL
docker exec [container_name] mysqldump -ueyal -peyalpass eyal_local > backup_$(date +%Y%m%d_%H%M%S).sql
```

**דוגמה:**
```bash
docker exec new-website-ai-nov-2025-db-1 mysqldump -ueyal -peyalpass eyal_local > backup_20250115.sql
```

#### גיבוי קבצים:

```bash
# יצירת ZIP של כל תיקיית האתר
Compress-Archive -Path ".\eyalamit.co.il_bm1763033821dm\*" -DestinationPath "backup_files_$(Get-Date -Format 'yyyyMMdd_HHmmss').zip"
```

---

### שיטה 3: גיבוי דרך phpMyAdmin

1. פתח את phpMyAdmin: http://localhost:8081
2. בחר את בסיס הנתונים `eyal_local`
3. לחץ על "Export" (ייצוא)
4. בחר "Quick" או "Custom"
5. לחץ על "Go" ושמור את הקובץ

---

### שיטה 4: גיבוי דרך WP-CLI

```bash
# גיבוי DB
docker exec [wordpress_container] wp db export backup.sql --path=/var/www/html

# העתקת הקובץ מהקונטיינר
docker cp [wordpress_container]:/var/www/html/backup.sql ./backup.sql
```

---

## שחזור מגיבוי

### שחזור בסיס הנתונים:

```bash
# דרך Docker
docker exec -i [container_name] mysql -ueyal -peyalpass eyal_local < database_backup.sql
```

**דוגמה:**
```bash
docker exec -i new-website-ai-nov-2025-db-1 mysql -ueyal -peyalpass eyal_local < backups/backup_2025-01-15_10-30-00/database_backup.sql
```

### שחזור קבצים:

1. עצור את הקונטיינרים: `docker compose down`
2. חלץ את `files_backup.zip` לתיקיית האתר
3. הפעל מחדש: `docker compose up -d`

---

## המלצות

### לפני ניקוי מדיה:
⚠️ **חובה לבצע גיבוי מלא!**

```bash
.\backup-site.ps1
```

### תדירות גיבוי:
- **לפני כל שינוי משמעותי** (ניקוי מדיה, עדכונים, וכו')
- **גיבוי שבועי** - אם עובדים על האתר באופן קבוע
- **גיבוי לפני עדכונים** - WordPress core, plugins, themes

### איפה לשמור גיבויים?
- ✅ תיקיית `backups/` (כבר מוגדרת ב-`.gitignore`)
- ✅ שמור עותק נוסף במקום אחר (כונן חיצוני, ענן)
- ✅ שמור לפחות 3-5 גיבויים אחרונים

---

## בדיקת תקינות גיבוי

### בדיקת גיבוי DB:
```bash
# בדיקה שהקובץ לא ריק
Get-Item backup.sql | Select-Object Length

# בדיקה שהקובץ תקין (יש בו SQL)
Select-String -Path backup.sql -Pattern "CREATE TABLE" | Measure-Object
```

### בדיקת גיבוי קבצים:
```bash
# בדיקה שהקובץ לא פגום
Expand-Archive -Path files_backup.zip -DestinationPath test_extract -Force
# אם זה עובד - הגיבוי תקין
Remove-Item -Path test_extract -Recurse -Force
```

---

## פתרון בעיות

### שגיאה: "קונטיינר לא רץ"
```bash
# הפעל את Docker Compose
docker compose up -d
```

### שגיאה: "לא נמצא mysqldump"
```bash
# בדוק שהקונטיינר רץ
docker ps

# נסה עם root
docker exec [container_name] mysqldump -uroot -pstrong_root_pass eyal_local > backup.sql
```

### שגיאה: "אין הרשאות כתיבה"
```bash
# בדוק הרשאות לתיקיית backups
New-Item -ItemType Directory -Force -Path .\backups
```

---

## סיכום

**השיטה המומלצת:** `.\backup-site.ps1`

**לפני ניקוי מדיה:**
1. ✅ הרץ `.\backup-site.ps1`
2. ✅ בדוק שהגיבוי נוצר בהצלחה
3. ✅ שמור עותק נוסף במקום אחר
4. ✅ רק אז התחל בניקוי מדיה

---

**נוצר:** 2025  
**גרסה:** 1.0



