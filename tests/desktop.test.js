import assert from "node:assert/strict";
import test from "node:test";
import { desktopOptions, mainProcess } from "noderyx-framework";
import config from "../noderyx.config.js";

test("desktop window uses the online studio with developer tools disabled", () => {
  const options = desktopOptions(config);
  assert.equal(options.appId, "com.sorrydotcom.editor");
  assert.equal(options.liveReloadUrl, "https://studio.sorrydotcom.lk");
  assert.equal(options.web.apiUrl, "https://studio.sorrydotcom.lk");
  assert.equal(options.devTools, false);

  const main = mainProcess(options);
  assert.match(main, /contextIsolation: true/);
  assert.match(main, /sandbox: true/);
  assert.match(main, /nodeIntegration: false/);
  assert.match(main, /window\.loadURL\(CONFIG\.liveReloadUrl/);
});
