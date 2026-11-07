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

Run `npm.cmd run seed` to add three sample projects and related production records for checking the editor. Repeating the command preserves existing demo projects. Samples belong to the first existing user; if no account exists, complete `/setup` to claim them. Media records contain briefs only, without generated files.

The application entry point is `server.js`; runtime settings are in `noderyx.config.js`, controllers are in `app/Controllers/`, repositories are in `app/Models/`, migrations are in `database/migrations/`, and views are in `resources/views/`.

The original VOID SHADOW production editor (`/projects/:id/editor`) and full-screen NLE studio (`/projects/:id/videoeditor`) use the escaped HTML renderers in `app/Views/` and local styles, scripts, fonts, and Bootstrap assets in `public/studio/`. Both `scene_id` and the original `active_scene` query parameter select a scene. Scene prompts save to the database; visual adjustments are monitor previews and are not baked into downloads. Rendering requires the configured generation provider.

An additional local Wan 2.1 provider is available in the AI Engine inspector and production editor. See [local Wan setup](deployment/LOCAL-WAN.md) for the D: installation, hardware limitations, startup, and result import.

For a cloud GPU workflow, import [the Wan Kaggle notebook](notebooks/wan21-kaggle.ipynb) into your signed-in Kaggle account. The editor's AI Engine panel provides notebook and scene-JSON downloads. After generating and downloading an MP4, use `npm.cmd run kaggle:import -- --scene 1 --file "path/to/video.mp4"` to attach it to a scene. See [Kaggle setup](deployment/KAGGLE-WAN.md) for the steps and session requirements.
