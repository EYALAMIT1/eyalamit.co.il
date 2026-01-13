#!/bin/bash
# Quick script to run console verification test
# Usage: ./tests/run_console_test.sh [URL]

URL=${1:-"http://localhost:9090"}
OUTPUT_DIR="docs/testing/reports"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "🔍 Running Console Verification Test"
echo "   URL: $URL"
echo "   Output: $OUTPUT_DIR/console-log-${TIMESTAMP}.txt"
echo ""

# Create output directory if it doesn't exist
mkdir -p "$OUTPUT_DIR"

# Run test
python3 tests/console_verification_test.py \
    --url "$URL" \
    --output "$OUTPUT_DIR/console-log-${TIMESTAMP}.txt" \
    --json "$OUTPUT_DIR/console-log-${TIMESTAMP}.json"

echo ""
echo "✅ Test completed. Check output files in $OUTPUT_DIR/"
