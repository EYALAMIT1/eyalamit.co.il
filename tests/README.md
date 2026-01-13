# Automated Browser Testing Suite

This directory contains automated browser testing scripts using Selenium + Firefox.

## Quick Start

1. **Install Python dependencies:**
   ```bash
   pip3 install -r requirements-testing.txt
   ```

2. **Start Selenium services:**
   ```bash
   docker-compose up -d selenium-hub firefox-node
   ```

3. **Run console verification test:**
   ```bash
   python3 tests/console_verification_test.py
   ```

## Available Tests

### Console Verification Test

Automated browser console testing that captures:
- JavaScript errors (Uncaught TypeError, ReferenceError, etc.)
- Console logs (console.log, console.error, etc.)
- Network errors (failed requests)
- CORS errors
- jQuery errors

**Usage:**
```bash
# Basic test (default: http://localhost:9090)
python3 tests/console_verification_test.py

# Test specific URL
python3 tests/console_verification_test.py --url http://localhost:9090/about

# Save output to file
python3 tests/console_verification_test.py --output logs/console-log.txt

# Save JSON report
python3 tests/console_verification_test.py --json logs/console-log.json
```

## Output Format

The test generates:
- **Text Report** - Human-readable console log output (SSOT v8.0 compliant)
- **JSON Report** - Machine-readable data for automated analysis

## Integration with SSOT v8.0

This test suite fulfills the **Zero Console Error Policy** requirement:
- ✅ Automated browser console testing
- ✅ Text-based console log evidence
- ✅ No manual intervention required
- ✅ Compatible with Team 2 verification workflow

## Troubleshooting

See `docs/testing/SELENIUM-SETUP-GUIDE.md` for detailed troubleshooting guide.
