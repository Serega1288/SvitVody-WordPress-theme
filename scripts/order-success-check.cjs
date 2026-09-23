const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  const output = path.resolve(__dirname, '../audit-screenshots/order-success');
  fs.mkdirSync(output, { recursive: true });

  try {
    await page.route('**/wp-admin/admin-ajax.php', async (route) => {
      const request = route.request();
      const body = request.postData() || '';
      if (request.method() === 'POST' && body.includes('action=sv_submit_order')) {
        await route.fulfill({
          status: 200,
          contentType: 'application/json; charset=UTF-8',
          body: JSON.stringify({ success: true, data: { order_id: 9999 } }),
        });
        return;
      }
      await route.continue();
    });

    await page.goto('http://localhost:8080/', { waitUntil: 'networkidle' });
    await page.locator('[data-modal-open]:visible').first().click();

    const modal = page.locator('#orderModal');
    const form = modal.locator('.order-modal-form');
    await form.locator('[name="name"]').fill('Тест інтерфейсу');
    await form.locator('[name="phone"]').fill('+380000000000');
    await form.locator('[name="address"]').fill('Тестова адреса');
    await form.locator('[name="date"]').fill('06.09');
    await form.locator('[type="submit"]').click();

    const success = form.locator('[data-order-success]');
    await success.waitFor({ state: 'visible' });
    await page.waitForTimeout(650);
    assert.equal((await success.locator('h3').innerText()).trim(), 'Заявку прийнято!');
    assert.match(await success.innerText(), /9999/);
    assert.equal(await form.locator('[name="name"]').isVisible(), false);
    await modal.locator('.order-modal-dialog').screenshot({ path: path.join(output, 'desktop-uk.png') });

    await page.waitForTimeout(7000);
    assert.equal(await modal.isVisible(), true, 'Modal closed before eight seconds elapsed.');
    await modal.waitFor({ state: 'hidden', timeout: 1800 });

    await page.locator('[data-modal-open]:visible').first().click();
    assert.equal(await form.locator('[name="name"]').isVisible(), true);
    assert.equal(await success.isVisible(), false);

    await modal.locator('[data-modal-close]').click();
    await modal.waitFor({ state: 'hidden' });
    await page.setViewportSize({ width: 390, height: 900 });
    await page.locator('[data-modal-open]:visible').first().click();
    await form.locator('[name="name"]').fill('Тест мобільного інтерфейсу');
    await form.locator('[name="phone"]').fill('+380000000000');
    await form.locator('[name="address"]').fill('Тестова адреса');
    await form.locator('[name="date"]').fill('06.09');
    await form.locator('[type="submit"]').click();
    await success.waitFor({ state: 'visible' });
    await page.waitForTimeout(650);
    await page.screenshot({ path: path.join(output, 'mobile-uk.png') });

    console.log('Order success state, mobile layout, eight-second auto-close, and form reset verified without creating an order.');
  } finally {
    await browser.close();
  }
})().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
