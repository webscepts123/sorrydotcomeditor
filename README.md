# sorrydotcomeditor

The production editor runs on Noderyx Framework 0.9. The Node.js server uses `.noderframe` views, MySQL, signed sessions, and CSRF-protected forms. The original video editor Blade view is retained as a conversion reference; it is not served by Noderyx.

## Requirements

- Node.js 26 or newer
- npm
- MySQL or MariaDB

## Run locally

```powershell
Copy-Item .env.example .env
npm.cmd install
npm.cmd run db:create
npm.cmd run migrate
npm.cmd run dev
```

Configure database credentials in `.env` before migrating. On a new database, create the first administrator from the one-time setup page. Existing Laravel bcrypt password hashes remain usable. Set a strong, private `APP_KEY` before deploying. Never commit `.env` or provider credentials.

AI story and character tools use the optional Noderyx AI configuration. Image generation requires `OPENAI_API_KEY`. Scene video generation can use `SEEDANCE_API_KEY` and `SEEDANCE_BASE_URL`; ComfyUI rendering needs `COMFYUI_URL` and a ComfyUI API-format workflow at `COMFYUI_VIDEO_WORKFLOW`. Configure provider services before using their respective actions.

The dashboard checks the configured ComfyUI worker every five seconds and shows available GPU, memory, and queue metrics. Storage usage refers to the filesystem hosting the application, not the remote worker. The timeline percentage is an estimate based on 15 minutes per scene and a 150-minute target.

Noderyx screens cover projects, scenes and timelines, cast identities, the video-editor preview, production planning, posters, settings, and production tools. Video-editor adjustments currently affect only the browser preview; they do not modify rendered video files. Audio-score generation and multipart file uploads are not yet connected.

Open Video editor from the sidebar (the most recently updated project), a project card, or the dashboard. Select a scene in the media list or timeline; rendered clips can be played and adjusted in the browser. The Edit, Color, Fairlight, and Deliver panels currently provide preview transforms, color controls, embedded-audio volume, and original clip/XML downloads. The original editor's transitions, audio mixing, and other advanced features are not yet ported. Frame stepping assumes 30 frames per second. Adjustments are not saved and are not included in the scene XML export.

## Useful commands

```powershell
npm.cmd start
npm.cmd run qa
npm.cmd run build
npm.cmd test
```

## Desktop app

Void Shadow Studio uses Electron to display the live studio. Website deployments appear when the app is reopened or reloaded; the existing main-branch deployment workflow publishes website changes.

Install desktop dependencies with `npm.cmd ci --prefix platforms/desktop`, launch with `npm.cmd run desktop:run`, and build the Windows installer with `npm.cmd run desktop:windows`. Desktop source is maintained in `platforms/desktop`; do not run the framework desktop generator over these files. The app uses a custom Void Shadow logo, isolated renderer, and disabled developer tools.

Packaged apps check GitHub Releases at startup and every six hours, download newer installers, and install on normal exit. Install the new updater-enabled build once on existing machines. Releases must be publicly readable. To publish a Windows release, increment the version in `platforms/desktop/package.json` and its lockfile, then push a matching `vX.Y.Z` tag. The desktop release workflow builds the installer, blockmap, and update metadata, then publishes the GitHub release. Optional `WINDOWS_CSC_LINK` and `WINDOWS_CSC_KEY_PASSWORD` repository secrets enable certificate signing. macOS packaging requires macOS and signing configuration and is not published by this Windows workflow.

Run `npm.cmd run seed` to add three sample projects and related production records for checking the editor. Repeating the command preserves existing demo projects. Samples belong to the first existing user; if no account exists, complete `/setup` to claim them. Media records contain briefs only, without generated files.

The application entry point is `server.js`; runtime settings are in `noderyx.config.js`, controllers are in `app/Controllers/`, repositories are in `app/Models/`, migrations are in `database/migrations/`, and views are in `resources/views/`.

The original VOID SHADOW production editor (`/projects/:id/editor`) and full-screen NLE studio (`/projects/:id/videoeditor`) use the escaped HTML renderers in `app/Views/` and local styles, scripts, fonts, and Bootstrap assets in `public/studio/`. Both `scene_id` and the original `active_scene` query parameter select a scene. Scene prompts save to the database; visual adjustments are monitor previews and are not baked into downloads. Rendering requires the configured generation provider.

An additional local Wan 2.1 provider is available in the AI Engine inspector and production editor. See [local Wan setup](deployment/LOCAL-WAN.md) for the D: installation, hardware limitations, startup, and result import.

For a cloud GPU workflow, import [the Wan Kaggle notebook](notebooks/wan21-kaggle.ipynb) into your signed-in Kaggle account. The editor's AI Engine panel provides notebook and scene-JSON downloads. After generating and downloading an MP4, use `npm.cmd run kaggle:import -- --scene 1 --file "path/to/video.mp4"` to attach it to a scene. See [Kaggle setup](deployment/KAGGLE-WAN.md) for the steps and session requirements.

The AI Engine also supports direct Kaggle API generation: connect a Kaggle username and API token (or legacy key), enter a scene prompt, and select **Generate this scene**. The server submits a private GPU notebook using the official Kaggle CLI library, polls jobs every minute, and downloads and attaches the completed 1080p MP4 to its original scene. This continues while the browser is closed and resumes after server restarts. Account verification and GPU quota still apply. Credentials are encrypted per studio user with `APP_KEY`, stored under `storage/kaggle-api`, and never included in notebook files. Keep `APP_KEY` stable; changing it requires reconnecting accounts. Disconnecting pauses automatic imports until the same Kaggle account is reconnected.

For local API setup install Python 3.11+ and run `python -m pip install -r deployment/kaggle-requirements.txt`. Set `KAGGLE_PYTHON` if that interpreter is not the system default. Live deployments run `deployment/setup-kaggle.sh` to install the CLI into a persistent virtual environment. Live inference requires an account connection and has not been verified without the user's credential.
