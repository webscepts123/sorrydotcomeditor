import assert from 'node:assert/strict';
import test from 'node:test';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';

test('packaged desktop checks releases and downloads without interrupting editing', async () => {
  const source = readFileSync(new URL('../platforms/desktop/updater.cjs', import.meta.url), 'utf8');
  let checks = 0, interval, cleared = false;
  const updater = { on() {}, checkForUpdatesAndNotify: async () => { checks++; } };
  const app = { isPackaged: true, once(event, callback) { assert.equal(event, 'before-quit'); this.quit = callback; } };
  const context = { module: { exports: {} }, require: name => name === 'electron' ? { app } : { autoUpdater: updater }, console, setInterval(callback, ms) { interval = { callback, ms, unref() {} }; return interval; }, clearInterval() { cleared = true; } };
  vm.runInNewContext(source, context);
  context.module.exports.startUpdates();
  assert.equal(checks, 1);
  assert.equal(updater.autoDownload, true);
  assert.equal(updater.autoInstallOnAppQuit, true);
  assert.equal(interval.ms, 21600000);
  await interval.callback(); assert.equal(checks, 2);
  app.quit(); assert.equal(cleared, true);
  app.isPackaged = false;
  context.module.exports.startUpdates(); assert.equal(checks, 2);
});
