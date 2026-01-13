#!/usr/bin/env python3
"""
Quick Site Testing Script - Focus on critical functionality
"""

import json
import sys
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.firefox.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, WebDriverException


def quick_test():
    """Quick test of critical pages"""
    results = {
        'metadata': {
            'test_start': datetime.now().isoformat(),
            'total_urls_tested': 0,
            'passed_tests': 0,
            'failed_tests': 0,
            'console_errors_total': 0
        },
        'test_results': [],
        'plugin_summary': {}
    }

    # Critical pages to test
    critical_pages = [
        {'url': 'http://host.docker.internal:9090/', 'title': 'Homepage', 'type': 'page'},
        {'url': 'http://host.docker.internal:9090/%d7%a6%d7%95%d7%a8-%d7%a7%d7%a9%d7%a8/', 'title': 'Contact Page', 'type': 'page'},
        {'url': 'http://host.docker.internal:9090/%d7%97%d7%a0%d7%95%d7%aa/', 'title': 'Shop Page', 'type': 'page'},
        {'url': 'http://host.docker.internal:9090/%d7%a2%d7%92%d7%9c%d7%aa-%d7%a7%d7%a0%d7%99%d7%95%d7%aa/', 'title': 'Cart Page', 'type': 'page'},
        {'url': 'http://host.docker.internal:9090/%d7%a7%d7%95%d7%a4%d7%94/', 'title': 'Checkout Page', 'type': 'page'}
    ]

    # Setup driver
    try:
        firefox_options = Options()
        firefox_options.add_argument('--headless')
        firefox_options.add_argument('--no-sandbox')
        firefox_options.add_argument('--disable-dev-shm-usage')

        driver = webdriver.Remote(
            command_executor='http://localhost:4444/wd/hub',
            options=firefox_options
        )
        driver.set_page_load_timeout(15)

        print("✅ WebDriver connected")

        for i, page in enumerate(critical_pages, 1):
            print(f"Testing {i}/{len(critical_pages)}: {page['title']}")

            result = {
                'url': page['url'],
                'title': page['title'],
                'type': page['type'],
                'test_time': datetime.now().isoformat(),
                'load_status': 'unknown',
                'load_time_ms': 0,
                'plugins_detected': []
            }

            try:
                start_time = datetime.now()
                driver.get(page['url'])

                WebDriverWait(driver, 15).until(
                    EC.presence_of_element_located((By.TAG_NAME, "body"))
                )

                load_time = (datetime.now() - start_time).total_seconds() * 1000
                result['load_time_ms'] = round(load_time, 2)
                result['load_status'] = 'success'
                result['page_title'] = driver.title

                # Check for plugins
                # WooCommerce
                wc_elements = driver.find_elements(By.CSS_SELECTOR, '.woocommerce, .wc-ajax-add-to-cart')
                if wc_elements:
                    result['plugins_detected'].append('woocommerce')

                # Elementor
                elementor_elements = driver.find_elements(By.CSS_SELECTOR, '[data-elementor-type], .elementor-element')
                if elementor_elements:
                    result['plugins_detected'].append('elementor')

                # Contact Form 7
                cf7_elements = driver.find_elements(By.CSS_SELECTOR, '.wpcf7-form')
                if cf7_elements:
                    result['plugins_detected'].append('contact_form_7')

                # Envira Gallery
                envira_elements = driver.find_elements(By.CSS_SELECTOR, '.envira-gallery')
                if envira_elements:
                    result['plugins_detected'].append('envira_gallery')

                results['metadata']['passed_tests'] += 1

            except Exception as e:
                result['load_status'] = 'error'
                result['error'] = str(e)
                results['metadata']['failed_tests'] += 1

            results['test_results'].append(result)
            results['metadata']['total_urls_tested'] += 1

        driver.quit()
        print("✅ WebDriver closed")

        # Save results
        with open('docs/testing/reports/quick-site-test-results.json', 'w', encoding='utf-8') as f:
            json.dump(results, f, indent=2, ensure_ascii=False, default=str)

        print("\n📊 Quick test completed!")
        print(f"   URLs tested: {results['metadata']['total_urls_tested']}")
        print(f"   Successful: {results['metadata']['passed_tests']}")
        print(f"   Failed: {results['metadata']['failed_tests']}")

        return results

    except Exception as e:
        print(f"❌ Test failed: {e}")
        return None


if __name__ == '__main__':
    quick_test()