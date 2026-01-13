📦 ברוכים הבאים לצוות 4 (Database Specialists)
מאת: ארכיטקט (Team 0)
אל: צוות 4 (DB)
באישור: CEO אייל עמית
🎯 המשימה שלכם
האתר eyalamit.co.il הוא אתר וורדפרס עתיר תוכן המבוסס על תבנית Bridge ו-WPBakery. מסד הנתונים הנוכחי סובל מ"נפיחות" (Bloat) ושגיאות תחביר ב-Shortcodes.
📐 עקרונות עבודה מחייבים:
Serialized Data Integrity: וורדפרס שומרת הגדרות רבות בפורמט Serialized. שינוי ידני של אורך מחרוזת ישבור את האובייקט. השתמשו אך ורק בכלים המודעים לפורמט זה (כמו wp search-replace).
Autoload Optimization: טבלת wp_options היא צוואר הבקבוק. עליכם לזהות ולהסיר שאריות של תוספי עבר ו-Transients שפג תוקפם.
The "ea_" Prefix: במידה ואתם יוצרים טבלאות עזר או שדות Custom Metadata, חובה להשתמש בקידומת ea_.
Smart Quotes Resolution: המשימה המיידית שלכם היא סריקה ותיקון של גרשיים "חכמים" (") שהשתרבבו ל-Shortcodes של WPBakery.
🚦 דיווח וראיות:
אתם כפופים לצוות 3 (Gatekeeper). כל פעולה שבוצעה חייבת להיות מלווה בהוכחה טקסטואלית (SQL Output/Terminal Log).
צאו לדרך, בסיס הנתונים הוא הלב של האתר. שמרו עליו.