const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  const output = path.resolve(__dirname, '../audit-screenshots/product-categories');
  fs.mkdirSync(output, { recursive: true });
  try {
    await page.goto('http://localhost:8080/wp-login.php');
    await page.locator('#user_login').fill(process.env.WP_USER);
    await page.locator('#user_pass').fill(process.env.WP_PASSWORD);
    await Promise.all([page.waitForURL('**/wp-admin/**'), page.locator('#wp-submit').click()]);

    for (const language of [
      { slug: 'uk', names: ['Аксесуари', 'Вода'] },
      { slug: 'ru', names: ['Аксессуары', 'Вода'] },
    ]) {
      await page.goto(`http://localhost:8080/wp-admin/edit-tags.php?taxonomy=sv_product_category&post_type=sv_product&lang=${language.slug}`, { waitUntil: 'networkidle' });
      const names = (await page.locator('.wp-list-table tbody .row-title').allInnerTexts()).map(value => value.trim()).sort();
      assert.deepEqual(names, [...language.names].sort());
      await page.locator('.wp-list-table').screenshot({ path: path.join(output, `categories-${language.slug}.png`) });
    }

    for (const product of [{ id: 735, category: 'Аксесуари' }, { id: 753, category: 'Вода' }]) {
      await page.goto(`http://localhost:8080/wp-admin/post.php?post=${product.id}&action=edit`, { waitUntil: 'networkidle' });
      const checked = await page.locator('#sv_product_categorychecklist input:checked').locator('xpath=..').allInnerTexts();
      assert.deepEqual(checked.map(value => value.trim()), [product.category]);
    }
    console.log('Two localized categories are visible and product assignments are correct.');
  } finally {
    await browser.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
