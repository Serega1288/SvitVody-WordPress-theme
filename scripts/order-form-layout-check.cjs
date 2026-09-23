const assert = require('node:assert/strict');
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  try {
    for (const width of [1440, 390]) {
      const page = await browser.newPage({ viewport: { width, height: 1000 } });
      const errors = [];
      page.on('pageerror', error => errors.push(error.message));
      await page.goto('http://localhost:8080/', { waitUntil: 'networkidle' });
      await page.locator('[data-constructor-quantity="6"]').first().click();
      const form = page.locator('.order-modal-form');
      assert.equal(await form.locator('[name="modal-time"]').count(), 0);
      for (const [name, value] of Object.entries({ name: 'Layout test', phone: '+380000000000', address: 'Test', date: new Date(Date.now() + 86400000).toISOString().slice(0, 10) })) {
        await form.locator(`[name="${name}"]`).fill(value);
      }
      assert.equal(await form.evaluate(node => node.checkValidity()), true);
      await form.scrollIntoViewIfNeeded();
      await page.screenshot({ path: `audit-screenshots/notifications/form-${width}.png` });
      assert.deepEqual(errors, []);
      await page.close();
    }
    console.log('Desktop/mobile form valid without delivery time; no submission or email test.');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
