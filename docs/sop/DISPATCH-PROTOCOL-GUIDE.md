# 📋 מדריך פרוטוקול העברת דיווח לצוותים
**מבוסס על:** SSOT v11.0  
**סעיפים רלוונטיים:** 2.1, 3, 12, 13

---

## 🔴 כלל יסוד #1: סמכות הפעלה בלעדית (Exclusive Dispatch Protocol)

### **רק המנכ"ל מפעיל את הצוותים!**

**צוות 3 (Gatekeeper) - תפקיד:**
- ✅ **מכין את ה"תסריט"** - הודעות מוכנות בבלוק קוד
- ✅ **מציג למנכ"ל** - כל הודעה בתוך בלוק קוד (```) להעתקה קלה
- ✅ **יוצר הודעות** - הודעות מוכנות לכל הצוותים (1, 2, 4, 0)
- ❌ **אין העברה פיזית** - צוות 3 לא מעביר הודעות בין צוותים

**המנכ"ל (CEO) - תפקיד:**
- ✅ **היחיד שמפיץ** - המנכ"ל הוא היחיד שמפיץ הודעות הפעלה לצוותים
- ✅ **מעתיק ומעביר** - המנכ"ל מעתיק את ההודעה מהבלוק קוד ומעביר לצוות המקבל

**🔴 נוהל CPFA (Pre-Flight Approval):**
- חובה להציג כל חבילת Payload למנכ"ל לאישור (🟢) לפני הפצה לצוותים

---

## 📝 פורמט תקשורת מחייב (v9.1 Standard)

### **כל הודעה חייבת להיות בפורמט הבא:**

```
From: [שם הצוות השולח]
To: [שם הצוות המקבל]
Subject: [נושא קצר וברור]
Status: [דגל צבע] [סטטוס טקסטואלי]

Done: [פירוט טכני של מה שבוצע]
Evidence: [נתיב לקובץ / לוג / מניפסט / סקרינשוט טקסטואלי]
Blockers: [גורמים מעכבים או 'None']
Next: [צעד הבא מיידי]
Timestamp: [YYYY-MM-DD HH:MM]
Extra details in professional report: [YES / NO]
```

---

## 🎨 דגלי צבע לסטטוס (Status Color Flags)

### 🔴 **אדום (RED):**
- קריטי, חסום (BLOCKED), נכשל (FAILED)
- דורש התערבות מיידית
- דוגמאות: `BLOCKED`, `FAILED`, `CRITICAL_ERROR`, `EMERGENCY`

### 🟡 **צהוב (YELLOW):**
- ממתין (PENDING), בתהליך (IN_PROGRESS)
- פעולה נדרשת (ACTION_REQUIRED)
- דורש תשומת לב
- דוגמאות: `PENDING`, `IN_PROGRESS`, `ACTION_REQUIRED`, `AWAITING_APPROVAL`, `VERIFICATION_PENDING`

### 🟢 **ירוק (GREEN):**
- הושלם (COMPLETED), מוכן (READY)
- אושר (APPROVED), הצלחה
- דוגמאות: `COMPLETED`, `READY`, `APPROVED`, `SUCCESS`, `FIXES_COMPLETE`

**חשוב:** כל הודעה חייבת לכלול דגל צבע בסטטוס. אין דיווח ללא דגל צבע.

---

## 🟢 פרוטוקול [DRAFT_FOR_DISPATCH]

### **שלבים:**

1. **סטטוס הצעה (Draft for Dispatch):**
   - כל הנחיה טכנית המיועדת לצוותי הביצוע תוגש למנכ"ל בפורמט `[DRAFT_FOR_DISPATCH]`
   - ההודעה מוצגת בתוך בלוק קוד להעתקה קלה

2. **אישור והפצה:**
   - המנכ"ל סוקר את הטיוטה
   - המנכ"ל מאשר (🟢) את ההודעה
   - המנכ"ל מעתיק את ההודעה ומעביר אותה לצוות הרלוונטי

3. **נצור (Safety Catch):**
   - צוותי הביצוע (1, 2, 4) לא יפעלו על סמך המלצות הארכיטקט
   - צוותים יפעלו רק אם הועברו אליהם ישירות על ידי המנכ"ל

---

## 📦 מבנה Payload למשימות (Task Payload Structure)

### **כל משימה מורכבת תלווה ב-Payload בפורמט JSON:**

```json
{
  "task_metadata": {
    "task_id": "EA-V11-[TASK-ID]",
    "priority": "HIGH|MEDIUM|LOW",
    "executor": "Team 1|Team 2|Team 3|Team 4",
    "context": "Brief context description"
  },
  "execution_details": {
    "target_files": ["path/to/file1.php", "path/to/file2.php"],
    "target_db_tables": ["wp_posts", "wp_postmeta"],
    "action": "DESCRIBE_ACTION",
    "requirements": [
      "Requirement 1",
      "Requirement 2",
      "Requirement 3"
    ]
  },
  "verification_steps": [
    "Step 1 description",
    "Step 2 description",
    "Step 3 description"
  ],
  "success_criteria": {
    "criterion_1": "Expected result",
    "criterion_2": "Expected result"
  }
}
```

### **שדות חובה ב-Payload:**

- **Task ID:** מזהה ייחודי למשימה (פורמט: `EA-V11-[TASK-ID]`)
- **Environment:** הסביבה הרלוונטית (`Local` / `Prod`)
- **Target Files/DB:** רשימת קבצים או טבלאות לשינוי
- **Requirements:** רשימת דרישות טכניות מפורטות
- **Validation:** קריטריונים להצלחה (Success Criteria)

---

## 📋 דוגמה מלאה להודעת [DRAFT_FOR_DISPATCH]

```
# [DRAFT_FOR_DISPATCH]
**אל:** צוות 1 (Development)  
**נושא:** Phase 2.3 Step 1 - Schema JSON-LD Implementation  
**Task ID:** EA-V11-PHASE-2.3-STEP-1  
**עדיפות:** HIGH  
**סטטוס:** 🟡 AWAITING_CEO_APPROVAL

---

## הודעה פשוטה:

צוות 1, עליכם להטמיע Schema JSON-LD עבור אתר אייל עמית. הקוד כבר מוכן ומוכן להפעלה, אך נדרש לוודא שהכל עובד בפועל ולפתור כל בעיה שנמצאת.

---

## 📦 Payload:

```json
{
  "task_metadata": {
    "task_id": "EA-V11-PHASE-2.3-STEP-1",
    "priority": "HIGH",
    "executor": "Team 1",
    "context": "Phase 2.3 - Semantic SEO & Schema Infrastructure"
  },
  "execution_details": {
    "target_files": [
      "wp-content/themes/bridge-child/schema-person-specialist.php"
    ],
    "action": "VERIFY_AND_FIX_SCHEMA_IMPLEMENTATION",
    "requirements": [
      "Verify schema-person-specialist.php is loaded correctly",
      "Check Page Source for Schema markup",
      "Validate Schema at https://validator.schema.org/"
    ]
  },
  "verification_steps": [
    "Run automated test script",
    "Check Page Source",
    "Validate Schema JSON-LD"
  ],
  "success_criteria": {
    "schema_status": "Valid and verified",
    "console_status": "Zero Errors maintained"
  }
}
```

---

**הודעה זו מוכנה לאישור המנכ"ל לפני הפצה לצוות 1**
```

---

## ⚠️ כללים חשובים:

1. **כל הודעה בתוך בלוק קוד:**
   - הודעות מוצגות בתוך בלוק קוד (```) להעתקה קלה
   - המנכ"ל מעתיק את ההודעה ומעביר לצוות

2. **אין העברה פיזית:**
   - צוות 3 לא מעביר הודעות בין צוותים
   - רק המנכ"ל מפיץ הודעות הפעלה

3. **חובת אישור:**
   - כל Payload חייב אישור המנכ"ל (🟢) לפני הפצה
   - אין הפצה ללא אישור

4. **Safety Catch:**
   - צוותים לא יפעלו על סמך המלצות ללא אישור המנכ"ל
   - רק הודעות שהועברו ישירות מהמנכ"ל מחייבות ביצוע

5. **דגל צבע חובה:**
   - כל הודעה חייבת לכלול דגל צבע בסטטוס
   - אין דיווח ללא דגל צבע

---

## 📚 קבצים רלוונטיים:

- `docs/sop/SSOT.md` - הנוהל המלא (סעיפים 2.1, 3, 12, 13)
- `docs/communication/DISPATCH-PHASE-2.3-TEAM-1.md` - דוגמה להודעת הפעלה
- `docs/communication/DISPATCH-PHASE-2.3-TEAM-2.md` - דוגמה להודעת הפעלה

---

**מסמך זה מבוסס על SSOT v11.0**  
**עודכן:** 2026-01-14  
**גרסה:** 1.0
