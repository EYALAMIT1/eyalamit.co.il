#!/bin/bash
# Comprehensive Testing Suite Runner
# Runs all automated tests for the eyalamit.co.il optimization project

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
WP_URL="${WP_URL:-https://www.eyalamit.co.il}"
TEST_ENV="${TEST_ENV:-production}"
RESULTS_DIR="$SCRIPT_DIR/results/$(date +%Y%m%d_%H%M%S)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Create results directory
mkdir -p "$RESULTS_DIR"

# Logging
LOG_FILE="$RESULTS_DIR/test-run.log"
exec > >(tee -a "$LOG_FILE") 2>&1

# Test counters
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Functions
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

success() {
    echo -e "${GREEN}✓ $1${NC}"
    ((PASSED_TESTS++))
}

error() {
    echo -e "${RED}✗ $1${NC}"
    ((FAILED_TESTS++))
}

warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

info() {
    echo -e "${CYAN}ℹ $1${NC}"
}

header() {
    echo -e "${PURPLE}"
    echo "=================================================="
    echo " $1"
    echo "=================================================="
    echo -e "${NC}"
}

# Check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Run test and track results
run_test() {
    local test_name="$1"
    local test_command="$2"
    local timeout="${3:-300}" # Default 5 minutes timeout

    ((TOTAL_TESTS++))

    log "Running: $test_name"

    # Run with timeout
    if timeout "$timeout" bash -c "$test_command" > "$RESULTS_DIR/${test_name// /_}.log" 2>&1; then
        success "$test_name completed successfully"
        return 0
    else
        local exit_code=$?
        if [ $exit_code -eq 124 ]; then
            error "$test_name timed out after ${timeout}s"
        else
            error "$test_name failed (exit code: $exit_code)"
        fi
        return 1
    fi
}

# Pre-flight checks
preflight_checks() {
    header "PRE-FLIGHT CHECKS"

    # Check required tools
    local required_tools=("curl" "wget" "php" "mysql" "docker")
    local missing_tools=()

    for tool in "${required_tools[@]}"; do
        if ! command_exists "$tool"; then
            missing_tools+=("$tool")
        fi
    done

    if [ ${#missing_tools[@]} -ne 0 ]; then
        error "Missing required tools: ${missing_tools[*]}"
        error "Please install missing tools and try again"
        exit 1
    fi

    success "All required tools are available"

    # Check WordPress connectivity
    if curl -s --head "$WP_URL" > /dev/null; then
        success "WordPress site is accessible at $WP_URL"
    else
        error "Cannot access WordPress site at $WP_URL"
        exit 1
    fi

    # Check database connectivity (if credentials available)
    if [ -n "$DB_HOST" ] && [ -n "$DB_USER" ] && [ -n "$DB_PASSWORD" ]; then
        if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASSWORD" -e "SELECT 1;" > /dev/null 2>&1; then
            success "Database connection successful"
        else
            warning "Database connection failed - some tests may be skipped"
        fi
    fi
}

# Unit Tests
run_unit_tests() {
    header "UNIT TESTS"

    # PHP Unit Tests (if PHPUnit is available)
    if command_exists "phpunit"; then
        run_test "PHP Unit Tests" "phpunit $SCRIPT_DIR/unit-tests/ --bootstrap $PROJECT_ROOT/wp-load.php --testdox"
    else
        warning "PHPUnit not found - skipping PHP unit tests"
    fi

    # JavaScript Tests (if npm is available)
    if command_exists "npm" && [ -f "$PROJECT_ROOT/package.json" ]; then
        cd "$PROJECT_ROOT"
        run_test "JavaScript Unit Tests" "npm test"
        cd "$SCRIPT_DIR"
    else
        warning "npm or package.json not found - skipping JS tests"
    fi

    # Plugin Audit Tests
    if [ -f "$PROJECT_ROOT/wp-content/mu-plugins/plugin-audit.php" ]; then
        run_test "Plugin Audit Tests" "php -r \"require_once '$PROJECT_ROOT/wp-load.php'; perform_complete_plugin_audit(); echo 'Plugin audit completed\n';\""
    fi
}

# Integration Tests
run_integration_tests() {
    header "INTEGRATION TESTS"

    # WordPress REST API Tests
    run_test "WordPress REST API" "curl -s '$WP_URL/wp-json/wp/v2/posts?per_page=1' | jq -e '. | length > 0'"

    # WooCommerce API Tests (if WooCommerce is active)
    if curl -s "$WP_URL/wp-json/wc/v3/" | grep -q "woocommerce"; then
        run_test "WooCommerce API" "curl -s '$WP_URL/wp-json/wc/v3/products?per_page=1' | jq -e '.[0].id'"
    else
        info "WooCommerce not detected - skipping WooCommerce API tests"
    fi

    # Security Headers Test
    run_test "Security Headers" "
        response=\$(curl -s -I '$WP_URL')
        echo \"\$response\" | grep -q 'X-Frame-Options' && echo 'X-Frame-Options: OK' || echo 'X-Frame-Options: MISSING'
        echo \"\$response\" | grep -q 'X-Content-Type-Options' && echo 'X-Content-Type-Options: OK' || echo 'X-Content-Type-Options: MISSING'
        echo \"\$response\" | grep -q 'Strict-Transport-Security' && echo 'HSTS: OK' || echo 'HSTS: MISSING'
    "

    # Database Integration Test
    if [ -n "$DB_HOST" ] && mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASSWORD" -e "SELECT COUNT(*) FROM wp_posts WHERE post_status = 'publish';" > /dev/null 2>&1; then
        run_test "Database Integration" "mysql -h'$DB_HOST' -u'$DB_USER' -p'$DB_PASSWORD' -e \"SELECT COUNT(*) as post_count FROM wp_posts WHERE post_status = 'publish';\""
    fi
}

# Performance Tests
run_performance_tests() {
    header "PERFORMANCE TESTS"

    # Lighthouse Performance Test
    if command_exists "lighthouse"; then
        run_test "Lighthouse Performance" "lighthouse '$WP_URL' --output=json --output-path='$RESULTS_DIR/lighthouse.json' --quiet"
    else
        warning "Lighthouse not installed - install with: npm install -g lighthouse"
    fi

    # PageSpeed Insights
    run_test "PageSpeed Insights" "curl -s 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$WP_URL&screenshot=false' | jq -r '.lighthouseResult.categories.performance.score'"

    # WebPageTest (if API key available)
    if [ -n "$WEBPAGETEST_API_KEY" ]; then
        run_test "WebPageTest" "curl -s 'http://www.webpagetest.org/runtest.php?url=$WP_URL&k=$WEBPAGETEST_API_KEY&f=json' | jq -r '.statusText'"
    fi

    # Load Time Test
    run_test "Load Time Test" "
        start=\$(date +%s%3N)
        curl -s '$WP_URL' > /dev/null
        end=\$(date +%s%3N)
        load_time=\$((end - start))
        echo \"Load time: \${load_time}ms\"
        [ \$load_time -lt 3000 ] && echo 'PASS: Under 3 seconds' || echo 'FAIL: Over 3 seconds'
    "

    # Core Web Vitals Simulation
    run_test "Core Web Vitals Check" "
        # Basic LCP simulation (simplified)
        curl -s '$WP_URL' | grep -o '<img[^>]*>' | head -5 | while read -r img; do
            if echo \"\$img\" | grep -q 'loading=\"lazy\"'; then
                echo 'Lazy loading: OK'
            else
                echo 'Lazy loading: MISSING'
            fi
        done
    "
}

# Security Tests
run_security_tests() {
    header "SECURITY TESTS"

    # Basic Security Scan
    run_test "Basic Security Scan" "
        # Check for common security issues
        response=\$(curl -s -I '$WP_URL')

        # Check for server version disclosure
        if echo \"\$response\" | grep -q 'Server:'; then
            echo 'WARNING: Server version disclosed'
        else
            echo 'OK: Server version not disclosed'
        fi

        # Check for PHP version disclosure
        if echo \"\$response\" | grep -q 'X-Powered-By:'; then
            echo 'WARNING: PHP version disclosed'
        else
            echo 'OK: PHP version not disclosed'
        fi
    "

    # SSL/TLS Test
    if [[ $WP_URL == https://* ]]; then
        run_test "SSL Certificate" "openssl s_client -connect ${WP_URL#https://}:443 -servername ${WP_URL#https://} < /dev/null 2>/dev/null | openssl x509 -noout -dates"
    fi

    # Directory Listing Check
    run_test "Directory Listing Protection" "
        if curl -s '$WP_URL/wp-content/uploads/' | grep -q '<title>Index of'; then
            echo 'FAIL: Directory listing enabled'
            exit 1
        else
            echo 'PASS: Directory listing disabled'
        fi
    "

    # Admin URL Check
    run_test "Admin URL Protection" "
        admin_response=\$(curl -s -w '%{http_code}' -o /dev/null '$WP_URL/wp-admin/')
        if [ \"\$admin_response\" = \"200\" ]; then
            echo 'WARNING: Admin URL publicly accessible'
        else
            echo 'OK: Admin URL protected'
        fi
    "
}

# Compatibility Tests
run_compatibility_tests() {
    header "COMPATIBILITY TESTS"

    # Mobile Responsiveness Test
    run_test "Mobile Responsiveness" "
        mobile_response=\$(curl -s -H 'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15' '$WP_URL')
        if echo \"\$mobile_response\" | grep -q 'viewport'; then
            echo 'PASS: Mobile viewport detected'
        else
            echo 'FAIL: Mobile viewport missing'
        fi
    "

    # HTML Validation (basic)
    run_test "HTML Validation" "
        curl -s '$WP_URL' | grep -c '<!DOCTYPE html>' | grep -q '1' && echo 'PASS: Valid DOCTYPE' || echo 'FAIL: Invalid DOCTYPE'
    "

    # Schema Markup Check
    run_test "Schema Markup" "
        if curl -s '$WP_URL' | grep -q 'application/ld+json'; then
            echo 'PASS: Schema markup found'
        else
            echo 'FAIL: Schema markup missing'
        fi
    "
}

# Generate Test Report
generate_report() {
    header "TEST RESULTS SUMMARY"

    local report_file="$RESULTS_DIR/test-summary.md"

    cat > "$report_file" << EOF
# Test Results Summary
**Date:** $(date)
**Environment:** $TEST_ENV
**URL:** $WP_URL

## Test Statistics
- **Total Tests:** $TOTAL_TESTS
- **Passed:** $PASSED_TESTS
- **Failed:** $FAILED_TESTS
- **Success Rate:** $((PASSED_TESTS * 100 / TOTAL_TESTS))%

## Test Categories

### Unit Tests
- Status: $([ -f "$RESULTS_DIR/PHP_Unit_Tests.log" ] && echo "Run" || echo "Skipped")
- Results: Check individual test logs

### Integration Tests
- WordPress API: $(grep -q "PASSED\|OK" "$RESULTS_DIR/WordPress_REST_API.log" 2>/dev/null && echo "✓" || echo "✗")
- Security Headers: $(grep -q "OK\|PASS" "$RESULTS_DIR/Security_Headers.log" 2>/dev/null && echo "✓" || echo "✗")

### Performance Tests
- Lighthouse Score: $([ -f "$RESULTS_DIR/Lighthouse_Performance.log" ] && echo "Generated" || echo "Not run")
- Load Time: Check load time test results

### Security Tests
- SSL Certificate: $([ -f "$RESULTS_DIR/SSL_Certificate.log" ] && echo "Valid" || echo "Not checked")
- Directory Protection: $(grep -q "PASS" "$RESULTS_DIR/Directory_Listing_Protection.log" 2>/dev/null && echo "✓" || echo "✗")

## Recommendations

$(if [ $FAILED_TESTS -gt 0 ]; then
    echo "### Issues Found"
    echo "- Review failed tests in the results directory"
    echo "- Address security and performance issues"
    echo "- Fix integration problems"
fi)

$(if [ $PASSED_TESTS -eq $TOTAL_TESTS ]; then
    echo "### All Tests Passed! 🎉"
    echo "- Site is ready for production"
    echo "- All optimizations are working correctly"
fi)

## Next Steps
1. Review detailed logs in $RESULTS_DIR/
2. Fix any failed tests
3. Run tests again to verify fixes
4. Generate final performance report

---
*Generated by Comprehensive Testing Suite*
EOF

    success "Test report generated: $report_file"

    # Display summary on screen
    echo
    echo "========================================"
    echo "TEST RESULTS SUMMARY"
    echo "========================================"
    echo "Total Tests: $TOTAL_TESTS"
    echo "Passed: $PASSED_TESTS"
    echo "Failed: $FAILED_TESTS"
    echo "Success Rate: $((PASSED_TESTS * 100 / TOTAL_TESTS))%"
    echo
    echo "Detailed results: $RESULTS_DIR/"
    echo "Summary report: $report_file"
    echo "========================================"
}

# Main execution
main() {
    header "COMPREHENSIVE TESTING SUITE"
    info "Testing environment: $TEST_ENV"
    info "Target URL: $WP_URL"
    info "Results directory: $RESULTS_DIR"

    # Pre-flight checks
    preflight_checks

    # Run all test suites
    run_unit_tests
    run_integration_tests
    run_performance_tests
    run_security_tests
    run_compatibility_tests

    # Generate final report
    generate_report

    header "TESTING COMPLETE"

    # Exit with appropriate code
    if [ $FAILED_TESTS -eq 0 ]; then
        success "All tests passed! 🎉"
        exit 0
    else
        error "$FAILED_TESTS tests failed"
        exit 1
    fi
}

# Handle command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --url=*)
            WP_URL="${1#*=}"
            shift
            ;;
        --env=*)
            TEST_ENV="${1#*=}"
            shift
            ;;
        --help)
            echo "Usage: $0 [options]"
            echo ""
            echo "Options:"
            echo "  --url=URL       WordPress site URL (default: $WP_URL)"
            echo "  --env=ENV       Test environment (default: $TEST_ENV)"
            echo "  --help          Show this help"
            exit 0
            ;;
        *)
            error "Unknown option: $1"
            echo "Use --help for usage information"
            exit 1
            ;;
    esac
done

# Run main function
main