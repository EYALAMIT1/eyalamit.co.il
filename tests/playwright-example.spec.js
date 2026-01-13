const { test, expect } = require('@playwright/test');

test('homepage loads successfully', async ({ page }) => {
  await page.goto('http://localhost:9090');
  await expect(page).toHaveTitle(/אייל עמית|Eyal Amit/);

  // בדיקת Schema markup - check existence instead of visibility
  const schemaScripts = page.locator('script[type="application/ld+json"]');
  await expect(schemaScripts).toHaveCount(4); // Yoast + Person + Business + FAQ
});

test('zero console errors', async ({ page }) => {
  const errors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') {
      errors.push(msg.text());
    }
  });

  await page.goto('http://localhost:9090');
  expect(errors).toHaveLength(0);
});

test('schema markup validation', async ({ page }) => {
  await page.goto('http://localhost:9090');

  // Get all JSON-LD scripts
  const jsonLdScripts = page.locator('script[type="application/ld+json"]');
  const scriptCount = await jsonLdScripts.count();

  // Should have at least 3 custom schemas (Person, Business, FAQ) plus Yoast
  expect(scriptCount).toBeGreaterThanOrEqual(4);

  // Check that our custom schemas exist by examining content
  let personFound = false;
  let businessFound = false;
  let faqFound = false;

  for (let i = 0; i < scriptCount; i++) {
    const scriptContent = await jsonLdScripts.nth(i).textContent();
    const scriptData = JSON.parse(scriptContent);

    if (scriptData['@type'] === 'Person' && scriptData.name === 'אייל עמית') {
      personFound = true;
    }
    if (scriptData['@type'] === 'HealthAndBeautyBusiness' && scriptData.name?.includes('מרכז לטיפול בדיגרידו')) {
      businessFound = true;
    }
    if (scriptData['@type'] === 'FAQPage' && Array.isArray(scriptData.mainEntity)) {
      faqFound = true;
    }
  }

  expect(personFound).toBe(true);
  expect(businessFound).toBe(true);
  expect(faqFound).toBe(true);
});

test('elementor layout renders', async ({ page }) => {
  await page.goto('http://localhost:9090');

  // בדיקת Elementor containers
  const elementorSections = page.locator('.elementor-section');
  await expect(elementorSections).toHaveCount(await elementorSections.count() > 0 ? await elementorSections.count() : 1);

  const elementorColumns = page.locator('.elementor-column');
  await expect(elementorColumns).toHaveCount(await elementorColumns.count() > 0 ? await elementorColumns.count() : 1);

  // בדיקת Hero content - more flexible text matching
  const h1Elements = page.locator('h1');
  const h1Count = await h1Elements.count();

  let heroTitleFound = false;
  for (let i = 0; i < h1Count; i++) {
    const text = await h1Elements.nth(i).textContent();
    if (text.includes('ברוכים הבאים')) {
      heroTitleFound = true;
      break;
    }
  }
  expect(heroTitleFound).toBe(true);

  // בדיקת H2 subtitle
  const h2Elements = page.locator('h2');
  const h2Count = await h2Elements.count();

  let heroSubtitleFound = false;
  for (let i = 0; i < h2Count; i++) {
    const text = await h2Elements.nth(i).textContent();
    if (text.includes('שיטת טיפול')) {
      heroSubtitleFound = true;
      break;
    }
  }
  expect(heroSubtitleFound).toBe(true);
});