# Live studio deployment

`https://studio.sorrydotcom.lk` deploys automatically when commits are pushed to
`main` in `webscepts123/sorrydotcomeditor`. See
`.github/workflows/deploy-cpanel.yml` and the repository's Actions tab.

The workflow runs the application tests, uploads the exact commit over SSH,
installs locked production dependencies, runs additive migrations, and starts
the new release on a loopback port. Traffic switches only after `/health`
passes. Failed startup leaves the previous release serving traffic. Database
migrations are not automatically reversed; keep future migrations backward
compatible. `workflow_dispatch` can deploy a new commit manually; an already
existing release is rejected to avoid overwriting running files.

Server layout:

- `/home/studiosorrydotco/apps/sorrydotcomeditor/releases/<commit>`: application releases.
- `current`: symlink to the active release.
- `shared/.env`, `shared/storage`, `shared/uploads`: persistent credentials and media.
- `shared/logs`: application logs; `shared/active-port`: the live loopback port.
- `/home/studiosorrydotco/public_html`: PHP reverse proxy and Apache rules only.
- `/home/studiosorrydotco/backups`: original Laravel files and database snapshot.

The server uses Node.js 26 under `~/.local/node`, with its private libatomic
runtime in `~/.local`. A cron job checks the application every minute and
restarts it after a process exit or server reboot. GitHub repository secrets
`CPANEL_KEY` and `CPANEL_KNOWN_HOSTS` hold the dedicated SSH key and pinned host
key. Neither the cPanel password nor the production environment is committed.

The PHP bridge and Apache rules are installed at initial cutover. Changes to
`deployment/live-index.php` or `deployment/live.htaccess` require a deliberate
server update; application changes deploy automatically.

## Generic framework deployment script

The older generic script below is retained for other installations. The live
studio uses `live-deploy.sh` instead.

    bash ~/public_html/deployment/deploy.sh

Run it in cPanel > Terminal, over SSH, from a cron job, or from GitHub Actions.
It fetches the branch, replaces the application files, installs dependencies
when `package.json` changed, and restarts the application.

## First run

    bash ~/public_html/deployment/deploy.sh init

That writes `deployment/deploy.config`, which holds this account's repository,
branch, and token. A deploy never replaces it. Set `REPOSITORY="owner/repository"`,
then deploy:

    bash ~/public_html/deployment/deploy.sh

## Commands

    deploy      Default. Fetch, sync, install, restart.
    init        Write deployment/deploy.config.
    status      What is configured, and what the last deploy did.
    install     Install dependencies only.
    restart     Restart the application only.
    rollback    Restore the snapshot taken before the last deploy.
    logs        Last 50 lines of tmp/deploy.log.
    cron        Print the cron line for scheduled deploys.

Options: `--repo=owner/name`, `--branch=name`, `--node=/path/node`,
`--mode=passenger`, `--install`, `--no-install`, `--no-restart`, `--force`,
`--quiet`, `--dry-run`.

## What is never replaced

`.env`, `.htaccess`, `tmp/`, `node_modules/`, `storage/`,
`public/uploads/`, the `noderyx-*.php` browser tools, and
`deployment/deploy.config`. Add more in `KEEP` inside `deploy.config`.

A snapshot of the application, without `node_modules/` and `tmp/`, is taken
before every deploy and kept under `~/.noderyx/backups`. The three newest are
kept; `rollback` restores the latest.

## Private repositories

Create a fine-grained personal access token with read access to the repository
and put it in `deployment/deploy.config` as `GITHUB_TOKEN`. Keep the file at
`chmod 600`; `init` does that for you.

## Deploy on every push

Add `.github/workflows/deploy-cpanel.yml` to the repository:

    npx noderyx cpanel:deploy-script --workflow --branch=main

It runs this same command over SSH using the `CPANEL_HOST`, `CPANEL_USER`,
and `CPANEL_KEY` repository secrets. On an account without SSH, use the
webhook that `noderyx-deploy.php` prints instead.

## Scheduled deploys

    */30 * * * * /bin/bash "$HOME/public_html/deployment/deploy.sh" --quiet >/dev/null 2>&1

A run with nothing new to fetch only touches files that changed.

## After a deploy

Put anything else the site needs in `deployment/after-deploy.sh`; the script
runs it from the application root, with `NODE_BIN` set. Migrations belong
there:

    "$NODE_BIN" node_modules/.bin/noderyx migrate

Commit that file. It comes from the repository like the rest of the
application, so a change to it reaches the server with the next deploy.

## From a browser

`noderyx-deploy.php` does the same without a terminal: **Pull latest**
downloads the branch and installs dependencies when `package.json` changed,
**Install dependencies** runs `npm ci` with the account's Node binary, and the
**Shell deploy** buttons run this script. A token saved under Source pulls a
private repository. Installing and the script buttons need PHP to be allowed
to start processes; the panel says whether it is.

## When it will not work

No `git`, `curl`, or `wget` on the account: use `noderyx-deploy.php` in a
browser. If git fails, the script falls back to the branch tarball by itself.
No `rsync`: files removed from the branch stay on disk, and the script says
so. No `npm`: upload `node_modules` once, and deploys keep it.

`$'\r': command not found` means the script was uploaded with Windows line
endings. This version repairs itself on the first run; an older one can be
fixed with `sed -i 's/\r$//' deployment/deploy.sh`.
