const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  try {
    const page = await browser.newPage({ viewport: { width: 1440, height: 1100 } });
    await page.goto('http://localhost:8080/wp-login.php');
    await page.locator('#user_login').fill(process.env.WP_USER);
    await page.locator('#user_pass').fill(process.env.WP_PASSWORD);
    await Promise.all([page.waitForURL('**/wp-admin/**'), page.locator('#wp-submit').click()]);
    const out = path.resolve(__dirname, '../audit-screenshots/notifications');
    fs.mkdirSync(out, { recursive: true });
    for (const language of ['uk', 'ru']) {
      await page.goto(`http://localhost:8080/wp-admin/admin.php?page=theme-settings&lang=${language}`, { waitUntil: 'networkidle' });
      await page.getByRole('link', { name: 'Сповіщення заявок', exact: true }).click();
      for (const name of ['email_enabled', 'email', 'telegram_enabled', 'token', 'chat', 'topic', 'clear_token']) {
        assert.equal(await page.locator(`[data-name="sv_notify_${name}"]`).isVisible(), true, name);
      }
      assert.equal(await page.locator('[data-name="sv_notify_token"] input[type="password"]').inputValue(), '');
      assert.equal(await page.locator('[data-name^="sv_notify_smtp_"]').count(), 0);
      await page.screenshot({ path: path.join(out, `options-${language}.png`) });
    }
    for (const url of ['http://localhost:8080/', 'http://localhost:8080/ru/']) {
      await page.goto(url, { waitUntil: 'networkidle' });
      assert.ok(!(await page.content()).includes('sv_notify_token'));
    }
    console.log('Notification tab and fields visible in both languages; token input empty and no notification credentials exposed on frontend.');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
