const assert = require('node:assert/strict');
const path = require('node:path');
const { randomUUID } = require('node:crypto');
const { chromium } = require('playwright');

(async () => {
  assert.ok(process.env.WP_USER && process.env.WP_PASSWORD, 'Admin credentials must be supplied through environment variables.');
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 1200 } });
  let changed = false;
  const editUrl = 'http://localhost:8080/wp-admin/post.php?post=753&action=edit';
  const rows = '[data-name="water_tiers"] > .acf-input > .acf-repeater > .acf-table > tbody > .acf-row:not(.acf-clone)';
  const tierPrice = () => page.locator(rows).nth(2).locator('[data-name="price"] input[type="number"]');
  const save = async () => {
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle', timeout: 30000 }), page.locator('#publish').click()]);
  };
  try {
    await page.goto('http://localhost:8080/wp-login.php');
    await page.locator('#user_login').fill(process.env.WP_USER);
    await page.locator('#user_pass').fill(process.env.WP_PASSWORD);
    await Promise.all([page.waitForURL('**/wp-admin/**'), page.locator('#wp-submit').click()]);
    await page.goto(editUrl, { waitUntil: 'networkidle' });
    assert.equal(await tierPrice().inputValue(), '160');
    changed = true;
    await tierPrice().fill('161.25');
    await save();
    assert.equal(await tierPrice().inputValue(), '161.25');
    const visitor = await browser.newPage();
    for (const url of ['/', '/water-for-home/', '/water-for-office/']) {
      await visitor.goto('http://localhost:8080' + url, { waitUntil: 'networkidle' });
      assert.equal(await visitor.locator('[data-constructor-quantity="12"]').getAttribute('data-constructor-price'), '161.25');
    }
    await visitor.goto('http://localhost:8080/about-water/', { waitUntil: 'networkidle' });
    const water = await visitor.evaluate(() => window.svitvodyWaterProducts[0]);
    assert.equal(water.tiers.at(-1).unitPrice, 161.25);
    const config = await visitor.evaluate(() => window.svitvodyCheckout);
    const response = await visitor.request.post(config.url, { form: { action: 'sv_submit_order', nonce: config.nonce, payload: JSON.stringify({
      request_id: randomUUID(), language: 'uk', name: 'Тест', address: 'Тестова адреса', phone: '+380671234567',
      date: new Date(Date.now() + 86400000).toISOString().slice(0, 10), time: 'morning', message: '[AUTOTEST-ORDERS] stale price',
      items: [{ id: water.id, amount: 12 }], expected_total_cents: 192000
    }) } });
    assert.equal(response.status(), 409);
    await visitor.close();
    if (process.env.TEST_ORDER_ID) {
      await page.goto(`http://localhost:8080/wp-admin/post.php?post=${Number(process.env.TEST_ORDER_ID)}&action=edit`, { waitUntil: 'networkidle' });
      assert.equal(await page.locator('#postdivrich').count(), 0);
      const snapshot = await page.locator('#sv_order_details').innerText();
      assert.ok(snapshot.includes('160,00'));
      assert.ok(snapshot.replace(/\s/g, '').includes('2120,00'));
      await page.screenshot({ path: path.resolve(__dirname, '../audit-screenshots/orders-water/admin-order.png') });
    }
    console.log('Admin water price save propagated to every UK pricing block and off-block cart; server rejected stale price; historical order retained original prices.');
  } finally {
    if (changed) {
      await page.goto(editUrl, { waitUntil: 'networkidle' });
      const current = await tierPrice().inputValue();
      assert.ok(['160', '161.25'].includes(current), 'Unexpected concurrent price change; inspect before restoring.');
      if (current === '161.25') { await tierPrice().fill('160'); await save(); }
      assert.equal(await tierPrice().inputValue(), '160');
      console.log('Original water price restored.');
    }
    await browser.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
