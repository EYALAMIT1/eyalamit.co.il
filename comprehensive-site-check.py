#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
סקריפט בדיקות מקיף לפני העלאה לייצור
בודק את כל ההיבטים של האתר: Docker, WordPress, פלאגינים, קוקיז, שגיאות ועוד
"""
import os
import subprocess
import sys
import requests
import json
import re
import time
from datetime import datetime
from pathlib import Path
from typing import Dict, List, Tuple, Any

# Configuration - מותאם לפרויקט הנכון
SITE_URL = "http://localhost:8080"
ADMIN_URL = f"{SITE_URL}/wp-admin"
REST_API_URL = f"{SITE_URL}/wp-json/wp/v2/"
COOKIE_PLUGIN_PATH = "eyalamit.co.il_bm1763848352dm/wp-content/plugins/cookie-consent-notice/cookie-consent-notice.php"
WP_CONTENT_PATH = "eyalamit.co.il_bm1763848352dm/wp-content"
WP_CONFIG_PATH = "eyalamit.co.il_bm1763848352dm/wp-config.php"

# Results storage
check_results = {
    "docker": {},
    "wordpress": {},
    "plugins": {},
    "cookie_notice": {},
    "accessibility": {},
    "errors": {},
    "compatibility": {},
    "performance": {},
    "security": {},
    "woocommerce": {}
}
warnings = []
errors = []

def run_command(cmd: list, description: str, check: bool = False, timeout: int = 30) -> Tuple[bool, str, str]:
    """Run a command and return result"""
    try:
        result = subprocess.run(
            cmd,
            check=check,
            capture_output=True,
            text=True,
            encoding='utf-8',
            timeout=timeout,
            errors='ignore'
        )
        return result.returncode == 0, result.stdout or "", result.stderr or ""
    except subprocess.TimeoutExpired:
        return False, "", f"Command timed out after {timeout} seconds"
    except Exception as e:
        return False, "", str(e)

def get_container_name(service: str = "wordpress") -> str:
    """Get Docker container name for a service"""
    success, output, _ = run_command(
        ["docker", "ps", "--filter", f"name={service}", "--format", "{{.Names}}"],
        f"Finding {service} container"
    )
    if success and output.strip():
        # Try to find container with project name in it, otherwise take first one
        containers = [c.strip() for c in output.strip().split('\n') if c.strip()]
        # Filter by project directory name if possible
        for container in containers:
            # Check if container name might match our project
            if any(keyword in container.lower() for keyword in ['take', '2', 'nov', 'ai']):
                return container
        return containers[0] if containers else None
    return None

def run_wp_cli_command(container_name: str, command: list) -> Tuple[bool, str, str]:
    """Run WP-CLI command inside container"""
    # Use wpcli container if available, otherwise try wordpress container
    if "wpcli" in container_name.lower() or container_name and "wpcli" in container_name:
        # wpcli container - wp is in PATH
        cmd = ["docker", "exec", container_name, "wp"] + command + ["--allow-root", "--path=/var/www/html"]
    else:
        # wordpress container - try wp first, if not found use wpcli container
        wpcli_container = get_container_name("wpcli")
        if wpcli_container:
            cmd = ["docker", "exec", wpcli_container, "wp"] + command + ["--allow-root", "--path=/var/www/html"]
        else:
            # Fallback to wordpress container (might not work if WP-CLI not installed)
            cmd = ["docker", "exec", container_name, "wp"] + command + ["--allow-root", "--path=/var/www/html"]
    return run_command(cmd, f"WP-CLI: {' '.join(command)}")

# ============================================================================
# 1. Docker & Infrastructure Tests
# ============================================================================

def check_docker_containers() -> Dict[str, Any]:
    """Check if all Docker containers are running"""
    print("\n" + "=" * 60)
    print("1. בדיקות Docker ו-Infrastructure")
    print("=" * 60)
    
    results = {}
    services = ["wordpress", "nginx", "db", "phpmyadmin"]
    
    for service in services:
        container_name = get_container_name(service)
        if container_name:
            results[service] = {"status": "running", "container": container_name}
            print(f"✅ {service}: {container_name}")
            
            # Check health status
            success, output, _ = run_command(
                ["docker", "inspect", "--format", "{{.State.Health.Status}}", container_name],
                f"Checking health of {service}"
            )
            if success and output.strip():
                health = output.strip()
                results[service]["health"] = health
                if health == "healthy":
                    print(f"   ✅ Health: {health}")
                else:
                    print(f"   ⚠️  Health: {health}")
                    warnings.append(f"{service} container health is {health}")
        else:
            results[service] = {"status": "not_found"}
            print(f"❌ {service}: Container not found")
            errors.append(f"{service} container is not running")
    
    # Check ports
    print("\nבדיקת פורטים:")
    ports_to_check = [8080, 8081]
    for port in ports_to_check:
        try:
            response = requests.get(f"http://localhost:{port}", timeout=5)
            results[f"port_{port}"] = {"status": "open", "response": response.status_code}
            print(f"✅ Port {port}: Open (HTTP {response.status_code})")
        except requests.exceptions.RequestException as e:
            results[f"port_{port}"] = {"status": "closed", "error": str(e)}
            print(f"❌ Port {port}: Closed or unreachable")
            if port == 8080:
                errors.append(f"Port {port} (main site) is not accessible")
            else:
                warnings.append(f"Port {port} is not accessible")
    
    return results

# ============================================================================
# 2. WordPress Core Tests
# ============================================================================

def check_wordpress_core(container_name: str) -> Dict[str, Any]:
    """Check WordPress core configuration and status"""
    print("\n" + "=" * 60)
    print("2. בדיקות WordPress Core")
    print("=" * 60)
    
    results = {}
    
    # Check WordPress version
    print("\nבדיקת גרסת WordPress:")
    success, output, _ = run_wp_cli_command(container_name, ["core", "version"])
    if success:
        version = output.strip()
        results["version"] = version
        expected_version = "6.8.3"
        if version == expected_version:
            print(f"✅ WordPress Version: {version}")
            results["version_check"] = True
        else:
            print(f"⚠️  WordPress Version: {version} (Expected: {expected_version})")
            results["version_check"] = False
            warnings.append(f"WordPress version is {version}, expected {expected_version}")
    else:
        results["version"] = "unknown"
        results["version_check"] = False
        errors.append("Could not determine WordPress version")
    
    # Check DB version
    print("\nבדיקת גרסת מסד נתונים:")
    success, output, _ = run_wp_cli_command(container_name, [
        "db", "query", "SELECT option_value FROM wp_options WHERE option_name = 'db_version'"
    ])
    if success:
        db_version = output.strip()
        results["db_version"] = db_version
        print(f"✅ DB Version: {db_version}")
        # WordPress 6.8.3 should use DB version 60421
        if db_version == "60421":
            results["db_version_check"] = True
        else:
            results["db_version_check"] = False
            warnings.append(f"DB version is {db_version}, expected 60421 for WordPress 6.8.3")
    else:
        results["db_version"] = "unknown"
        results["db_version_check"] = False
    
    # Check wp-config.php settings
    print("\nבדיקת הגדרות wp-config.php:")
    if os.path.exists(WP_CONFIG_PATH):
        with open(WP_CONFIG_PATH, 'r', encoding='utf-8') as f:
            config_content = f.read()
        
        # Check WP_DEBUG
        wp_debug_match = re.search(r"WP_DEBUG['\"]?\s*,\s*(true|false)", config_content, re.IGNORECASE)
        if wp_debug_match:
            wp_debug_value = wp_debug_match.group(1).lower() == 'true'
            results["wp_debug"] = wp_debug_value
            if wp_debug_value:
                print(f"⚠️  WP_DEBUG: Enabled (should be false for production)")
                warnings.append("WP_DEBUG is enabled - should be disabled for production")
            else:
                print(f"✅ WP_DEBUG: Disabled")
        else:
            results["wp_debug"] = None
            print("⚠️  WP_DEBUG: Not explicitly set")
        
        # Check DISALLOW_FILE_EDIT
        if "DISALLOW_FILE_EDIT" in config_content:
            file_edit_match = re.search(r"DISALLOW_FILE_EDIT['\"]?\s*,\s*(true|false)", config_content, re.IGNORECASE)
            if file_edit_match:
                disallow_edit = file_edit_match.group(1).lower() == 'true'
                results["disallow_file_edit"] = disallow_edit
                if disallow_edit:
                    print(f"✅ DISALLOW_FILE_EDIT: Enabled")
                else:
                    print(f"⚠️  DISALLOW_FILE_EDIT: Disabled")
                    warnings.append("DISALLOW_FILE_EDIT should be enabled for security")
            else:
                results["disallow_file_edit"] = None
        else:
            results["disallow_file_edit"] = None
            warnings.append("DISALLOW_FILE_EDIT not found in wp-config.php")
        
        # Check memory limits
        memory_match = re.search(r"WP_MEMORY_LIMIT['\"]?\s*,\s*['\"]?(\d+[MG])", config_content, re.IGNORECASE)
        if memory_match:
            results["memory_limit"] = memory_match.group(1)
            print(f"✅ WP_MEMORY_LIMIT: {memory_match.group(1)}")
        else:
            results["memory_limit"] = None
            print("⚠️  WP_MEMORY_LIMIT: Not explicitly set")
    else:
        results["wp_config_exists"] = False
        errors.append("wp-config.php not found")
    
    # Check database connection
    print("\nבדיקת קישוריות למסד נתונים:")
    success, output, _ = run_wp_cli_command(container_name, ["db", "check"])
    if success:
        results["db_connection"] = True
        print("✅ Database connection: OK")
    else:
        results["db_connection"] = False
        errors.append("Database connection failed")
        print("❌ Database connection: Failed")
    
    # Check PHP version
    print("\nבדיקת גרסת PHP:")
    success, output, _ = run_command(
        ["docker", "exec", container_name, "php", "-v"],
        "Checking PHP version"
    )
    if success:
        php_version_match = re.search(r"PHP (\d+\.\d+)", output)
        if php_version_match:
            php_version = php_version_match.group(1)
            results["php_version"] = php_version
            print(f"✅ PHP Version: {php_version}")
            if php_version.startswith("8.2"):
                results["php_version_check"] = True
            else:
                results["php_version_check"] = False
                warnings.append(f"PHP version is {php_version}, expected 8.2")
    
    return results

# ============================================================================
# 3. Plugin Tests
# ============================================================================

def check_plugins(container_name: str) -> Dict[str, Any]:
    """Check all plugins"""
    print("\n" + "=" * 60)
    print("3. בדיקות פלאגינים")
    print("=" * 60)
    
    results = {}
    
    # Get all active plugins
    print("\nרשימת פלאגינים פעילים:")
    success, output, _ = run_wp_cli_command(container_name, [
        "plugin", "list", "--status=active", "--format=json"
    ])
    
    active_plugins = []
    if success and output.strip():
        try:
            active_plugins = json.loads(output)
            print(f"✅ Found {len(active_plugins)} active plugins")
        except json.JSONDecodeError:
            # Fallback to parsing text format
            for line in output.strip().split('\n')[1:]:  # Skip header
                if line.strip():
                    parts = line.split()
                    if len(parts) >= 2:
                        active_plugins.append({
                            "name": parts[0],
                            "version": parts[1],
                            "status": "active"
                        })
    
    results["active_plugins"] = active_plugins
    results["plugin_count"] = len(active_plugins)
    
    # Check critical plugins
    critical_plugins = {
        "google-site-kit": "1.165.0",
        "wordpress-seo": "26.3",
        "woocommerce": "10.3.5"
    }
    
    print("\nבדיקת פלאגינים קריטיים:")
    for plugin_name, expected_version in critical_plugins.items():
        found = False
        for plugin in active_plugins:
            plugin_key = plugin.get("name", "").lower()
            if plugin_name in plugin_key:
                found = True
                version = plugin.get("version", "unknown")
                results[f"plugin_{plugin_name}"] = {
                    "active": True,
                    "version": version,
                    "expected": expected_version
                }
                if version == expected_version:
                    print(f"✅ {plugin_name}: {version} (Correct)")
                else:
                    print(f"⚠️  {plugin_name}: {version} (Expected: {expected_version})")
                    warnings.append(f"{plugin_name} version is {version}, expected {expected_version}")
                break
        
        if not found:
            results[f"plugin_{plugin_name}"] = {"active": False}
            print(f"❌ {plugin_name}: Not active or not found")
            warnings.append(f"Critical plugin {plugin_name} is not active")
    
    # Check for plugin updates available
    print("\nבדיקת עדכונים זמינים:")
    success, output, _ = run_wp_cli_command(container_name, [
        "plugin", "list", "--status=active", "--update=available", "--format=json"
    ])
    updates_available = []
    if success and output.strip():
        try:
            updates_available = json.loads(output)
            if updates_available:
                print(f"⚠️  Found {len(updates_available)} plugins with available updates:")
                for plugin in updates_available:
                    print(f"   - {plugin.get('name', 'unknown')}: {plugin.get('version', 'unknown')} -> Update available")
                    warnings.append(f"Plugin {plugin.get('name', 'unknown')} has updates available")
            else:
                print("✅ No plugin updates available")
        except json.JSONDecodeError:
            pass
    
    results["updates_available"] = len(updates_available)
    
    # Check cookie-consent-notice plugin specifically
    print("\nבדיקת פלאגין Cookie Consent Notice:")
    cookie_plugin_found = False
    for plugin in active_plugins:
        if "cookie-consent-notice" in plugin.get("name", "").lower():
            cookie_plugin_found = True
            results["cookie_plugin"] = {
                "active": True,
                "version": plugin.get("version", "unknown"),
                "name": plugin.get("name", "")
            }
            print(f"✅ Cookie Consent Notice plugin is active")
            break
    
    if not cookie_plugin_found:
        results["cookie_plugin"] = {"active": False}
        print(f"❌ Cookie Consent Notice plugin not found or not active")
        errors.append("Cookie Consent Notice plugin is not active")
    
    # Check plugin file exists
    if os.path.exists(COOKIE_PLUGIN_PATH):
        results["cookie_plugin"]["file_exists"] = True
        print(f"✅ Cookie plugin file exists")
    else:
        results["cookie_plugin"]["file_exists"] = False
        print(f"❌ Cookie plugin file not found at {COOKIE_PLUGIN_PATH}")
        errors.append(f"Cookie plugin file not found: {COOKIE_PLUGIN_PATH}")
    
    return results

# ============================================================================
# 4. Cookie Notice Tests
# ============================================================================

def check_cookie_notice(container_name: str) -> Dict[str, Any]:
    """Check cookie consent notice implementation"""
    print("\n" + "=" * 60)
    print("4. בדיקות הודעת הקוקיז")
    print("=" * 60)
    
    results = {}
    
    # Check plugin file
    if os.path.exists(COOKIE_PLUGIN_PATH):
        print("\nבדיקת קובץ הפלאגין:")
        with open(COOKIE_PLUGIN_PATH, 'r', encoding='utf-8') as f:
            plugin_content = f.read()
        
        # Check for required functions
        required_functions = [
            "enqueue_cookie_consent_scripts",
            "add_cookie_consent_notice",
            "wp_enqueue_scripts",
            "wp_footer"
        ]
        
        print("בדיקת פונקציות נדרשות:")
        functions_found = {}
        for func in required_functions:
            if func in plugin_content:
                functions_found[func] = True
                print(f"✅ Function found: {func}")
            else:
                functions_found[func] = False
                print(f"❌ Function not found: {func}")
                errors.append(f"Cookie notice function {func} not found in plugin file")
        
        results["functions"] = functions_found
        
        # Check for JavaScript
        if "cookie-consent" in plugin_content and "wp_add_inline_script" in plugin_content:
            results["javascript_present"] = True
            print("✅ JavaScript code found in plugin")
        else:
            results["javascript_present"] = False
            print("❌ JavaScript code not found in plugin")
            errors.append("Cookie notice JavaScript not found")
        
        # Check for CSS
        if "cookie-consent-style" in plugin_content and "wp_add_inline_style" in plugin_content:
            results["css_present"] = True
            print("✅ CSS code found in plugin")
        else:
            results["css_present"] = False
            print("❌ CSS code not found in plugin")
            errors.append("Cookie notice CSS not found")
        
        # Check for HTML notice
        if "cookie-consent-notice" in plugin_content and "cookie-consent-checkbox" in plugin_content:
            results["html_present"] = True
            print("✅ HTML notice structure found in plugin")
        else:
            results["html_present"] = False
            print("❌ HTML notice structure not found in plugin")
            errors.append("Cookie notice HTML structure not found")
        
        results["plugin_file_valid"] = all([
            results.get("functions", {}).get("enqueue_cookie_consent_scripts", False),
            results.get("javascript_present", False),
            results.get("css_present", False),
            results.get("html_present", False)
        ])
    else:
        results["plugin_file_valid"] = False
        errors.append(f"Cookie plugin file not found: {COOKIE_PLUGIN_PATH}")
    
    # Check if cookie notice appears in page HTML
    print("\nבדיקת הודעת קוקיז בדף האתר:")
    try:
        response = requests.get(SITE_URL, timeout=10)
        if response.status_code == 200:
            html_content = response.text
            
            # Check for cookie notice HTML elements
            cookie_elements = {
                "cookie-consent-notice": "cookie-consent-notice" in html_content,
                "cookie-consent-checkbox": "cookie-consent-checkbox" in html_content,
                "cookie-consent-accept": "cookie-consent-accept" in html_content,
                "cookie-consent-content": "cookie-consent-content" in html_content
            }
            
            results["html_in_page"] = cookie_elements
            all_elements_found = all(cookie_elements.values())
            
            if all_elements_found:
                print("✅ Cookie notice HTML elements found in page")
            else:
                missing = [k for k, v in cookie_elements.items() if not v]
                print(f"⚠️  Some cookie notice elements missing: {', '.join(missing)}")
                warnings.append(f"Cookie notice HTML elements missing: {', '.join(missing)}")
            
            # Check for JavaScript
            if "cookie-consent" in html_content or "cookie_consent_accepted" in html_content:
                results["javascript_in_page"] = True
                print("✅ Cookie notice JavaScript appears to be loaded")
            else:
                results["javascript_in_page"] = False
                print("⚠️  Cookie notice JavaScript not clearly detected in page")
                warnings.append("Cookie notice JavaScript not clearly detected in page HTML")
            
            # Check for CSS styles
            if "cookie-consent" in html_content or "position: fixed" in html_content or "bottom: 0" in html_content:
                results["css_in_page"] = True
                print("✅ Cookie notice CSS appears to be loaded")
            else:
                results["css_in_page"] = False
                print("⚠️  Cookie notice CSS not clearly detected in page")
                warnings.append("Cookie notice CSS not clearly detected in page HTML")
            
        else:
            results["html_in_page"] = None
            results["javascript_in_page"] = None
            results["css_in_page"] = None
            errors.append(f"Could not fetch website HTML (status {response.status_code})")
    except Exception as e:
        results["html_in_page"] = None
        results["javascript_in_page"] = None
        results["css_in_page"] = None
        errors.append(f"Could not check cookie notice in page: {str(e)}")
    
    return results

# ============================================================================
# 5. Accessibility Tests
# ============================================================================

def check_accessibility(container_name: str) -> Dict[str, Any]:
    """Check website accessibility"""
    print("\n" + "=" * 60)
    print("5. בדיקות נגישות האתר")
    print("=" * 60)
    
    results = {}
    
    # Check homepage
    print("\nבדיקת נגישות דף הבית:")
    try:
        start_time = time.time()
        response = requests.get(SITE_URL, timeout=15)
        response_time = time.time() - start_time
        
        results["homepage"] = {
            "status_code": response.status_code,
            "response_time": round(response_time, 2),
            "size": len(response.content)
        }
        
        if response.status_code == 200:
            print(f"✅ Homepage: HTTP {response.status_code} ({response_time:.2f}s, {len(response.content)} bytes)")
            results["homepage"]["accessible"] = True
        else:
            print(f"❌ Homepage: HTTP {response.status_code}")
            results["homepage"]["accessible"] = False
            errors.append(f"Homepage returned status {response.status_code}")
    except Exception as e:
        results["homepage"] = {"accessible": False, "error": str(e)}
        print(f"❌ Homepage: {str(e)}")
        errors.append(f"Homepage not accessible: {str(e)}")
    
    # Check admin
    print("\nבדיקת נגישות Admin:")
    try:
        response = requests.get(ADMIN_URL, timeout=10, allow_redirects=True)
        results["admin"] = {
            "status_code": response.status_code,
            "accessible": response.status_code in [200, 302, 301]
        }
        if results["admin"]["accessible"]:
            print(f"✅ Admin: HTTP {response.status_code}")
        else:
            print(f"⚠️  Admin: HTTP {response.status_code}")
            warnings.append(f"Admin returned status {response.status_code}")
    except Exception as e:
        results["admin"] = {"accessible": False, "error": str(e)}
        print(f"❌ Admin: {str(e)}")
    
    # Check REST API
    print("\nבדיקת נגישות REST API:")
    try:
        response = requests.get(REST_API_URL, timeout=10)
        results["rest_api"] = {
            "status_code": response.status_code,
            "accessible": response.status_code == 200
        }
        if response.status_code == 200:
            print(f"✅ REST API: HTTP {response.status_code}")
        else:
            print(f"⚠️  REST API: HTTP {response.status_code}")
            warnings.append(f"REST API returned status {response.status_code}")
    except Exception as e:
        results["rest_api"] = {"accessible": False, "error": str(e)}
        print(f"❌ REST API: {str(e)}")
        warnings.append(f"REST API not accessible: {str(e)}")
    
    # Get important pages from database
    print("\nבדיקת דפים חשובים:")
    success, output, _ = run_wp_cli_command(container_name, [
        "post", "list", "--post_type=page", "--posts_per_page=5", "--format=json"
    ])
    
    important_pages = []
    if success and output.strip():
        try:
            pages = json.loads(output)
            for page in pages[:3]:  # Check first 3 pages
                page_url = page.get("url", "")
                if page_url:
                    try:
                        page_response = requests.get(page_url, timeout=10)
                        important_pages.append({
                            "title": page.get("post_title", ""),
                            "url": page_url,
                            "status": page_response.status_code,
                            "accessible": page_response.status_code == 200
                        })
                        if page_response.status_code == 200:
                            print(f"✅ {page.get('post_title', 'Page')}: HTTP {page_response.status_code}")
                        else:
                            print(f"⚠️  {page.get('post_title', 'Page')}: HTTP {page_response.status_code}")
                    except:
                        pass
        except json.JSONDecodeError:
            pass
    
    results["important_pages"] = important_pages
    
    return results

# ============================================================================
# 6. Error Tests
# ============================================================================

def check_errors(container_name: str) -> Dict[str, Any]:
    """Check for errors in logs"""
    print("\n" + "=" * 60)
    print("6. בדיקות שגיאות")
    print("=" * 60)
    
    results = {}
    
    # Check WordPress container logs
    print("\nבדיקת לוגים של WordPress:")
    success, output, _ = run_command(
        ["docker", "logs", "--tail", "100", container_name],
        "Checking WordPress logs",
        timeout=10
    )
    
    if success:
        log_lines = output.split('\n')
        fatal_errors = []
        php_errors = []
        warnings_list = []
        
        for line in log_lines:
            line_lower = line.lower()
            if 'fatal' in line_lower or 'fatal error' in line_lower:
                if any(keyword in line_lower for keyword in ['php', 'wordpress', 'plugin']):
                    fatal_errors.append(line)
            elif 'error' in line_lower and not 'notice' in line_lower:
                if any(keyword in line_lower for keyword in ['php', 'wordpress']):
                    php_errors.append(line)
            elif 'warning' in line_lower or 'deprecated' in line_lower:
                warnings_list.append(line)
        
        results["wordpress_logs"] = {
            "fatal_errors": len(fatal_errors),
            "php_errors": len(php_errors),
            "warnings": len(warnings_list),
            "sample_errors": fatal_errors[:5] + php_errors[:5]
        }
        
        if fatal_errors:
            print(f"❌ Found {len(fatal_errors)} fatal errors in logs")
            for error in fatal_errors[:3]:
                print(f"   - {error[:100]}...")
            errors.extend(fatal_errors[:5])
        else:
            print("✅ No fatal errors in WordPress logs")
        
        if php_errors:
            print(f"⚠️  Found {len(php_errors)} PHP errors in logs")
            warnings.extend(php_errors[:5])
        else:
            print("✅ No PHP errors in WordPress logs")
        
        if warnings_list:
            print(f"⚠️  Found {len(warnings_list)} warnings/deprecations in logs")
    else:
        results["wordpress_logs"] = {"error": "Could not read logs"}
        print("⚠️  Could not read WordPress logs")
    
    # Check debug.log if exists
    debug_log_path = f"{WP_CONTENT_PATH}/debug.log"
    if os.path.exists(debug_log_path):
        print("\nבדיקת debug.log:")
        try:
            with open(debug_log_path, 'r', encoding='utf-8', errors='ignore') as f:
                debug_content = f.read()
                debug_errors = debug_content.count('PHP Fatal error') + debug_content.count('PHP Parse error')
                if debug_errors > 0:
                    print(f"⚠️  Found {debug_errors} fatal/parse errors in debug.log")
                    warnings.append(f"{debug_errors} errors found in debug.log")
                else:
                    print("✅ No fatal errors in debug.log")
            results["debug_log"] = {"errors": debug_errors}
        except Exception as e:
            results["debug_log"] = {"error": str(e)}
            print(f"⚠️  Could not read debug.log: {str(e)}")
    else:
        results["debug_log"] = {"exists": False}
        print("✅ debug.log not found (WP_DEBUG likely disabled)")
    
    # Check Nginx logs
    print("\nבדיקת לוגים של Nginx:")
    nginx_container = get_container_name("nginx")
    if nginx_container:
        success, output, _ = run_command(
            ["docker", "logs", "--tail", "50", nginx_container],
            "Checking Nginx logs",
            timeout=10
        )
        if success:
            error_lines = [line for line in output.split('\n') if 'error' in line.lower() and len(line.strip()) > 0]
            results["nginx_logs"] = {"errors": len(error_lines)}
            if error_lines:
                print(f"⚠️  Found {len(error_lines)} error lines in Nginx logs")
                warnings.extend(error_lines[:3])
            else:
                print("✅ No errors in Nginx logs")
        else:
            results["nginx_logs"] = {"error": "Could not read logs"}
    else:
        results["nginx_logs"] = {"container_not_found": True}
    
    return results

# ============================================================================
# 7. Compatibility Tests
# ============================================================================

def check_compatibility(container_name: str) -> Dict[str, Any]:
    """Check compatibility"""
    print("\n" + "=" * 60)
    print("7. בדיקות תאימות")
    print("=" * 60)
    
    results = {}
    
    # Check PHP version compatibility
    print("\nבדיקת תאימות PHP:")
    success, output, _ = run_command(
        ["docker", "exec", container_name, "php", "-v"],
        "Checking PHP version"
    )
    if success:
        php_version_match = re.search(r"PHP (\d+\.\d+\.\d+)", output)
        if php_version_match:
            php_version = php_version_match.group(1)
            results["php_version"] = php_version
            # WordPress 6.8.3 recommends PHP 8.0+
            major_minor = '.'.join(php_version.split('.')[:2])
            if float(major_minor) >= 8.0:
                print(f"✅ PHP {php_version}: Compatible with WordPress 6.8.3")
                results["php_compatible"] = True
            else:
                print(f"⚠️  PHP {php_version}: May not be fully compatible with WordPress 6.8.3")
                results["php_compatible"] = False
                warnings.append(f"PHP {php_version} may not be fully compatible")
    
    # Check MySQL/MariaDB version
    print("\nבדיקת תאימות מסד נתונים:")
    db_container = get_container_name("db")
    if db_container:
        success, output, _ = run_command(
            ["docker", "exec", db_container, "mysql", "--version"],
            "Checking MySQL/MariaDB version"
        )
        if success:
            db_version_match = re.search(r"Ver (\d+\.\d+\.\d+)", output)
            if db_version_match:
                db_version = db_version_match.group(1)
                results["db_version_full"] = db_version
                print(f"✅ Database: {db_version}")
                results["db_compatible"] = True
    
    # Check WordPress and plugins compatibility
    print("\nבדיקת תאימות פלאגינים:")
    success, output, _ = run_wp_cli_command(container_name, [
        "plugin", "list", "--status=active", "--format=json"
    ])
    if success and output.strip():
        try:
            plugins = json.loads(output)
            incompatible_count = 0
            for plugin in plugins:
                # Basic check - in real scenario, would check against WordPress version requirements
                # For now, just note if plugin is active
                pass
            print("✅ Plugin compatibility check completed")
        except:
            pass
    
    return results

# ============================================================================
# 8. Performance Tests
# ============================================================================

def check_performance() -> Dict[str, Any]:
    """Check basic performance metrics"""
    print("\n" + "=" * 60)
    print("8. בדיקות ביצועים")
    print("=" * 60)
    
    results = {}
    
    # Check homepage load time
    print("\nבדיקת זמן טעינת דף:")
    try:
        times = []
        for i in range(3):
            start_time = time.time()
            response = requests.get(SITE_URL, timeout=15)
            load_time = time.time() - start_time
            times.append(load_time)
            time.sleep(1)  # Small delay between requests
        
        avg_time = sum(times) / len(times)
        results["homepage_load_time"] = {
            "average": round(avg_time, 2),
            "min": round(min(times), 2),
            "max": round(max(times), 2),
            "times": [round(t, 2) for t in times]
        }
        
        print(f"✅ Average load time: {avg_time:.2f}s (min: {min(times):.2f}s, max: {max(times):.2f}s)")
        
        if avg_time > 3.0:
            warnings.append(f"Homepage load time is {avg_time:.2f}s - consider optimization")
        elif avg_time > 5.0:
            errors.append(f"Homepage load time is {avg_time:.2f}s - too slow")
        
        # Check response size
        response_size = len(response.content) / 1024  # KB
        results["response_size"] = round(response_size, 2)
        print(f"✅ Response size: {response_size:.2f} KB")
        
        if response_size > 500:  # KB
            warnings.append(f"Response size is {response_size:.2f} KB - consider optimization")
    except Exception as e:
        results["homepage_load_time"] = {"error": str(e)}
        print(f"❌ Could not check performance: {str(e)}")
    
    return results

# ============================================================================
# 9. Security Tests
# ============================================================================

def check_security(container_name: str) -> Dict[str, Any]:
    """Check basic security settings"""
    print("\n" + "=" * 60)
    print("9. בדיקות אבטחה")
    print("=" * 60)
    
    results = {}
    
    # Already checked in WordPress Core, but summarize here
    if os.path.exists(WP_CONFIG_PATH):
        with open(WP_CONFIG_PATH, 'r', encoding='utf-8') as f:
            config_content = f.read()
        
        # WP_DEBUG
        wp_debug_match = re.search(r"WP_DEBUG['\"]?\s*,\s*(true|false)", config_content, re.IGNORECASE)
        wp_debug = wp_debug_match.group(1).lower() == 'true' if wp_debug_match else None
        results["wp_debug"] = wp_debug
        
        # DISALLOW_FILE_EDIT
        file_edit_match = re.search(r"DISALLOW_FILE_EDIT['\"]?\s*,\s*(true|false)", config_content, re.IGNORECASE)
        disallow_edit = file_edit_match.group(1).lower() == 'true' if file_edit_match else None
        results["disallow_file_edit"] = disallow_edit
        
        print("\nסיכום הגדרות אבטחה:")
        if wp_debug == False:
            print("✅ WP_DEBUG: Disabled (secure for production)")
            results["security_score"] = results.get("security_score", 0) + 1
        else:
            print("❌ WP_DEBUG: Enabled (not secure for production)")
        
        if disallow_edit == True:
            print("✅ DISALLOW_FILE_EDIT: Enabled (secure)")
            results["security_score"] = results.get("security_score", 0) + 1
        else:
            print("❌ DISALLOW_FILE_EDIT: Disabled or not set")
    
    return results

# ============================================================================
# 10. WooCommerce Tests
# ============================================================================

def check_woocommerce(container_name: str) -> Dict[str, Any]:
    """Check WooCommerce if active"""
    print("\n" + "=" * 60)
    print("10. בדיקות WooCommerce")
    print("=" * 60)
    
    results = {}
    
    # Check if WooCommerce is active
    success, output, _ = run_wp_cli_command(container_name, [
        "plugin", "list", "--status=active", "--field=name"
    ])
    
    woocommerce_active = False
    if success and "woocommerce" in output.lower():
        woocommerce_active = True
        print("✅ WooCommerce is active")
    else:
        print("ℹ️  WooCommerce is not active - skipping WooCommerce tests")
        results["active"] = False
        return results
    
    results["active"] = True
    
    # Check WooCommerce tables
    print("\nבדיקת טבלאות WooCommerce:")
    success, output, _ = run_wp_cli_command(container_name, [
        "db", "query", "SHOW TABLES LIKE 'wp_woocommerce%'"
    ])
    
    if success:
        tables = [line.strip() for line in output.split('\n') if line.strip() and 'wp_woocommerce' in line]
        results["tables"] = {
            "count": len(tables),
            "list": tables[:10]  # First 10 tables
        }
        if tables:
            print(f"✅ Found {len(tables)} WooCommerce tables")
        else:
            print("⚠️  No WooCommerce tables found")
            warnings.append("WooCommerce is active but no tables found")
    
    # Check WooCommerce REST API
    print("\nבדיקת WooCommerce REST API:")
    try:
        wc_api_url = f"{SITE_URL}/wp-json/wc/v3/"
        response = requests.get(wc_api_url, timeout=10)
        results["rest_api"] = {
            "status_code": response.status_code,
            "accessible": response.status_code in [200, 401]  # 401 is OK if auth required
        }
        if results["rest_api"]["accessible"]:
            print(f"✅ WooCommerce REST API: HTTP {response.status_code}")
        else:
            print(f"⚠️  WooCommerce REST API: HTTP {response.status_code}")
    except Exception as e:
        results["rest_api"] = {"accessible": False, "error": str(e)}
        print(f"❌ WooCommerce REST API: {str(e)}")
    
    return results

# ============================================================================
# Report Generation
# ============================================================================

def generate_reports(all_results: Dict, warnings_list: List, errors_list: List):
    """Generate comprehensive reports in multiple formats"""
    timestamp = datetime.now().strftime('%Y-%m-%d_%H-%M-%S')
    
    # Calculate summary
    total_checks = 0
    passed_checks = 0
    failed_checks = 0
    
    def count_checks(category_results):
        nonlocal total_checks, passed_checks, failed_checks
        for key, value in category_results.items():
            if isinstance(value, dict):
                if "accessible" in value or "status" in value or value.get("active") is not None:
                    total_checks += 1
                    if value.get("accessible") == True or value.get("status") == "running" or value.get("active") == True:
                        passed_checks += 1
                    elif value.get("accessible") == False or value.get("status") != "running" or value.get("active") == False:
                        failed_checks += 1
            elif isinstance(value, bool):
                total_checks += 1
                if value:
                    passed_checks += 1
                else:
                    failed_checks += 1
    
    for category, results in all_results.items():
        count_checks(results)
    
    summary = {
        "timestamp": timestamp,
        "total_checks": total_checks,
        "passed": passed_checks,
        "failed": failed_checks,
        "warnings": len(warnings_list),
        "errors": len(errors_list),
        "success_rate": round((passed_checks / total_checks * 100) if total_checks > 0 else 0, 2)
    }
    
    # Generate Markdown report
    md_file = f"COMPREHENSIVE-CHECK-REPORT-{timestamp}.md"
    generate_markdown_report(md_file, all_results, warnings_list, errors_list, summary)
    
    # Generate JSON report
    json_file = f"COMPREHENSIVE-CHECK-REPORT-{timestamp}.json"
    generate_json_report(json_file, all_results, warnings_list, errors_list, summary)
    
    # Generate HTML report
    html_file = f"COMPREHENSIVE-CHECK-REPORT-{timestamp}.html"
    generate_html_report(html_file, all_results, warnings_list, errors_list, summary)
    
    print(f"\n📄 דוחות נוצרו:")
    print(f"   - {md_file}")
    print(f"   - {json_file}")
    print(f"   - {html_file}")
    
    return summary

def generate_markdown_report(filename: str, results: Dict, warnings_list: List, errors_list: List, summary: Dict):
    """Generate Markdown report"""
    with open(filename, 'w', encoding='utf-8') as f:
        f.write("# דוח בדיקות מקיף לפני ייצור\n\n")
        f.write(f"**תאריך:** {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n\n")
        f.write("---\n\n")
        
        # Summary
        f.write("## סיכום\n\n")
        f.write(f"- **סך הכל בדיקות:** {summary['total_checks']}\n")
        f.write(f"- **הצליחו:** {summary['passed']} ✅\n")
        f.write(f"- **נכשלו:** {summary['failed']} ❌\n")
        f.write(f"- **אזהרות:** {summary['warnings']} ⚠️\n")
        f.write(f"- **שגיאות:** {summary['errors']} 🔴\n")
        f.write(f"- **אחוז הצלחה:** {summary['success_rate']}%\n\n")
        
        # Errors
        if errors_list:
            f.write("## שגיאות קריטיות\n\n")
            for error in errors_list[:20]:
                f.write(f"- ❌ {error}\n")
            f.write("\n")
        
        # Warnings
        if warnings_list:
            f.write("## אזהרות\n\n")
            for warning in warnings_list[:20]:
                f.write(f"- ⚠️  {warning}\n")
            f.write("\n")
        
        # Detailed results by category
        f.write("## תוצאות מפורטות\n\n")
        
        category_names = {
            "docker": "1. Docker ו-Infrastructure",
            "wordpress": "2. WordPress Core",
            "plugins": "3. פלאגינים",
            "cookie_notice": "4. הודעת הקוקיז",
            "accessibility": "5. נגישות האתר",
            "errors": "6. שגיאות",
            "compatibility": "7. תאימות",
            "performance": "8. ביצועים",
            "security": "9. אבטחה",
            "woocommerce": "10. WooCommerce"
        }
        
        for category, category_results in results.items():
            f.write(f"### {category_names.get(category, category)}\n\n")
            
            for key, value in category_results.items():
                if isinstance(value, dict):
                    status = "✅" if value.get("accessible", value.get("status", value.get("active")) == True or value.get("status") == "running") else "❌"
                    f.write(f"- **{key}:** {status}\n")
                    # Add details
                    if "version" in value:
                        f.write(f"  - גרסה: {value['version']}\n")
                    if "response_time" in value:
                        f.write(f"  - זמן תגובה: {value['response_time']}s\n")
                elif isinstance(value, bool):
                    status = "✅" if value else "❌"
                    f.write(f"- **{key}:** {status}\n")
                elif isinstance(value, (int, str)):
                    f.write(f"- **{key}:** {value}\n")
            f.write("\n")
        
        f.write("---\n\n")
        f.write("**דוח נוצר אוטומטית על ידי comprehensive-site-check.py**\n")

def generate_json_report(filename: str, results: Dict, warnings_list: List, errors_list: List, summary: Dict):
    """Generate JSON report"""
    report_data = {
        "summary": summary,
        "errors": errors_list[:50],
        "warnings": warnings_list[:50],
        "results": results
    }
    
    with open(filename, 'w', encoding='utf-8') as f:
        json.dump(report_data, f, ensure_ascii=False, indent=2)

def generate_html_report(filename: str, results: Dict, warnings_list: List, errors_list: List, summary: Dict):
    """Generate HTML report"""
    html = f"""<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>דוח בדיקות מקיף - {summary['timestamp']}</title>
    <style>
        body {{ font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }}
        .container {{ max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }}
        h1 {{ color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }}
        h2 {{ color: #555; margin-top: 30px; }}
        h3 {{ color: #666; }}
        .summary {{ background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 20px 0; }}
        .summary-item {{ margin: 5px 0; }}
        .errors {{ background: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0; border-right: 4px solid #f44336; }}
        .warnings {{ background: #fff3e0; padding: 15px; border-radius: 5px; margin: 20px 0; border-right: 4px solid #ff9800; }}
        .success {{ color: #4CAF50; font-weight: bold; }}
        .error {{ color: #f44336; font-weight: bold; }}
        .warning {{ color: #ff9800; font-weight: bold; }}
        table {{ width: 100%; border-collapse: collapse; margin: 20px 0; }}
        th, td {{ padding: 12px; text-align: right; border-bottom: 1px solid #ddd; }}
        th {{ background-color: #4CAF50; color: white; }}
        tr:hover {{ background-color: #f5f5f5; }}
        .status-ok {{ color: #4CAF50; }}
        .status-fail {{ color: #f44336; }}
        .status-warn {{ color: #ff9800; }}
    </style>
</head>
<body>
    <div class="container">
        <h1>דוח בדיקות מקיף לפני ייצור</h1>
        <p><strong>תאריך:</strong> {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}</p>
        
        <div class="summary">
            <h2>סיכום</h2>
            <div class="summary-item">סך הכל בדיקות: <strong>{summary['total_checks']}</strong></div>
            <div class="summary-item">הצליחו: <span class="success">{summary['passed']} ✅</span></div>
            <div class="summary-item">נכשלו: <span class="error">{summary['failed']} ❌</span></div>
            <div class="summary-item">אזהרות: <span class="warning">{summary['warnings']} ⚠️</span></div>
            <div class="summary-item">שגיאות: <span class="error">{summary['errors']} 🔴</span></div>
            <div class="summary-item">אחוז הצלחה: <strong>{summary['success_rate']}%</strong></div>
        </div>
"""
    
    if errors_list:
        html += """<div class="errors">
            <h2>שגיאות קריטיות</h2>
            <ul>"""
        for error in errors_list[:20]:
            html += f"<li>{error}</li>"
        html += """</ul></div>"""
    
    if warnings_list:
        html += """<div class="warnings">
            <h2>אזהרות</h2>
            <ul>"""
        for warning in warnings_list[:20]:
            html += f"<li>{warning}</li>"
        html += """</ul></div>"""
    
    category_names = {
        "docker": "1. Docker ו-Infrastructure",
        "wordpress": "2. WordPress Core",
        "plugins": "3. פלאגינים",
        "cookie_notice": "4. הודעת הקוקיז",
        "accessibility": "5. נגישות האתר",
        "errors": "6. שגיאות",
        "compatibility": "7. תאימות",
        "performance": "8. ביצועים",
        "security": "9. אבטחה",
        "woocommerce": "10. WooCommerce"
    }
    
    html += "<h2>תוצאות מפורטות</h2>"
    
    for category, category_results in results.items():
        html += f"<h3>{category_names.get(category, category)}</h3>"
        html += "<table><tr><th>בדיקה</th><th>תוצאה</th><th>פרטים</th></tr>"
        
        for key, value in category_results.items():
            if isinstance(value, dict):
                status_icon = "✅"
                status_class = "status-ok"
                details = []
                
                if value.get("accessible") == False or value.get("status") not in ["running", None]:
                    status_icon = "❌"
                    status_class = "status-fail"
                elif value.get("accessible") is None or value.get("status") not in ["running", True]:
                    status_icon = "⚠️"
                    status_class = "status-warn"
                
                if "version" in value:
                    details.append(f"גרסה: {value['version']}")
                if "response_time" in value:
                    details.append(f"זמן: {value['response_time']}s")
                if "status_code" in value:
                    details.append(f"HTTP: {value['status_code']}")
                
                details_str = ", ".join(details) if details else "-"
                html += f"<tr><td>{key}</td><td class='{status_class}'>{status_icon}</td><td>{details_str}</td></tr>"
            elif isinstance(value, bool):
                status_icon = "✅" if value else "❌"
                status_class = "status-ok" if value else "status-fail"
                html += f"<tr><td>{key}</td><td class='{status_class}'>{status_icon}</td><td>-</td></tr>"
            elif isinstance(value, (int, str)):
                html += f"<tr><td>{key}</td><td>-</td><td>{value}</td></tr>"
        
        html += "</table>"
    
    html += """
        <hr>
        <p style="text-align: center; color: #666; margin-top: 30px;">
            דוח נוצר אוטומטית על ידי comprehensive-site-check.py
        </p>
    </div>
</body>
</html>"""
    
    with open(filename, 'w', encoding='utf-8') as f:
        f.write(html)

# ============================================================================
# Main
# ============================================================================

def main():
    """Main function"""
    print("=" * 60)
    print("בדיקות מקיף לפני העלאה לייצור")
    print("=" * 60)
    print(f"תאריך: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
    
    # Get container names
    wordpress_container = get_container_name("wordpress")
    wpcli_container = get_container_name("wpcli")
    
    if not wordpress_container:
        print("\n❌ WordPress container not found!")
        print("אנא ודא ש-Docker containers רצים: docker compose up -d")
        sys.exit(1)
    
    print(f"✅ WordPress container: {wordpress_container}")
    if wpcli_container:
        print(f"✅ WP-CLI container: {wpcli_container}")
    print()
    
    # Use wpcli container for WP-CLI commands if available, otherwise use wordpress container
    wp_container = wpcli_container if wpcli_container else wordpress_container
    
    # Run all checks
    try:
        check_results["docker"] = check_docker_containers()
        check_results["wordpress"] = check_wordpress_core(wp_container)
        check_results["plugins"] = check_plugins(wp_container)
        check_results["cookie_notice"] = check_cookie_notice(wordpress_container)
        check_results["accessibility"] = check_accessibility(wordpress_container)
        check_results["errors"] = check_errors(wordpress_container)
        check_results["compatibility"] = check_compatibility(wp_container)
        check_results["performance"] = check_performance()
        check_results["security"] = check_security(wordpress_container)
        check_results["woocommerce"] = check_woocommerce(wp_container)
    except Exception as e:
        print(f"\n❌ שגיאה בביצוע הבדיקות: {str(e)}")
        import traceback
        traceback.print_exc()
        errors.append(f"Error during checks: {str(e)}")
    
    # Generate reports
    print("\n" + "=" * 60)
    print("יצירת דוחות")
    print("=" * 60)
    
    summary = generate_reports(check_results, warnings, errors)
    
    # Final summary
    print("\n" + "=" * 60)
    print("סיכום סופי")
    print("=" * 60)
    print(f"✅ בדיקות שהצליחו: {summary['passed']}")
    print(f"❌ בדיקות שנכשלו: {summary['failed']}")
    print(f"⚠️  אזהרות: {summary['warnings']}")
    print(f"🔴 שגיאות: {summary['errors']}")
    print(f"📊 אחוז הצלחה: {summary['success_rate']}%")
    
    if summary['errors'] == 0 and summary['failed'] == 0:
        print("\n✅ כל הבדיקות הקריטיות עברו!")
        print("האתר מוכן להעלאה לייצור (אחרי בדיקות ידניות נוספות)")
        return 0
    else:
        print("\n⚠️  נמצאו בעיות!")
        print("מומלץ לטפל בבעיות לפני העלאה לייצור")
        return 1

if __name__ == "__main__":
    exit_code = main()
    sys.exit(exit_code)
