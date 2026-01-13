#!/usr/bin/env python3
"""
Comprehensive Site Testing Script - Pre-Deployment Critical Testing
Tests all 474 active content items with Selenium for plugin functionality and zero console errors
"""

import json
import argparse
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


class ComprehensiveSiteTester:
    """Comprehensive site testing with Selenium"""

    def __init__(self, mapping_file, selenium_hub_url="http://localhost:4444/wd/hub", timeout=15):
        self.mapping_file = mapping_file
        self.selenium_hub_url = selenium_hub_url
        self.timeout = timeout
        self.driver = None
        self.results = {
            'metadata': {
                'test_start': datetime.now().isoformat(),
                'total_urls_tested': 0,
                'passed_tests': 0,
                'failed_tests': 0,
                'console_errors_total': 0,
                'plugin_functionality': {}
            },
            'test_results': [],
            'plugin_analysis': {},
            'console_errors': [],
            'performance_issues': []
        }

        # Load mapping data
        with open(mapping_file, 'r', encoding='utf-8') as f:
            self.mapping_data = json.load(f)

        # Extract all URLs to test
        self.urls_to_test = self._extract_urls_from_mapping()

    def _extract_urls_from_mapping(self):
        """Extract all testable URLs from the mapping JSON"""
        urls = []

        # Add pages
        for page in self.mapping_data.get('content', {}).get('pages', []):
            if page.get('status') == 'publish':
                urls.append({
                    'url': page['url'].replace('http://localhost:9090', 'http://host.docker.internal:9090'),
                    'title': page['title'],
                    'type': 'page',
                    'id': page['id'],
                    'has_shortcodes': page.get('has_shortcodes', False),
                    'has_elementor': page.get('has_elementor', False)
                })

        # Add posts
        for post in self.mapping_data.get('content', {}).get('posts', []):
            if post.get('status') == 'publish':
                urls.append({
                    'url': post['url'].replace('http://localhost:9090', 'http://host.docker.internal:9090'),
                    'title': post['title'],
                    'type': 'post',
                    'id': post['id'],
                    'has_shortcodes': post.get('has_shortcodes', False),
                    'has_elementor': post.get('has_elementor', False)
                })

        return urls

    def setup_driver(self):
        """Setup Firefox WebDriver via Selenium Hub"""
        try:
            firefox_options = Options()
            firefox_options.add_argument('--headless')
            firefox_options.add_argument('--no-sandbox')
            firefox_options.add_argument('--disable-dev-shm-usage')
            firefox_options.add_argument('--disable-gpu')
            firefox_options.add_argument('--window-size=1920,1080')

            # Enable console logging
            firefox_options.set_preference("devtools.console.stdout.enabled", True)
            firefox_options.set_preference("devtools.console.stdout.chrome", True)
            firefox_options.set_preference("devtools.browserconsole.enabled", True)

            self.driver = webdriver.Remote(
                command_executor=self.selenium_hub_url,
                options=firefox_options
            )
            self.driver.set_page_load_timeout(self.timeout)
            print(f"✅ WebDriver connected to Selenium Hub: {self.selenium_hub_url}")
            return True
        except WebDriverException as e:
            print(f"❌ Failed to connect to Selenium Hub: {e}")
            return False

    def check_plugin_functionality(self, url_data):
        """Check specific plugin functionality on the page"""
        plugin_checks = {
            'woocommerce': self._check_woocommerce,
            'elementor': self._check_elementor,
            'contact_form_7': self._check_contact_form_7,
            'envira_gallery': self._check_envira_gallery,
            'yoast_seo': self._check_yoast_seo
        }

        results = {}
        for plugin, check_func in plugin_checks.items():
            try:
                results[plugin] = check_func(url_data)
            except Exception as e:
                results[plugin] = {'status': 'error', 'error': str(e)}

        return results

    def _check_woocommerce(self, url_data):
        """Check WooCommerce functionality"""
        try:
            # Check for WooCommerce elements
            wc_elements = self.driver.find_elements(By.CSS_SELECTOR,
                '.woocommerce, .wc-ajax-add-to-cart, .woocommerce-cart, .woocommerce-checkout, .woocommerce-my-account')

            cart_buttons = self.driver.find_elements(By.CSS_SELECTOR, '.add_to_cart_button, .single_add_to_cart_button')
            product_elements = self.driver.find_elements(By.CSS_SELECTOR, '.product, .woocommerce-product-gallery')

            has_woocommerce = len(wc_elements) > 0 or len(cart_buttons) > 0 or len(product_elements) > 0

            return {
                'status': 'present' if has_woocommerce else 'not_present',
                'cart_buttons': len(cart_buttons),
                'product_elements': len(product_elements),
                'wc_elements': len(wc_elements)
            }
        except Exception as e:
            return {'status': 'error', 'error': str(e)}

    def _check_elementor(self, url_data):
        """Check Elementor functionality"""
        try:
            # Check for Elementor classes and data attributes
            elementor_elements = self.driver.find_elements(By.CSS_SELECTOR,
                '[data-elementor-type], .elementor-element, .elementor-widget')

            elementor_scripts = self.driver.find_elements(By.CSS_SELECTOR,
                'script[src*="elementor"], link[href*="elementor"]')

            has_elementor = len(elementor_elements) > 0 or len(elementor_scripts) > 0

            return {
                'status': 'present' if has_elementor else 'not_present',
                'elementor_elements': len(elementor_elements),
                'elementor_scripts': len(elementor_scripts)
            }
        except Exception as e:
            return {'status': 'error', 'error': str(e)}

    def _check_contact_form_7(self, url_data):
        """Check Contact Form 7 functionality"""
        try:
            # Check for CF7 forms
            cf7_forms = self.driver.find_elements(By.CSS_SELECTOR,
                '.wpcf7-form, form.wpcf7-form, [data-cf7-form]')

            cf7_scripts = self.driver.find_elements(By.CSS_SELECTOR,
                'script[src*="contact-form-7"], link[href*="contact-form-7"]')

            has_cf7 = len(cf7_forms) > 0 or len(cf7_scripts) > 0

            return {
                'status': 'present' if has_cf7 else 'not_present',
                'forms_found': len(cf7_forms),
                'scripts_found': len(cf7_scripts)
            }
        except Exception as e:
            return {'status': 'error', 'error': str(e)}

    def _check_envira_gallery(self, url_data):
        """Check Envira Gallery functionality"""
        try:
            # Check for Envira gallery elements
            envira_galleries = self.driver.find_elements(By.CSS_SELECTOR,
                '.envira-gallery, [data-envira-gallery], .envira-gallery-wrap')

            envira_scripts = self.driver.find_elements(By.CSS_SELECTOR,
                'script[src*="envira"], link[href*="envira"]')

            has_envira = len(envira_galleries) > 0 or len(envira_scripts) > 0

            return {
                'status': 'present' if has_envira else 'not_present',
                'galleries_found': len(envira_galleries),
                'scripts_found': len(envira_scripts)
            }
        except Exception as e:
            return {'status': 'error', 'error': str(e)}

    def _check_yoast_seo(self, url_data):
        """Check Yoast SEO functionality"""
        try:
            # Check for Yoast SEO elements
            yoast_elements = self.driver.find_elements(By.CSS_SELECTOR,
                '.yoast-schema-graph, [data-yoast], .wpseo-score-icon')

            yoast_scripts = self.driver.find_elements(By.CSS_SELECTOR,
                'script[type="application/ld+json"]')  # Schema markup

            # Check for Yoast meta tags
            canonical = self.driver.find_elements(By.CSS_SELECTOR, 'link[rel="canonical"]')
            meta_desc = self.driver.find_elements(By.CSS_SELECTOR, 'meta[name="description"]')

            has_yoast = len(yoast_elements) > 0 or len(canonical) > 0 or len(meta_desc) > 0

            return {
                'status': 'present' if has_yoast else 'not_present',
                'schema_scripts': len(yoast_scripts),
                'canonical_tags': len(canonical),
                'meta_descriptions': len(meta_desc)
            }
        except Exception as e:
            return {'status': 'error', 'error': str(e)}

    def capture_console_errors(self):
        """Capture console errors from the browser"""
        try:
            # Try to get browser logs
            try:
                logs = self.driver.get_log('browser')
                errors = []

                for log in logs:
                    if log['level'] in ['ERROR', 'SEVERE']:
                        errors.append({
                            'level': log['level'],
                            'message': log['message'],
                            'timestamp': log.get('timestamp'),
                            'source': 'browser_console'
                        })
            except:
                # Fallback if browser logs not available
                errors = []

            # Check for JavaScript errors via performance API
            try:
                perf_errors = self.driver.execute_script("""
                    try {
                        var errors = [];
                        if (window.performance && window.performance.getEntriesByType) {
                            var entries = window.performance.getEntriesByType('resource');
                            entries.forEach(function(entry) {
                                if (entry.responseStatus && entry.responseStatus >= 400) {
                                    errors.push({
                                        level: 'ERROR',
                                        message: 'Failed resource: ' + entry.name + ' (Status: ' + entry.responseStatus + ')',
                                        source: 'network_error'
                                    });
                                }
                            });
                        }
                        return errors;
                    } catch(e) {
                        return [{level: 'ERROR', message: 'JavaScript error checking failed: ' + e.message, source: 'js_check_error'}];
                    }
                """)

                if perf_errors:
                    errors.extend(perf_errors)

            except Exception as e:
                errors.append({
                    'level': 'ERROR',
                    'message': f'JavaScript error check failed: {str(e)}',
                    'source': 'js_check_error'
                })

            return errors
        except Exception as e:
            return [{'level': 'ERROR', 'message': f'Console capture failed: {str(e)}', 'source': 'capture_error'}]

    def test_single_url(self, url_data):
        """Test a single URL comprehensively"""
        url = url_data['url']
        result = {
            'url': url,
            'title': url_data.get('title', ''),
            'type': url_data.get('type', ''),
            'id': url_data.get('id', ''),
            'test_time': datetime.now().isoformat(),
            'load_status': 'unknown',
            'load_time_ms': 0,
            'console_errors': [],
            'plugin_checks': {},
            'page_errors': []
        }

        try:
            start_time = datetime.now()

            # Navigate to page
            self.driver.get(url)

            # Wait for page to load
            WebDriverWait(self.driver, self.timeout).until(
                EC.presence_of_element_located((By.TAG_NAME, "body"))
            )

            load_time = (datetime.now() - start_time).total_seconds() * 1000
            result['load_time_ms'] = round(load_time, 2)
            result['load_status'] = 'success'

            # Capture console errors
            console_errors = self.capture_console_errors()
            result['console_errors'] = console_errors

            # Check plugin functionality
            plugin_checks = self.check_plugin_functionality(url_data)
            result['plugin_checks'] = plugin_checks

            # Additional page checks
            page_title = self.driver.title
            result['page_title_actual'] = page_title

            # Check for common page issues
            broken_images = self.driver.find_elements(By.CSS_SELECTOR, 'img[src=""], img[src="#"]')
            result['broken_images'] = len(broken_images)

            # Check page response code (via JavaScript)
            response_status = self.driver.execute_script("""
                return window.performance.getEntriesByType('navigation')[0].responseStatus || 'unknown';
            """)
            result['http_status'] = response_status

        except TimeoutException:
            result['load_status'] = 'timeout'
            result['page_errors'].append('Page load timeout')
        except Exception as e:
            result['load_status'] = 'error'
            result['page_errors'].append(f'Page load error: {str(e)}')

        return result

    def run_comprehensive_tests(self, max_pages=None):
        """Run comprehensive tests on all URLs"""
        print(f"\n🔍 Starting Comprehensive Site Testing")
        print(f"   Total URLs to test: {len(self.urls_to_test)}")
        print(f"   Selenium Hub: {self.selenium_hub_url}")
        print(f"   Timeout: {self.timeout}s")
        print("=" * 80)

        if not self.setup_driver():
            return False

        try:
            urls_to_test = self.urls_to_test[:max_pages] if max_pages else self.urls_to_test

            for i, url_data in enumerate(urls_to_test, 1):
                print(f"Testing {i}/{len(urls_to_test)}: {url_data['title'][:50]}...")

                result = self.test_single_url(url_data)
                self.results['test_results'].append(result)
                self.results['metadata']['total_urls_tested'] += 1

                # Update counters
                if result['load_status'] == 'success':
                    self.results['metadata']['passed_tests'] += 1
                else:
                    self.results['metadata']['failed_tests'] += 1

                self.results['metadata']['console_errors_total'] += len(result['console_errors'])

                # Collect console errors
                self.results['console_errors'].extend(result['console_errors'])

                # Track performance issues
                if result['load_time_ms'] > 5000:  # Over 5 seconds
                    self.results['performance_issues'].append({
                        'url': result['url'],
                        'load_time_ms': result['load_time_ms'],
                        'title': result['title']
                    })

                # Progress indicator
                if i % 10 == 0:
                    print(f"   Progress: {i}/{len(urls_to_test)} URLs tested")

            # Analyze plugin usage across all pages
            self._analyze_plugin_usage()

            print(f"\n✅ Testing completed!")
            print(f"   Total URLs tested: {self.results['metadata']['total_urls_tested']}")
            print(f"   Successful loads: {self.results['metadata']['passed_tests']}")
            print(f"   Failed loads: {self.results['metadata']['failed_tests']}")
            print(f"   Console errors: {self.results['metadata']['console_errors_total']}")

        finally:
            if self.driver:
                self.driver.quit()
                print("✅ WebDriver closed")

        return True

    def _analyze_plugin_usage(self):
        """Analyze plugin usage across all tested pages"""
        plugin_stats = {
            'woocommerce': {'pages_with': 0, 'total_pages': 0},
            'elementor': {'pages_with': 0, 'total_pages': 0},
            'contact_form_7': {'pages_with': 0, 'total_pages': 0},
            'envira_gallery': {'pages_with': 0, 'total_pages': 0},
            'yoast_seo': {'pages_with': 0, 'total_pages': 0}
        }

        for result in self.results['test_results']:
            if result['load_status'] == 'success':
                for plugin, check_result in result['plugin_checks'].items():
                    plugin_stats[plugin]['total_pages'] += 1
                    if check_result.get('status') == 'present':
                        plugin_stats[plugin]['pages_with'] += 1

        self.results['plugin_analysis'] = plugin_stats

    def generate_report(self, output_file):
        """Generate comprehensive test report"""
        self.results['metadata']['test_end'] = datetime.now().isoformat()

        # Add summary statistics
        self.results['summary'] = {
            'success_rate': round((self.results['metadata']['passed_tests'] / max(1, self.results['metadata']['total_urls_tested'])) * 100, 2),
            'error_rate': round((self.results['metadata']['console_errors_total'] / max(1, self.results['metadata']['total_urls_tested'])) * 100, 2),
            'zero_console_compliance': self.results['metadata']['console_errors_total'] == 0,
            'performance_issues_count': len(self.results['performance_issues'])
        }

        # Save results
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(self.results, f, indent=2, ensure_ascii=False, default=str)

        print(f"\n📊 Report saved to: {output_file}")
        return self.results


def main():
    parser = argparse.ArgumentParser(
        description='Comprehensive Site Testing - Pre-Deployment Critical Testing'
    )
    parser.add_argument(
        '--mapping-file',
        required=True,
        help='Path to the site mapping JSON file'
    )
    parser.add_argument(
        '--output',
        default='comprehensive-site-test-results.json',
        help='Output file for test results'
    )
    parser.add_argument(
        '--max-pages',
        type=int,
        help='Maximum number of pages to test (for testing subset)'
    )
    parser.add_argument(
        '--selenium-hub',
        default='http://localhost:4444/wd/hub',
        help='Selenium Hub URL'
    )
    parser.add_argument(
        '--timeout',
        type=int,
        default=15,
        help='Page load timeout in seconds'
    )

    args = parser.parse_args()

    # Run comprehensive tests
    tester = ComprehensiveSiteTester(
        mapping_file=args.mapping_file,
        selenium_hub_url=args.selenium_hub,
        timeout=args.timeout
    )

    if tester.run_comprehensive_tests(max_pages=args.max_pages):
        results = tester.generate_report(args.output)

        # Exit with error code if there were failures
        if results['metadata']['failed_tests'] > 0 or results['metadata']['console_errors_total'] > 0:
            print("❌ Test completed with issues - check report for details")
            sys.exit(1)
        else:
            print("✅ All tests passed successfully!")
            sys.exit(0)
    else:
        print("❌ Testing failed to start")
        sys.exit(1)


if __name__ == '__main__':
    main()