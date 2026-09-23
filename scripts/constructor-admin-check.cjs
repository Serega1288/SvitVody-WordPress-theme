const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

(async () => {
  if (!process.env.WP_USER || !process.env.WP_PASSWORD) throw new Error('Local admin credentials required in environment');
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  await page.goto('http://localhost:8080/wp-login.php');
  await page.locator('#user_login').fill(process.env.WP_USER);
  await page.locator('#user_pass').fill(process.env.WP_PASSWORD);
  await Promise.all([page.waitForURL('**/wp-admin/**'), page.locator('#wp-submit').click()]);
  await page.goto('http://localhost:8080/wp-admin/post.php?post=7&action=edit', { waitUntil: 'networkidle' });
  const admin = await page.evaluate(() => ({
    template: document.querySelector('#page_template')?.value,
    editorHidden: !document.querySelector('#postdivrich') || getComputedStyle(document.querySelector('#postdivrich')).display === 'none',
    constructorFields: document.querySelectorAll('[data-name="constructor"]').length,
    layouts: [...document.querySelectorAll('.acf-flexible-content > .values > .layout')].map(el => el.dataset.layout),
    messages: [...document.querySelectorAll('.acf-field-message .acf-label label')].map(el => el.textContent.trim()),
    images: document.querySelectorAll('.acf-image-uploader.has-value').length,
  }));
  const dir = path.resolve(__dirname, '../audit-screenshots/constructor');
  await page.screenshot({ path: path.join(dir, 'admin-constructor.png') });
  await page.goto('http://localhost:8080/wp-admin/edit.php?post_type=acf-field-group', { waitUntil: 'networkidle' });
  admin.fieldGroups = await page.locator('#the-list .row-title').allTextContents();
  await page.goto('http://localhost:8080/wp-admin/edit.php?post_type=acf-post-type', { waitUntil: 'networkidle' });
  admin.postTypes = await page.locator('#the-list .row-title').allTextContents();
  await page.goto('http://localhost:8080/wp-admin/edit.php?post_type=acf-taxonomy', { waitUntil: 'networkidle' });
  admin.taxonomies = await page.locator('#the-list .row-title').allTextContents();
  if (process.env.CT_TEST_PAGE) {
    await page.goto(`http://localhost:8080/wp-admin/post.php?post=${process.env.CT_TEST_PAGE}&action=edit`, { waitUntil: 'networkidle' });
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle' }), page.locator('#save-post').click()]);
    await page.setViewportSize({ width: 390, height: 900 });
    await page.goto(`http://localhost:8080/?page_id=${process.env.CT_TEST_PAGE}&preview=true`, { waitUntil: 'networkidle' });
    admin.preview = await page.evaluate(() => ({
      sections: [...document.querySelectorAll('.constructor > section')].map(el => el.id),
      sliders: document.querySelectorAll('.mobile-content-swiper').length,
      values: [...document.querySelectorAll('.constructor-promotions h2')].map(el => el.textContent.trim()),
    }));
    const swiper = page.locator('.mobile-content-swiper').first();
    admin.preview.slideBefore = await swiper.evaluate(el => el.swiper.activeIndex);
    await swiper.evaluate(el => el.swiper.slideNext());
    await page.waitForTimeout(500);
    admin.preview.slideAfter = await swiper.evaluate(el => el.swiper.activeIndex);
    await page.screenshot({ path: path.join(dir, 'constructor-repeated-mobile.png'), fullPage: true });
  }
  admin.errors = errors;
  fs.writeFileSync(path.join(dir, 'admin-results.json'), JSON.stringify(admin, null, 2));
  console.log(JSON.stringify(admin, null, 2));
  await browser.close();
})();
