# [DRAFT_FOR_DISPATCH] - הודעת איפוס לצוות 4
**תאריך:** 2026-01-14  
**מטרה:** איפוס קונטקסט והבהרת המשימה הנוכחית לצוות 4

---

## 🗄️ הודעת איפוס לצוות 4 (Database Specialists)

```
# [DRAFT_FOR_DISPATCH]
**אל:** צוות 4 (Database Specialists)  
**נושא:** 🔄 RESET - Phase 2.3 - Database Support & Alt-Text Inventory  
**Task ID:** EA-V11-PHASE-2.3-DB-SUPPORT  
**עדיפות:** MEDIUM  
**סטטוס:** 🟡 STANDBY (תמיכה לפי דרישה)

---

## 📍 קונטקסט המשימה:

אנחנו ב-Phase 2.3 - Semantic SEO & Schema Infrastructure. תפקידכם הוא לספק תמיכה במסד הנתונים לפי הצורך, במיוחד עבור Alt-Text Inventory.

## 🎯 הסקופ שלכם:

**תפקיד כללי:**
- ניהול, ניקוי ואופטימיזציה של ה-Database (Serialization Aware)
- מומחיות בעבודה בטוחה עם נתונים מסודרים (Serialized Data)
- שימוש ב-`wp search-replace` בלבד (אין REPLACE ידני ב-SQL)

**מה נדרש מכם ב-Phase 2.3:**
1. **תמיכה ב-Alt-Text Inventory** - אם נדרש עדכון מסיבי של alt tags דרך DB
2. **גיבוי לפני שינויים** - יצירת Snapshot לפני כל פעולה
3. **תמיכה טכנית** - לפי דרישה מצוות 1 או צוות 2

## 📋 הוראות ביצוע (לפי דרישה):

### שלב 1: גיבוי לפני פעולה (חובה תמיד)
```bash
# יצירת Snapshot מלא למסד הנתונים
wp db export docs/database/backups/backup-phase2.3-$(date +%Y%m%d-%H%M%S).sql
```

**חובה:** כל פעולה ב-DB חייבת להתחיל בגיבוי מלא!

### שלב 2: תמיכה ב-Alt-Text Inventory (אם נדרש)
אם צוות 1 או צוות 2 מבקשים עדכון מסיבי של alt tags דרך DB:

```bash
# בדיקת תמונות ללא alt text
wp db query "SELECT p.ID, p.post_title FROM wp_posts p LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt' WHERE p.post_type = 'attachment' AND p.post_mime_type LIKE 'image/%' AND (pm.meta_value IS NULL OR pm.meta_value = '')"

# עדכון alt text (דוגמה - רק לאחר אישור!)
# wp post meta update [ID] _wp_attachment_image_alt "[alt text]"
```

**חשוב:** 
- אין לעדכן ישירות ב-SQL על נתונים serialized
- השתמש ב-WP-CLI commands בלבד
- תמיד גבה לפני שינויים

### שלב 3: אופטימיזציה של DB (אם נדרש)
```bash
# אופטימיזציה של מסד הנתונים
wp db optimize
```

## ⚠️ כללי בטיחות קריטיים:

1. **Serialization Safety:**
   - איסור מוחלט על REPLACE ידני ב-SQL על נתונים serialized
   - שימוש ב-`wp search-replace` בלבד
   - תמיד לבדוק לפני ביצוע

2. **גיבוי חובה:**
   - כל פעולה ב-DB חייבת להתחיל בגיבוי מלא
   - שמור גיבויים ב: `docs/database/backups/`

3. **Elementor URLs:**
   - חובה להשתמש בפקודה הייעודית:
   ```bash
   wp elementor replace-urls http://www.eyalamit.co.il http://localhost:9090 --allow-root
   ```

## 📋 פרוטוקול עבודה:

1. **קבלת בקשה** - צוות אחר מבקש תמיכה ב-DB
2. **גיבוי מלא** - יצירת Snapshot לפני כל פעולה
3. **ביצוע פעולה** - שימוש ב-WP-CLI בלבד
4. **אימות** - וידוא שהפעולה הצליחה
5. **דיווח** - דיווח על הפעולה שבוצעה

## ⏸️ סטטוס נוכחי:

**🟡 STANDBY** - ממתינים לפי דרישה

צוות 4 לא פעיל כרגע ב-Phase 2.3, אך זמין לתמיכה אם נדרש:
- עדכון מסיבי של alt tags דרך DB
- אופטימיזציה של DB
- תמיכה טכנית אחרת

## 📚 קבצים רלוונטיים:

- `docs/sop/SSOT.md` - סעיף 7: פרוטוקול עבודה בטוחה במסד הנתונים
- `docs/development/ALT-TEXT-INVENTORY-SCRIPT.php` - סקריפט Alt-Text
- `docs/database/backups/` - תיקיית גיבויים

---

**הודעה זו מוכנה לאישור המנכ"ל לפני הפצה לצוות 4**
```

---

## 📝 תשובה לשאלתך:

**מה התפקיד שלי?**
אני **צוות 3 (Gatekeeper)** - מנהל התנועה, הסנכרון וה-Proxy. המנצח על התזמורת והגשר בין כל הצוותים לארכיטקט. הנציג הרשמי של הארכיטקט בשטח.

**תפקידי כולל:**
- ✅ יצירת הודעות הפעלה מוכנות למנכ"ל
- ✅ תזמור בין הצוותים
- ✅ מעקב אחר התקדמות
- ✅ איסוף דיווחים
- ✅ הכנת מניפסטים וסנכרון

**מה אני לא עושה:**
- ❌ לא מעביר הודעות בין צוותים (רק המנכ"ל מפיץ)
- ❌ לא מבצע פעולות Git לפני אישור המנכ"ל
- ❌ לא מפעיל צוותים (רק המנכ"ל מפיץ)

---

**הודעת איפוס לצוות 4 מוכנה להעתקה והפצה!**
