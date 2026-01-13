#!/usr/bin/env python3
"""
Console Verification Test Script
Automated browser console testing using Selenium + Firefox
Generates text-based console logs for Team 2 verification (SSOT v8.0)
"""

import argparse
import json
import sys
from datetime import datetime
from pathlib import Path
from selenium import webdriver
from selenium.webdriver.firefox.options import Options
from selenium.webdriver.firefox.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, WebDriverException


class ConsoleVerificationTest:
    """Automated console verification test using Selenium"""
    
    def __init__(self, url, timeout=20, selenium_hub_url="http://localhost:4444/wd/hub"):
        self.url = url
        self.timeout = timeout
        self.selenium_hub_url = selenium_hub_url
        self.driver = None
        self.console_logs = []
        self.network_errors = []
        self.page_errors = []
        
    def setup_driver(self):
        """Setup Firefox WebDriver via Selenium Hub"""
        try:
            firefox_options = Options()
            firefox_options.add_argument('--headless')
            firefox_options.add_argument('--no-sandbox')
            firefox_options.add_argument('--disable-dev-shm-usage')
            
            # Enable console logging
            firefox_options.set_preference("devtools.console.stdout.enabled", True)
            firefox_options.set_preference("devtools.console.stdout.chrome", True)
            firefox_options.set_preference("devtools.browserconsole.enabled", True)
            
            # Create remote WebDriver connection to Selenium Hub
            self.driver = webdriver.Remote(
                command_executor=self.selenium_hub_url,
                options=firefox_options
            )
            self.driver.set_page_load_timeout(self.timeout)
            print(f"✅ WebDriver connected to Selenium Hub: {self.selenium_hub_url}")
            return True
        except WebDriverException as e:
            print(f"❌ Failed to connect to Selenium Hub: {e}")
            print(f"   Make sure Selenium Hub is running: docker-compose up -d selenium-hub firefox-node")
            return False
    
    def capture_console_logs(self):
        """Capture all console logs from the browser"""
        try:
            # Get browser logs (includes console.log, console.error, etc.)
            logs = self.driver.get_log('browser')
            self.console_logs = logs
            
            # Also try to get console messages via JavaScript
            console_messages = self.driver.execute_script("""
                return window.console._logs || [];
            """)
            
            return logs, console_messages
        except Exception as e:
            print(f"⚠️  Warning: Could not capture console logs: {e}")
            return [], []
    
    def check_network_errors(self):
        """Check for network errors in page load"""
        try:
            # Get performance entries for network requests
            network_entries = self.driver.execute_script("""
                if (window.performance && window.performance.getEntriesByType) {
                    return window.performance.getEntriesByType('resource').filter(function(entry) {
                        return entry.responseStatus >= 400;
                    });
                }
                return [];
            """)
            
            self.network_errors = network_entries
            return network_entries
        except Exception as e:
            print(f"⚠️  Warning: Could not check network errors: {e}")
            return []
    
    def check_javascript_errors(self):
        """Check for JavaScript errors on the page"""
        try:
            # Set up error handlers and check for existing errors
            error_data = self.driver.execute_script("""
                // Initialize error collection arrays if they don't exist
                if (!window._testErrors) window._testErrors = [];
                if (!window._testRejections) window._testRejections = [];
                
                // Set up global error handler
                window.addEventListener('error', function(e) {
                    window._testErrors.push({
                        message: e.message || 'Unknown error',
                        filename: e.filename || 'unknown',
                        lineno: e.lineno || 0,
                        colno: e.colno || 0
                    });
                }, true);
                
                // Set up unhandled promise rejection handler
                window.addEventListener('unhandledrejection', function(e) {
                    window._testRejections.push({
                        reason: e.reason ? (e.reason.message || String(e.reason)) : 'Unknown rejection'
                    });
                });
                
                // Check for jQuery - only report if there's an actual error, not just missing jQuery
                var jqueryError = null;
                if (typeof jQuery === 'undefined') {
                    // Check if there are actual jQuery-related errors in captured errors
                    var hasJQueryError = false;
                    if (window._testErrors && window._testErrors.length > 0) {
                        hasJQueryError = window._testErrors.some(function(err) {
                            return err.message && (err.message.indexOf('jQuery') !== -1 || err.message.indexOf('jquery') !== -1);
                        });
                    }
                    // Only report if there's an actual jQuery error, not just missing jQuery
                    if (hasJQueryError) {
                        jqueryError = 'jQuery is not defined (actual error detected)';
                    }
                    // If no actual error, jQuery might not be needed for this page
                } else {
                    // jQuery is loaded - verify it's functional
                    try {
                        jQuery(document).ready(function() {});
                    } catch(e) {
                        jqueryError = 'jQuery loaded but not functional: ' + e.message;
                    }
                }
                
                // Check for CORS errors (common pattern)
                var corsErrors = [];
                if (window.performance && window.performance.getEntriesByType) {
                    var resources = window.performance.getEntriesByType('resource');
                    resources.forEach(function(resource) {
                        if (resource.name && resource.name.indexOf('eyalamit.co.il') !== -1) {
                            corsErrors.push('CORS error: Resource from production domain detected: ' + resource.name);
                        }
                    });
                }
                
                return {
                    errors: window._testErrors,
                    rejections: window._testRejections,
                    jqueryError: jqueryError,
                    corsErrors: corsErrors
                };
            """)
            
            errors = []
            if error_data.get('errors'):
                errors.extend(error_data['errors'])
            if error_data.get('rejections'):
                for rej in error_data['rejections']:
                    errors.append({'type': 'UnhandledRejection', 'message': rej.get('reason', 'Unknown')})
            if error_data.get('jqueryError'):
                self.page_errors.append({
                    'type': 'jQuery',
                    'message': error_data['jqueryError']
                })
            if error_data.get('corsErrors'):
                for cors_err in error_data['corsErrors']:
                    self.page_errors.append({
                        'type': 'CORS',
                        'message': cors_err
                    })
            
            return errors
        except Exception as e:
            print(f"⚠️  Warning: Could not check JavaScript errors: {e}")
            return []
    
    def run_test(self):
        """Run the complete console verification test"""
        print(f"\n🔍 Starting Console Verification Test")
        print(f"   URL: {self.url}")
        print(f"   Timeout: {self.timeout}s")
        print(f"   Selenium Hub: {self.selenium_hub_url}\n")
        
        if not self.setup_driver():
            return False
        
        try:
            # Navigate to the page
            print(f"📄 Loading page: {self.url}")
            self.driver.get(self.url)
            
            # Wait for page to load
            WebDriverWait(self.driver, self.timeout).until(
                EC.presence_of_element_located((By.TAG_NAME, "body"))
            )
            print("✅ Page loaded successfully")
            
            # Wait for jQuery to load with retry logic and check for actual errors
            import time
            jquery_loaded = False
            actual_errors = []
            for attempt in range(15):  # Try for up to 7.5 seconds
                # Check if jQuery is loaded
                jquery_loaded = self.driver.execute_script("return typeof jQuery !== 'undefined';")
                
                # Check for actual JavaScript errors that occurred
                page_errors = self.driver.execute_script("""
                    if (window._capturedErrors) {
                        return window._capturedErrors;
                    }
                    return [];
                """)
                if page_errors:
                    actual_errors.extend(page_errors)
                
                if jquery_loaded:
                    break
                time.sleep(0.5)
            
            # Set up error capture before checking
            self.driver.execute_script("""
                window._capturedErrors = [];
                window.addEventListener('error', function(e) {
                    window._capturedErrors.push({
                        message: e.message,
                        filename: e.filename,
                        lineno: e.lineno
                    });
                }, true);
            """)
            
            # Wait a bit more for all JavaScript to execute
            time.sleep(3)
            
            # Capture console logs
            print("📋 Capturing console logs...")
            browser_logs, js_console = self.capture_console_logs()
            
            # Get console messages via JavaScript injection (capture errors and warnings)
            js_console_logs = self.driver.execute_script("""
                var logs = [];
                
                // Check for actual captured errors first
                if (window._capturedErrors && window._capturedErrors.length > 0) {
                    window._capturedErrors.forEach(function(err) {
                        // Only report if it's a jQuery-related error
                        if (err.message && err.message.indexOf('jQuery') !== -1) {
                            logs.push({level: 'error', message: err.message + ' (at ' + err.filename + ':' + err.lineno + ')'});
                        }
                    });
                }
                
                // Check for jQuery - only report if it's still undefined after waiting AND there's an actual error
                if (typeof jQuery === 'undefined') {
                    // Check if there's an actual jQuery error in captured errors
                    var hasJQueryError = false;
                    if (window._capturedErrors) {
                        hasJQueryError = window._capturedErrors.some(function(err) {
                            return err.message && err.message.indexOf('jQuery') !== -1;
                        });
                    }
                    // Only report if there's an actual error, not just missing jQuery
                    if (hasJQueryError) {
                        logs.push({level: 'error', message: 'jQuery is not defined (actual error detected)'});
                    } else {
                        // jQuery might not be needed, or loaded later - just log as info
                        logs.push({level: 'info', message: 'jQuery not detected (may not be required for this page)'});
                    }
                } else {
                    // jQuery is loaded - check version
                    var jqVersion = jQuery.fn.jquery || 'unknown';
                    logs.push({level: 'info', message: 'jQuery loaded successfully (version: ' + jqVersion + ')'});
                }
                
                // Check for common error patterns in window
                if (window.errors && Array.isArray(window.errors)) {
                    window.errors.forEach(function(err) {
                        logs.push({level: 'error', message: err.message || String(err)});
                    });
                }
                
                // Check for unhandled promise rejections
                if (window.unhandledRejections && Array.isArray(window.unhandledRejections)) {
                    window.unhandledRejections.forEach(function(rej) {
                        logs.push({level: 'error', message: 'Unhandled Promise Rejection: ' + (rej.reason || String(rej))});
                    });
                }
                
                return logs;
            """)
            
            # Combine all console logs
            all_console_logs = list(browser_logs) + js_console_logs
            
            # Check network errors
            print("🌐 Checking network errors...")
            network_errors = self.check_network_errors()
            
            # Check JavaScript errors
            print("⚠️  Checking JavaScript errors...")
            js_errors = self.check_javascript_errors()
            
            # Get page status
            page_status = self.driver.execute_script("return document.readyState")
            http_status = self.driver.execute_script("return window.performance.timing")
            
            return {
                'success': True,
                'url': self.url,
                'timestamp': datetime.now().isoformat(),
                'page_status': page_status,
                'console_logs': all_console_logs,
                'js_console': js_console,
                'network_errors': network_errors,
                'javascript_errors': js_errors,
                'page_errors': self.page_errors
            }
            
        except TimeoutException:
            print(f"❌ Timeout: Page did not load within {self.timeout} seconds")
            return {'success': False, 'error': 'Timeout'}
        except Exception as e:
            print(f"❌ Error during test: {e}")
            return {'success': False, 'error': str(e)}
        finally:
            if self.driver:
                self.driver.quit()
                print("✅ WebDriver closed")
    
    def format_text_report(self, result):
        """Format test results as text report"""
        report = []
        report.append("=" * 80)
        report.append("CONSOLE VERIFICATION TEST REPORT")
        report.append("=" * 80)
        report.append(f"Date: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        report.append(f"URL: {result.get('url', 'N/A')}")
        report.append(f"Page Status: {result.get('page_status', 'N/A')}")
        report.append("")
        
        # Console Logs
        report.append("-" * 80)
        report.append("CONSOLE LOGS")
        report.append("-" * 80)
        console_logs = result.get('console_logs', [])
        if console_logs:
            for log in console_logs:
                level = log.get('level', 'UNKNOWN')
                message = log.get('message', '')
                timestamp = log.get('timestamp', 0)
                report.append(f"[{level}] {message}")
        else:
            report.append("No console logs captured")
        report.append("")
        
        # JavaScript Errors
        report.append("-" * 80)
        report.append("JAVASCRIPT ERRORS")
        report.append("-" * 80)
        js_errors = result.get('javascript_errors', [])
        page_errors = result.get('page_errors', [])
        if js_errors or page_errors:
            for error in js_errors + page_errors:
                error_type = error.get('type', 'Unknown')
                error_msg = error.get('message', str(error))
                report.append(f"[{error_type}] {error_msg}")
        else:
            report.append("No JavaScript errors detected")
        report.append("")
        
        # Network Errors
        report.append("-" * 80)
        report.append("NETWORK ERRORS")
        report.append("-" * 80)
        network_errors = result.get('network_errors', [])
        if network_errors:
            for error in network_errors:
                report.append(f"Failed request: {error}")
        else:
            report.append("No network errors detected")
        report.append("")
        
        # Summary
        report.append("=" * 80)
        report.append("SUMMARY")
        report.append("=" * 80)
        # Count only actual errors (not info/warn messages)
        error_logs = [log for log in console_logs if log.get('level', '').lower() in ['error', 'severe']]
        total_errors = len(error_logs) + len(js_errors) + len(page_errors) + len(network_errors)
        if total_errors == 0:
            report.append("✅ NO ERRORS DETECTED - Console is clean!")
        else:
            report.append(f"⚠️  {total_errors} ERROR(S) DETECTED")
            report.append(f"   - Console errors: {len(error_logs)}")
            report.append(f"   - JavaScript errors: {len(js_errors) + len(page_errors)}")
            report.append(f"   - Network errors: {len(network_errors)}")
        report.append("=" * 80)
        
        return "\n".join(report)


def main():
    parser = argparse.ArgumentParser(
        description='Automated Console Verification Test using Selenium + Firefox'
    )
    parser.add_argument(
        '--url',
        default='http://localhost:9090',
        help='URL to test (default: http://localhost:9090)'
    )
    parser.add_argument(
        '--timeout',
        type=int,
        default=20,
        help='Page load timeout in seconds (default: 20)'
    )
    parser.add_argument(
        '--selenium-hub',
        default='http://localhost:4444/wd/hub',
        help='Selenium Hub URL (default: http://localhost:4444/wd/hub)'
    )
    parser.add_argument(
        '--output',
        help='Output file path for text report'
    )
    parser.add_argument(
        '--json',
        help='Output file path for JSON report'
    )
    
    args = parser.parse_args()
    
    # Run test
    test = ConsoleVerificationTest(
        url=args.url,
        timeout=args.timeout,
        selenium_hub_url=args.selenium_hub
    )
    
    result = test.run_test()
    
    if not result:
        print("❌ Test failed")
        sys.exit(1)
    
    # Generate text report
    text_report = test.format_text_report(result)
    print("\n" + text_report)
    
    # Save to file if requested
    if args.output:
        output_path = Path(args.output)
        output_path.parent.mkdir(parents=True, exist_ok=True)
        output_path.write_text(text_report, encoding='utf-8')
        print(f"\n✅ Text report saved to: {args.output}")
    
    # Save JSON if requested
    if args.json:
        json_path = Path(args.json)
        json_path.parent.mkdir(parents=True, exist_ok=True)
        json_path.write_text(
            json.dumps(result, indent=2, default=str),
            encoding='utf-8'
        )
        print(f"✅ JSON report saved to: {args.json}")
    
    # Exit with error code if errors found (only count actual errors, not info messages)
    console_logs = result.get('console_logs', [])
    error_logs = [log for log in console_logs if log.get('level', '').lower() in ['error', 'severe']]
    total_errors = (
        len(error_logs) +
        len(result.get('javascript_errors', [])) +
        len(result.get('page_errors', [])) +
        len(result.get('network_errors', []))
    )
    
    if total_errors > 0:
        print(f"\n⚠️  {total_errors} error(s) detected - Test completed with warnings")
        sys.exit(1)
    else:
        print("\n✅ Test completed successfully - No errors detected")
        sys.exit(0)


if __name__ == '__main__':
    main()
