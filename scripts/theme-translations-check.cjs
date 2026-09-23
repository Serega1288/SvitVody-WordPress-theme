const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  const output = path.resolve(__dirname, '../audit-screenshots/translations');
  fs.mkdirSync(output, { recursive: true });
  try {
    for (const setup of [
      { slug: 'uk', url: 'http://localhost:8080/', title: 'Оформлення замовлення', name: "Ваше ім'я*" },
      { slug: 'ru', url: 'http://localhost:8080/ru/', title: 'Оформление заказа', name: 'Ваше имя*' },
    ]) {
      const page = await browser.newPage({ viewport: { width: 390, height: 900 } });
      const errors = [];
      page.on('pageerror', error => errors.push(error.message));
      await page.goto(setup.url, { waitUntil: 'networkidle' });
      assert.equal(await page.evaluate(() => window.svitvodyI18n.checkoutTitle), setup.title);
      assert.equal((await page.locator('.order-modal-form-section h2').innerText()).trim(), setup.title);
      assert.equal((await page.locator('.order-modal-form [name="name"]').locator('xpath=..').locator('span').innerText()).trim(), setup.name);
      assert.equal(await page.locator('.order-modal-form [name="modal-time"]').count(), 0);
      const deliveryDate = page.locator('.order-modal-form [name="date"]');
      assert.equal(await deliveryDate.getAttribute('type'), 'text');
      assert.equal(await deliveryDate.getAttribute('placeholder'), 'дд.мм');
      await page.locator('[data-modal-open]').first().click();
      await deliveryDate.fill('0609');
      assert.equal(await deliveryDate.inputValue(), '06.09');
      await page.locator('.order-modal-dialog').screenshot({ path: path.join(output, `checkout-${setup.slug}.png`) });
      assert.deepEqual(errors, []);
      await page.close();
    }
    console.log('Ukrainian and Russian WordPress translations render in the checkout UI.');
  } finally {
    await browser.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
