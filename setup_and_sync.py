import os
import sys

# נתיב הפרויקט המחייב - מאומת ע"י המנכ"ל
REQUIRED_PATH = "/Users/nimrod/Documents/Eyal Amit/eyalamit.co.il"

def check_path():
    """בדיקה שהסקריפט רץ בתיקיית השורש הנכונה של הפרויקט"""
    if os.getcwd() != REQUIRED_PATH:
        print(f"❌ ERROR: הסקריפט חייב לרוץ מתוך הנתיב: {REQUIRED_PATH}")
        print(f"נתיב נוכחי: {os.getcwd()}")
        sys.exit(1)

def generate_manifest():
    """מייצר מניפסט טקסטואלי עבור הארכיטקט בקנבס"""
    check_path()
    output = "SYNC_MANIFEST.txt"
    # נתיבים קריטיים לסנכרון
    paths = ['docs/sop/', 'docs/project/', 'wp-content/mu-plugins/']
    
    print("📦 מייצר מניפסט לבדיקת מנכ\"ל/ארכיטקט...")
    with open(output, "w", encoding="utf-8") as m:
        m.write("=== PROJECT SYNC MANIFEST v4.7 ===\n")
        m.write(f"Authority: CEO Eyal Amit Verified\n")
        m.write(f"Branch: {os.popen('git branch --show-current 2>/dev/null').read().strip() or 'main'}\n\n")
        
        for path in paths:
            if os.path.exists(path):
                for root, dirs, files in os.walk(path):
                    for file in files:
                        if file.endswith(('.md', '.php', '.txt')):
                            f_path = os.path.join(root, file)
                            m.write(f"--- FILE: {f_path} ---\n")
                            try:
                                with open(f_path, "r", encoding="utf-8") as f:
                                    m.write(f.read())
                            except Exception as e:
                                m.write(f"[Error reading file: {e}]")
                            m.write("\n--- END OF FILE ---\n")
    print(f"✅ הקובץ {output} נוצר בהצלחה. העתק את תוכנו לצ'אט בקנבס.")

def apply_payload():
    """מטמיע עדכונים מהקנבס לתוך הסביבה המקומית"""
    check_path()
    print("📥 הדבק את ה-PAYLOAD מהקנבס (סיים עם המילה 'END_PAYLOAD' בשורה חדשה):")
    lines = []
    while True:
        line = sys.stdin.readline()
        if not line or line.strip() == "END_PAYLOAD":
            break
        lines.append(line)
    
    content = "".join(lines)
    if "--- FILE: " not in content:
        print("❌ שגיאה: פורמט ה-Payload אינו תקין. הפעולה בוטלה.")
        return

    # פירוק הבלוק לקבצים בודדים ופריסתם
    for part in content.split("--- FILE: ")[1:]:
        try:
            header_end = part.find(" ---")
            filepath = part[:header_end].strip()
            body_start = header_end + 4
            body_end = part.find("--- END OF FILE ---")
            file_content = part[body_start:body_end].strip()
            
            # יצירת תיקיות במידה ואינן קיימות
            os.makedirs(os.path.dirname(filepath), exist_ok=True)
            with open(filepath, "w", encoding="utf-8") as f:
                f.write(file_content)
            print(f"🚀 עודכן: {filepath}")
        except Exception as e:
            print(f"❌ שגיאה בעיבוד קובץ: {e}")

if __name__ == "__main__":
    print("="*45)
    print("👑 eyalamit.co.il Sync Tool v4.7")
    print("Authority: CEO Eyal Amit")
    print("="*45)
    
    mode = input("בחר מצב: [G]enerate Manifest או [A]pply Payload? ").lower()
    if mode == 'g':
        generate_manifest()
    elif mode == 'a':
        apply_payload()
    else:
        print("❌ בחירה לא תקינה.")