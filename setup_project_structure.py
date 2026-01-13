import os

# הגדרת הקבצים הבסיסיים לניהול הפרויקט
project_files = {
    "docs/sop/STANDARD-OPERATING-PROCEDURES.md": """# 📖 נוהל עבודה סטנדרטי (SOP)
## 1. מבנה הצוותים
- צוות 1: פיתוח
- צוות 2: QA
- צוות 3: דוקומנטציה וגיט (Gatekeeper)

## 2. פורטוקול דיווח
חובה להשתמש בפורמט From/To/Subject בכל הודעה.""",

    "docs/communication/MESSAGES.md": "# 📩 יומן הודעות פרויקט\n\n",

    "docs/TEAM-WORKFLOW-AND-TASKS.md": "# 📋 הנחיות עבודה וחלוקת משימות",

    "docs/ROADMAP-2026.md": "# 🗺️ מפת דרכים - אופטימיזציה 2026\n\n- [ ] שלב 1: הקמת תשתית וצוותים (בביצוע)"
}

def setup():
    print("Initializing Project Documentation Structure...")
    for path, content in project_files.items():
        os.makedirs(os.path.dirname(path), exist_ok=True)
        with open(path, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Created: {path}")
    print("\n✅ Structure ready. Team 3, please proceed with documentation updates.")

if __name__ == "__main__":
    setup()