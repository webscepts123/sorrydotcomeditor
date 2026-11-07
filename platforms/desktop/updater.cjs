"use strict";
const { app } = require('electron');
const { autoUpdater } = require('electron-updater');
function startUpdates() {
  if (!app.isPackaged) return;
  autoUpdater.autoDownload = true;
  autoUpdater.autoInstallOnAppQuit = true;
  autoUpdater.on('error', error => console.error('Studio update failed:', error.message));
  const check = () => autoUpdater.checkForUpdatesAndNotify().catch(error => console.error('Update check failed:', error.message));
  check();
  const timer = setInterval(check, 6 * 60 * 60 * 1000);
  timer.unref();
  app.once('before-quit', () => clearInterval(timer));
}
module.exports = { startUpdates };
