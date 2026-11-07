import { connect, loadEnvironment } from "noderyx-framework";
import { parseArgs } from "node:util";
import { importKaggleVideo } from "../app/Services/KaggleVideoImport.js";

const { values } = parseArgs({ options: { scene: { type: "string" }, file: { type: "string" }, help: { type: "boolean" } } });
if (values.help) {
  console.log('npm run kaggle:import -- --scene 1 --file "D:/Downloads/scene-1-wan21.mp4"');
} else {
  loadEnvironment();
  const sceneId = Number(values.scene);
  if (!Number.isSafeInteger(sceneId) || sceneId <= 0 || !values.file) {
    console.error('Provide --scene <positive scene ID> and --file "<downloaded MP4 path>".');
    process.exitCode = 1;
  } else {
    const { default: config } = await import("../noderyx.config.js");
    const database = await connect(config.database);
    try {
      const result = await importKaggleVideo(database, { sceneId, source: values.file });
      console.log(`Imported MP4 into scene ${sceneId}.`);
      console.log(`Open ${String(process.env.APP_URL || "http://localhost:3000").replace(/\/$/, "")}${result.editor_url}`);
    } catch (error) {
      console.error(`Import failed: ${error.message}`);
      process.exitCode = 1;
    } finally { await database.close(); }
  }
}
